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
 * @version		3.3.35
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
	 * inits the core
	 *
	 */
	public static function Init() {

		ob_start();

		if(isset($_SERVER['HTTP_X_IS_BACKEND']) && $_SERVER['HTTP_X_IS_BACKEND'] == 1) {
			Resources::addData("goma.ENV.is_backend = true;");
			define("IS_BACKEND", true);
		}

		if(isset($_POST) && $handle = @fopen("php://input", "r")) {
			if(PROFILE)
				Profiler::mark("php://input read");
			$random = randomString(20);
			if(!file_exists(FRAMEWORK_ROOT . "temp/")) {
				mkdir(FRAMEWORK_ROOT . "temp/", 0777, true);
				chmod(FRAMEWORK_ROOT . "temp/", 0777);
			}
			$file = FRAMEWORK_ROOT . "temp/php_input_" . $random;
			file_put_contents($file, $handle);
			fclose($handle);
			self::$phpInputFile = $file;

			register_shutdown_function(array("Core", "cleanUpInput"));

			if(PROFILE)
				Profiler::unmark("php://input read");
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

		
		// delete-cache-support
		if(isset($_GET['flush'])) {
			if(PROFILE)
				Profiler::mark("delete_cache");

			if(Permission::check("ADMIN")) {
				logging('Deleting FULL Cache');
				self::deletecache(true);
				// delete files in cache
			} else {
				logging("Deleting Cache");
				self::deletecache();
				// delete some files in cache
			}

			if(PROFILE)
				Profiler::unmark("delete_cache");
		}

		// some vars for javascript
		Resources::addData("if(typeof current_project == 'undefined'){ var current_project = '" . CURRENT_PROJECT . "';var root_path = '" . ROOT_PATH . "';var ROOT_PATH = '" . ROOT_PATH . "';var BASE_SCRIPT = '" . BASE_SCRIPT . "'; goma.ENV.framework_version = '" . GOMA_VERSION . "-" . BUILD_VERSION . "'; var activelang = '".Core::$lang."'; }");

		Object::instance("Core")->callExtending("construct");
		self::callHook("init");

		Resources::add("system/libs/thirdparty/modernizr/modernizr.js", "js", "main");
		Resources::add("system/libs/thirdparty/jquery/jquery.js", "js", "main");
		Resources::add("system/libs/thirdparty/jquery/jquery.ui.js", "js", "main");
		Resources::add("system/libs/thirdparty/jquery-throttle-debounce/debounce-throttle.js", "js", "main");
		Resources::add("system/libs/thirdparty/jquery-ui-touch-punch-master/jquery.ui.touch-punch.js", "js", "main");
		Resources::add("system/libs/thirdparty/respond/respond.min.js", "js", "main");
		Resources::add("system/libs/thirdparty/jResize/jResize.js", "js", "main");
		Resources::add("system/libs/javascript/loader.js", "js", "main");
		Resources::add("box.css", "css", "main");

		Resources::add("default.css", "css", "main");
		Resources::add("goma_default.css", "css", "main");

		HTTPResponse::setHeader("x-base-uri", BASE_URI);
		HTTPResponse::setHeader("x-root-path", ROOT_PATH);

		if(PROFILE)
			Profiler::unmark("Core::Init");
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
		self::$hooks[strtolower($name)][] = $callback;
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
				if(is_callable($callback))
					call_user_func_array($callback, array($p1, $p2, $p3, $p4, $p5, $p6, $p7));
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
	 * detetes the cache
	 *@use to delete the cache
	 */
	public static function deletecache($all = false) {
		$dir = ROOT . CACHE_DIRECTORY;

		if(!$all && file_exists($dir . "/deletecache") && filemtime($dir . "/deletecache") > NOW - 60 * 5)
			return true;

		clearstatcache();

		foreach(scandir($dir) as $file) {
			if((substr($file, 0, 3) == "gfs" && filemtime($dir . $file) > NOW - 7200) || $file == "autoloader_exclude" || $file == "deletecache")
				continue;

			// session store
			if(preg_match('/^data\.([a-zA-Z0-9_]{10})\.goma$/Usi', $file)) {
				if(file_exists($dir . $file) && filemtime($dir . $file) < NOW - 3600) {
					@unlink($dir . $file);
				}
				continue;
			}

			if($all) {
				if($file != "." && $file != "..") {
					if(!is_dir($dir . $file) || !file_exists($dir . $file . "/.dontremove")) {
						FileSystem::Delete($dir . $file);
					}
				}
			} else {
				if(preg_match('/\.php$/i', $file) && is_file($dir . $file)) {
					unlink($dir . $file);
				}
			}
		}

		// empty framework cache
		foreach(scandir(ROOT . "system/temp/") as $file) {
			if($file != "." && $file != ".." && filemtime(ROOT . "system/temp/" . $file) + 600 < NOW && !file_exists($dir . $file . "/.dontremove") && $file != "autoloader_exclude")
				FileSystem::delete(ROOT . "system/temp/" . $file);
		}

		FileSystem::Delete(ROOT . APPLICATION . "/uploads/d05257d352046561b5bfa2650322d82d");

		Core::callHook("deletecache", $all);

		clearstatcache();

		FileSystem::Write($dir . "/deletecache", 1);

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
 * Controller for Dev-Mode of Goma-Framework. Handles stuff like rebuilding DB or
 * building versions
 *
 * @package		Goma\Core
 * @version		2.0
 */
class Dev extends RequestHandler {
	/**
	 * title of current view
	 *
	 */
	public static $title = "Creating new Database";

	public $url_handlers = array("build" => "builddev", "rebuildcaches" => "rebuild", "flush" => "flush", "buildDistro/\$name!/\$subname" => "buildAppDistro", "buildDistro" => "buildDistro", "cleanUpVersions" => "cleanUpVersions", "setChmod777" => "setChmod777", "test" => "test");

	public $allowed_actions = array("builddev", "rebuild", "flush", "buildDistro" => "->isDev", "buildAppDistro" => "->isDev", "buildExpDistro" => "->isDev", "cleanUpVersions" => "->isDev", "setChmod777", "test");

	/**
	 * runs dev and redirects back to REDIRECT
	 *
	 */
	public static function redirectToDev() {
		@session_start();
		$_SESSION["dev_without_perms"] = true;
		HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "/dev?redirect=" . getredirect(false));
		exit ;
	}

	/**
	 * shows dev-site or not
	 */
	public function handleRequest($request, $subController = false) {

		define("DEV_CONTROLLER", true);

		HTTPResponse::unsetCacheable();

		if(!isset($_SESSION["dev_without_perms"]) && !Permission::check("ADMIN")) {
			makeProjectAvailable();

			throw new PermissionException();
		}

		return parent::handleRequest($request, $subController);

	}

	/**
	 * serves data
	 *
	 */
	public function serve($content) {
		$viewabledata = new ViewAccessableData();
		$viewabledata->content = $content;
		$viewabledata->title = self::$title;

		return $viewabledata->renderWith("framework/dev.html");
	}

	/**
	 * sets chmod 0777 to the whole system
	 *
	 */
	public function setChmod777() {
		FileSystem::chmod(ROOT, 0777, false);
		return "Okay";
	}

	/**
	 * returns if we are in dev-mode
	 *
	 */
	public function isDev() {

		return DEV_MODE;
	}

	/**
	 * the index site of the dev-mode
	 *
	 *@name index
	 */
	public function index() {

		// make 503
		makeProjectUnavailable();

		ClassInfo::delete();
		Core::callHook("deleteCachesInDev");

		// check if dev-without-perms, so redirect directly
		if(isset($_SESSION["dev_without_perms"])) {
			$url = ROOT_PATH . BASE_SCRIPT . "dev/rebuildcaches" . URLEND . "?redirect=" . urlencode(getredirect(true));
			header("Location: " . $url);
			echo "<script>location.href = '" . $url . "';</script><br /> Redirecting to: <a href='" . $url . "'>'.$url.'</a>";
			Core::callHook("onBeforeShutDown");
			exit ;
		}
		return '<h3>Creating new Database</h3>
			<script type="text/javascript">
				setTimeout(function(){ location.href = "' . ROOT_PATH . BASE_SCRIPT . 'dev/rebuildcaches/"; }, 500);
			</script>
			
			<img src="images/16x16/loading.gif" alt="Loading..." /> Rebuilding Caches... <br /><br />If it doesn\'t reload within 15 seconds, please click <a href="' . ROOT_PATH . 'dev/rebuildcaches">here</a>.
			<noscript>Please click <a href="' . ROOT_PATH . BASE_SCRIPT . 'dev/rebuildcaches/">here</a>.</noscript>';
	}

	/**
	 * this step regenerates the cache
	 *
	 *@name rebuild
	 */
	public function rebuild() {
		// 503
		makeProjectUnavailable();

		Core::callHook("rebuildCachesInDev");

		// generate class-info
		defined('GENERATE_CLASS_INFO') OR define('GENERATE_CLASS_INFO', true);
		define("DEV_BUILD", true);

		// redirect if needed
		if(isset($_SESSION["dev_without_perms"])) {
			$url = ROOT_PATH . BASE_SCRIPT . "dev/builddev" . URLEND . "?redirect=" . urlencode(getredirect(true));
			header("Location: " . $url);
			echo "<script>location.href = '" . $url . "';</script><br /> Redirecting to: <a href='" . $url . "'>'.$url.'</a>";
			Core::callHook("onBeforeShutDown");
			exit ;
		}

		return '<h3>Creating new Database</h3>
			<script type="text/javascript">
				setTimeout(function(){ location.href = "' . ROOT_PATH . BASE_SCRIPT . 'dev/builddev/"; }, 500);
			</script>
			<div><img src="images/success.png" height="16" alt="Loading..." /> Rebuilding Caches...</div>
			<noscript>Please click <a href="' . ROOT_PATH . BASE_SCRIPT . 'dev/builddev/">here</a>.<br /></noscript>
			<div><img src="images/16x16/loading.gif"  alt="Loading..." /> Rebuilding Database...</div>If it doesn\'t reload within 15 seconds, please click <a href="' . ROOT_PATH . BASE_SCRIPT . 'dev/builddev">here</a>.';
	}

	/**
	 * this step regenerates the db
	 */
	public function builddev() {
		// 503
		makeProjectUnavailable();

		// patch
		Object::$cache_singleton_classes = array();

		// show progress
		$data = '<h3>Creating new Database</h3>
			<div><img src="images/success.png" height="16" alt="Loading..." /> Rebuilding Caches...</div>
			<div><img src="images/success.png" height="16" alt="Success" />  Rebuilding Database...</div>';

		if(defined("SQL_LOADUP")) {
			// remake db
			foreach(classinfo::getChildren("dataobject") as $value) {
				$obj = new $value;

				$data .= nl2br($obj->buildDB(DB_PREFIX));
			}
		}

		logging(strip_tags(preg_replace("/(\<br\s*\\\>|\<\/div\>)/", "\n", $data)));

		// after that rewrite classinfo
		ClassInfo::write();

		unset($obj);
		$data .= "<br />";

		Core::callHook("rebuildDBInDev");

		// restore page, so delete 503
		makeProjectAvailable();

		// redirect if needed
		if(isset($_GET["redirect"])) {
			if(isset($_SESSION["dev_without_perms"])) {
				unset($_SESSION["dev_without_perms"]);
			}
			HTTPResponse::redirect($_GET["redirect"]);
			exit ;
		}

		// redirect if needed
		if(isset($_SESSION["dev_without_perms"])) {
			unset($_SESSION["dev_without_perms"]);
			header("Location: " . ROOT_PATH);
			Core::callHook("onBeforeShutDown");
			exit ;
		}

		return $data;
	}

	/**
	 * just for flushing the whole (!) cache
	 *
	 *@name flush
	 */
	public function flush() {
		defined('GENERATE_CLASS_INFO') OR define('GENERATE_CLASS_INFO', true);
		define("DEV_BUILD", true);

		classinfo::delete();
		classinfo::loadfile();

		header("Location: " . ROOT_PATH . "");
		Core::callHook("onBeforeShutDown");
		exit ;
	}

	/**
	 * builds a distributable of the application
	 *
	 */
	public function buildDistro() {
		self::$title = lang("DISTRO_BUILD");
		return g_SoftwareType::listAllSoftware()->renderWith("framework/buildDistro.html");
	}

	/**
	 * builds an app-distro
	 *
	 */
	public function buildAppDistro($name = null, $subname = null) {
		if(!isset($name)) {
			$name = $this->getParam("name");
		}

		if(!isset($subname))
			$subname = $this->getParam("subname");

		self::$title = lang("DISTRO_BUILD");

		if(!$name)
			return false;

		if(ClassInfo::exists("G_" . $name . "SoftwareType") && is_subclass_of("G_" . $name . "SoftwareType", "G_SoftwareType")) {
			$filename = call_user_func_array(array("G_" . $name . "SoftwareType", "generateDistroFileName"), array($subname));
			if($filename === false)
				return false;
			$file = ROOT . CACHE_DIRECTORY . "/" . $filename;

			$return = call_user_func_array(array("G_" . $name . "SoftwareType", "buildDistro"), array($file, $subname));
			if(is_string($return))
				return $return;

			FileSystem::sendFile($file);
			exit ;
		}

		return false;
	}

	/**
	 * cleans up versions
	 *
	 */
	public function cleanUpVersions() {
		$log = "";
		foreach(ClassInfo::getChildren("DataObject") as $child) {
			if(ClassInfo::getParentClass($child) == "dataobject") {
				$c = new $child;
				if(DataObject::versioned($child)) {

					$baseTable = ClassInfo::$class_info[$child]["table"];
					if(isset(ClassInfo::$database[$child . "_state"])) {
						// first get ids NOT to delete

						$recordids = array();
						$ids = array();
						// first recordids
						$sql = "SELECT * FROM " . DB_PREFIX . $child . "_state";
						if($result = SQL::Query($sql)) {
							while($row = SQL::fetch_object($result)) {
								$recordids[$row->id] = $row->id;
								$ids[$row->publishedid] = $row->publishedid;
								$ids[$row->stateid] = $row->stateid;
							}
						}

						$deleteids = array();
						// now generate ids to delete
						$sql = "SELECT id FROM " . DB_PREFIX . $baseTable . " WHERE id NOT IN('" . implode("','", $ids) . "') OR recordid NOT IN ('" . implode("','", $recordids) . "')";
						if($result = SQL::Query($sql)) {
							while($row = SQL::fetch_object($result)) {
								$deleteids[] = $row->id;
							}
						}

						// now delete

						// first generate tables
						$tables = array(ClassInfo::$class_info[$child]["table"]);
						foreach(ClassInfo::dataClasses($child) as $class => $table) {
							if($baseTable != $table && isset(ClassInfo::$database[$table])) {
								$tables[] = $table;
							}
						}

						foreach($tables as $table) {
							$sql = "DELETE FROM " . DB_PREFIX . $table . " WHERE id IN('" . implode("','", $deleteids) . "')";
							if(SQL::Query($sql))
								$log .= '<div><img src="images/success.png" height="16" alt="Loading..." /> Delete versions of ' . $table . '</div>';
							else
								$log .= '<div><img src="images/16x16/del.png" height="16" alt="Loading..." /> Failed to delete versions of ' . $table . '</div>';
						}
					}

				}
			}
		}

		return '<h3>DB-Cleanup</h3>' . $log;
	}

	/**
	 * test-implementation
	 */
	public function test() {
		if(DEV_MODE) {
			$c = new GomaTestController();
			return $c->handleRequest($this->request);
		} else {
			return false;
		}
	}

}

/**
 * SECURITY
 */

/**
 * escapes SQL-data
 * very important!!!
 *@name dbescape
 *@param string - the string to protect
 *@return string - the protected string
 */
function dbescape($str) {
	Core::Deprecate(2.0, "convert::raw2sql");
	return convert::raw2sql($str);
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

interface TestAble {
}
