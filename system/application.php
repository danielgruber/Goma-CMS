<?php
/** >
/*****************************************************************
 * Goma - Open Source Content Management System
 * if you see this text, please install PHP 5.3 or higher        *
 *****************************************************************
  *@package goma framework
  *@subpackage framework loader
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 12.10.2012
  * $Version 2.5.13
*/

error_reporting(E_ALL);

/**
 * first check if we use a good version ;)
 *
 * PHP 5.2 is necessary
*/

if(version_compare(phpversion(), "5.2.0", "<"))
{
		header("HTTP/1.1 500 Server Error");
		echo file_get_contents(dirname(__FILE__) . "/templates/framework/php5.html");
		die();
}

if(!isset($_SERVER["SERVER_NAME"], $_SERVER["DOCUMENT_ROOT"])) {
	die("Goma needs the Server-Vars SERVER_NAME and DOCUMENT_ROOT");
}

if(function_exists("ini_set")) {
	if (!@ini_get('display_errors')) {
		@ini_set('display_errors', 1);
	}
}

if( ini_get('safe_mode') ) {
	define("IN_SAFE_MODE", true);
} else {
	define("IN_SAFE_MODE", false);
}

/* --- */

// some loading

defined("EXEC_START_TIME") OR define("EXEC_START_TIME", microtime(true));
define("IN_GOMA", true);
defined("MOD_REWRITE") OR define("MOD_REWRITE", true);

if(isset($_REQUEST["profile"]) || defined("PROFILE")) {
	require_once(dirname(__FILE__) . '/core/profiler.php');	
	Profiler::init();
	defined("PROFILE") OR define("PROFILE", true);
	Profiler::mark("init");
} else {
	define("PROFILE", false);
}


// check if we are running on nginx without mod_rewrite
if(isset($_SERVER["SERVER_SOFTWARE"]) && preg_match('/nginx/i', $_SERVER["SERVER_SOFTWARE"]) && !MOD_REWRITE) {
	die(file_get_contents(dirname(__FILE__) . "/templates/framework/nginx_no_rewrite.html"));
}

/* --- */

/**
 * default language code
*/
define("DEFAULT_TIMEZONE", "Europe/Berlin");

/**
 * the language-directory
*/
define('LANGUAGE_DIRECTORY','languages/');

/**
 * you shouldn't edit anything below this if you don't know, what you do
*/

define("PHP_MAIOR_VERSION", strtok(PHP_VERSION,"."));
/**
 * root
*/
define('ROOT',realpath(dirname(__FILE__) . "/../") . "/");
define("FRAMEWORK_ROOT", ROOT . "system/");

/**
 * current date
*/
define('DATE', time());

/**
 * TIME
*/
define('TIME', DATE);
define("NOW", DATE);

/**
 * status-constans for config.php
*/
define('STATUS_ACTIVE', 1);
define('STATUS_MAINTANANCE', 2);
define('STATUS_DISABLED', 0);

// version
define("BUILD_VERSION", "049");
define("GOMA_VERSION", "2.0");

// fix for debug_backtrace
defined("DEBUG_BACKTRACE_PROVIDE_OBJECT") OR define("DEBUG_BACKTRACE_PROVIDE_OBJECT", true);

chdir(ROOT);

// require data

if(PROFILE) Profiler::mark("core_requires");

// core
require_once(FRAMEWORK_ROOT . 'core/Object.php');
require_once(FRAMEWORK_ROOT . 'core/ClassManifest.php');
require_once(FRAMEWORK_ROOT . 'core/ClassInfo.php');
require_once(FRAMEWORK_ROOT . 'core/requesthandler.php');
require_once(FRAMEWORK_ROOT . 'libs/file/FileSystem.php');
require_once(FRAMEWORK_ROOT . 'libs/template/tpl.php');
require_once(FRAMEWORK_ROOT . 'libs/http/httpresponse.php');
require_once(FRAMEWORK_ROOT . 'core/Core.php');
require_once(FRAMEWORK_ROOT . 'libs/sql/sql.php');

