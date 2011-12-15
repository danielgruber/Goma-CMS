<?php
/**
/*****************************************************************
 * Goma - Open Source Content Management System
 * if you see this text, please install PHP 5.2 or higher        *
 *****************************************************************
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 03.12.2011
  * $Version 005
*/

/**
 * first check if we use a good version ;)
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

error_reporting(E_ALL); // default
if (!@ini_get('display_errors')) {
	@ini_set('display_errors', 1);
}
			


/* --- */

// some loading

$start = microtime(true);
define("IN_GOMA", true);
defined("MOD_REWRITE") OR define("MOD_REWRITE", true);
defined("BASE_SCRIPT") OR define("BASE_SCRIPT", "");

require_once(dirname(__FILE__) . '/core/profiler.php');

if(isset($_REQUEST["profile"]) || defined("PROFILE"))
{
		Profiler::init();
		defined("PROFILE") OR define("PROFILE", true);
		Profiler::mark("init");
} else
{
		define("PROFILE", false);
}

/* --- */

chdir(substr(__FILE__, 0, -22));

/* --- */

// generate ROOT_PATH
$root_path = str_replace("\\","/",substr(__FILE__, 0, -22));
$root_path = preg_replace('/^(.*)'.preg_quote(substr($_SERVER["DOCUMENT_ROOT"], -5), '/').'(.*)$/', '/\\2', $root_path);
// if root_path is like that: //cms/, we will make it to that: /cms/
if(preg_match("/^\/\/.*$/",$root_path))
{
		$root_path = substr($root_path, 1);
}

define('ROOT_PATH',$root_path);



/* --- */

// sessids
if(isset($_GET["PHPSESSID"]))
{
		session_id($_GET["PHPSESSID"]);
}

if(isset($_POST["PHPSESSID"]))
{
		session_id($_POST["PHPSESSID"]);
}

// now init session
if(PROFILE) Profiler::mark("session");
session_start();
if(PROFILE) Profiler::unmark("session");

/**
 * default language code
*/
define("DEFAULT_LANG", "de");
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
define("BUILD_VERSION", "017");
define("GOMA_VERSION", "2.0.0");

// fix for debug_backtrace
defined("DEBUG_BACKTRACE_PROVIDE_OBJECT") OR define("DEBUG_BACKTRACE_PROVIDE_OBJECT", true);

// require data

if(PROFILE) Profiler::mark("core_requires");


// core
require_once(FRAMEWORK_ROOT . 'core/Object.php');
require_once(FRAMEWORK_ROOT . 'core/requesthandler.php');
require_once(FRAMEWORK_ROOT . 'libs/file/FileSystem.php');
require_once(FRAMEWORK_ROOT . 'libs/template/tpl.php');
require_once(FRAMEWORK_ROOT . 'libs/http/httpresponse.php');
require_once(FRAMEWORK_ROOT . 'libs/globalfunctions/text.php');
require_once(FRAMEWORK_ROOT . 'core/Core.php');
require_once(FRAMEWORK_ROOT . 'libs/sql/sql.php');

if(PROFILE) Profiler::unmark("core_requires");
// get base-uri

$http = (isset($_SERVER["HTTPS"])) ? "https" : "http";
$port = $_SERVER["SERVER_PORT"];
if($http == "http" && $port == 80){
	$port = "";
} else if($http == "https" && $port == 443){
	$port = "";
} else {
	$port = ":" . $port;
}
if(BASE_SCRIPT != "") {
	define("BASE_URI",$http.'://'.$_SERVER["SERVER_NAME"] . $port . ROOT_PATH );
} else {
	define("BASE_URI",$http.'://'.$_SERVER["SERVER_NAME"] . $port . ROOT_PATH);
}

if(file_exists(ROOT . '_config.php'))
{
		

		
		// configuration
		require_once(ROOT . '_config.php');

		define("URLEND", $urlend);
		define("PROFILE_DETAIL", $profile_detail);
		
		define("DEV_MODE", $dev);
		define("BROWSERCACHE", $browsercache);
		
		
		define('SQL_DRIVER', $sql_driver);
		
		
		if(DEV_MODE) {
			// error-reporting
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ERROR | E_WARNING);
		}
		
		/*
		 * get the current application
		*/
		if($apps)
		{
				foreach( $apps as $data )
				{
						if(isset($data['domain']))
						{
								if(_eregi($data['domain'] . '$', $_SERVER['SERVER_NAME']))
								{
										$application = $data["directory"];
										break;
								}
						}
				}
				// no app found
				if(!isset($application))
				{
						$application = $apps[0]["directory"];
				}
		} else
		{
				$application = "mysite";

		}
		

		
} else
{
		$application = "mysite";

		define("URLEND", "/");
		define("PROFILE_DETAIL", false);
		
		define("DEV_MODE", false);
		define("BROWSERCACHE", true);
		
		
		define('SQL_DRIVER', "mysqli");
}



define("SYSTEM_TPL_PATH", "system/templates");

// set timezone for security
date_default_timezone_set("Europe/Berlin");

