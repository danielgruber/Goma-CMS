<?php defined("IN_GOMA") OR die();
/**
 * This file provides necessary functions for Goma.
 *
 * @package Goma\System\Core
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version 1.0.5
 */

/**
 * Load a language file from /languages.
 *
 * @param string $name Filename
 * @param string $directory Subdirectory
 *
 * @return void
 */
function loadlang($name = "lang", $directory = "") {
	i18n::addLang($directory . '/' . $name);
}

/**
 * Generates a random string.
 *
 * @param int $length Length of the string.
 * @param boolean $numeric Are numbers allowed?
 *
 * @return string
 */
function randomString($length, $numeric = true) {
	$possible = "ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnpqrstuvwxyz";
	if($numeric === true) {
		$possible .= "123456789";
	}
	$s = "";
	for($i = 0; $i < $length; $i++) {
		$s .= $possible{mt_rand(0, strlen($possible) - 1)};
	}
	return $s;
}

/**
 * Looks up for a localized version of a string.
 *
 * @param string $name Name identifier for the string.
 * @param string $default Default value for non existent localized strings.
 *
 * @return string Localized string or $default
 */
function lang($name, $default = "") {
	$name = strtoupper($name);
	if(isset($GLOBALS["lang"][$name])) {
		$lang = $GLOBALS["lang"][$name];
	} else if($default) {
		$lang = $default;
	} else {
		$lang = $name;
	}

	if(!strpos($lang, ">\n") && !strpos($lang, "</")) {
		return nl2br($lang);
	} else {
		return $lang;
	}
}

/**
 * Merges arrays recursive.
 *
 * Merges any number of arrays / parameters recursively, replacing
 * entries with string keys with values from latter arrays.
 * If the entry or the next value to be assigned is an array, then it
 * automagically treats both arguments as an array.
 * Numeric entries are appended, not replaced, but only if they are
 * unique.
 *
 * @author mark dot roduner at gmail dot com
 * @link http://php.net/manual/de/function.array-merge-recursive.php
 *
 * @return array
 **/