if(PROFILE) Profiler::unmark("core_requires");

if(file_exists(ROOT . '_config.php'))
{
		
		// load configuration
		// configuration
		require(ROOT . '_config.php');
		
		// define the defined vars in config
		
		if(isset($logFolder)) {
			define("LOG_FOLDER", $logFolder);
		} else {
			writeSystemConfig();
			require(ROOT . '_config.php');
			define("LOG_FOLDER", $logFolder);
		}
		
		define("URLEND", $urlend);
		define("PROFILE_DETAIL", $profile_detail);
		
		define("DEV_MODE", $dev);
		define("BROWSERCACHE", $browsercache);
		
		define('SQL_DRIVER', $sql_driver);
		define("SLOW_QUERY", isset($slowQuery) ? $slowQuery : 50);
		if(isset($defaultLang)) {
			define("DEFAULT_LANG", $defaultLang);
		} else {
			define("DEFAULT_LANG", "de");
		}
		
		if(DEV_MODE) {
			// error-reporting
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ERROR | E_WARNING);
		}
		
		// END define vars
		
		// get temporary a root_path
		$root_path = str_replace("\\","/",substr(__FILE__, 0, -22));
		$root_path = substr($root_path, strlen(realpath($_SERVER["DOCUMENT_ROOT"])));
		
		/*
		 * get the current application
		*/
		if($apps) {
			foreach($apps as $data) {
				$u = $root_path . "selectDomain/" . $data["directory"] . "/";
				if(substr($_SERVER["REQUEST_URI"], 0, strlen($u)) == $u) {
					$application = $data["directory"];
					define("BASE_SCRIPT", "selectDomain/" . $data["directory"] . "/");
					break;
				}
				if(isset($data['domain'])) {
					if(_eregi($data['domain'] . '$', $_SERVER['SERVER_NAME'])) {
						$application = $data["directory"];
						define("DOMAIN_LOAD_DIRECTORY", $data["domain"]);
						break;
					}
				}
			}
			// no app found
			if(!isset($application)) {
				$application = $apps[0]["directory"];
			}
		} else {
			$application = "mysite";
		}
} else {
		$application = "mysite";

		define("URLEND", "/");
		define("PROFILE_DETAIL", false);
		
		define("DEV_MODE", false);
		define("BROWSERCACHE", true);
		
		define('SQL_DRIVER', "mysqli");
		
		define("LOG_FOLDER", "log");
		define("DEFAULT_LANG", "de");
}

define("SYSTEM_TPL_PATH", "system/templates");

// set timezone for security
date_default_timezone_set(DEFAULT_TIMEZONE);

parseUrl();

if(PROFILE) Profiler::unmark("init");

if(!MOD_REWRITE && !preg_match('/nginx/i', $_SERVER["SERVER_SOFTWARE"]) && !file_exists(ROOT . ".htaccess")) {
	$template = 'RewriteEngine on

	RewriteBase '.ROOT_PATH.'
	
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_URI} !^/system/application.php
	RewriteRule (.*) system/application.php [QSA]
	
	
	<IfModule mod_headers.c>
		<FilesMatch ".(jpg|jpeg|png|gif|swf|js|css)$">
			Header set Cache-Control "max-age=86400, public"
		</FilesMatch>
	</IfModule>
	
	<IfModule mod_headers.c>
	  <FilesMatch "\.(gdf|ggz)\.(css|js)$">
	    Header append Vary Accept-Encoding
	  </FilesMatch>
	</IfModule>
	
	AddEncoding x-gzip .ggz
	AddEncoding deflate .gdf
	
	<files *.plist>
		order allow,deny
		deny from all
	</files>
	';
	
	if(!file_put_contents(ROOT . ".htaccess", $template)) {
		die("Could not write .htaccess");
	}
}


loadApplication($application);

