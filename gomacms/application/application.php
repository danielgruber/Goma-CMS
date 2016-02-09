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
		URL,
		$_GET,
		$_POST
	);
	$welcomeController = new welcomeController();
	Core::serve($welcomeController->handleRequest($request));
	return;
}

require("loadSettings.php");

if(PROFILE) Profiler::unmark("settings");

$core = new Core();
$core->render(URL);
