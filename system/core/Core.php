<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

ClassInfo::AddSaveVar("Core", "rules");
ClassInfo::AddSaveVar("Core", "hooks");
ClassInfo::AddSaveVar("Core", "cmsVarCallbacks");

/**
 * Goma Core.
 *
 * @package		Goma\Core
 * @version		3.3.36
 */
class Core extends object {
	/**
	 *@var array
	 */
	public static $breadcrumbs = array();

	/**
	 * title of the page
	 *
	 */
	public static $title = "";

	/**
	 * headers
	 *
	 */
	public static $header = array();

	/**
	 * current languages
	 */
	public static $lang;

	/**
	 * An array that contains all CMS Variables.
	 *
	 * This arrays contains system wide system variables. They can be accessed by
	 * using the mehtods Core::setCMSVar and Core::getCMSVar.
	 *
	 * @see Core::setCMSVar() to set variables.
	 * @see Core::getCMSVar() to get variables.
	 */
	private static $cms_vars = array();

	/**
	 * Controllers used in this Request
	 *@name Controllers
	 */
	public static $controller = array();

	/**
	 * this var contains the site_mode
	 */
	public static $site_mode = STATUS_ACTIVE;

	/**
	 * addon urls by modules or others
	 *@name urls
	 *@var array
	 */
	public static $rules = array();

	/**
	 * the current active controller
	 *
	 *@var object
	 */
	public static $requestController;

	/**
	 * global hooks
	 *
	 */
	public static $hooks = array();

	/**
	 * current active url
	 *
	 */
	public static $url;

	/**
	 * if mobile is activated
	 *
	 */
	public static $isMobile = true;

	/**
	 * file which contains data from php://input
	 *
	 *@name phpInputFile
	 *@accesss public
	 */
	public static $phpInputFile;

	/**
	 * callbacks for $_cms_blah
	 *
	 *@name cmsVarCallbacks
	 */
	private static $cmsVarCallbacks = array();

	/**
	 * contains the path to the favicon.
	 *
	 * @access public
	 * @var string
	 */
	public static $favicon;
	
	/**
	 * cache-managers.
	*/
	public static $cacheManagerFramework;
	public static $cacheManagerApplication;

	/**
	 * inits the core
	 *
	 */
	public static function Init() {

		ob_start();

		if(isset($_SERVER['HTTP_X_IS_BACKEND']) && $_SERVER['HTTP_X_IS_BACKEND'] == 1) {
			Resources::addData("goma.ENV.is_backend = true;");
			define("IS_BACKEND", true);
		}
		

		// now init session
		if(PROFILE)
			Profiler::mark("session");
		session_start();
		if(PROFILE)
			Profiler::unmark("session");
			
			
		// init language-support
		require_once (FRAMEWORK_ROOT . "core/i18n.php");
		ClassManifest::$loaded["i18n"] = true;
		i18n::Init();	
		
		if(defined("SQL_LOADUP"))
			member::Init();

		if(PROFILE)
			Profiler::mark("Core::Init");
		
		Object::instance("Core")->callExtending("construct");
		self::callHook("init");

		if(PROFILE)
			Profiler::unmark("Core::Init");
	}

	/**
	 * inits cache-managers.
	*/
	public static function initCache($shouldFlush = false) {
		self::$cacheManagerApplication = new CacheManager(ROOT . APPLICATION . "/temp");
		self::$cacheManagerFramework = new CacheManager(ROOT . "system/temp");

		// check for flush from dev
		if($shouldFlush) {
			if(Permission::check("ADMIN")) {
				self::deleteCache(true);
			} else {
				self::deleteCache(false);
			}
		}
	}
	