function array_merge_recursive_distinct() {
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
 * Stores session data in a file.
 *
 * Because storing many data in a session is slow, the data is stored in a file.
 * This data can be accessed with an ID, that is stored in the session instead.
 *
 * @see session_restore() to restore data from a session.
 *
 * @param string $key Data identification key
 * @param mixed $data The data, that has to be stored.
 */
function session_store($key, $data) {
	GlobalSessionManager::globalSession()->set($key, $data);
}

/**
 * Accesses session data.
 *
 * Because storing many data in a session is slow, the data is stored in a file.
 * This data can be accessed with an ID, that is stored in the session instead.
 *
 * @see session_restore() to store data in a session.
 *
 * @param string $key Data identification key
 *
 * @return mixed Data on success, otherwise null.
 */
function session_restore($key) {
	return GlobalSessionManager::globalSession()->get($key);
}

/**
 * Checks for a key, if he is linked with session data.
 *
 * @param string $key Data identification key
 *
 * @return boolean
 */
function session_store_exists($key) {
	return GlobalSessionManager::globalSession()->hasKey($key);
}

/**
 * Gets the redirect.
 *
 * @param boolean $parentDir Get only the name of the parent directory in the
 * url.
 *
 * @return string
 */
function getRedirect($parentDir = false, $controller = null) {
	// AJAX Request
	if(Core::is_ajax() && isset($_SERVER["HTTP_X_REFERER"])) {
		return htmlentities($_SERVER["HTTP_X_REFERER"], ENT_COMPAT, "UTF-8", false);
	}

	if($parentDir) {

		if(isset($_GET["redirect"]) && $_GET["redirect"]) {
			return htmlentities($_GET["redirect"], ENT_COMPAT, "UTF-8", false);
		} else if(isset($controller)) {
			return htmlentities(ROOT_PATH . BASE_SCRIPT . $controller->originalNamespace, ENT_COMPAT, "UTF-8", false);
		} else if(isset(Director::$requestController)) {
			return htmlentities(ROOT_PATH . BASE_SCRIPT . Director::$requestController->originalNamespace, ENT_COMPAT, "UTF-8", false);
		} else {
			// TODO What is with redirect from other sites with other URLEND?
			if(URLEND == "/") {
				$uri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/"));
				return htmlentities(substr($uri, 0, strrpos($uri, "/")) . URLEND, ENT_COMPAT, "UTF-8", false);
			} else {
				return htmlentities(substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/")) . URLEND, ENT_COMPAT, "UTF-8", false);
			}

		}

	} else {

		if(isset($_GET["redirect"]) && $_GET["redirect"]) {
			return htmlentities($_GET["redirect"], ENT_COMPAT, "UTF-8", false);
		} else if(isset($controller)) {
			return htmlentities(ROOT_PATH . BASE_SCRIPT . $controller->originalNamespace, ENT_COMPAT, "UTF-8", false);
		} else if(isset(Director::$requestController)) {
			return htmlentities(ROOT_PATH . BASE_SCRIPT . Director::$requestController->originalNamespace, ENT_COMPAT, "UTF-8", false);
		} else {
			return htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8", false);
		}

	}
}

function protectUrl($url) {
	if(preg_match('/^(http|https|ftp)\:\/\/(.*)/i', $url, $matches)) {
		$server = $_SERVER["SERVER_NAME"];
		if(strtolower(substr($matches[2], 0, strlen($server))) != strtolower($server)) {
			return "";
		} else {
			if(strtolower($matches[2]) != $server && strtolower(substr($matches[2], 0, strlen($server) + 1)) != strtolower($server) . "/") {
				return "";
			}
		}
	}

	return $url;
}

/**
 * generates a translated date.
 */
function goma_date($format, $date = NOW) {

	$str = date($format, $date);

	require (ROOT . LANGUAGE_DIRECTORY . Core::getCMSVar("lang") . "/calendar.php");

	$str = str_replace(array_keys($calendar), array_values($calendar), $str);
	return $str;
}

/**
 * Places a file in the application folder, that indicates Goma, that this
 * project is unavailable.
 *
 * @see makeProjectAvailable() to enable projects.
 * @see isProjectUnavailable() to check, if a project is disabled.
 *
 * @param string $project Name of the project, default is the current
 * application.
 *
 * @return void
 */
function makeProjectUnavailable($project = APPLICATION, $ip = null) {
	$ip = isset($ip) ? $ip : $_SERVER["REMOTE_ADDR"];
	if(!file_put_contents(ROOT . $project . "/503.goma", $ip, LOCK_EX)) {
		die("Could not make project unavailable.");
	}
	chmod(ROOT . $project . "/503.goma", 0777);
}

/**
 * Removes the project unavailable file, that indicates Goma, that this project
 * is unavailable.
 *
 * @see makeProjectUnavailable() to disable projects.
 * @see isProjectUnavailable() to check, if a project is disabled.
 *
 * @param string $project Name of the project, default is the current
 * application.
 *
 * @return void
 */
function makeProjectAvailable($project = APPLICATION) {
	if(file_exists(ROOT . $project . "/503.goma")) {
		@unlink(ROOT . $project . "/503.goma");
	}
}

/**
 * Checks, if a project is unavailable.
 *
 * @see makeProjectUnavailable() to disable projects.
 * @see makeProjectAvailable() to enable projects.
 *
 * @param string $project Name of the project, default is the current
 * application.
 *
 * @return void
 */
function isProjectUnavailable($project = APPLICATION) {
	return (file_exists(ROOT . $project . "/503.goma") && filemtime(ROOT . $project . "/503.goma") > NOW - 10);
}

function isProjectUnavailableForIP($ip, $project = APPLICATION) {
	return isProjectUnavailable($project) && file_get_contents(ROOT . $project . "/503.goma") != $ip;
}


/**
 * Writes the system configuration.
 *
 * @see writeProjectConfig() to write the config for a project.
 *
 * @param array[] $data An array with configuration variables.
 *
 * @return void
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
	$SSLpublicKey = null;
	$SSLprivateKey = null;

	if(file_exists(ROOT . "_config.php"))
		include (ROOT . "_config.php");

	foreach($data as $key => $val) {
		if(isset($$key))
			$$key = $val;
	}

	$contents = file_get_contents(FRAMEWORK_ROOT . "core/samples/config_main.sample.php");
	preg_match_all('/\{([a-zA-Z0-9_]+)\}/Usi', $contents, $matches);
	foreach($matches[1] as $name) {
		if(isset($$name))
			$contents = str_replace('{' . $name . '}', var_export($$name, true), $contents);
		else
			$contents = str_replace('{' . $name . '}', var_export("", true), $contents);
	}

	if(@file_put_contents(ROOT . "_config.php", $contents, LOCK_EX)) {
		@chmod(ROOT . "_config.php", 0644);
		return true;
	} else {
		throw new LogicException("Could not write System-Config. Please apply Permissions 0777 to /_config.php");
	}
}

/**
 * Returns the SSL public key of the installation.
 *
 * @return string SSL public key
 */
function getSSLPublicKey() {
	if(!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}

	include (ROOT . "_config.php");

	return $SSLpublicKey;
}

/**
 * Returns the SSL private key of the installation.
 *
 * @return string SSL private key
 */
function getSSLPrivateKey() {
	if(!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}

	include (ROOT . "_config.php");

	return $SSLprivateKey;
}

/**
 * Writes the config of a project.
 *
 * @see writeSystemConfig() to write the system config.
 *
 * @param array[] $data An array with configuration variables.
 * @param string $project Name of the project, default is CURRENT_PROJECT.
 *
 * @return void
 */
function writeProjectConfig($data = array(), $project = CURRENT_PROJECT) {

	$config = $project . "/config.php";

	if(file_exists($config)) {
		// get current data
		include ($config);
		$defaults = (array)$domaininfo;
	} else {
		$defaults = array(
			"status" => 1,
			"date_format_date" => "d.m.Y",
			"date_format_time"	=> " H:i",
			"timezone" => DEFAULT_TIMEZONE,
			"lang" => DEFAULT_LANG,
			"safe_mode"	=> false
		);
	}

	$new = array_merge($defaults, $data);
	$info = array();
	$info["status"] = $new["status"];
	
	if(isset($new["date_format_date"]))
		$info["date_format_date"] = $new["date_format_date"];
		
	if(isset( $new["date_format_time"]))
		$info["date_format_time"] = $new["date_format_time"];
	
	$info["timezone"] = $new["timezone"];
	$info["lang"] = $new["lang"];

	$info["safe_mode"] = (bool)(isset($new["safe_mode"]) ? $new["safe_mode"] : false);

	if(isset($new["db"]))
		$info["db"] = $new["db"];

	if(defined("SQL_DRIVER_OVERRIDE") && !isset($info["sql_driver"])) {
		$info["sql_driver"] = SQL_DRIVER_OVERRIDE;
	}

	$config_content = file_get_contents(FRAMEWORK_ROOT . "core/samples/config_locale.sample.php");
	$config_content = str_replace('{info}', var_export($info, true), $config_content);
	$config_content = str_replace('{folder}', $project, $config_content);
	if(@file_put_contents($config, $config_content, LOCK_EX)) {
		@chmod($config, 0644);
		return true;
	} else {
		die("6: Could not write Project-Config '" . $config . "'. Please set Permissions to 0777!");
	}
}

/**
 * Gets the private key of the installation.
 *
 * @return string 15 chars private key
 */
function getPrivateKey() {
	if(!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}

	include (ROOT . "_config.php");

	return $privateKey;
}

/******************** project management ********************/

/**
 * sets a project-folder in the project-stack
 *
 *@name setProject
 *@access public
 */
function setProject($project, $domain = null) {
	if(file_exists(ROOT . "_config.php")) {
		include (ROOT . "_config.php");
	} else {
		$apps = array();
	}

	$app = array("directory" => $project);
	if(isset($domain)) {
		$app["domain"] = $domain;
	}

	// first check existing
	foreach($apps as $key => $data) {
		if($data["directory"] == $app["directory"]) {
			if(!isset($app["domain"]) || (isset($data["domain"]) && $data["domain"] == $app["domain"])) {

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
		include (ROOT . "_config.php");
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
	for($i = 0, $int = '', $concat_flag = true; $i < $length; $i++) {
		if(is_numeric($string[$i]) && $concat_flag) {
			$int .= $string[$i];
		} else if(!$concat && $concat_flag && strlen($int) > 0) {
			$concat_flag = false;
		}
	}

	return (int)$int;
}

/**
 * this parses lanuage variables in a string, e.g. {$_lang_imprint}
 *@name parse_lang
 *@param string - the string to parse
 *@param array - a array of variables in the lanuage like %e%
 *@return string - the parsed string
 */
function parse_lang($str, $arr = array()) {
	return preg_replace_callback('/\{\$_lang_(.*)\}/Usi', "var_lang_callback", $str);
	// find lang vars
}

function var_lang_callback($data) {
	return var_lang($data[1]);
}

/**
 * parses the %e% in the string
 *@name var_lang
 *@param string - the name of the languagevar
 *@param array - the array of variables
 *@return string - the parsed string
 */
function var_lang($str, $replace = array()) {
	if(!is_string($str))
		throw new LogicException("first argument of var_lang must be string.");
	
	$language = lang($str, $str);
	preg_match_all('/%(.*)%/', $language, $regs);
	foreach($regs[1] as $key => $value) {

		$re = $replace[$value];
		$language = preg_replace("/%" . preg_quote($value, '/') . "%/", $re, $language);
	}

	return $language;
	// return it!!
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
function escapejson($str) {
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
function showsite($content, $title) {
	if($title) {
		Core::setTitle($title);
	}

	return Core::serve($content);
}

/**
 * in goma we now compare version and buildnumber seperate
 *
 * @name goma_version_compare
 * @access public
 * @return bool|int
 */
function goma_version_compare($v1, $v2, $operator = null) {
	// first split version
	if(strpos($v1, "-") !== false) {
		$version1 = substr($v1, 0, strpos($v1, "-"));
		$build1 = substr($v1, strpos($v1, "-") + 1);
	} else {
		$version1 = $v1;
	}

	if(strpos($v2, "-") !== false) {
		$version2 = substr($v2, 0, strpos($v2, "-"));
		$build2 = substr($v2, strpos($v2, "-") + 1);
	} else {
		$version2 = $v2;
	}

	if(!isset($build1) || !isset($build2)) {
		return version_compare($version1, $version2, $operator);
	}

	if(isset($operator)) {
		switch($operator) {
			case "gt":
			case ">":
				return version_compare($build1, $build2, ">");
				break;
			case "lt":
			case "<":
				return version_compare($build1, $build2, "<");
				break;
			case "eq":
			case "=":
			case "==":
				if(version_compare($version1, $version2, "==") && version_compare($build1, $build2, "==")) {
					return true;
				}
				return false;
				break;
			case ">=":
			case "ge":
				return version_compare($build1, $build2, ">=");
				break;
			case "<=":
			case "le":
				return version_compare($build1, $build2, "<=");
				break;
			case "!=":
			case "<>":
			case "ne":
				return version_compare($build1, $build2, "<>");
				break;
		}
	} else {
		if(version_compare($build1, $build2, ">")) {
			return 1;
		} else if(version_compare($build1, $build2, "==")) {
			return 0;
		} else {
			return -1;
		}
	}

	return false;
}

/**
 * PHP-Error-Handdling
 */
//!PHP-Error-Handling

function Goma_ErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	switch ($errno) {
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_PARSE:
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			HTTPResponse::setResHeader(500);
			HTTPResponse::sendHeader();
			log_error("PHP-USER-Error: " . $errno . " " . $errstr . " in " . $errfile . " on line " . $errline . ".");
			$content = file_get_contents(ROOT . "system/templates/framework/phperror.html");
			$content = str_replace('{BASE_URI}', BASE_URI, $content);
			$content = str_replace('{$errcode}', 6, $content);
			$content = str_replace('{$errname}', "PHP-Error $errno", $content);
			$content = str_replace('{$errdetails}', $errstr . " on line $errline in file $errfile", $content);
			$content = str_replace('$uri', $_SERVER["REQUEST_URI"], $content);
			echo $content;
			exit ;
			break;

		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			if(strpos($errstr, "chmod") === false && strpos($errstr, "unlink") === false) {
				log_error("PHP-USER-Warning: " . $errno . " " . $errstr . " in " . $errfile . " on line " . $errline . ".");
				if(DEV_MODE && !isset($_GET["ajax"]) && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest")) {
					echo "<b>WARNING:</b> [$errno] $errstr in $errfile on line $errline<br />\n";
				}
			}
			break;
		case E_USER_NOTICE:
		case E_NOTICE:
		case E_USER_NOTICE:
			if(strpos($errstr, "chmod") === false && strpos($errstr, "unlink") === false) {
				logging("Notice: [$errno] $errstr in $errfile on line $errline");
				if(DEV_MODE && !isset($_GET["ajax"]) && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"))
					echo "<b>NOTICE:</b> [$errno] $errstr in $errfile on line $errline<br />\n";
			}
			break;
		case E_STRICT:
			// nothing
			break;
		default:
			HTTPResponse::setResHeader(500);
			HTTPResponse::sendHeader();
			log_error("PHP-Error: " . $errno . " " . $errstr . " in " . $errfile . " on line " . $errline . ".");
			$content = file_get_contents(ROOT . "system/templates/framework/phperror.html");
			$content = str_replace('{BASE_URI}', BASE_URI, $content);
			$content = str_replace('{$errcode}', 6, $content);
			$content = str_replace('{$errname}', "PHP-Error: " . $errno, $content);
			$content = str_replace('{$errdetails}', $errstr . " on line $errline in file $errfile", $content);
			$content = str_replace('$uri', $_SERVER["REQUEST_URI"], $content);
			echo $content;
			exit ;
	}

	// block PHP's internal Error-Handler
	return true;
}

function Goma_ExceptionHandler($exception) {
	log_exception($exception);

	$content = file_get_contents(ROOT . "system/templates/framework/phperror.html");
	$content = str_replace('{BASE_URI}', BASE_URI, $content);
	$content = str_replace('{$errcode}', $exception->getCode(), $content);
	$content = str_replace('{$errname}', get_class($exception), $content);
	$content = str_replace('{$errdetails}', $exception->getMessage() . "\n<br />\n<br />\n<textarea style=\"width: 100%; height: 300px;\">" . $exception->getTraceAsString() . "</textarea>", $content);
	$content = str_replace('$uri', $_SERVER["REQUEST_URI"], $content);

	if(Object::method_exists($exception, "http_status")) {
		HTTPResponse::setResHeader($exception->http_status());
	} else {
		HTTPResponse::setResHeader(500);
	}
	HTTPResponse::sendHeader();

	echo $content;
	exit ;
}


function log_exception(Exception $exception) {
	$message = get_class($exception) . " " . $exception->getCode() . ":\n\n" . $exception->getMessage() . "\n\n Backtrace: " . $exception->getTraceAsString();
	log_error($message);
	
	$debugMsg = "URL: " . $_SERVER["REQUEST_URI"] . "\nGoma-Version: " . GOMA_VERSION . "-" . BUILD_VERSION . "\nApplication: " . print_r(ClassInfo::$appENV, true) . "\n\n" . $message;
	debug_log($debugMsg);
}

//!Logging

/**
 * logging
 *
 * log an error
 *
 *@name log_error
 *@access public
 *@param string - error-string
 */
function log_error($string) {
	if(PROFILE)
		Profiler::mark("log_error");
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/");
	if(isset($GLOBALS["error_logfile"])) {
		$file = $GLOBALS["error_logfile"];
	} else {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/" . date("m-d-y"));
		$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/" . date("m-d-y") . "/";
		$file = $folder . "1.log";
		$i = 1;
		while(file_exists($folder . $i . ".log") && filesize($file) > 10000) {
			$i++;
			$file = $folder . $i . ".log";
		}
		$GLOBALS["error_logfile"] = $file;
	}
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	if(!file_exists($file)) {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", null, 0777);
	} else {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", FILE_APPEND, 0777);
	}

	if(PROFILE)
		Profiler::unmark("log_error");
}

/**
 * log things
 *
 *@name logging
 *@access public
 *@param string - log-string
 */
function logging($string) {
	if(PROFILE)
		Profiler::mark("logging");

	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/");
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	if(isset($GLOBALS["log_logfile"])) {
		$file = $GLOBALS["log_logfile"];
	} else {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/" . date("m-d-y"));
		$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/" . date("m-d-y") . "/";
		$file = $folder . "1.log";
		$i = 1;
		while(file_exists($folder . $i . ".log") && filesize($file) > 10000) {
			$i++;
			$file = $folder . $i . ".log";
		}
		$GLOBALS["log_logfile"] = $file;
	}
	if(!file_exists($file)) {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", null, 0777);
	} else {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", FILE_APPEND, 0777);
	}

	if(PROFILE)
		Profiler::unmark("logging");
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
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/debug/" . date("m-d-y"));
	$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/debug/" . date("m-d-y") . "/" . date("H_i_s");
	$file = $folder . "-1.log";
	$i = 1;
	while(file_exists($folder . "-" . $i . ".log")) {
		$i++;
		$file = $folder . "-" . $i . ".log";
	}

	FileSystem::write($file, $data, null, 0777);
}

/**
 * checks for available retina-file on file-path.
 *
 * @param file
*/
function RetinaPath($file) {
	$retinaPath = substr($file, 0, strrpos($file, ".")) . "@2x." . substr($file, strpos($file, ".") + 1);
	if(file_exists($retinaPath))
		return $retinaPath;
	
	return $file;
}

/**
 * Writes the server configuration file
 *@name writeServerConfig
 *@access public
 */
function writeServerConfig() {
	if(strpos($_SERVER["SERVER_SOFTWARE"], "Apache") !== false) {
		$file = "htaccess";
		$toFile = ".htaccess";
	} else if(strpos($_SERVER["SERVER_SOFTWARE"], "IIS") !== false) {
		$file = "web.config";
		$toFile = "web.config";
	} else {
		return;
	}

	require (ROOT . "system/resources/" . $file . ".php");


	if(!file_put_contents(ROOT . $toFile, $serverconfig, FILE_APPEND | LOCK_EX)) {
		die("Could not write " . $file);
	}
}

function GUID()
{
	if (function_exists('com_create_guid') === true)
	{
		return trim(com_create_guid(), '{}');
	}

	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

/**
 * returns all http-headers.
 */
if (!function_exists('getallheaders'))
{
	function getallheaders()
	{
		$headers = '';
		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}

class SQLException extends Exception {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::DB_CONNECT_ERROR, Exception $previous = null) {
		$sqlerr = SQL::errno() . ": " . sql::error() . "<br /><br />\n\n <strong>Query:</strong> <br />\n<code>" . sql::$last_query . "</code>\n";
		$m = $sqlerr . "\n" . $m;
		parent::__construct($m, $code, $previous);
	}

}

class MySQLException extends SQLException {
}

class SecurityException extends Exception {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::SECURITY_ERROR, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}

}

class PermissionException extends Exception {

    /**
     * which permission is missing.
     *
     * @var string
     */
    protected $missingPerm;

    /**
     * constructor.
     * @param string $m
     * @param int $code
     * @param string $missingPerm
     * @param Exception $previous
     */
	public function __construct($m = "", $code = ExceptionManager::PERMISSION_ERROR, $missingPerm = null, Exception $previous = null) {
        $this->missingPerm = $missingPerm;
		parent::__construct($m, $code, $previous);
	}

    public function getMissingPerm() {
        return $this->missingPerm;
    }

}

class PHPException extends Exception {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::PHP_ERROR, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}

}

class DBConnectError extends MySQLException {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::DB_CONNECT_ERROR, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}

}

class ServiceUnavailable extends Exception {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::SERVICE_UNAVAILABLE, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}

	public function http_status() {
		return 503;
	}

}
