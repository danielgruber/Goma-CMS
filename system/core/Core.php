<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 04.11.2012
  * $Version 3.3.17
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

ClassInfo::AddSaveVar("Core", "rules");
ClassInfo::AddSaveVar("Core", "hooks");

class Core extends object
{
		/**
		 *@name breadcrumbs
		 *@access public
		 *@var array 
		*/
		public static $breadcrumbs = array();
		
		/**
		 * title of the page
		 *
		 *@name title
		 *@access public
		*/
		public static $title = "";
		
		/**
		 * headers
		 *
		 *@name header
		 *@access public
		*/
		public static $header = array(
			
		);
		
		/**
		 * current languages
		*/
		public static $lang;
		
		/**
		 * cms-vars
		*/
		public static $cms_vars = array();
		
		/**
		 * Controllers used in this Request
		 *@name Controllers
		*/
		public static $controller = array();
		
		/**
		 * this var contains the site_mode
		 *@name site_mode
		 *@access public
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
		 *@name requestController
		 *@access public
		 *@var object
		*/
		public static $requestController;
		
		/**
		 * global hooks
		 *
		 *@name hooks
		 *@access public
		*/
		public static $hooks = array();
		
		/**
		 * current active url
		 *
		 *@name url
		 *@access public
		*/
		public static $url;
		
		/**
		 * if mobile is activated
		 *
		 *@name isMobile
		 *@access public
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
		 *@access public
		 *@param string - title of the link
		 *@param string - href attribute of the link
		 *@use: for adding breadcrumbs
		 */
		public static function addBreadcrumb($title, $link)
		{
				self::$breadcrumbs[$link] = $title;
				return true;
		}
		
		/**
		 *@access public
		 *@param string - title of addtitle
		 *@use: for adding title
		 */
		public static function setTitle($title)
		{
				self::$title = convert::raw2text($title);
				return true;
		}
		
		/**
		 * adds a callback to a hook
		 *
		 *@name addToHook
		 *@access public
		 *@param string - name of the hook
		 *@param callback
		*/
		public static function addToHook($name, $callback) {
			self::$hooks[strtolower($name)][] = $callback; 
		}
		
		/**
		 * calls all callbacks for a hook
		 *
		 *@name callHook
		 *@access public
		 *@param string - name of the hook
		 *@param array - params
		*/
		public static function callHook($name, $params = array()) {
			if(isset(self::$hooks[strtolower($name)]) && is_array(self::$hooks[strtolower($name)])) {
				foreach(self::$hooks[strtolower($name)] as $callback) {
					if(is_callable($callback))
						call_user_func_array($callback, $params);
				}
			}
		}
		
		
		