	/**
	 * delete-cache.
	*/
	public static function deleteCache($force = false) {

		if(PROFILE) Profiler::mark("delete_cache");

		if($force) {
			logging('Deleting FULL Cache');
			
			self::$cacheManagerApplication->deleteCache(0, true);
			self::$cacheManagerFramework->deleteCache(600, true);

			FileSystem::Delete(ROOT . APPLICATION . "/uploads/d05257d352046561b5bfa2650322d82d");
		} else if(self::$cacheManagerApplication->shouldDeleteCache()) {
			logging("Deleting Cache");

			self::$cacheManagerApplication->deleteCache();
		}

		if(PROFILE) Profiler::unmark("delete_cache");
	}

	/**
	 * inits framework-resources.
	*/
	public static function InitResources() {
		// some vars for javascript
		Resources::addData("if(typeof current_project == 'undefined'){ var current_project = '" . CURRENT_PROJECT . "';var root_path = '" . ROOT_PATH . "';var ROOT_PATH = '" . ROOT_PATH . "';var BASE_SCRIPT = '" . BASE_SCRIPT . "'; goma.ENV.framework_version = '" . GOMA_VERSION . "-" . BUILD_VERSION . "'; var activelang = '".Core::$lang."'; }");


		Resources::add("system/libs/thirdparty/modernizr/modernizr.js", "js", "main");
		Resources::add("system/libs/thirdparty/jquery/jquery.js", "js", "main");
		Resources::add("system/libs/thirdparty/jquery/jquery.ui.js", "js", "main");
		//Resources::add("system/libs/thirdparty/jquery-throttle-debounce/debounce-throttle.js", "js", "main");
		Resources::add("system/libs/thirdparty/hammer.js/hammer.js", "js", "main");
		Resources::add("system/libs/thirdparty/respond/respond.min.js", "js", "main");
		Resources::add("system/libs/thirdparty/jResize/jResize.js", "js", "main");
		Resources::add("system/libs/javascript/loader.js", "js", "main");
		Resources::add("box.css", "css", "main");

		Resources::add("default.css", "css", "main");
		Resources::add("goma_default.css", "css", "main");

		HTTPResponse::setHeader("x-base-uri", BASE_URI);
		HTTPResponse::setHeader("x-root-path", ROOT_PATH);
        
		if(isset($_GET["debug"])) {
			Resources::enableDebug();
		}
	}

	/**
	 * returns the data of php://input as a file.
	*/
	public static function phpInputFile() {
		if(isset(self::$phpInputFile)) {
			return self::$phpInputFile;
		}
		
		
		if(isset($_POST) && $handle = @fopen("php://input", "rb")) {
			if(PROFILE)
				Profiler::mark("php://input read");
				
			$random = randomString(20);
			if(!file_exists(FRAMEWORK_ROOT . "temp/")) {
				FileSystem::requireDir(FRAMEWORK_ROOT . "temp/");
			}
			$filename = FRAMEWORK_ROOT . "temp/php_input_" . $random;
			
			$file = fopen($filename, 'wb');
			stream_copy_to_stream($handle, $file);
			fclose($handle);
			fclose($file);
			self::$phpInputFile = $filename;

			register_shutdown_function(array("Core", "cleanUpInput"));

			if(PROFILE)
				Profiler::unmark("php://input read");
		} else {
			self::$phpInputFile = false;
		}
		
		return self::$phpInputFile;
	}

	/**
	 *@param string - title of the link
	 *@param string - href attribute of the link
	 *@use: for adding breadcrumbs
	 */
	public static function addBreadcrumb($title, $link) {
		self::$breadcrumbs[$link] = $title;
		return true;
	}

	/**
	 *@access public
	 *@param string - title of addtitle
	 *@use: for adding title
	 */
	public static function setTitle($title) {
		self::$title = convert::raw2text($title);
		return true;
	}

	/**
	 * adds a callback to a hook
	 *
	 *@param string - name of the hook
	 *@param callback
	 */
	public static function addToHook($name, $callback) {
		// check for existance
		if(!isset(self::$hooks[strtolower($name)]) || !in_array($callback, self::$hooks[strtolower($name)])) {
			self::$hooks[strtolower($name)][] = $callback;
		}
			
	}