/**
 * loads the autoloader for the framework
 *
 *@name loadFramework
 *@access public
*/
function loadFramework() {

	if(defined("CURRENT_PROJECT")) {
		// if we have this directory, we have to install some files
		$directory = CURRENT_PROJECT;
		if(is_dir(ROOT . $directory . "/" . getPrivateKey() . "-install/")) {
			foreach(scandir(ROOT . $directory . "/" . getPrivateKey() . "-install/") as $file) {
				if($file != "." && $file != ".." && is_file(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file)) {
					if(preg_match('/\.sql$/i', $file)) {
						$sqls = file_get_contents(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file);
						
						$sqls = SQL::split($sqls);
						
						foreach($sqls as $sql) {
							$sql = str_replace('{!#PREFIX}', DB_PREFIX, $sql);
							$sql = str_replace('{!#CURRENT_PROJECT}', CURRENT_PROJECT, $sql);
							$sql = str_replace('\n', "\n", $sql);
							
							SQL::Query($sql);
						}
					} else if(preg_match('/\.php$/i', $file)) {
						include_once(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file);
					}
					
					@unlink(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file);
				}
			}
			
			FileSystem::delete(ROOT . $directory . "/" . getPrivateKey() . "-install/");
		}
	} else {
		throwError(6, "PHP-Error", "Calling loadFramework without defined CURRENT_PROJECT is illegal.");
	}

	if(PROFILE) Profiler::mark("loadFramework");
	
	if(PROFILE) Profiler::mark("Manifest");

	ClassInfo::loadfile();
	
	if(PROFILE) Profiler::unmark("Manifest");
		
	// set some object-specific vars
	ClassInfo::setSaveVars("core");
	ClassInfo::setSaveVars("object");
	
	if(PROFILE) Profiler::unmark("loadFramework");
	
	// let's init Core
	Core::Init();
}

/**
 * this function loads an application
 *
 *@name loadApplication
 *@access public
*/
function loadApplication($directory) {
	
	if(is_dir(ROOT . $directory) && file_exists(ROOT . $directory . "/application/application.php")) {
		
		// defines
		define("CURRENT_PROJECT", $directory);
		define("APPLICATION", $directory);
		define("APP_FOLDER", ROOT . $directory . "/");
		defined("APPLICATION_TPL_PATH") OR define("APPLICATION_TPL_PATH", $directory . "/templates");
		defined("CACHE_DIRECTORY") OR define("CACHE_DIRECTORY", $directory . "/temp/");
		defined("UPLOAD_DIR") OR define("UPLOAD_DIR", $directory . "/uploads/");
		
		// cache-directory
		if(!is_dir(ROOT . CACHE_DIRECTORY)) {
			mkdir(ROOT . CACHE_DIRECTORY, 0777, true);
			@chmod(ROOT . CACHE_DIRECTORY, 0777);
		}
		
		// load config
		if(file_exists(ROOT . $directory . "/config.php")) {
			
			
			require(ROOT . $directory . "/config.php");
			
			if(isset($domaininfo["db"])) {
				foreach($domaininfo['db'] as $key => $value)
				{
					$GLOBALS['db' . $key] = $value;
				}
				define('DB_PREFIX',$GLOBALS["dbprefix"]);
			}
			
			define('DATE_FORMAT', $domaininfo['date_format']);
			define("SITE_MODE", $domaininfo["status"]);
			define("PROJECT_LANG", $domaininfo["lang"]);
			
			Core::setCMSVar("TIMEZONE",$domaininfo["timezone"]);
			Core::$site_mode = SITE_MODE;
			
			if(isset($domaininfo["sql_driver"])) {
				define("SQL_DRIVER_OVERRIDE", $domaininfo["sql_driver"]);
			}
			
		} else {
			define("DATE_FORMAT", "d.m.Y - H:i");
			Core::setCMSVar("TIMEZONE", DEFAULT_TIMEZONE);
		}
		
		ClassManifest::$directories[] = $directory . "/code/";
		ClassManifest::$directories[] = $directory . "/application/";
		
		
		if(file_exists(ROOT . "503." . md5(basename($directory)) . ".goma")) {
			if(filemtime(ROOT . "503." . md5(basename($directory)) . ".goma") > NOW - 10) {
				$allowed_ip = file_get_contents(ROOT . "503." . md5(basename($directory)) . ".goma");
				if($_SERVER["REMOTE_ADDR"] != $allowed_ip) {
					$content = file_get_contents(ROOT . "system/templates/framework/503.html");
					$content = str_replace('{BASE_URI}', BASE_URI, $content);
					die($content);
				}
			} else {
				@unlink(ROOT . "503." . md5(basename($directory)) . ".goma");
			}
				
		}
		require(ROOT . $directory . "/application/application.php");
	} else {
		define("PROJECT_LOAD_DIRECTORY", $directory);
		// this doesn't look like an app, load installer
		loadApplication("system/installer");
	}
}

