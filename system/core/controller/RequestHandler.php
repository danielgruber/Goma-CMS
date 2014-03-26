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
 * @version     2.2.7
 */
class RequestHandler extends Object {
	/**
	 * current depth of request-handlers
	 */
	private $requestHandlerKey;

	/**
	 * url-handlers
	 *@name url_handlers
	 *@access public
	 */
	public $url_handlers = array('$Action' => '$Action');

	/**
	 * defines whether shift on success or not
	 *
	 *@name shiftOnSuccess
	 *@access protected
	 */
	protected $shiftOnSuccess = true;

	/**
	 * requests, key is name of the request and value the function for it
	 *
	 *@name allowed_actions
	 *@access public
	 *@var array
	 */
	public $allowed_actions = array();

	/**
	 * the url base-path of this controller
	 *
	 *@name namespace
	 *@access public
	 */
	public $namespace;

	/**
	 * original namespace, so always from first controller
	 *
	 *@name originalNamespace
	 */
	public $originalNamespace;

	/**
	 * the current request
	 *
	 *@name request
	 */
	public $request;

	/**
	 * sets vars
	 *
	 *@name __construct
	 *@access public
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
			if (!isset($this -> namespace)) {
				$this -> namespace = $this -> request -> shiftedPart;
				$this -> originalNamespace = $this -> namespace;
			} else {
				$this -> originalNamespace = $this -> namespace;
				$this -> namespace = $this -> request -> shiftedPart;
			}
		} else {
			throwError(6, "No-Request-Error", "Object of type " . $this -> classname . " has no request");
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
			throwError(6, 'PHP-Error', 'Class ' . get_class($this) . ' has no class_name. Please make sure you ran <code>parent::__construct();</code> ');
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

			if (classinfo::isAbstract($class)) {
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

					$action = str_replace('-', '_', strtolower($action));

					if (!$this -> hasAction($action)) {
						$action = "index";
					}

					$this -> request -> params["action"] = $action;

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
	 *@name serve
	 *@access public
	 *@param string - content
	 */
	public function serve($content) {
		return $content;
	}

	/**
	 * checks if this class has a given action
	 *@name hasAction
	 *@access public
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
	 * handles the action
	 *@name handleAction
	 *@access public
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
		while ($class -> classname != "object") {
			if (in_array($action, $class -> allowed_actions)) {
				if (PROFILE)
					Profiler::unmark("RequestHandler::checkPermission");
				return true;
			} else if (isset($class -> allowed_actions[$action])) {
				$data = $class -> allowed_actions[$action];
				if (is_bool($data)) {
					if (PROFILE)
						Profiler::unmark("RequestHandler::checkPermission");
					return $data;
				} else if (substr($data, 0, 2) == "->") {
					$func = substr($data, 2);
					if (Object::method_exists($this, $func)) {
						if (PROFILE)
							Profiler::unmark("RequestHandler::checkPermission");
						return $this -> $func();
					} else {
						if (PROFILE)
							Profiler::unmark("RequestHandler::checkPermission");
						return false;
					}
				} else if ($data == "admins") {
					return (member::$groupType == 2);
				} else if ($data == "users") {
					return (member::$groupType == 1);
				} else {
					if (PROFILE)
						Profiler::unmark("RequestHandler::checkPermission");
					return Permission::check($data);
				}
			}

			if (get_parent_class($class) == "Object") {
				if (PROFILE)
					Profiler::unmark("RequestHandler::checkPermission");
				return false;
			}

			if (!classinfo::isAbstract(get_parent_class($class)))
				$class = Object::instance(get_parent_class($class));
			else
				break;
		}
		if (PROFILE)
			Profiler::unmark("RequestHandler::checkPermission");
		return false;
	}

	/**
	 * default Action
	 *@name index
	 *@access public
	 */
	public function index() {
		return "";
	}

	/**
	 * some developers don't want to use $this->request->getParam, because it's too long, so we have a simpler way
	 * gets a param from the request
	 *@name getParam
	 *@access public
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