	/**
	 * calls all callbacks for a hook
	 *
	 *@param string - name of the hook
	 *@param array - params
	 */
	public static function callHook($name, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null) {
		if(isset(self::$hooks[strtolower($name)]) && is_array(self::$hooks[strtolower($name)])) {
			foreach(self::$hooks[strtolower($name)] as $callback) {
				if(is_callable($callback)) {
					call_user_func_array($callback, array($p1, $p2, $p3, $p4, $p5, $p6, $p7));
				}
			}
		}
	}

	/**
	 * registers an CMS-Var-Callback
	 *
	 *@param callback
	 *@param int - priority
	 */
	public function addCMSVarCallback($callback, $prio = 10) {
		if(is_callable($callback))
			self::$cmsVarCallbacks[$prio][] = $callback;
	}

	/**
	 * Sets a CMS variable.
	 *
	 * @see Core::$cms_vars for the variable containing array.
	 * @see Core::getCMSVar() to get variables.
	 *
	 * @param string $name Variable name.
	 * @param string $value Value of the variable.
	 *
	 * @return void
	 */
	public static function setCMSVar($name, $value) {
		self::$cms_vars[$name] = $value;
	}

	/**
	 * Returns a CMS variable.
	 *
	 * @see Core::$cms_vars for the variable containing array.
	 * @see Core::setCMSVar() to set variables.
	 *
	 * @param string $name Variable name.
	 *
	 * @return mixed Value of the variable.
	 */
	public static function getCMSVar($name) {
		if(PROFILE)
			Profiler::mark("Core::getCMSVar");
		if($name == "lang") {
			if(PROFILE)
				Profiler::unmark("Core::getCMSVar");
			return self::$lang;
		}

		if(isset(self::$cms_vars[$name])) {
			if(PROFILE)
				Profiler::unmark("Core::getCMSVar");
			return self::$cms_vars[$name];

		}

		if($name == "year") {
			if(PROFILE)
				Profiler::unmark("Core::getCMSVar");
			return date("Y");

		}

		if($name == "tpl") {
			if(PROFILE)
				Profiler::unmark("Core::getCMSVar");
			return self::getTheme();
		}

		if($name == "user") {
			self::$cms_vars["user"] = (member::$loggedIn) ? convert::raw2text(member::$loggedIn->title()) : null;
			if(PROFILE)
				Profiler::unmark("Core::getCMSVar");
			return self::$cms_vars["user"];
		}

		krsort(self::$cmsVarCallbacks);
		foreach(self::$cmsVarCallbacks as $callbacks) {
			foreach($callbacks as $callback) {
				if(($data = call_user_func_array($callback, array($name))) !== null) {
					if(PROFILE)
						Profiler::unmark("Core::getCMSVar");
					return $data;
				}
			}
		}

		if(PROFILE)
			Profiler::unmark("Core::getCMSVar");
		return isset($GLOBALS["cms_" . $name]) ? $GLOBALS["cms_" . $name] : null;

	}

	/**
	 * sets the theme
	 *
	 */
	public static function setTheme($theme) {
		self::setCMSVar("theme", $theme);
	}

	/**
	 * gets the theme
	 *
	 */
	public static function getTheme() {
		return self::getCMSVar("theme") ? self::getCMSVar("theme") : "default";
	}

	/**
	 * sets a header-field
	 *
	 */
	public static function setHeader($name, $value, $overwrite = true) {
		if($overwrite || !isset(self::$header[strtolower($name)]))
			self::$header[strtolower($name)] = array("name" => $name, "value" => $value);
	}

	/**
	 * sets a http-equiv header-field
	 *
	 */
	public static function setHTTPHeader($name, $value, $overwrite = true) {
		if($overwrite || !isset(self::$header[strtolower($name)]))
			self::$header[strtolower($name)] = array("name" => $name, "value" => $value, "http" => true);
	}

