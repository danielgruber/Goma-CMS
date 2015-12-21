<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

StaticsManager::AddSaveVar(Core::ID, "hooks");
StaticsManager::AddSaveVar(Core::ID, "cmsVarCallbacks");

/**
 * Goma Core.
 *
 * @package		Goma\Core
 * @version		3.4
 */
class Core extends gObject {
	const HEADER_HTML_HOOK = "getHeaderHTML";
	const ID = "Core";

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
	 * this var contains the site_mode
	 */
	public static $site_mode = STATUS_ACTIVE;

	/**
	 * the current active controller
	 *
	 *@var gObject
	 */
	public static $requestController;

	/**
	 * global hooks
	 */
	public static $hooks = array();

	/**
	 * file which contains data from php://input
	 *
	 *@name phpInputFile
	 *@accesss public
	 */
	protected static $phpInputFile;

	/**
	 * callbacks for $_cms_blah
	 *
	 *@name cmsVarCallbacks
	 */
	private static $cmsVarCallbacks = array();
	
	/**
	 * cache-managers.
	*/
	public static $cacheManagerFramework;
	public static $cacheManagerApplication;

	/**
	 * repository.
	 *
	 * @var IModelRepository
	 */
	protected static $repository;

	/**
	 * inits the core
	 *
	 */
	public static function Init() {

		ob_start();

		StaticsManager::setSaveVars("core");

		if(isset($_SERVER['HTTP_X_IS_BACKEND']) && $_SERVER['HTTP_X_IS_BACKEND'] == 1) {
			Resources::addData("goma.ENV.is_backend = true;");
			define("IS_BACKEND", true);
		}
		
		self::$repository = new ModelRepository();

		// now init session
		if(PROFILE)
			Profiler::mark("session");
		GlobalSessionManager::Init();
		if(PROFILE)
			Profiler::unmark("session");
			
			
		// init language-support
		i18n::Init();	
		
		if(defined("SQL_LOADUP"))
			member::Init();

		if(PROFILE)
			Profiler::mark("Core::Init");
		
		gObject::instance("Core")->callExtending("construct");
		self::callHook("init");

		if(PROFILE)
			Profiler::unmark("Core::Init");
	}

	/**
	 * returns repository.
	 *
	 * @return IModelRepository
	 */
	public static function repository() {
		return self::$repository;
	}

	/**
	 * sets repository.
	 */
	public static function __setRepo($repository) {
		self::$repository = $repository;
	}

	/**
	 * returns session.
	 *
	 * @return ISessionManager
	 */
	public static function globalSession()
	{
		return GlobalSessionManager::globalSession();
	}

	/**
	 * sets global session.
	 *
	 * @param ISessionManager $session
	 */
	public static function __setSession($session)
	{
		GlobalSessionManager::__setSession($session);
	}

	/**
	 * inits cache-managers.
	*/
	public static function initCache() {
		self::$cacheManagerApplication = new CacheManager(ROOT . APPLICATION . "/temp");
		self::$cacheManagerFramework = new CacheManager(ROOT . "system/temp");
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

            g_SoftwareType::cleanUpUpdates();
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
	 * @return string|false
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
	 * @param string $title
	 * @param string $link
	 * @return bool
	 */
	public static function addBreadcrumb($title, $link) {
		self::$breadcrumbs[$link] = $title;
		return true;
	}

	/**
	 * @access public
	 * @param string $title
	 * @return bool
	 */
	public static function setTitle($title) {
		self::$title = convert::raw2text($title);
		return true;
	}

	/**
	 * adds a callback to a hook
	 *
	 * @param string $name
	 * @param Closure $callback
	 */
	public static function addToHook($name, $callback) {
		if(!isset(self::$hooks[strtolower($name)]) || !in_array($callback, self::$hooks[strtolower($name)])) {
			self::$hooks[strtolower($name)][] = $callback;
		}
	}

	/**
	 * calls all callbacks for a hook
	 *
	 * @param 		string 	$name of the hook
	 * @params.. 	mixed 	additional params up to 7
	 */
	public static function callHook($name, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null) {
		if(isset(self::$hooks[strtolower($name)]) && is_array(self::$hooks[strtolower($name)])) {
			foreach(self::$hooks[strtolower($name)] as $callback) {
				if(is_callable($callback)) {
					call_user_func_array($callback, array(&$p1, &$p2, &$p3, &$p4, &$p5, &$p6, &$p7));
				}
			}
		}
	}

	/**
	 * registers an CMS-Var-Callback
	 *
	 * @param 	Closure
	 * @param 	int $priority
	 */
	public function addCMSVarCallback($callback, $priority = 10) {
		if(is_callable($callback)) {
			self::$cmsVarCallbacks[$priority][] = $callback;
		}
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
	 * @param string $name
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

		Core::callHook(self::HEADER_HTML_HOOK, $html);

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
		Director::addRules($rules, $priority);
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
		if(isset(self::$phpInputFile) && file_exists(self::$phpInputFile)) {
			@unlink(self::$phpInputFile);
		}
	}

	/**
	 * clean-up for log-files
	 *
	 *@param int - days
	 */
	public static function cleanUpLog($count = 30) {
		$logDir = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER;
		foreach(scandir($logDir) as $type) {
			if($type != "." && $type != ".." && is_dir($logDir . "/" . $type)) {
				foreach (scandir($logDir . "/" . $type . "/") as $date) {
					if ($date != "." && $date != "..") {

						if (preg_match('/^(\d{2})\-(\d{2})\-(\d{2})$/', $date, $matches)) {
							$time = mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
							if ($time < NOW - 60 * 60 * 24 * $count || isset($_GET["forceAll"])) {
								FileSystem::delete($logDir . "/" . $type . "/" . $date);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * returns current active url
	 *
	 * @return string
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

		if(is_object(Director::$requestController)) {
			echo Director::$requestController->__throwError($code, $name, $message);
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
				header("X-Powered-By: Goma Framework " . GOMA_VERSION . "-" . BUILD_VERSION);
				$content = file_get_contents(ROOT . "system/templates/framework/phperror.html");
				$content = str_replace('{BASE_URI}', BASE_URI, $content);
				$content = str_replace('{$errcode}', $code, $content);
				$content = str_replace('{$errname}', $name, $content);
				$content = str_replace('{$errdetails}', $message, $content);
				$content = str_replace('$uri', $_SERVER["REQUEST_URI"], $content);
				echo $content;
			}
		}

		exit;
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
		return (!defined("IS_BACKEND") && GlobalSessionManager::globalSession()->hasKey(SystemController::ADMIN_AS_USER));
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
		Director::serve($output);
	}

	/**
	 * renders the page
	 */
	public function render($url) {

		self::InitResources();

		Director::direct($url);
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