		/**
		 * sets a cms-var
		 *
		 *@name setCMSVar
		 *@access public
		*/
		public static function setCMSVar($name, $value) {
			self::$cms_vars[$name] = $value;
		}
		/**
		 * gets a CMS-Var
		 *
		 *@name getCMSVar
		 *@access public
		 *@param string - name: cms-var
		*/
		public static function getCMSVar($name) {
			if(PROFILE) Profiler::mark("Core::getCMSVar");
			if($name == "lang") {
				if(PROFILE) Profiler::unmark("Core::getCMSVar");
				return self::$lang;
			}
			
			if(isset(self::$cms_vars[$name])) {
				if(PROFILE) Profiler::unmark("Core::getCMSVar");
				return self::$cms_vars[$name];
				
			}	
			
			if($name == "year") {
				if(PROFILE) Profiler::unmark("Core::getCMSVar");
				return date("Y");
				
			}
			
			if($name == "tpl") {
				if(PROFILE) Profiler::unmark("Core::getCMSVar");
				return self::getTheme();
			}
			
			if($name == "user") {
				self::$cms_vars["user"] = convert::raw2text(member::$loggedIn->title());
				if(PROFILE) Profiler::unmark("Core::getCMSVar");
				return self::$cms_vars["user"];
			}
			
			
			if(PROFILE) Profiler::unmark("Core::getCMSVar");
			return isset($GLOBALS["cms_" . $name]) ? $GLOBALS["cms_" . $name] : null;
			
		}
		/**
		 * sets the theme
		 *
		 *@name setTheme
		 *@access public
		*/
		public static function setTheme($theme) {
			self::setCMSVar("theme", $theme);
		}
		/**
		 * gets the theme
		 *
		 *@name getTheme
		 *@access public
		*/
		public static function getTheme() {
			return self::getCMSVar("theme") ? self::getCMSVar("theme") : "default";
		}
		/**
		 * sets a header-field
		 *
		 *@name setHeader
		 *@access public
		*/
		public static function setHeader($name, $value, $overwrite = true) {
			if($overwrite || !isset(self::$header[strtolower($name)]))
				self::$header[strtolower($name)] = array(
					"name" => $name,
					"value"=> $value
				);
		}
		/**
		 * sets a http-equiv header-field
		 *
		 *@name setHTTPHeader
		 *@access public
		*/
		public static function setHTTPHeader($name, $value, $overwrite = true) {
			if($overwrite || !isset(self::$header[strtolower($name)]))
				self::$header[strtolower($name)] = array(
					"name"	 => $name,
					"value"	=> $value,
					"http"	=> true
				);
		}
		/**
		 * makes a new entry in the log, because the method is deprecated
		 * but if the given version is higher than the current, nothing happens
		 * if DEV_MODE is not true, nothing happens
		 *
		 *@name Deprecate
		 *@access public
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
						log_error("DEPRECATED: ".$method." is marked as DEPRECATED in ".$file." on line ".$line);
					else
						log_error("DEPRECATED: ".$method." is marked as DEPRECATED in ".$file." on line ".$line . ". Please use ".$newmethod." instead.");
				}
			}
		}
		/**
		 * gets all headers
		 *
		 *@name getHeader
		 *@access public
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
					$html .= "<meta http-equiv=\"".$data["name"]."\" content=\"".$data["value"]."\" />\n";
				} else {
					$html .= "<meta name=\"".$data["name"]."\" content=\"".$data["value"]."\" />\n";
				}
		 	}
		 	return $html;
		}
		/**
		 * gets all headers
		 *
		 *@name getHeader
		 *@access public
		*/
		public static function getHeader() {
			
			self::callHook("setHeader");
			
			self::setHeader("generator", "Goma " . GOMA_VERSION . " with " . ClassInfo::$appENV["app"]["name"], false);
			
			return self::$header;
		}
		