	/**
	 * makes a new entry in the log, because the method is deprecated
	 * but if the given version is higher than the current, nothing happens
	 * if DEV_MODE is not true, nothing happens
	 *
	 *@param int - version
	 *@param string - method
	 */
	public static function Deprecate($version, $newmethod = "") {
		if(DEV_MODE) {
			if(!version_compare(GOMA_VERSION . "-" . BUILD_VERSION, $version, "<")) {

				$trace = @debug_backtrace();

				$method = (isset($trace[1]["class"])) ? $trace[1]["class"] . "::" . $trace[1]["function"] : $trace[1]["function"];
				$file = isset($trace[1]["file"]) ? $trace[1]["file"] : (isset($trace[2]["file"]) ? $trace[2]["file"] : "Undefined");
				$line = isset($trace[1]["line"]) ? $trace[1]["line"] : (isset($trace[2]["line"]) ? $trace[2]["line"] : "Undefined");
				if($newmethod == "")
					log_error("DEPRECATED: " . $method . " is marked as DEPRECATED in " . $file . " on line " . $line);
				else
					log_error("DEPRECATED: " . $method . " is marked as DEPRECATED in " . $file . " on line " . $line . ". Please use " . $newmethod . " instead.");
			}
		}
	}

	/**
	 * gets all headers
	 *
	 */
	public static function getHeaderHTML() {
		$html = "";
		$i = 0;
		foreach(self::getHeader() as $data) {
			if($i == 0)
				$i++;
			else
				$html .= "		";
			if(isset($data["http"])) {
				$html .= "<meta http-equiv=\"" . $data["name"] . "\" content=\"" . $data["value"] . "\" />\n";
			} else {
				$html .= "<meta name=\"" . $data["name"] . "\" content=\"" . $data["value"] . "\" />\n";
			}
		}

		if(self::$favicon) {
			$html .= '		<link rel="icon" href="' . self::$favicon . '" type="image/x-icon" />';
			$html .= '		<link rel="apple-touch-icon-precomposed" href="'.RetinaPath(self::$favicon).'" />';
		}

		return $html;
	}

	/**
	 * gets all headers
	 *
	 */
	public static function getHeader() {

		self::callHook("setHeader");

		self::setHeader("generator", "Goma " . GOMA_VERSION . " with " . ClassInfo::$appENV["app"]["name"], false);

		return self::$header;
	}

	/**
	 * adds some rules to controller
	 *@param array - rules
	 *@param numeric - priority
	 */
	public static function addRules($rules, $priority = 50) {
		if(isset(self::$rules[$priority])) {
			self::$rules[$priority] = array_merge(self::$rules[$priority], $rules);
		} else {
			self::$rules[$priority] = $rules;
		}

	}

