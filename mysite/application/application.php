<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 30.10.2011
  * $Version 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 * here you can define the seperator for the dynamic title in the <title></title>-Tag
*/
define('TITLE_SEPERATOR',' - ');


SQL::Init();

loadFramework();

if(file_exists(APP_FOLDER . "application/ENABLE_WELCOME")) {
	$request = new Request(
						(isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
						URL
						);
	$welcomeController = new welcomeController();
	return Core::serve($welcomeController->handleRequest($request));
	
}


// first load config
require(APP_FOLDER . "config.php");


if(PROFILE) Profiler::mark("settings");

require_once(ROOT . APPLICATION . "/application/control/settingscontroller.php");

Autoloader::$loaded["settingscontroller"] = true;
Autoloader::$loaded["newsettings"] = true;
settingsController::preInit();



if(PROFILE) Profiler::unmark("settings");
Resources::$gzip = settingsController::get("gzip");
Core::setCMSVar("ptitle", settingsController::get("titel"));
Core::setCMSVar("title", settingsController::get("titel"));
Core::setTheme(settingsController::Get("stpl"));
Core::setHeader("keywords", settingsController::Get("meta_keywords"));
Core::setHeader("description", settingsController::Get("meta_description"));
Core::setHeader("robots", "index,follow");
Core::setHeader("copyright", date("Y", NOW) . " - " . settingsController::get("titel"));

date_default_timezone_set(Core::GetCMSVar("TIMEZONE"));

if(PROFILE) Profiler::unmark("settings");

userController::execute();

$core = new Core();
$core->render(URL);