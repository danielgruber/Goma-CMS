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
 *@Copyright (C) 2009 - 2013  Goma-Team
 * last modified: 27.02.2013
 * $Version 1.0
*/
 
defined("IN_GOMA") OR die("");

/**
 * this loads a lang file in the languages-directory
 *@name loadlang
 *@param string - name of the file
 *@param string - subdirectory
 *@return null
 */
function loadlang($name = "lang", $dir = "") {
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
	if ($numeric === true) {
		$possible .= "123456789";
	}
	$s = "";
	for ($i = 0; $i < $len; $i++) {
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
	$lang = isset($GLOBALS["lang"][$name]) ? $GLOBALS["lang"][$name] : $default;
    if(!strpos($lang, ">\n") && !strpos($lang, "</")) {
        return nl2br($lang);
    } else {
        return $lang;
    }
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
function array_merge_recursive_distinct() {
	$arrays = func_get_args();
	$base = array_shift($arrays);
	if (!is_array($base))
		$base = empty($base) ? array() : array($base);
	foreach ($arrays as $append) {
		if (!is_array($append))
			$append = array($append);
		foreach ($append as $key => $value) {
			if (!array_key_exists($key, $base) and !is_numeric($key)) {
				$base[$key] = $append[$key];
				continue;
			}
			if (is_array($value) or is_array($base[$key])) {
				$base[$key] = array_merge_recursive_distinct($base[$key], $append[$key]);
			} else if (is_numeric($key)) {
				if (!in_array($value, $base))
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
	if (isset($_SESSION["store"][$key]))
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
	if (isset($_SESSION["store"][$key]))
		if (file_exists(ROOT . CACHE_DIRECTORY . "data." . $_SESSION["store"][$key] . ".goma"))
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
	if (isset($_SESSION["store"][$key]))
		if (file_exists(ROOT . CACHE_DIRECTORY . "data." . $_SESSION["store"][$key] . ".goma"))
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
	if (file_exists(ROOT . CACHE_DIRECTORY . "data." . $id . ".goma")) {
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
	if (session_store_exists_byID($id)) {
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
	if (isset($_SESSION["store"][$key]))
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
	if (Core::is_ajax() && isset($_SERVER["HTTP_X_REFERER"])) {
		return htmlentities($_SERVER["HTTP_X_REFERER"], ENT_COMPAT, "UTF-8", false);
	}
	if ($parentDir) {
		if (isset($_GET["redirect"])) {
			return htmlentities($_GET["redirect"], ENT_COMPAT, "UTF-8", false);
			/*} else if(isset($_POST["redirect"])) {
			 return $_POST["redirect"];
			 */
		} else {
			if (URLEND == "/") {
				$uri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/"));
				return htmlentities(substr($uri, 0, strrpos($uri, "/")) . URLEND, ENT_COMPAT, "UTF-8", false);
			} else {
				return htmlentities(substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/")) . URLEND, ENT_COMPAT, "UTF-8", false);
			}
		}
	} else {
		if (isset($_GET["redirect"])) {
			return htmlentities($_GET["redirect"], ENT_COMPAT, "UTF-8", false);
			/*} else if(isset($_POST["redirect"])) {
			 return $_POST["redirect"];
			 */
		} else {
			return htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8", false);
		}
	}
}

function getRedirection($parentDir = true) {
	if ($parentDir) {
		if (isset($_GET["redirect"])) {
			return convert::raw2text($_GET["redirect"]);
		} else if (isset($_POST["redirect"])) {
			return convert::raw2text($_POST["redirect"]);
		} else {
			if (URLEND == "/") {
				$uri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/"));
				return convert::raw2text(substr($uri, 0, strrpos($uri, "/")) . URLEND);
			} else {
				return convert::raw2text(substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/")) . URLEND);
			}
		}
	} else {
		if (isset($_GET["redirect"])) {
			return convert::raw2text($_GET["redirect"]);
		} else if (isset($_POST["redirect"])) {
			return convert::raw2text($_POST["redirect"]);
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

	require (ROOT . LANGUAGE_DIRECTORY . Core::getCMSVar("lang") . "/calendar.php");

	$str = str_replace(array_keys($calendar), array_values($calendar), $str);
	return $str;
}

function makeProjectUnavailable($project = APPLICATION) {
	if (!file_put_contents(ROOT . "503." . md5(basename($project)) . ".goma", $_SERVER["REMOTE_ADDR"])) {
		die("Could not make project unavailable.");
	}
	chmod(ROOT . "503." . md5(basename($project)) . ".goma", 0777);
}

function makeProjectAvailable($project = APPLICATION) {
	if (file_exists(ROOT . "503." . md5(basename($project)) . ".goma")) {
		@unlink(ROOT . "503." . md5(basename($project)) . ".goma");
	}
}

function isProjectUnavailable($project = APPLICATION) {
	return (file_exists(ROOT . "503." . md5(basename($project)) . ".goma") && filemtime(ROOT . "503." . md5(basename($project)) . ".goma") > NOW - 10);
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

	if (file_exists(ROOT . "_config.php"))
		include (ROOT . "_config.php");

	foreach ($data as $key => $val) {
		if (isset($$key))
			$$key = $val;
	}

	if (!isset($SSLprivateKey) || !isset($SSLpublicKey) || !$SSLprivateKey || !$SSLpublicKey) {
		// generate new key-pair
		// Create the keypair
		$res = openssl_pkey_new();
		if ($res === false)
			die("Please check your OpenSSL configuration. If you're server administrator, please have a look at <a href='http://www.goma-cms.org'>Goma</a>");

		// Get private key
		openssl_pkey_export($res, $SSLprivateKey);

		// Get public key
		$SSLpublicKey = openssl_pkey_get_details($res);
		$SSLpublicKey = $SSLpublicKey["key"];
	}

	$contents = file_get_contents(FRAMEWORK_ROOT . "core/samples/config_main.sample.php");
	preg_match_all('/\{([a-zA-Z0-9_]+)\}/Usi', $contents, $matches);
	foreach ($matches[1] as $name) {
		if (isset($$name))
			$contents = str_replace('{' . $name . '}', var_export($$name, true), $contents);
		else
			$contents = str_replace('{' . $name . '}', var_export("", true), $contents);
	}

	if (@file_put_contents(ROOT . "_config.php", $contents)) {
		@chmod(ROOT . "_config.php", 0644);
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

	if (file_exists($config)) {
		// get current data
		include ($config);
		$defaults = (array)$domaininfo;
	} else {
		$defaults = array("status" => 1, "date_format" => "d.m.Y - H:i", "timezone" => DEFAULT_TIMEZONE, "lang" => DEFAULT_LANG);
	}

	$new = array_merge($defaults, $data);
	$info = array();
	$info["status"] = $new["status"];
	$info["date_format"] = $new["date_format"];
	$info["timezone"] = $new["timezone"];
	$info["lang"] = $new["lang"];

	if (isset($new["db"]))
		$info["db"] = $new["db"];

	if (defined("SQL_DRIVER_OVERRIDE") && !isset($info["sql_driver"])) {
		$info["sql_driver"] = SQL_DRIVER_OVERRIDE;
	}

	$config_content = file_get_contents(FRAMEWORK_ROOT . "core/samples/config_locale.sample.php");
	$config_content = str_replace('{info}', var_export($info, true), $config_content);
	$config_content = str_replace('{folder}', $project, $config_content);
	if (@file_put_contents($config, $config_content)) {
		@chmod($config, 0644);
		return true;
	} else {
		die("6: Could not write Project-Config '" . $config . "'. Please set Permissions to 0777!");
	}
}

/**
 * gets the private Key
 *
 *@name getPrivateKey
 *@access public
 */
function getPrivateKey() {
	if (!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}

	include (ROOT . "_config.php");

	return $privateKey;
}

/**
 * gets the SSL-public Key
 *
 *@name getPublicKey
 *@access public
 */
function getSSLPublicKey() {
	if (!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}

	include (ROOT . "_config.php");

	return $SSLpublicKey;
}

/**
 * gets the SSL-private Key
 *
 *@name getPublicKey
 *@access public
 */
function getSSLPrivateKey() {
	if (!file_exists(ROOT . "_config.php")) {
		writeSystemConfig();
	}

	include (ROOT . "_config.php");

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
	if (file_exists(ROOT . "_config.php")) {
		include (ROOT . "_config.php");
	} else {
		$apps = array();
	}

	$app = array("directory" => $project);
	if (isset($domain)) {
		$app["domain"] = $domain;
	}

	// first check existing
	foreach ($apps as $key => $data) {
		if ($data["directory"] == $app["directory"]) {
			if (!isset($app["domain"]) ||  (isset($data["domain"]) && $data["domain"] == $app["domain"])) {
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
	if (file_exists(ROOT . "_config.php")) {
		include (ROOT . "_config.php");
	} else {
		return true;
	}

	foreach ($apps as $key => $data) {
		if ($data["directory"] == $project) {
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
		} elseif (!$concat && $concat_flag && strlen($int) > 0) {
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
	return preg_replace('/\{\$_lang_(.*)\}/Usie', "''.var_lang('\\1').''", $str);
	// find lang vars
}

/**
 * parses the %e% in the string
 *@name var_lang
 *@param string - the name of the languagevar
 *@param array - the array of variables
 *@return string - the parsed string
 */
function var_lang($str, $replace = array()) {
	$language = lang($str, "");
	preg_match_all('/%(.*)%/', $language, $regs);
	foreach ($regs[1] as $key => $value) {
		$re = $replace[$value];
		$language = preg_replace("/%" . preg_quote($value, '/') . "%/", $re, $language);
	}

	return $language;
	// return it!!
}

/**
 * the function ereg with preg_match
 *@name _ereg
 *@params: view php manual of ereg
 */
function _ereg($pattern, $needed, &$reg = "") {
	if (is_array($needed)) {
		return false;
	}
	return preg_match('/' . str_replace('/', '\\/', $pattern) . '/', $needed, $reg);
}

/**
 * the function eregi with preg_match
 *@name _eregi
 *@params: view php manual of eregi
 */
function _eregi($pattern, $needed, &$reg = "") {
	return preg_match('/' . str_replace('/', '\\/', $pattern) . '/i', $needed, $reg);
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
	if ($title) {
		Core::setTitle($title);
	}

	return Core::serve($content);
}

/**
 * in goma we now compare version and buildnumber seperate
 *
 *@name goma_version_compare
 *@access public
 */
function goma_version_compare($v1, $v2, $operator = null) {
	// first split version
	if (strpos($v1, "-") !== false) {
		$version1 = substr($v1, 0, strpos($v1, "-"));
		$build1 = substr($v1, strpos($v1, "-") + 1);
	} else {
		$version1 = $v1;
	}

	if (strpos($v2, "-") !== false) {
		$version2 = substr($v2, 0, strpos($v2, "-"));
		$build2 = substr($v2, strpos($v2, "-") + 1);
	} else {
		$version2 = $v2;
	}

	if (!isset($build1) || !isset($build2)) {
		return version_compare($version1, $version2, $operator);
	}

	if (isset($operator)) {
		switch($operator) {
			case "gt" :
			case ">" :
				return version_compare($build1, $build2, ">");
				break;
			case "lt" :
			case "<" :
				return version_compare($build1, $build2, "<");
				break;
			case "eq" :
			case "=" :
			case "==" :
				if (version_compare($version1, $version2, "==") && version_compare($build1, $build2, "==")) {
					return true;
				}
				return false;
				break;
			case ">=" :
			case "ge" :
				return version_compare($build1, $build2, ">=");
				break;
			case "<=" :
			case "le" :
				return version_compare($build1, $build2, "<=");
				break;
			case "!=" :
			case "<>" :
			case "ne" :
				return version_compare($build1, $build2, "<>");
				break;
		}
	} else {
		if (version_compare($build1, $build2, ">")) {
			return 1;
		} else if (version_compare($build1, $build2, "==")) {
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
		case E_ERROR :
		case E_CORE_ERROR :
		case E_COMPILE_ERROR :
		case E_PARSE :
		case E_USER_ERROR :
		case E_RECOVERABLE_ERROR :
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

		case E_WARNING :
		case E_CORE_WARNING :
		case E_COMPILE_WARNING :
		case E_USER_WARNING :
			if (strpos($errstr, "chmod") === false && strpos($errstr, "unlink") === false) {
				log_error("PHP-USER-Warning: " . $errno . " " . $errstr . " in " . $errfile . " on line " . $errline . ".");
				if (DEV_MODE && !isset($_GET["ajax"]) && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest")) {
					echo "<b>WARNING:</b> [$errno] $errstr in $errfile on line $errline<br />\n";
				}
			}
			break;
		case E_USER_NOTICE :
		case E_NOTICE :
		case E_USER_NOTICE :
			if (strpos($errstr, "chmod") === false && strpos($errstr, "unlink") === false) {
				logging("Notice: [$errno] $errstr");
				if (DEV_MODE && !isset($_GET["ajax"]) && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"))
					echo "<b>NOTICE:</b> [$errno] $errstr in $errfile on line $errline<br />\n";
			}
			break;
		case E_STRICT :
			// nothing
			break;
		default :
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
	if (PROFILE)
		Profiler::mark("log_error");
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/");
	if (isset($GLOBALS["error_logfile"])) {
		$file = $GLOBALS["error_logfile"];
	} else {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/" . date("m-d-y"));
		$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/error/" . date("m-d-y") . "/";
		$file = $folder . "1.log";
		$i = 1;
		while (file_exists($folder . $i . ".log") && filesize($file) > 10000) {
			$i++;
			$file = $folder . $i . ".log";
		}
		$GLOBALS["error_logfile"] = $file;
	}
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	if (!file_exists($file)) {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", null, 0777);
	} else {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", FILE_APPEND, 0777);
	}

	if (PROFILE)
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
	if (PROFILE)
		Profiler::mark("logging");

	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/");
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	if (isset($GLOBALS["log_logfile"])) {
		$file = $GLOBALS["log_logfile"];
	} else {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/" . date("m-d-y"));
		$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log/" . date("m-d-y") . "/";
		$file = $folder . "1.log";
		$i = 1;
		while (file_exists($folder . $i . ".log") && filesize($file) > 10000) {
			$i++;
			$file = $folder . $i . ".log";
		}
		$GLOBALS["log_logfile"] = $file;
	}
	if (!file_exists($file)) {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", null, 0777);
	} else {
		FileSystem::write($file, date($date_format) . ': ' . $string . "\n\n", FILE_APPEND, 0777);
	}

	if (PROFILE)
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
	while (file_exists($folder . "-" . $i . ".log")) {
		$i++;
		$file = $folder . "-" . $i . ".log";
	}

	FileSystem::write($file, $data, null, 0777);
}

/**
 * Writes the server configuration file
 *@name writeServerConfig
 *@access public
 */
function writeServerConfig() {
	if (strpos($_SERVER["SERVER_SOFTWARE"], "Apache") !== false) {
		$file = "htaccess";
		$toFile = ".htaccess";
	} else if (strpos($_SERVER["SERVER_SOFTWARE"], "IIS") !== false) {
		$file = "web.config";
		$toFile = "web.config";
	} else {
		return;
	}

	require (ROOT . "system/resources/" . $file . ".php");

	if (!file_put_contents(ROOT . $toFile, $serverconfig, FILE_APPEND)) {
		die("Could not write " . $file);
	}
}