		/**
		 * detetes the cache
		 *@name deletecache
		 *@access public
		 *@use to delete the cache
		*/
		public static function deletecache($all = false)
		{
				$dir = ROOT . CACHE_DIRECTORY;
				
				clearstatcache();
				
				foreach(scandir($dir) as $file) {
					// session store
					if(preg_match('/^data\.([a-zA-Z0-9_]{10})\.goma$/Usi',$file)) {
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
						if(preg_match('/\.php$/i', $file) && is_file($dir . $file) && substr($file, 0 - strlen(CLASS_INFO_DATAFILE)) != CLASS_INFO_DATAFILE) {
							unlink($dir . $file);
						}
					}
				}
				
				// empty framework cache
				foreach(scandir(ROOT . "system/temp/") as $file) {
					if($file != "." && $file != ".." && filemtime(ROOT . "system/temp/" . $file) + 60 < NOW && !file_exists($dir . $file . "/.dontremove"))
						FileSystem::delete (ROOT . "system/temp/" . $file);
				}
				
				FileSystem::Delete(ROOT . APPLICATION . "/uploads/d05257d352046561b5bfa2650322d82d");
				
				Core::callHook("deletecache", array($all));
				global $_REGISTRY;
				$_REGISTRY["cache"] = array();
				
		}
		/**
		 * adds some rules to controller
		 *@name addRules
		 *@access public
		 *@param array - rules
		 *@param numeric - priority
		*/
		public static function addRules($rules, $priority = 50)
		{
				if(isset(self::$rules[$priority]))
				{
						self::$rules[$priority] = array_merge(self::$rules[$priority], $rules);
				} else
				{
						self::$rules[$priority] = $rules;
				}	 
						
		}
		/**
		 * checks if ajax
		 *@name is_ajax
		 *@access public
		 *@return bool
		*/
		public static function is_ajax()
		{
 	 	 	return (
					isset($_REQUEST['ajax']) ||
					(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest")
				);
		}
		
		/**
		 * inits the core
		 *
		 *@name init
		 *@access public
		*/
		public static function Init() {
			
			ob_start();
			
			if(isset($_POST) && $handle = @fopen("php://input", "r")) {
				if(PROFILE) Profiler::mark("php://input read");
				$random = randomString(20);
				$file = FRAMEWORK_ROOT . "temp/php_input_" . $random;
				file_put_contents($file, $handle);
				fclose($handle);
				self::$phpInputFile = $file;
				
				register_shutdown_function(array("Core", "cleanUpInput"));
				
				if(PROFILE) Profiler::unmark("php://input read");
			}
			
			// now init session
			if(PROFILE) Profiler::mark("session");
			session_start();
			if(PROFILE) Profiler::unmark("session");
			
			if(PROFILE) Profiler::mark("Core::Init");
			
			// init language-support
			require_once(FRAMEWORK_ROOT . "core/i18n.php");
 	 	 	ClassManifest::$loaded["i18n"] = true;
			i18n::Init();
			
			
			// delete-cache-support
			if(isset($_GET['flush']))
			{
					if(PROFILE)
							Profiler::mark("delete_cache");
					
					if(Permission::check(7))
					{
						logging('Deleting FULL Cache');
						self::deletecache(true); // delete files in cache
					} else {
						logging("Deleting Cache");
						 self::deletecache(); // delete some files in cache
					}
					
					if(PROFILE)
 	 	 				Profiler::unmark("delete_cache");
			}
			
			// some vars for javascript
			Resources::addData("var current_project = '".CURRENT_PROJECT."';var root_path = '".ROOT_PATH."';var ROOT_PATH = '".ROOT_PATH."';var BASE_SCRIPT = '".BASE_SCRIPT."';var is_mobile = ".var_export(self::isMobile(), true).";");
			
			Object::instance("Core")->callExtending("construct");
			self::callHook("init");
			
			Resources::add("system/libs/thirdparty/modernizr/modernizr.js", "js", "main");
			Resources::add("system/libs/thirdparty/jquery/jquery.js", "js", "main");
			Resources::add("system/libs/thirdparty/respond/respond.min.js", "js", "main");
			Resources::add("system/libs/thirdparty/jResize/jResize.js", "js", "main");
			Resources::add("system/libs/javascript/loader.js", "js", "main");
			Resources::add("box.css", "css", "main");
			
			Resources::add("default.css", "css", "main");
			
			if(DEV_MODE) {
				Resources::add("system/libs/javascript/profiler.js", "js", "main");
			}
			
			if(PROFILE) Profiler::unmark("Core::Init");
		}
		
		/**
		 * clean-up for saved file-data
		 *
		 *@name cleanUpInput
		 *@access public
		*/
		public static function cleanUpInput() {
			if(isset(self::$phpInputFile) && file_exists(self::$phpInputFile))
				@unlink(self::$phpInputFile);
		}
		
		/**
		 * END STATIC METHODS
		*/
		
		
		/**
		 * renders the page
		 *@name render
		 *@access public
		*/
		public function render($url)
		{
				self::$url = $url;
				if(PROFILE) Profiler::mark("render");
				
				// we will merge $_POST with $_FILES, but before we validate $_FILES
				foreach($_FILES as $name => $arr)
				{
						if(is_array($arr["tmp_name"]))
						{
								foreach($arr["tmp_name"] as $tmp_file)
								{
										if($tmp_file && !is_uploaded_file($tmp_file))
										{
												throwError(6, 'PHP-Error', "".$tmp_file." is no valid upload! Please try again uploading the file.");
										}
								}
						} else
						{
								if($arr["tmp_name"] && !is_uploaded_file($arr["tmp_name"]))
								{
										throwError(6, 'PHP-Error', "".$arr["tmp_name"]." is no valid upload! Please try again uploading the file.");
								}
						}
				}
				
				$request = new Request(
					(isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
					$url,
					$_GET,
					array_merge((array)$_POST, (array)$_FILES)
				);
				
				krsort(Core::$rules);
				
				// get  current controller
				foreach(self::$rules as $priority => $rules)
				{
						foreach($rules as $rule => $controller)
						{
								if($args = $request->match($rule, true))
								{
										if($request->getParam("controller"))
										{
												$controller = $request->getParam("controller");
										}
										$inst = new $controller;
										self::$requestController = $inst;
										self::$controller[] = $inst;
										
										self::serve($inst->handleRequest($request));
										break 2;
								}
						}
				}
				
		}
 	  
		/**
		 * serves the output given
		 *
		 *@name serve
		 *@access public
		 *@param string - content
		*/
		public static function serve($output) {
			
			
			if(PROFILE) Profiler::unmark("render");
			
			
			if(PROFILE) Profiler::mark("serve");
			
			Core::callHook("serve", array($output));
			
			if(isset(self::$requestController))
				$output = self::$requestController->serve($output);
			
			if(PROFILE) Profiler::unmark("serve");
			
			Core::callHook("onBeforeShutdown");
			
			HTTPResponse::setBody($output);
			HTTPResponse::output();
			exit;
		}
		
		/**
		 * returns current active url
		 *
		 *@name activeURL
		 *@access public
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
		 *@name thorwError
		 *@access public
		*/		
		public static function throwError($code, $name, $message, $callDebug = true) {
 	 	 	
			if(defined("ERROR_CODE")) {
				echo ERROR_CODE . ": " . ERROR_NAME . "\n\n" . ERROR_MESSAGE;
				exit;
			}
			
			define("ERROR_CODE", $code);
			define("ERROR_NAME", $name);
			define("ERROR_MESSAGE", $message);
			if($code == 6) {
				ClassInfo::delete();
			}
			
			log_error("Code: " . $code . ", Name: " . $name . ", Details: ".$message.", URL: " . $_SERVER["REQUEST_URI"]);
			
			if(($code != 1 && $code != 2 && $code != 5) || !$callDebug) {
	 	 		  debug_log("Code: " . $code . "\nName: " . $name . "\nDetails: " . $message . "\nURL: " . $_SERVER["REQUEST_URI"] . "\nGoma-Version: " . GOMA_VERSION . "-" . BUILD_VERSION . "\nApplication: " . print_r(ClassInfo::$appENV["app"], true) . "\n\n\nBacktrace:\n" . print_r(debug_backtrace(), true));
	 	 	}
			
			if(is_object(self::$requestController)) {
				echo self::$requestController->__throwError($code, $name, $message);
				exit;
			} else {
				if(class_exists("ClassInfo", false) && CLASS_INFO_LOADED) {
					$template = new template;
					$template->assign('errcode',convert::raw2text($code));
					$template->assign('errname',convert::raw2text($name));
					$template->assign('errdetails',$message);
					HTTPresponse::sendHeader();
			 		
					
			 		
					echo $template->display('framework/error.html');
				} else {
					header("X-Powered-By: Goma Error-Management under Goma Framework " . GOMA_VERSION . "-" . BUILD_VERSION);
					echo "Code: " . $code . "<br /> Name: " . $name . "<br /> Details: " . $message ;
				}

				exit;
			}
			
			exit;
		}
		
		/**
		 * checks if debug-mode
		 *
		 *@name debug
		 *@access public
		*/
		public static function is_debug() {
			return (Permission::check(10) && isset($_GET["debug"]));
		}
		/**
		 * checks if mobile browser
		 *
		 *@name is_mobile
		 *@access public
		 *@thanks to http://detectmobilebrowser.com/
		*/
		public static function isMobile() {
			$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			if(self::$isMobile && preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)) || isset($_GET["mobile"]))
			{
				if(!isset($_SESSION["nomobile"])) {
					return true;
				}
			}
			
			
			return false;
		}
		