/**
 * returns the array of all registered applications
 *
 *@name ListApplications
 *@access public
*/
function ListApplications() {
	if(file_exists(ROOT . "_config.php")) {
		require(ROOT . "_config.php");
		return $apps;
	} else {
		return array();
	}
}

/**
 * parses the URL, so that we have a clean url
*/
function parseUrl() {

	defined("BASE_SCRIPT") OR define("BASE_SCRIPT", "");
	
	// generate ROOT_PATH
	$root_path = str_replace("\\","/",substr(__FILE__, 0, -22));
	$root_path = substr($root_path, strlen(realpath($_SERVER["DOCUMENT_ROOT"])));
	define('ROOT_PATH',$root_path);
	
	
	// generate URL
	$url = isset($GLOBALS["url"]) ? $GLOBALS["url"] : $_SERVER["REQUEST_URI"];
	$url = urldecode($url); // we should do this, because the url is not correct else
	if(preg_match('/\?/',$url))
	{
			$url = substr($url, 0, strpos($url,'?') );
	} else
	{
			$url = $url;
	}
	$url = substr($url, strlen(ROOT_PATH . BASE_SCRIPT));
	// parse URL
	if(substr($url, 0, 1) == "/")
			$url = substr($url, 1);
	if(preg_match('/^(.*)'.preg_quote(URLEND, "/").'$/Usi', $url, $matches))
	{
			$url = $matches[1];
	}
	$url = str_replace('//','/', $url);
	
	define("URL", $url);
	
	
	// generate BASE_URI
	$http = (isset($_SERVER["HTTPS"])) ? "https" : "http";
	$port = $_SERVER["SERVER_PORT"];
	if($http == "http" && $port == 80){
		$port = "";
	} else if($http == "https" && $port == 443){
		$port = "";
	} else {
		$port = ":" . $port;
	}
	
	if($_SERVER["HTTP_HOST"] != $_SERVER["SERVER_NAME"]) {
		header("Location: " . $http.'://'.$_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"]);
		exit;
	}
	
	define("BASE_URI",$http.'://'.$_SERVER["SERVER_NAME"] . $port . ROOT_PATH);
}

/**
  * this loads a lang file in the languages-directory
  *@name loadlang
  *@param string - name of the file
  *@param string - subdirectory
  *@return null
  */
function loadlang($name = "lang", $dir = "")
{   
		i18n::addLang($dir . '/' . $name);
}


/**
 * generates a random string
 *@name randomString
 *@param numeric - length of the string
 *@param bool - if numbers are allowed
 *@return string
*/
function randomString($len, $numeric = true) {
		$possible = "ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnpqrstuvwxyz";
		if($numeric === true) {
			$possible .= "123456789";
		}
		$s = "";
		for($i = 0; $i < $len; $i++)
		{
				$s .= $possible{mt_rand(0, strlen($possible) - 1)};
		}
		return $s;
}

/**
 * language 
 *@name l
 *@access public
 *@param string - name
 *@param string - default
*/
function lang($name, $default = "")
{
		return nl2br(isset($GLOBALS["lang"][$name]) ? $GLOBALS["lang"][$name] : $default);
}

/**
 * Permission-provider
*/
interface PermissionProvider
{
		public function providePermissions();
}

interface PermProvider {
	public function providePerms();
}

/**
 * right management
*/

/**
 * checks if a group have the rights
 *@name advrights
 *@param string - name of the rights
 *@param string - name of group
 *@return bool
*/
function advrights($name, $rang) {
		return Permission::advrights($name, $rang);	
}

/**
  * checks rights
  *@name right
  *@param string - right
*/
function right($r) {
		return Permission::check($r);
}

/**
 * Merges any number of arrays / parameters recursively, replacing 
 * entries with string keys with values from latter arrays. 
 * If the entry or the next value to be assigned is an array, then it 
 * automagically treats both arguments as an array.
 * Numeric entries are appended, not replaced, but only if they are 
 * unique
 *
 * calling: result = array_merge_recursive_distinct(a1, a2, ... aN)
 *
 * thanks to mark dot roduner at gmail dot com
 *
 *@link http://php.net/manual/de/function.array-merge-recursive.php
**/
function array_merge_recursive_distinct () {
	$arrays = func_get_args();
	$base = array_shift($arrays);
	if(!is_array($base)) 
		$base = empty($base) ? array() : array($base);
	foreach($arrays as $append) {
		if(!is_array($append)) 
			$append = array($append);
		foreach($append as $key => $value) {
			if(!array_key_exists($key, $base) and !is_numeric($key)) {
				$base[$key] = $append[$key];
				continue;
			}
			if(is_array($value) or is_array($base[$key])) {
				$base[$key] = array_merge_recursive_distinct($base[$key], $append[$key]);
			} else if(is_numeric($key)) {
				if(!in_array($value, $base)) 
					$base[] = $value;
			} else {
				$base[$key] = $value;
			}
		}
	}
	return $base;
}

/**
 * if you want to store very much data in the session, session will slow down page dramatically, so we store just a key and then store it external in a file
 *@name session_store
 *@access public
 *@param string - key
 *@param data
*/
function session_store($key, $data) {
	if(isset($_SESSION["store"][$key]))
		$random = $_SESSION["store"][$key];
	else
		$random = randomString(10);
	// create file
	FileSystem::write(ROOT . CACHE_DIRECTORY . "data." . $random . ".goma", serialize($data), null, 0773);
	unset($file);
	$_SESSION["store"][$key] = $random;
	return true;
}

/**
 * gets data from session-store
 *
 *@name session_restore
 *@access public
 *@param string - key
*/
function session_restore($key) {
	if(isset($_SESSION["store"][$key]))
		if(file_exists(ROOT . CACHE_DIRECTORY . "data." . $_SESSION["store"][$key] . ".goma"))
			return unserialize(file_get_contents(ROOT . CACHE_DIRECTORY . "data." . $_SESSION["store"][$key] . ".goma"));
		else 
			return false;	
	else
		return false;
}

/**
 * checks if a store exists
 *
 *@name session_store_exists
 *@access public
 *@param string - key
*/
function session_store_exists($key) {
	if(isset($_SESSION["store"][$key]))
		if(file_exists(ROOT . CACHE_DIRECTORY . "data." . $_SESSION["store"][$key] . ".goma"))
			return true;
		else
			return false;
	else
		return false;
}

/**
 * checks session-store by storeid
 *
 *@name session_restore_byID
*/ 
function session_store_exists_byID($id) {
	$id = basename($id);
	return file_exists(ROOT . CACHE_DIRECTORY . "data." . $id . ".goma");
}

/**
 * gets session-store by storeid
 *
 *@name session_store_exists_byID
*/ 
function session_restore_byID($id) {
	$id = basename($id);
	if(file_exists(ROOT . CACHE_DIRECTORY . "data." . $id . ".goma")) {
		return unserialize(file_get_contents(ROOT . CACHE_DIRECTORY . "data." . $id . ".goma"));
	} else {
		return false;
	}
}

/**
 * bind's a session key to the id
 *
 *@name bindSessionKeyToID
*/ 
function bindSessionKeyToID($id, $key) {
	if(session_store_exists_byID($id)) {
		$_SESSION["store"][$key] = $id;
		return true;
	} else {
		return false;
	}
}

/**
 * gets the id for the session-store
 *
 *@name getStoreID
*/
function getStoreID($key) {
	if(isset($_SESSION["store"][$key])) 
		return $_SESSION["store"][$key];
	else
		return false;
}

/**
 * returns a redirect-uri for inserting into and uri or elsewhere
 *
 *@name getRedirect
 *@access public
*/
function getRedirect($parentDir = false) {
	if(Core::is_ajax() && isset($_SERVER["HTTP_X_REFERER"])) {
		return $_SERVER["HTTP_X_REFERER"];
	}
	if($parentDir) {
		if(isset($_GET["redirect"])) {
			return $_GET["redirect"];
		/*} else if(isset($_POST["redirect"])) {
			return $_POST["redirect"];
		*/} else {
			if(URLEND == "/") {
				$uri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/"));
				return substr($uri, 0, strrpos($uri, "/")) . URLEND;
			} else {
				return substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/")) . URLEND;
			}
		}
	} else {
		if(isset($_GET["redirect"])) {
			return $_GET["redirect"];
		/*} else if(isset($_POST["redirect"])) {
			return $_POST["redirect"];
		*/} else {
			return $_SERVER["REQUEST_URI"];
		}
	}
}

function getRedirection($parentDir = true) {
	if($parentDir) {
		if(isset($_GET["redirect"])) {
			return $_GET["redirect"];
		} else if(isset($_POST["redirect"])) {
			return $_POST["redirect"];
		} else {
			if(URLEND == "/") {
				$uri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/"));
				return substr($uri, 0, strrpos($uri, "/")) . URLEND;
			} else {
				return substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/")) . URLEND;
			}
		}
	} else {
		if(isset($_GET["redirect"])) {
			return $_GET["redirect"];
		} else if(isset($_POST["redirect"])) {
			return $_POST["redirect"];
		} else {
			return BASE_URI . BASE_SCRIPT;
		}
	}
}

/**
 * generates a translated date
 *
 *@name goma_date
 *@access public
*/
function goma_date($format, $date = NOW) {

	$str = date($format, $date);
	
	require(ROOT . LANGUAGE_DIRECTORY . Core::getCMSVar("lang") . "/calendar.php");

	$str = str_replace(array_keys($calendar), array_values($calendar), $str);
	return $str;
}

function makeProjectUnavailable($project = APPLICATION) {
	if(!file_put_contents(ROOT . "503.".md5(basename($project)).".goma", $_SERVER["REMOTE_ADDR"])) {
		die("Could not make project unavailable.");
	}
	chmod(ROOT . "503.".md5(basename($project)).".goma", 0777);
}

function makeProjectAvailable($project = APPLICATION) {
	if(file_exists(ROOT . "503.".md5(basename($project)).".goma")) {
		@unlink(ROOT . "503.".md5(basename($project)).".goma");
	}
}

function isProjectUnavailable($project = APPLICATION) {
	return (file_exists(ROOT . "503.".md5(basename($project)).".goma") && filemtime(ROOT . "503." . md5(basename($project)) . ".goma") > NOW - 10);
}

/**
 * rewrites the Application-Configuration
 *
 *@name writeSystemConfig
 *@access public
*/
function writeSystemConfig($data = array()) {
	
	// first set defaults
	$apps = array();
	$sql_driver = "mysqli";
	$dev = false;
	$urlend = "/";
	$profile_detail = false;
	$logFolder = "log_" . randomString(5);
	$privateKey = randomString(15);
	$browsercache = true;
	$defaultLang = defined("DEFAULT_LANG") ? DEFAULT_LANG : "de";
	$slowQuery = 50;
	
	if(file_exists(ROOT . "_config.php"))
		include(ROOT . "_config.php");
	
	foreach($data as $key => $val) {
		if(isset($$key))
			$$key = $val;
	}
	
	if(!isset($SSLprivateKey) || !isset($SSLpublicKey) || !$SSLprivateKey || !$SSLpublicKey) {
		// generate new key-pair
		// Create the keypair
		$res = openssl_pkey_new();

		// Get private key
		openssl_pkey_export($res, $SSLprivateKey);

		// Get public key
		$SSLpublicKey = openssl_pkey_get_details($res);
		$SSLpublicKey = $SSLpublicKey["key"];
	}
	
	$contents = file_get_contents(FRAMEWORK_ROOT . "core/samples/config_main.sample.php");
	preg_match_all('/\{([a-zA-Z0-9_]+)\}/Usi', $contents, $matches);
	foreach($matches[1] as $name) {
		if(isset($$name))
			$contents = str_replace('{'.$name.'}', var_export($$name, true), $contents);
		else
			$contents = str_replace('{'.$name.'}', var_export("", true), $contents);
	}
	
	if(@file_put_contents(ROOT . "_config.php", $contents)) {
		@chmod(ROOT . "_config.php", 0600);
		return true;
	} else
		throwError(6, 'PHP-Error', "Could not write System-Config. Please apply Permissions 0777 to /_config.php");
}

/**
 * rewrites the project-config
 *
 *@name writeProjectConfig
 *@access public
*/
function writeProjectConfig($data = array(), $project = CURRENT_PROJECT) {
	
	$config = $project . "/config.php";
	
	if(file_exists($config)) {
		// get current data
		include($config);
		$defaults = (array) $domaininfo;
	} else {
		$defaults = array
		(
			"status" 		=> 1,
			"date_format"	=> "d.m.Y - H:i",
			"timezone"		=> DEFAULT_TIMEZONE,
			"lang"			=> DEFAULT_LANG
		);
	}
	
	$new = array_merge($defaults, $data);
	$info = array();
	$info["status"] = $new["status"];
	$info["date_format"] = $new["date_format"];
	$info["timezone"] = $new["timezone"];
	$info["lang"] = $new["lang"];
	
	if(isset($new["db"]))
		$info["db"] = $new["db"];
	
	if(defined("SQL_DRIVER_OVERRIDE") && !isset($info["sql_driver"])) {
		$info["sql_driver"] = SQL_DRIVER_OVERRIDE;
	}
	
	
	$config_content = file_get_contents(FRAMEWORK_ROOT . "core/samples/config_locale.sample.php");
	$config_content = str_replace('{info}', var_export($info, true), $config_content);
	$config_content = str_replace('{folder}', $project, $config_content);
	if(@file_put_contents($config, $config_content)) {
		@chmod($config, 0600);
		return true;
	} else {
		die("6: Could not write Project-Config");
	}
}

/**
 * gets the private Key
 *
 *@name getPrivateKey
 *@access public
*/
function getPrivateKey() {
	if(!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}
	
	include(ROOT . "_config.php");
	
	return $privateKey;
}

/**
 * gets the SSL-public Key
 *
 *@name getPublicKey
 *@access public
*/
function getSSLPublicKey() {
	if(!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}
	
	include(ROOT . "_config.php");
	
	return $SSLpublicKey;
}

/**
 * gets the SSL-private Key
 *
 *@name getPublicKey
 *@access public
*/
function getSSLPrivateKey() {
	if(!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}
	
	include(ROOT . "_config.php");
	
	return $SSLprivateKey;
}


/**
 * project-management
 *
*/

/**
 * sets a project-folder in the project-stack
 *
 *@name setProject
 *@access public
*/
function setProject($project, $domain = null) {
	if(file_exists(ROOT . "_config.php")) {
		include(ROOT . "_config.php");
	} else {
		$apps = array();
	}
	
	$app = array(
		"directory"	=> $project
	);
	if(isset($domain)) {
		$app["domain"] = $domain;
	}
	
	// first check existing
	foreach($apps as $key => $data) {
		if($data["directory"] == $app["directory"]) {
			if(!isset($app["domain"]) || (isset($data["domain"]) && $data["domain"] == $app["domain"])) {
				return true;
			} else {
				$apps[$key]["domain"] = $app["domain"];
				return writeSystemConfig(array("apps" => $apps));
			}
		}
	}
	$apps[] = $app;
	
	return writeSystemConfig(array("apps" => $apps));
}

/**
 * removes a given project from project-stack
 *
 *@name removeProject
 *@access public
*/
function removeProject($project) {
	if(file_exists(ROOT . "_config.php")) {
		include(ROOT . "_config.php");
	} else {
		return true;
	}
	
	foreach($apps as $key => $data) {
		if($data["directory"] == $project) {
			unset($apps[$key]);
		}
	}
	
	$apps = array_values($apps);
	
	return writeSystemConfig(array("apps" => $apps));
}

// alias for setProject
function addProject($project, $domain = null) {
	return setProject($project, $domain);
}


/**
 * @url http://de3.php.net/manual/en/function.intval.php#79766
*/
function str2int($string, $concat = true) {
    $length = strlen($string);    
    for ($i = 0, $int = '', $concat_flag = true; $i < $length; $i++) {
        if (is_numeric($string[$i]) && $concat_flag) {
            $int .= $string[$i];
        } elseif(!$concat && $concat_flag && strlen($int) > 0) {
            $concat_flag = false;
        }        
    }
    
    return (int) $int;
}

/**
 * this parses lanuage variables in a string, e.g. {$_lang_imprint}
 *@name parse_lang
 *@param string - the string to parse
 *@param array - a array of variables in the lanuage like %e%
 *@return string - the parsed string
*/
function parse_lang($str, $arr = array())
{
		return preg_replace('/\{\$_lang_(.*)\}/Usie' , "''.var_lang('\\1').''" , $str);  // find lang vars
}

/**
 * parses the %e% in the string
 *@name var_lang
 *@param string - the name of the languagevar
 *@param array - the array of variables
 *@return string - the parsed string
*/
function var_lang($str, $replace = array())
{
		$language = lang($str, "");
		preg_match_all('/%(.*)%/',$language,$regs);
		foreach($regs[1] as $key => $value)
		{
				$re = $replace[$value];
				$language = preg_replace("/%".preg_quote($value,'/')."%/",$re,$language);
		}

		return $language;   // return it!!
}

/**
* the function ereg with preg_match
*@name _ereg
*@params: view php manual of ereg
*/
function _ereg($pattern, $needed, &$reg = "")
{
		if(is_array($needed)) {
			return false;
		}
		return preg_match('/'.str_replace('/','\\/',$pattern).'/',$needed, $reg);
}

/**
* the function eregi with preg_match
*@name _eregi
*@params: view php manual of eregi
*/
function _eregi($pattern, $needed, &$reg = "")
{
		return preg_match('/'.str_replace('/','\\/',$pattern).'/i',$needed, $reg);
}

/**
 * checks of the file-extension
 *
 *@name checkFileExt
 *@access public
*/
function checkFileExt($string, $ext) {
	return (strtolower(substr($string, 0 - strlen($ext) - 1)) == "." . $ext);
}

/**
 * escapes a string to use it in json
 *@name escapejson
 *@param string - string to escape
 *@return string - escaped string
*/
function escapejson($str)
{
		$str = convert::raw2js($str);
		$str = utf8_encode($str);
		return $str;
}

/**
 * shows a normals site with given content
 *@name showSite
 *@access public
 *@param string - content
 *@param string - title
*/
function showsite($content, $title)
{
	if($title) {
		Core::setTitle($title);
	}
		
	return Core::serve($content);
}