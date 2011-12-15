<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 26.10.2011
  * $Version: 2.0.0 - 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class RequestHandler extends Object
{
		/**
		 * current depth of request-handlers
		*/
		private $requestHandlerKey;
		/**
		 * url-handlers
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array(
			'$Action'	=> '$Action'
		);
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
		 * sets vars
		 *
		 *@name __construct
		 *@access public
		*/
		public function __construct() {
			parent::__construct();
			
			/* --- */
			if(isset(ClassInfo::$class_info[$this->class]["allowed_actions"]))
				$this->allowed_actions = classinfo::$class_info[$this->class]["allowed_actions"];
				
			if(isset(ClassInfo::$class_info[$this->class]["url_params"]))
				$this->allowed_actions = classinfo::$class_info[$this->class]["url_params"];
		}
		/**
		 * handles requests
		 *@name handleRequest
		*/
		public function handleRequest($request)
		{
				
				
				if($this->class == "")
				{
						throwError(6, 'PHP-Error', 'Class '.get_class($this).' has no class_name. Please make sure you ran <code>parent::__construct();</code> ');
				}
				$this->request = $request;
				$this->namespace = $request->shiftedPart;
				
				
				$this->Init();			
				
				$class = $this->class;
				
				while($class != "object")
				{			
						if(empty($class))
						{
								break;
						}
						
						if(classinfo::isAbstract($class))
						{
								$class = get_parent_class($class);
								continue;
						}
						
						
						foreach(Object::instance($class)->url_handlers as $pattern => $action)
						{
								if($argument = $request->match($pattern, $this->shiftOnSuccess, $this->class))
								{
										$this->request = $request;
										
										if($request->getParam("Action", false))
										{
												$action = $request->getParam("Action", false);
										}
										
										if($action{0} == "$" && $action != '$Action')
										{
												$action = substr($action, 1);
										}
										
										$action = strtolower($action);
										
										if(!$this->hasAction($action))
										{
												$action = "index";
										}
										
										$data = $this->handleAction($action);
										array_pop(Core::$controller);
										return $data;
								}
						}
						
						$class = get_parent_class($class);
				}
				return $this->handleAction("index");
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
		public function hasaction($action)
		{
				if((!Object::method_exists($this, $action) && !method_exists($this, $action)) || !$this->checkPermission($action))
				{
						return false;
				} else
				{
						return true;
				}
		}
		/**
		 * handles the action
		 *@name handleAction
		 *@access public
		*/
		public function handleAction($action)
		{
				return call_user_func_array(array($this, $action), array());
		}
		/**
		 * checks the permissions
		 *@name checkPermission
		 *@access protected
		 *@param string - permission
		*/
		protected function checkPermission($action)
		{
				if(PROFILE) Profiler::mark("RequestHandler::checkPermission");
				$class = $this;
				while($class->class != "object") {
					if(in_array($action, $class->allowed_actions))
					{
							if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
							return true;
					} else if(isset($class->allowed_actions[$action]))
					{
							$data = $class->allowed_actions[$action];
							if(is_bool($data))
							{
									if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
									return $data;
							} else if(substr($data, 0, 2) == "->")
							{
									$func = substr($data, 2);
									if(Object::method_exists($this, $func))
									{
											if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
											return $this->$func();
									} else
									{
											if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
											return false;
									}
							} else
							{
									if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
									return right($data);
							}
					}	
					if(get_parent_class($class) == "Object") {
						if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
						return false;
					}
					if(!classinfo::isAbstract(get_parent_class($class)))
						$class = Object::instance(get_parent_class($class));
					else
						break;
				}
				if(PROFILE) Profiler::unmark("RequestHandler::checkPermission");
				return false;
		}
		/**
		 * init-function
		 *@name init
		 *@access public
		*/
		public function init()
		{
			Core::$requestController = $this;
			$this->requestHandlerKey = count(Core::$controller);
			Core::$controller[] = $this;
		}
		/**
		 * default Action
		 *@name index
		 *@access public
		*/
		public function index()
		{
				return "";
		}
		/**
		 * some developers don't want to use $this->request->getParam, because it's too long, so we have a simpler way
		 * gets a param from the request
		 *@name getParam
		 *@access public
		*/
		public function getParam($param, $useall = true)
		{
				if(isset($this->request)) {
					return $this->request->getParam($param, $useall);
				}
				
				if(strtolower($useall) == "get") {
					return isset($_GET[$param]) ? $_GET[$param] : null;
				}
				
				if(strtolower($useall) == "post") {
					return isset($_POST[$param]) ? $_POST[$param] : null;
				}
				
				if(isset($_GET[$param]))
				{
						return $_GET[$param];
				}  else if(isset($_POST[$param]))
				{
						return $_POST[$param];
				} else
				{
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
		public function __throwError($errcode, $errname, $errdetails) {
			if(Core::is_ajax()) {
				HTTPresponse::sendHeader();
				echo "<h1>".text::protect($errcode).": ".text::protect($errname)."</h1>\n";
				echo $errdetails;
				exit;
			} else if(Core::is_ajax() && isset($_GET["ajaxfy"])) {
				
			} else  {
				$template = new template;
				$template->assign('errcode',text::protect($errcode));
				$template->assign('errname',text::protect($errname));
				$template->assign('errdetails',$errdetails);
				$template->assign("debug", print_r(debug_backtrace(), true));
				HTTPresponse::sendHeader();
				
				echo $template->display('framework/error.html');
				
				exit;
			}
		}
		/**
		 * gets parent controller of this
		 *
		 *@name parentController
		 *@access public
		*/
		public function parentController() {
			return Core::$Controller[$this->requestHandlerKey - 1];
		}
}