	/**
	 * checks if ajax
	 *@return bool
	 */
	public static function is_ajax() {
		return (isset($_REQUEST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest"));
	}

	/**
	 * clean-up for saved file-data
	 *
	 */
	public static function cleanUpInput() {
		if(isset(self::$phpInputFile) && file_exists(self::$phpInputFile))
			@unlink(self::$phpInputFile);
	}

	/**
	 * clean-up for log-files
	 *
	 *@param int - days
	 */
	public static function cleanUpLog($count = 30) {
		$logDir = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER;
		foreach(scandir($logDir) as $type) {
			if($type != "." && $type != ".." && is_dir($logDir . "/" . $type))
				foreach(scandir($logDir . "/" . $type . "/") as $date) {
					if($date != "." && $date != "..") {

						if(preg_match('/^(\d{2})\-(\d{2})\-(\d{2})$/', $date, $matches)) {
							$time = mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
							if($time < NOW - 60 * 60 * 24 * $count || isset($_GET["forceAll"])) {
								FileSystem::delete($logDir . "/" . $type . "/" . $date);
							}
						}
					}
				}
		}
	}

	/**
	 * returns current active url
	 *
	 */
	public static function activeURL() {
		if(Core::is_ajax()) {
			if(isset($_GET["redirect"])) {
				return $_GET["redirect"];
			} else if(isset($_SERVER["HTTP_REFERER"])) {
				return $_SERVER["HTTP_REFERER"];
			}
		}

		return $_SERVER["REQUEST_URI"];

	}

	/**
	 * throw an eror
	 *
	 */
	public static function throwError($code, $name, $message) {

		if(defined("ERROR_CODE")) {
			echo ERROR_CODE . ": " . ERROR_NAME . "\n\n" . ERROR_MESSAGE;
			exit ;
		}

		define("ERROR_CODE", $code);
		define("ERROR_NAME", $name);
		define("ERROR_MESSAGE", $message);
		if($code == 6) {
			ClassInfo::delete();
		}

		log_error("Code: " . $code . ", Name: " . $name . ", Details: " . $message . ", URL: " . $_SERVER["REQUEST_URI"]);

		if(($code != 1 && $code != 2 && $code != 5)) {
			$data = debug_backtrace();
			if(count($data) > 6) {
				$data = array($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
			}

			debug_log("Code: " . $code . "\nName: " . $name . "\nDetails: " . $message . "\nURL: " . $_SERVER["REQUEST_URI"] . "\nGoma-Version: " . GOMA_VERSION . "-" . BUILD_VERSION . "\nApplication: " . print_r(ClassInfo::$appENV, true) . "\n\n\nBacktrace:\n" . print_r($data, true));
		} else {
			debug_log("Code: " . $code . "\nName: " . $name . "\nDetails: " . $message . "\nURL: " . $_SERVER["REQUEST_URI"] . "\nGoma-Version: " . GOMA_VERSION . "-" . BUILD_VERSION . "\nApplication: " . print_r(ClassInfo::$appENV, true) . "\n\n\nBacktrace unavailable due to call");
		}

		if(is_object(self::$requestController)) {
			echo self::$requestController->__throwError($code, $name, $message);
			exit ;
		} else {
			if(Core::is_ajax())
				HTTPResponse::setResHeader(200);

			if(class_exists("ClassInfo", false) && defined("CLASS_INFO_LOADED")) {
				$template = new template;
				$template->assign('errcode', convert::raw2text($code));
				$template->assign('errname', convert::raw2text($name));
				$template->assign('errdetails', $message);
				HTTPresponse::sendHeader();

				echo $template->display('framework/error.html');
			} else {
				header("X-Powered-By: Goma Error-Management under Goma Framework " . GOMA_VERSION . "-" . BUILD_VERSION);
				$content = file_get_contents(ROOT . "system/templates/framework/phperror.html");
				$content = str_replace('{BASE_URI}', BASE_URI, $content);
				$content = str_replace('{$errcode}', $code, $content);
				$content = str_replace('{$errname}', $name, $content);
				$content = str_replace('{$errdetails}', $message, $content);
				$content = str_replace('$uri', $_SERVER["REQUEST_URI"], $content);
				echo $content;
				exit ;
			}

			exit ;
		}

		exit ;
	}

	/**
	 * checks if debug-mode
	 *
	 */
	public static function is_debug() {
		return (Permission::check(10) && isset($_GET["debug"]));
	}

	/**
	 * gives back if the current logged in admin want's to be see everything as a
	 * simple user
	 *
	 */
	public static function adminAsUser() {
		return (!defined("IS_BACKEND") && isset($_SESSION["adminAsUser"]));
	}

	//!Rendering-Methods
	/**
	 * Rendering-Methods
	 */

	/**
	 * serves the output given
	 *
	 *@param string - content
	 */
	public static function serve($output) {

		if(isset($_GET["flush"]) && Permission::check("ADMIN"))
			Notification::notify("Core", lang("CACHE_DELETED"));

		if(PROFILE)
			Profiler::unmark("render");

		if(PROFILE)
			Profiler::mark("serve");

		Core::callHook("serve", $output);

		if(isset(self::$requestController))
			$output = self::$requestController->serve($output);

		if(PROFILE)
			Profiler::unmark("serve");

		Core::callHook("onBeforeServe", $output);

		HTTPResponse::setBody($output);
		HTTPResponse::output();

		Core::callHook("onBeforeShutdown");

		exit ;
	}

	/**
	 * renders the page
	 */
	public function render($url) {
	
		self::InitResources();
		
	
		self::$url = $url;
		if(PROFILE)
			Profiler::mark("render");

		// we will merge $_POST with $_FILES, but before we validate $_FILES
		foreach($_FILES as $name => $arr) {
			if(is_array($arr["tmp_name"])) {
				foreach($arr["tmp_name"] as $tmp_file) {
					if($tmp_file && !is_uploaded_file($tmp_file)) {
						throw new LogicException($tmp_file . " is no valid upload! Please try again uploading the file.");
					}
				}
			} else {
				if($arr["tmp_name"] && !is_uploaded_file($arr["tmp_name"])) {
					throw new LogicException($arr["tmp_name"] . " is no valid upload! Please try again uploading the file.");
				}
			}
		}

		$orgrequest = new Request((isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'], $url, $_GET, array_merge((array)$_POST, (array)$_FILES));

		krsort(Core::$rules);

		// get  current controller
		foreach(self::$rules as $priority => $rules) {
			foreach($rules as $rule => $controller) {
				$request = clone $orgrequest;
				if($args = $request->match($rule, true)) {
					if($request->getParam("controller")) {
						$controller = $request->getParam("controller");
					}

					if(!ClassInfo::exists($controller)) {
						ClassInfo::delete();
						throw new LogicException("Controller $controller does not exist.");
					}

					$inst = new $controller;
					self::$requestController = $inst;
					self::$controller = array($inst);

					$data = $inst->handleRequest($request);
					if($data === false) {
						continue;
					}
					self::serve($data);
					break 2;
				}
			}
		}

	}

}



/**
 * shows an page with error details and nothing else
 *@name throwerror
 *@param string - errorcode
 *@param string - errorname
 *@param string - errordetails
 *@return  null
 */
function throwerror($errcode, $errname, $errdetails, $http_status = 500, $throwDebug = true) {
	HTTPResponse::setResHeader($http_status);
	return Core::throwError($errcode, $errname, $errdetails, $throwDebug);
}

/**
 * shows an page with error details and nothing else
 * data is generated by id
 *@name throwErrorById
 *@param numeric - errorcode
 *@return  null
 */
function throwErrorById($code) {
	$sqlerr = SQL::errno() . ": " . sql::error() . "<br /><br />\n\n <strong>Query:</strong> <br />\n<code>" . sql::$last_query . "</code>\n";
	$codes = array(1 => array('name' => 'Security Error', 'details' => '', "status_code" => 500), 2 => array('name' => 'Security Error', 'details' => 'Ip banned! Please wait 60 seconds!', "status_code" => 403), 3 => array('name' => lang("MYSQL_ERROR_SMALL"), 'details' => lang("MYSQL_ERROR") . $sqlerr, "status_code" => 500), 4 => array('name' => lang("MYSQL_CONNECT_ERROR"), 'details' => $sqlerr, "status_code" => 500), 5 => array('name' => lang("LESS_RIGHTS"), 'details' => '', "status_code" => 403), 6 => array('name' => "PHP-Error", 'details' => "", "status_code" => 500), 7 => array('name' => 'Service Unavailable', 'details' => 'The Service is currently not available', "status_code" => 503), );
	if(isset($codes[$code])) {
		HTTPresponse::setResHeader($codes[$code]["status_code"]);
		Core::throwerror($code, $codes[$code]['name'], $codes[$code]['details']);
	} else {
		HTTPresponse::setResHeader(500);
		Core::throwerror(6, $codes[6]['name'], $codes[6]['details']);
	}
}