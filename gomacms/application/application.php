<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 11.04.2013
  * $Version 1.1.7
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 * here you can define the seperator for the dynamic title in the <title></title>-Tag
*/
define('TITLE_SEPERATOR',' - ');

SQL::Init();

loadFramework();

if(isset($_SESSION["welcome_screen"]) || (!file_exists(APP_FOLDER . "application/.WELCOME_RUN") && !file_exists(APP_FOLDER . "application/WELCOME_RUN.php") && !isset($_SESSION["dev_without_perms"]) && DataObject::count("user") == 0)) {
	$request = new Request(
						(isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
						URL
						);
	$welcomeController = new welcomeController();
	return Core::serve($welcomeController->handleRequest($request));
}

if(PROFILE) Profiler::mark("settings");

settingsController::preInit();

if(PROFILE) Profiler::unmark("settings");

if(settingsController::get("useSSL") == 1) {
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

	if($http == "http" && !isset($_GET["forceNoSSL"]) && !isset($_SESSION["forceNoSSL"])) {
		header("Location: https://" . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"]);
		exit;
	} else if(isset($_GET["forceNoSSL"])) {
		$_SESSION["forceNoSSL"] = true;
	}
}

Resources::$gzip = settingsController::get("gzip");
RegisterExtension::$enabled = settingsController::get("register_enabled");
RegisterExtension::$validateMail = settingsController::get("register_email");
RegisterExtension::$registerCode = settingsController::get("register");
Core::setCMSVar("ptitle", settingsController::get("titel"));
Core::setCMSVar("title", settingsController::get("titel"));
Core::setTheme(settingsController::Get("stpl"));
Core::setHeader("description", settingsController::Get("meta_description"));
Core::setHeader("robots", "index,follow");

if(settingsController::get("p_app_id") && settingsController::get("p_app_key") && settingsController::get("p_app_secret")) {
	PushController::initPush(settingsController::get("p_app_key"), settingsController::get("p_app_secret"), settingsController::get("p_app_id"));
}

if(settingsController::get("google_site_verification")) {
	$code = settingsController::get("google_site_verification");
	if(preg_match('/\<meta[^\>]+content\=\"([a-zA-Z0-9_\-]+)\"\s+\/\>/', $code, $matches)) {
		$code = $matches[1];
	}
    Core::setHeader("google-site-verification", convert::raw2xml($code));
}

date_default_timezone_set(Core::GetCMSVar("TIMEZONE"));

if(PROFILE) Profiler::unmark("settings");

$core = new Core();
$core->render(URL);