parseUrl();


if(PROFILE) Profiler::mark("preloads");

// global-functions
// autoload
require_once(FRAMEWORK_ROOT . "core/viewaccessabledata.php");
require_once(FRAMEWORK_ROOT . "core/Extension.php");	
require_once(FRAMEWORK_ROOT . "core/DataObject/DataObject.php");	
require_once(FRAMEWORK_ROOT . "core/autoloader.php");
require_once(FRAMEWORK_ROOT . "libs/javascript/gloader.php");
require_once(FRAMEWORK_ROOT . "libs/globalfunctions/content.php");
require_once(FRAMEWORK_ROOT . "libs/cache/cacher.php");
require_once(FRAMEWORK_ROOT.  "core/fields/DBField.php");

if(PROFILE) Profiler::unmark("preloads");

if(PROFILE) Profiler::unmark("init");

loadApplication($application);

/**
 * loads the autoloader for the framework
 *
 *@name loadFramework
 *@access public
*/
function loadFramework() {

	if(PROFILE) Profiler::mark("loadFramework");
	
	if(PROFILE) Profiler::mark("ClassInfo");
	
	ClassInfo::loadfile();
	
	if(PROFILE) Profiler::unmark("ClassInfo");
	
	require_once(FRAMEWORK_ROOT . 'core/resources.php');
	require_once(FRAMEWORK_ROOT . 'core/request.php');
	require_once(FRAMEWORK_ROOT . "config.php");
	
	// set some object-specific vars
	ClassInfo::setSaveVars("core");
	ClassInfo::setSaveVars("object");
	ClassInfo::setSaveVars("dataobject");
	ClassInfo::setSaveVars("tpl");
	ClassInfo::setSaveVars("resources");
	
	// load the autoloader
	$autoloader = new autoloader();
	
	if(PROFILE) Profiler::unmark("loadFramework");
		
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
		
		if(!is_dir(ROOT . CACHE_DIRECTORY))
			mkdir(ROOT . CACHE_DIRECTORY, 0777, true);
		
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
			$GLOBALS['lang'] = $domaininfo['lang'];
			define("PROJECT_LANG", $domaininfo["lang"]);
			Core::setCMSVar("TIMEZONE",$domaininfo["timezone"]);
			define("SITE_MODE", $domaininfo["status"]);
			Core::$site_mode = SITE_MODE;
			
			if(isset($domaininfo["sql_driver"])) {
				define("SQL_DRIVER_OVERRIDE", $domaininfo["sql_driver"]);
			}
		} else {
			define("DATE_FORMAT", "d.m.Y - H:i");
			Core::setCMSVar("TIMEZONE", DEFAULT_TIMEZONE);
		}
		
		Autoloader::$directories[] = $directory . "/code/";
		Autoloader::$directories[] = $directory . "/application/";
		
		
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
 * parses the URL, so that we have a clean url
*/
function parseUrl() {
	/* --- */

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
	
	$url = substr($url, strlen(ROOT_PATH));
	
	
	// parse URL
	if(substr($url, 0, 1) == "/")
			$url = substr($url, 1);
	
	if(preg_match('/^(.*)'.preg_quote(URLEND, "/").'$/Usi', $url, $matches))
	{
			$url = $matches[1];
	}
	$url = str_replace('//','/', $url);
	define("URL", $url);
	unset($url);
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
		return isset($GLOBALS["lang"][$name]) ? $GLOBALS["lang"][$name] : $default;
}

/**
 * Permission-provider
*/
interface PermissionProvider
{
		public function providePermissions();
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
function advrights($name, $rang)
{
		return Permission::advrights($name, $rang);	
}
/**
  * checks rights
  *@name right
  *@param string - right
*/
function right($r)
{
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
  if(!is_array($base)) $base = empty($base) ? array() : array($base);
  foreach($arrays as $append) {
    if(!is_array($append)) $append = array($append);
    foreach($append as $key => $value) {
      if(!array_key_exists($key, $base) and !is_numeric($key)) {
        $base[$key] = $append[$key];
        continue;
      }
      if(is_array($value) or is_array($base[$key])) {
        $base[$key] = array_merge_recursive_distinct($base[$key], $append[$key]);
      } else if(is_numeric($key)) {
        if(!in_array($value, $base)) $base[] = $value;
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
				$uri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/")) . URLEND;
				return substr($uri, 0, strrpos($uri, "/"));
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
 * rewrites the project-config
 *
 *@name writeProjectConfig
 *@access public
*/
function writeProjectConfig($data, $project = CURRENT_PROJECT) {
	
	$config = $project . "/config.php";
	
	if(file_exists($config)) {
		// get current data
		include($config);
		$defaults = array
		(
			"status" 		=> $domaininfo["status"],
			"date_format"	=> $domaininfo["date_format"],
			"timezone"		=> $domaininfo["timezone"],
			"lang"			=> $domaininfo["lang"]
		);
		if(isset($domaininfo["db"]))
			$defaults["db"] = $domaininfo["db"];
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
	return file_put_contents($config, $config_content);
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