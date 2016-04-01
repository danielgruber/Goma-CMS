<?php
/**
 * Main file of Goma-CMS.
 * 
 * @package Goma\System
 * 
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * 
 * @version 2.6.11
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_NOTICE);

/*
 * first check if we use a good version ;)
 *
 * PHP 5.4 is necessary
 */

if (version_compare(phpversion(), "5.4.0", "<")) {
	header("HTTP/1.1 500 Server Error");
	echo file_get_contents(dirname(__FILE__) . "/templates/framework/php5.html");
	die();
}

if (!isset($_SERVER["SERVER_NAME"], $_SERVER["DOCUMENT_ROOT"])) {
	die("Goma needs the Server-Vars SERVER_NAME and DOCUMENT_ROOT");
}

if (function_exists("ini_set")) {
	if (!@ini_get('display_errors')) {
		@ini_set('display_errors', 1);
	}
}

if (ini_get('safe_mode')) {
	define("IN_SAFE_MODE", true);
} else {
	define("IN_SAFE_MODE", false);
}

/* --- */

// some loading

defined("EXEC_START_TIME") OR define("EXEC_START_TIME", microtime(true));
define("IN_GOMA", true);
defined("MOD_REWRITE") OR define("MOD_REWRITE", true);

if (isset($_REQUEST["profile"]) || defined("PROFILE")) {
	require_once (dirname(__FILE__) . '/core/profiler.php');
	Profiler::init();
	defined("PROFILE") OR define("PROFILE", true);
} else {
	define("PROFILE", false);
}

// check if we are running on nginx without mod_rewrite
if (isset($_SERVER["SERVER_SOFTWARE"]) && preg_match('/nginx/i', $_SERVER["SERVER_SOFTWARE"]) && !MOD_REWRITE) {
	header("HTTP/1.1 500 Server Error");
	die(file_get_contents(dirname(__FILE__) . "/templates/framework/nginx_no_rewrite.html"));
}

// check if we are running without mod-php-xml
if (!class_exists("DOMDocument")) {
	header("HTTP/1.1 500 Server Error");
	die(file_get_contents(dirname(__FILE__) . "/templates/framework/no_php_xml.html"));
}

/* --- */

/**
 * default language code
 */
define("DEFAULT_TIMEZONE", "Europe/Berlin");

/**
 * the language-directory
 */
define('LANGUAGE_DIRECTORY', 'languages/');

/**
 * you shouldn't edit anything below this if you don't know, what you do
 */

define("PHP_MAIOR_VERSION", strtok(PHP_VERSION, "."));
/**
 * root
 */
define('ROOT', realpath(dirname(__FILE__) . "/../") . "/");
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
 * status-constants for config.php
 */
define('STATUS_ACTIVE', 1);
define('STATUS_MAINTANANCE', 2);
define('STATUS_DISABLED', 0);

// version
define("GOMA_VERSION", "2.0RC5");
define("BUILD_VERSION", "121");

// fix for debug_backtrace
defined("DEBUG_BACKTRACE_PROVIDE_OBJECT") OR define("DEBUG_BACKTRACE_PROVIDE_OBJECT", true);

chdir(ROOT);

define("GOMA_FREE_SPACE", 100000000000);

// require data

if (PROFILE)
	Profiler::mark("core_requires");

// core
require_once (FRAMEWORK_ROOT . 'core/applibs.php');
require_once (FRAMEWORK_ROOT . 'core/CoreLibs/StaticsManager.php');
require_once (FRAMEWORK_ROOT . 'core/Object.php');
require_once (FRAMEWORK_ROOT . 'core/CoreLibs/GlobalSessionManager.php');
require_once (FRAMEWORK_ROOT . 'core/ClassManifest.php');
require_once (FRAMEWORK_ROOT . 'core/ClassInfo.php');
require_once (FRAMEWORK_ROOT . 'core/controller/RequestHandler.php');
require_once (FRAMEWORK_ROOT . 'libs/file/FileSystem.php');
require_once (FRAMEWORK_ROOT . 'libs/template/tpl.php');
require_once (FRAMEWORK_ROOT . 'libs/http/httpresponse.php');
require_once (FRAMEWORK_ROOT . 'core/Core.php');
require_once (FRAMEWORK_ROOT . 'core/controller/Director.php');
require_once (FRAMEWORK_ROOT . 'security/ISessionManager.php');
require_once (FRAMEWORK_ROOT . 'core/CoreLibs/CacheManager.php');
require_once (FRAMEWORK_ROOT . 'libs/sql/sql.php');


if (PROFILE)
	Profiler::unmark("core_requires");



// set error-handler
set_error_handler("Goma_ErrorHandler");

set_exception_handler("Goma_ExceptionHandler");