		/**
		 * checks if iPod/iPhone
		 *
		 *@name isiPhone
		 *@access public
		*/
		public static function isiPhone() {
			$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			return (self::$isMobile && preg_match("/(iphone|ipod)/Usi", $useragent) && !isset($_SESSION["nomobile"]));
		}
		
		/**
		 * checks if iPad
		 *
		 *@name isiPad
		 *@access public
		*/
		public static function isiPad() {
			$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			return (self::$isMobile && preg_match("/ipad/Usi", $useragent)  && !isset($_SESSION["nomobile"]));
		}
		
		/**
		 * checks if mobile is available, but not activated, because the user didn't want to see mobile version
		 *
		 *@name isMobileAvailable
		 *@access public
		*/
		public static function isMobileAvailable() {
			$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			if(self::$isMobile && preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)) || isset($_GET["mobile"]))
			{
				return true;
			}
			
			
			return false;
		}
		/**
		 * checks if iPod/iPhone is available, but not activated, because the user didn't want to see mobile version
		 *
		 *@name isiPhoneAvailable
		 *@access public
		*/
		public static function isiPhoneAvailable() {
			$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			return (self::$isMobile && preg_match("/(iphone|ipod)/Usi",$useragent));
		}
		/**
		 * checks if iPad is available, but not activated, because the user didn't want to see mobile version
		 *
		 *@name isiPhoneAvailable
		 *@access public
		*/
		public static function isiPadAvailable() {
			$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			return (self::$isMobile && preg_match("/(ipad/Usi", $useragent));
		}
		/**
		 * disables whole mobile functionallity
		 *
		 *@name disableMobile
		 *@access public
		*/
		public static function disableMobile() {
			self::$isMobile = false;
		}
		/**
		 * enables mobile functionallity
		 *
		 *@name enableMobile
		 *@access public
		*/
		public static function enableMobile() {
			self::$isMobile = true;
		}
		/**
		 * gives back if the current logged in admin want's to be see everything as a simple user
		 *
		 *@name adminAsUser
		 *@access public
		*/
		public static function adminAsUser() {
			return (!defined("IS_BACKEND") && isset($_SESSION["adminAsUser"]));
		}
}

