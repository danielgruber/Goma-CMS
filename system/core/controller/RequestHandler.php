<?php
/**
 * @package		Goma\System\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined("IN_GOMA") OR die();

/**
 * This class is the basic class for each controller of Goma. It provides basic methods to handle requests and parsing URLs automatically and calling the correct Action.
 *
 * @package     Goma\System\Core
 * @version     2.3.1
 */
class RequestHandler extends Object {

	/**
	 * defines if this is a sub-controller.
	 *
	 * @access public
	*/
	public $subController;

	/**
	 * current depth of request-handlers
	 */
	private $requestHandlerKey;

	/**
	 * url-handlers
	 * @name 	url_handlers
	 * @access 	public
	 */
	public $url_handlers = array('$Action' => '$Action');

	/**
	 * defines whether shift on success or not
	 *
	 * @name 	shiftOnSuccess
	 * @access 	protected
	 */
	protected $shiftOnSuccess = true;

	/**
	 * requests, key is name of the request and value the function for it
	 *
	 * @name 	allowed_actions
	 * @access 	public
	 * @var 	array
	 */
	public $allowed_actions = array();

	/**
	 * the url base-path of this controller
	 *
	 * @name 	namespace
	 * @access 	public
	 */
	public $namespace;

	/**
	 * original namespace, so always from first controller
	 *
	 * @name 	originalNamespace
	 */
	public $originalNamespace;

	/**
	 * the current request
	 *
	 * @name 	request
	 */
	public $request;

	/**
	 * sets vars
	 *
	 * @name 	__construct
	 * @access 	public
	 */
	public function __construct() {
		parent::__construct();

		/* --- */

		if (PROFILE)
			Profiler::mark("RequestHandler::__construct");

		$this->allowed_actions = ArrayLib::map_key("strtolower", array_map("strtolower", $this -> allowed_actions));
		$this->url_handlers = array_map("strtolower", $this->url_handlers);

		if (isset(ClassInfo::$class_info[$this -> classname]["allowed_actions"]))
			$this->allowed_actions = array_merge(ClassInfo::$class_info[$this->classname]["allowed_actions"], $this->allowed_actions);

		if (isset(ClassInfo::$class_info[$this -> classname]["url_handlers"]))
			$this->url_handlers = array_merge(ClassInfo::$class_info[$this->classname]["url_handlers"], $this->url_handlers);

		if (PROFILE)
			Profiler::unmark("RequestHandler::__construct");
	}

	/**
	 * Inits the RequestHandler with a request-object.
	 *
	 * It generates the current URL-namespace ($this->namespace) and registers the Controller as an activeController in Core as Core::$activeController
	 *
	 * @param   Request $request The Request Object
	 */
	public function Init($request = null) {
		if (isset($request))
			$this -> request = $request;

		if (isset($this -> request)) {
			if (!isset($this->namespace)) {
				$this->namespace = $this->request->shiftedPart;
				$this->originalNamespace = $this->namespace;
			} else {
				$this->originalNamespace = $this->namespace;
				$this->namespace = $this -> request->shiftedPart;
			}
		} else {
			throw new InvalidArgumentException("RequestHandler" . $this -> classname . " has no request-instance.");
		}

		if (!isset($this -> subController) || !$this -> subController) {
			Core::$requestController = $this;
			Core::$controller[] = $this;
		}
		$this -> requestHandlerKey = count(Core::$controller);
	}

	/**
	 * handles requests
	 *@name handleRequest
	 */
	public function handleRequest($request, $subController = false) {

		if ($this -> classname == "") {
			throw new LogicException('Class ' . get_class($this) . ' has no class_name. Please make sure you call <code>parent::__construct();</code> ');
		}

		$this -> subController = $subController;
		$this -> Init($request);
		$class = $this -> classname;

		$content = null;

        $this -> callExtending("onBeforeHandleRequest", $request, $subController, $content);

        if($content !== null) {
            return $content;
        }

		while ($class != "object") {
			if (empty($class)) {
				break;
			}

			if (ClassInfo::isAbstract($class)) {
				$class = get_parent_class($class);
				continue;
			}

			foreach (Object::instance($class)->url_handlers as $pattern => $action) {

				if ($argument = $request -> match($pattern, $this -> shiftOnSuccess, $this -> classname)) {
					$this -> request = $request;

					if ($action{0} == "$") {
						$action = substr($action, 1);
						if ($this -> getParam($action, false)) {
							$action = $this -> getParam($action, false);
						}
					}

					$action = str_replace('-', '_', $action);

					if (!$this -> hasAction($action)) {
						$action = "index";
					}

					$data = $this -> handleAction($action);
					array_pop(Core::$controller);
					return $data;
				}
			}

			$class = get_parent_class($class);
		}
		return $this -> handleAction("index");
	}

	/**
	 * in the end this function is called to do last modifications
	 *
	 * @param   string content
     * @return  string
	 */
	public function serve($content) {
		return $content;
	}

    /**
     * checks if this class has a given action.
     * it also checks for permissions.
     *
     * @param   string $action
     * @return  bool
     */
	public function hasAction($action) {
		$hasAction = true;
		if (!Object::method_exists($this, $action) || !$this -> checkPermission($action)) {
			$hasAction = false;
		}

		$this -> extendHasAction($action, $hasAction);
		$this -> callExtending("extendHasAction", $action, $hasAction);

		return $hasAction;
	}