if (file_exists(ROOT . '_config.php')) {

	// load configuration
	// configuration
	require (ROOT . '_config.php');

	// define the defined vars in config

	if (isset($logFolder)) {
		define("LOG_FOLDER", $logFolder);
	} else {
		writeSystemConfig();
		require (ROOT . '_config.php');
		define("LOG_FOLDER", $logFolder);
	}

	define("URLEND", $urlend);
	define("PROFILE_DETAIL", $profile_detail);

	define("DEV_MODE", $dev);
	define("BROWSERCACHE", $browsercache);

	define('SQL_DRIVER', $sql_driver);
	define("SLOW_QUERY", isset($slowQuery) ? $slowQuery : 50);
	if (isset($defaultLang)) {
		define("DEFAULT_LANG", $defaultLang);
	} else {
		define("DEFAULT_LANG", "de");
	}

	if (DEV_MODE) {
		// error-reporting
		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_NOTICE);
	} else {
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ClassManifest::addUnitTest();
	}

	// END define vars

	// get a temporary root_path
	$root_path = str_replace("\\", "/", substr(__FILE__, 0, -22));
	$root_path = substr($root_path, strlen(realpath($_SERVER["DOCUMENT_ROOT"])));

	/*
	 * get the current application
	 */
	if ($apps) {
		foreach ($apps as $data) {
			$u = $root_path . "selectDomain/" . $data["directory"] . "/";
			if (substr($_SERVER["REQUEST_URI"], 0, strlen($u)) == $u) {
				$application = $data["directory"];
				define("BASE_SCRIPT", "selectDomain/" . $data["directory"] . "/");
				break;
			}
			if (isset($data['domain'])) {
				if (preg_match('/' . str_replace($data['domain'], '/', '\\/') . '$/i', $_SERVER['SERVER_NAME'])) {
					$application = $data["directory"];
					define("DOMAIN_LOAD_DIRECTORY", $data["directory"]);

					break;
				}
			}
		}
		// no app found
		if (!isset($application)) {
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

define("URL", parseUrl());

if (!file_exists(ROOT . ".htaccess") && !file_exists(ROOT . "web.config")) {
	writeServerConfig();
}

// some hacks for changes in .htaccess
if (file_exists(ROOT . ".htaccess") && !strpos(file_get_contents(".htaccess"), "ErrorDocument 404")) {
	if (!file_put_contents(ROOT . ".htaccess", "\nErrorDocument 404 " . ROOT_PATH . "system/application.php", FILE_APPEND)) {
		die("Could not write .htaccess");
	}
}

if (file_exists(ROOT . ".htaccess") && !strpos(file_get_contents(".htaccess"), "ErrorDocument 500")) {
	if (!file_put_contents(ROOT . ".htaccess", "\nErrorDocument 500 " . ROOT_PATH . "system/templates/framework/500.html", FILE_APPEND)) {
		die("Could not write .htaccess");
	}
}

if (file_exists(ROOT . ".htaccess") && (strpos(file_get_contents(".htaccess"), " system"))) {
	$contents = file_get_contents(ROOT . ".htaccess");
	$contents = str_replace(' system', ' ' . ROOT_PATH . "system", $contents);
	if (!file_put_contents(ROOT . ".htaccess", $contents)) {
		die("Could not write .htaccess");
	}
	unset($contents);
}

loadApplication($application);

/**
 * loads the autoloader for the framework
 *
 *@name loadFramework
 *@access public
 */
function loadFramework() {

	if (defined("CURRENT_PROJECT")) {
		// if we have this directory, we have to install some files
		$directory = CURRENT_PROJECT;
		if (is_dir(ROOT . $directory . "/" . getPrivateKey() . "-install/")) {
			foreach (scandir(ROOT . $directory . "/" . getPrivateKey() . "-install/") as $file) {
				if ($file != "." && $file != ".." && is_file(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file)) {
					if (preg_match('/\.sql$/i', $file)) {
						$sqls = file_get_contents(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file);

						$sqls = SQL::split($sqls);

						foreach ($sqls as $sql) {
							$sql = str_replace('{!#PREFIX}', DB_PREFIX, $sql);
							$sql = str_replace('{!#CURRENT_PROJECT}', CURRENT_PROJECT, $sql);
							$sql = str_replace('\n', "\n", $sql);

							SQL::Query($sql);
						}
					} else if (preg_match('/\.php$/i', $file)) {
						include_once (ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file);
					}

					@unlink(ROOT . $directory . "/" . getPrivateKey() . "-install/" . $file);
				}
			}

			FileSystem::delete(ROOT . $directory . "/" . getPrivateKey() . "-install/");
		}
	} else {
		throw new Exception("Calling loadFramework() without defined CURRENT_PROJECT is illegal.");
	}

	if (PROFILE)
		Profiler::mark("Manifest");

	Core::InitCache();
	ClassInfo::loadfile();

	if (PROFILE)
		Profiler::unmark("Manifest");

	Director::Init();
	Core::Init();
}

/**
 * this function loads an application
 *
 *@name loadApplication
 *@access public
 */
function loadApplication($directory) {

	if (is_dir(ROOT . $directory) && file_exists(ROOT . $directory . "/application/application.php")) {

		// defines
		define("CURRENT_PROJECT", $directory);
		define("APPLICATION", $directory);
		define("APP_FOLDER", ROOT . $directory . "/");
		defined("APPLICATION_TPL_PATH") OR define("APPLICATION_TPL_PATH", $directory . "/templates");
		defined("CACHE_DIRECTORY") OR define("CACHE_DIRECTORY", $directory . "/temp/");
		defined("UPLOAD_DIR") OR define("UPLOAD_DIR", $directory . "/uploads/");

		// cache-directory
		if (!is_dir(ROOT . CACHE_DIRECTORY)) {
			mkdir(ROOT . CACHE_DIRECTORY, 0777, true);
			@chmod(ROOT . CACHE_DIRECTORY, 0777);
		}

		// load config
		if (file_exists(ROOT . $directory . "/config.php")) {

			require (ROOT . $directory . "/config.php");

			if (isset($domaininfo["db"])) {
				foreach ($domaininfo['db'] as $key => $value) {
					$GLOBALS['db' . $key] = $value;
				}
				define('DB_PREFIX', $GLOBALS["dbprefix"]);
			}

			$domaininfo['date_format_date'] = isset($domaininfo['date_format_date']) ? $domaininfo['date_format_date'] : "d.m.Y";
			$domaininfo['date_format_time'] = isset($domaininfo['date_format_time']) ? $domaininfo['date_format_time'] : "H:i";

			FileSystem::$safe_mode = isset($domaininfo["safe_mode"]) ? $domaininfo["safe_mode"] : false;

			define('DATE_FORMAT', $domaininfo['date_format_date'] . " - " . $domaininfo['date_format_time']);
			define('DATE_FORMAT_DATE', $domaininfo['date_format_date']);
			define('DATE_FORMAT_TIME', $domaininfo['date_format_time']);
			define("SITE_MODE", $domaininfo["status"]);
			define("PROJECT_LANG", $domaininfo["lang"]);

			Core::setCMSVar("TIMEZONE", $domaininfo["timezone"]);
			Core::$site_mode = SITE_MODE;

			if (isset($domaininfo["sql_driver"])) {
				define("SQL_DRIVER_OVERRIDE", $domaininfo["sql_driver"]);
			}

		} else {
			define("DATE_FORMAT", "d.m.Y - H:i");
			Core::setCMSVar("TIMEZONE", DEFAULT_TIMEZONE);
		}

		ClassManifest::$directories[] = $directory . "/code/";
		ClassManifest::$directories[] = $directory . "/application/";

		if(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"], basename($directory))) {
			$content = file_get_contents(ROOT . "system/templates/framework/503.html");
			$content = str_replace('{BASE_URI}', BASE_URI, $content);
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			header('Retry-After: 10');
			die($content);
		}

		require (ROOT . $directory . "/application/application.php");
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
	if (file_exists(ROOT . "_config.php")) {
		require (ROOT . "_config.php");
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
	$root_path = str_replace("\\", "/", substr(__FILE__, 0, -22));
	$root_path = substr($root_path, strlen(realpath($_SERVER["DOCUMENT_ROOT"])));
	define('ROOT_PATH', $root_path);

	// generate BASE_URI
	$http = (isset($_SERVER["HTTPS"])) && $_SERVER["HTTPS"] != "off" ? "https" : "http";
	$port = $_SERVER["SERVER_PORT"];
	if ($http == "http" && $port == 80) {
		$port = "";
	} else if ($http == "https" && $port == 443) {
		$port = "";
	} else {
		$port = ":" . $port;
	}

	define("BASE_URI", $http . '://' . $_SERVER["SERVER_NAME"] . $port . ROOT_PATH);

	// generate URL
	$url = isset($GLOBALS["url"]) ? $GLOBALS["url"] : $_SERVER["REQUEST_URI"];
	$url = urldecode($url);
	// we should do this, because the url is not correct else
	if (preg_match('/\?/', $url)) {
		$url = substr($url, 0, strpos($url, '?'));
	}

	$url = substr($url, strlen(ROOT_PATH . BASE_SCRIPT));

	// parse URL
	if (substr($url, 0, 1) == "/")
		$url = substr($url, 1);

	// URL-END
	if (preg_match('/^(.*)' . preg_quote(URLEND, "/") . '$/Usi', $url, $matches)) {
		$url = $matches[1];
	} else if ($url != "" && !Core::is_ajax() && !preg_match('/\.([a-zA-Z]+)$/i', $url) && count($_POST) == 0) {
		// enforce URLEND
		$get = "";
		$i = 0;
		foreach ($_GET as $k => $v) {
			if ($i == 0)
				$i++;
			else
				$get .= "&";

			$get .= urlencode($k) . "=" . urlencode($v);
		}

		if ($get) {
			header("Location: " . BASE_URI . BASE_SCRIPT . $url . URLEND . "?" . $get);
		} else {
			header("Location: " . BASE_URI . BASE_SCRIPT . $url . URLEND);
		}
		exit ;
	}

	$url = str_replace('//', '/', $url);

	if(isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == 404 && MOD_REWRITE == true && !preg_match('/nginx/i', $_SERVER["SERVER_SOFTWARE"])) {
		header("Location: " . BASE_URI . "index.php/" . $url);
		exit;
	}

	return $url;
}