/**
 * represents the dev-mode of goma
 *
 *@name dev
*/
class Dev extends RequestHandler
{
		/**
		 * title of current view
		 *
		 *@name title
		 *@access public
		*/
		public static $title = "Creating new Database";
		
		public $url_handlers = array(
			"build"							=> "builddev",
			"rebuildcaches"					=> "rebuild",
			"flush"							=> "flush",
			"buildDistro/\$name!/\$subname"	=> "buildAppDistro",
			"buildDistro"					=> "buildDistro",
			"cleanUpVersions"				=> "cleanUpVersions",
			"setChmod777"					=> "setChmod777"
		);
		
		public $allowed_actions = array("builddev", 
										"rebuild", 
										"flush", 
										"buildDistro"		 	=> "->isDev",
										"buildAppDistro"		=> "->isDev",
										"buildExpDistro"		=> "->isDev",
										"cleanUpVersions"		=> "->isDev",
										"setChmod777");
										
		/**
		 * runs dev and redirects back to REDIRECT
		 *
		 *@name redirectToDev
		 *@access public
		*/
		public static function redirectToDev() {
			$_SESSION["dev_without_perms"] = true;
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "/dev?redirect=" . getredirect(true));
			exit;
		}
		
		/**
		 * shows dev-site or not 
		*/
		public function handleRequest($request, $subController = false)
		{
				
				define("DEV_CONTROLLER", true);
				
				HTTPResponse::unsetCacheable();
				
				if(!isset($_SESSION["dev_without_perms"]) && !Permission::check("ADMIN"))
				{
					makeProjectAvailable();
					
					throwErrorByID(5);
				}
				
				
				return parent::handleRequest($request, $subController);
				
		}
		
		/**
		 * serves data
		 *
		 *@name serve
		 *@access public
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
		 *@name setChmod777
		 *@access public
		*/
		public function setChmod777() {
			FileSystem::chmod(ROOT, 0777, false);
			return "Okay";
		}
		
		/**
		 * returns if we are in dev-mode
		 *
		 *@name isDev
		 *@access public
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
				$redirect = getRedirect(true);
				header("Location: " . ROOT_PATH . BASE_SCRIPT . "dev/rebuildcaches?redirect=" . urlencode($redirect));
				Core::callHook("onBeforeShutDown");
				exit;
			}
 	 	 return '<h3>Creating new Database</h3>
			<script type="text/javascript">
				setTimeout(function(){ location.href = "'.ROOT_PATH . BASE_SCRIPT.'dev/rebuildcaches"; }, 500);
			</script>
			
			<img src="images/16x16/loading.gif" alt="Loading..." /> Rebuilding Caches... <br /><br />If it doesn\'t reload within 15 seconds, please click <a href="'.ROOT_PATH.'dev/rebuildcaches">here</a>.
			<noscript>Please click <a href="'.ROOT_PATH . BASE_SCRIPT.'dev/rebuildcaches">here</a>.</noscript>';
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
				header("Location: " . ROOT_PATH . BASE_SCRIPT . "dev/builddev?redirect=" . urlencode(getredirect(true)));
				Core::callHook("onBeforeShutDown");
				exit;
			}
			
			return '<h3>Creating new Database</h3>
			<script type="text/javascript">
				setTimeout(function(){ location.href = "'.ROOT_PATH . BASE_SCRIPT.'dev/builddev"; }, 500);
			</script>
			<div><img src="images/success.png" height="16" alt="Loading..." /> Rebuilding Caches...</div>
			<noscript>Please click <a href="'.ROOT_PATH . BASE_SCRIPT.'dev/builddev">here</a>.<br /></noscript>
			<img src="images/16x16/loading.gif"  alt="Loading..." /> Rebuilding Database...<br /><br /> If it doesn\'t reload within 15 seconds, please click <a href="'.ROOT_PATH . BASE_SCRIPT.'dev/builddev">here</a>.';
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
				foreach(classinfo::getChildren("dataobject") as $value)
				{		
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
			if(isset($_GET["redirect"]))
			{
				if(isset($_SESSION["dev_without_perms"])) {
					unset($_SESSION["dev_without_perms"]);
				}
				HTTPResponse::redirect($_GET["redirect"]);
				exit;
			}
			
			
			// redirect if needed
			if(isset($_SESSION["dev_without_perms"])) {
				unset($_SESSION["dev_without_perms"]);
				header("Location: " . ROOT_PATH . "");
				Core::callHook("onBeforeShutDown");
				exit;
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
			
			header("Location: ".ROOT_PATH."");
			Core::callHook("onBeforeShutDown");
			exit;
		}
		
		/**
		 * builds a distributable of the application
		 *
		 *@name buildDistro
		 *@access public
		*/
		public function buildDistro() {
			self::$title = lang("distro_build");
			return g_SoftwareType::listAllSoftware()->renderWith("framework/buildDistro.html");
		}
		
		/**
		 * builds an app-distro
		 *
		 *@name buildAppDistro
		 *@access public
		*/
		public function buildAppDistro($name = null, $subname = null) {
			if(!isset($name)) {
				$name = $this->getParam("name");
			}
			
			if(!isset($subname))
				$subname = $this->getParam("subname");
			
			self::$title = lang("distro_build");
			
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
				exit;
			}
			
			return false;
		}
		
		/**
		 * cleans up versions
		 *
		 *@name cleanUpVersions
		 *@access public
		*/
		public function cleanUpVersions() {
			foreach(ClassInfo::getChildren("DataObject") as $child) {
				if(ClassInfo::getParentClass($child) == "dataobject") {
					$c = new $child;
					if($c->versioned) {
						$log = "";
						$baseTable = ClassInfo::$class_info[$child]["table_name"];
						if(isset(ClassInfo::$database[$child . "_state"])) {
							// first get ids NOT to delete
							
							$recordids = array();
							$ids = array();
							// first recordids
							$sql = "SELECT * FROM ".DB_PREFIX.$child."_state";
							if($result = SQL::Query($sql)) {
								while($row = SQL::fetch_object($result)) {
									$recordids[$row->id] = $row->id;
									$ids[$row->publishedid] = $row->publishedid;
									$ids[$row->stateid] = $row->stateid;
								}
							}
							
							$deleteids = array();
							// now generate ids to delete
							$sql = "SELECT id FROM ".DB_PREFIX . $baseTable." WHERE id NOT IN('".implode("','", $ids)."') OR recordid NOT IN ('".implode("','", $recordids)."')";
							if($result = SQL::Query($sql)) {
								while($row = SQL::fetch_object($result)) {
									$deleteids[] = $row->id;
								}
							}
							
							// now delete
							
							// first generate tables
							$tables = array(ClassInfo::$class_info[$child]["table_name"]);
							foreach(ClassInfo::dataClasses($child) as $class => $table) {
								if($baseTable != $table && isset(ClassInfo::$database[$table])) {
									$tables[] = $table;
								}
							}
							
							foreach($tables as $table) {
								$sql = "DELETE FROM " . DB_PREFIX . $table . " WHERE id IN('".implode("','", $deleteids)."')";
								if(SQL::Query($sql))
									$log .= '<div><img src="images/success.png" height="16" alt="Loading..." /> Delete versions of '.$table.'</div>';
								else
									$log .= '<div><img src="images/16x16/del.png" height="16" alt="Loading..." /> Failed to delete versions of '.$table.'</div>';
							}			
							
	 	 					return '<h3>DB-Cleanup</h3>' . $log;
						}
						
					}
				}
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
function dbescape($str)
{
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
function throwerror($errcode, $errname, $errdetails, $http_status = 500)
{
		HTTPResponse::setResHeader($http_status);
		return Core::throwError($errcode, $errname, $errdetails);
}
/**
* shows an page with error details and nothing else
* data is generated by id
*@name throwErrorById
*@param numeric - errorcode
*@return  null
*/ 
function throwErrorById($code)
{
		$sqlerr = sql::error() . "<br /><br />\n\n <strong>Query:</strong> <br />\n<code>".sql::$last_query."</code>\n";
		$codes = array(
			1 => array('name' => 'Security Error',		 						'details' => ''	,											"status_code"		=> 500),
			2 => array('name' => 'Security Error',		 						'details' => 'Ip banned! Please wait 60 seconds!',			"status_code"		=> 403),
			3 => array('name' => $GLOBALS['lang']['mysql_error_small'],		 'details' => $GLOBALS['lang']['mysql_error'] . $sqlerr,		"status_code"		=> 500),
			4 => array('name' => $GLOBALS['lang']['mysql_connect_error'],		 'details' => $sqlerr,		 								"status_code"		=> 500),
			5 => array('name' => $GLOBALS['lang']['less_rights'],		 		'details' => '',		 									"status_code"		=> 403),
			6 => array('name' => "PHP-Error",		 							'details' => "",		 									"status_code"		=> 500),
			7 => array('name' => 'Service Unavailable',							'details' => 'The Service is currently not available',		"status_code"		=> 503),
		);
		if(isset($codes[$code]))
		{
				
				Core::throwerror($code, $codes[$code]['name'], $codes[$code]['details'], HTTPresponse::setResHeader($codes[$code]["status_code"]));
		} else
		{
				Core::throwerror(6, $codes[6]['name'], $codes[6]['details'], 500);
		}
}

/**
 * logging
 *
 * log an error
 *
 *@name log_error
 *@access public
 *@param string - error-string
*/
function log_error($string)
{
	if(PROFILE) Profiler::mark("log_error");
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/");
	if(isset($GLOBALS["error_logfile"])) {
		$file = $GLOBALS["error_logfile"];	
	} else {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/".date("m-d-y"));
		$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/".date("m-d-y")."/";
		$file = $folder . "1.log";
		$i = 1;
		while(file_exists($folder.$i.".log") && filesize($file) > 10000) {
			$i++;
			$file = $folder.$i.".log";
		}
		$GLOBALS["error_logfile"] = $file;
	}
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	if(!file_exists($file))
	{
			FileSystem::write($file,date($date_format) . ': ' . $string . "\n\n", null, 0777);
	} else
	{
			FileSystem::write($file,date($date_format) . ': ' . $string . "\n\n", FILE_APPEND, 0777);
	}
	
	 if(PROFILE) Profiler::unmark("log_error");
}

/**
 * log things
 *
 *@name logging
 *@access public
 *@param string - log-string
*/
function logging($string)
{
	if(PROFILE) Profiler::mark("logging");
	
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/");
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	if(isset($GLOBALS["log_logfile"])) {
		$file = $GLOBALS["log_logfile"];	
	} else {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/".date("m-d-y"));
		$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/".date("m-d-y")."/";
		$file = $folder . "1.log";
		$i = 1;
		while(file_exists($folder.$i.".log") && filesize($file) > 10000) {
			$i++;
			$file = $folder.$i.".log";
		}
		$GLOBALS["log_logfile"] = $file;
	}
	if(!file_exists($file)) {
		FileSystem::write($file,date($date_format) . ': ' . $string . "\n\n", null, 0777);
	} else {
		FileSystem::write($file,date($date_format) . ': ' . $string . "\n\n", FILE_APPEND, 0777);
	}
	
	if(PROFILE) Profiler::unmark("logging");
}

/**
 * logs debug-information
 *
 * this information may uploaded to the goma-server for debug-use
 *
 *@name debug_log
 *@access public
 *@param string - debug-string
*/
function debug_log($data) {
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/debug/");
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/debug/".date("m-d-y"));
	$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/debug/".date("m-d-y")."/" . date("H_i_s");
	$file = $folder . "-1.log";
	$i = 1;
	while(file_exists($folder. "-" . $i.".log")) {
		$i++;
		$file = $folder. "-" . $i.".log";
	}

	FileSystem::write($file,$data, null, 0777);
}