    /**
     * handles the action.
     *
     * @name    handleAction
     * @access  public
     * @return  mixed|null|false
     */
	public function handleAction($action) {
		$handleWithMethod = true;
		$content = null;

		$this -> onBeforeHandleAction($action, $content, $handleWithMethod);
		$this -> callExtending("onBeforeHandleAction", $action, $content, $handleWithMethod);

		if ($handleWithMethod && Object::method_exists($this, $action))
			$content = call_user_func_array(array($this, $action), array());

		$this -> extendHandleAction($action, $content);
		$this -> callExtending("extendHandleAction", $action, $content);

		return $content;
	}

	/**
	 * on before handle action
	 *
	 *@name onBeforeHandleAction
	 *@param string - action
	 *@param string - content
	 *@param bool - handleWithMethod
	 */
	public function onBeforeHandleAction($action, $content, &$handleWithMethod) {

	}

	/**
	 *@name extendHandleAction
	 *@access public
	 *@param string - action
	 *@param string - content
	 */
	public function extendHandleAction($action, &$content) {

	}

	/**
	 * extends hasAction
	 *
	 *@name extendHasAction
	 *@access public
	 */
	public function extendHasAction($action, &$hasAction) {

	}

	/**
	 * checks the permissions
	 *@name checkPermission
	 *@access protected
	 *@param string - permission
	 */
	protected function checkPermission($action) {
		if (PROFILE)
			Profiler::mark("RequestHandler::checkPermission");

		$class = $this;
		$actionLower = strtolower($action);

		while (isset($class) && $class->classname != "object") {
			
			if(Object::method_exists($class, "checkPermissionsOnClass")) {

				// check class
				$r = $class->checkPermissionsOnClass($action);

				// if we have an result which is a boolean.
				if (is_bool($r)) {
					if (PROFILE)
						Profiler::unmark("RequestHandler::checkPermission");

					return $r;
				}

				// check for parent class
				if (!ClassInfo::isAbstract(get_parent_class($class))) {
					$class = Object::instance(get_parent_class($class));
				} else {
					$class = null;
				}
			} else {
				$class = null;
			}
		}

		if (PROFILE)
			Profiler::unmark("RequestHandler::checkPermission");
		return false;
	}

	/**
	 * checks permissions on this class.
	 *
	 * @return 	null when no definition was found or a boolean when definition was found.
	*/
	protected function checkPermissionsOnClass($action) {
		$actionLower = strtolower($action);

		if (in_array($actionLower, $this->allowed_actions)) {
				return true;
		} else if (isset($this->allowed_actions[$actionLower])) {
			$data = $this->allowed_actions[$actionLower];

			// advanced options for Action.
			if (is_bool($data)) {
				return $data;
			} else if (substr($data, 0, 2) == "->") {
				$func = substr($data, 2);
				if (Object::method_exists($this, $func)) {
					return $this->$func();
				} else {
					return false;
				}
			} else if ($data == "admins") {
				return (member::$groupType == 2);
			} else if ($data == "users") {
				return (member::$groupType == 1);
			} else {
				return Permission::check($data);
			}
		}

		return null;
	}

	/**
	 * default Action
	 *
	 * @name 	index
	 * @access 	public
	 */
	public function index() {
		return "";
	}

	/**
	 * simple way for $this->request->getParam which also supports get and post.
	 *
	 * @name 	getParam
	 * @access 	public
	 */
	public function getParam($param, $useall = true) {
		if (isset($this -> request) && is_a($this -> request, "request")) {
			return $this -> request -> getParam($param, $useall);
		}

		if (strtolower($useall) == "get") {
			return isset($_GET[$param]) ? $_GET[$param] : null;
		}

		if (strtolower($useall) == "post") {
			return isset($_POST[$param]) ? $_POST[$param] : null;
		}
		
		if($useall === false) {
			return null;
		}
		
		if (isset($_GET[$param])) {
			return $_GET[$param];
		} else if (isset($_POST[$param])) {
			return $_POST[$param];
		} else {
			return null;
		}
	}

	/**
	 * magic functions of goma
	 */

	/**
	 * throws an error
	 *
	 *@name __throwError
	 *@access public
	 */
	public function __throwError($errcode, $errname, $errdetails, $debug = true) {

		if (Core::is_ajax())
			HTTPResponse::setResHeader(200);

		if (class_exists("ClassInfo", false)) {
			$template = new template;
			$template -> assign('errcode', convert::raw2text($errcode));
			$template -> assign('errname', convert::raw2text($errname));
			$template -> assign('errdetails', $errdetails);
			$template -> assign("throwdebug", $debug);
			HTTPresponse::sendHeader();

			echo $template -> display('framework/error.html');
		} else {
			header("X-Powered-By: Goma Error-Management under Goma Framework " . GOMA_VERSION . "-" . BUILD_VERSION);
			echo "Code: " . $errcode . "<br /> Name: " . $errname . "<br /> Details: " . $errdetails;
		}

		exit ;

	}

	/**
	 * gets parent controller of this
	 *
	 *@name parentController
	 *@access public
	 */
	public function parentController() {
		return Core::$Controller[$this -> requestHandlerKey - 1];
	}
}


class RequestException extends Exception {
	/**
	 * constructor.
	*/
	public function __construct($m = "", $code = 8, Exception $previous) {
		parent::__construct($m, $code, $previous);
	}
}
