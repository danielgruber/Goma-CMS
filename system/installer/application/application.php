<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 26.08.2011
  * $Version 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadFramework(); // set up the framework

require(APP_FOLDER . "application/config.php");

if(fopen(APP_FOLDER . "data/apps/write.test", "w")) {
	@unlink(APP_FOLDER . "data/apps/write.test");
} else {
	die("<h3>Please set Permissions of ".APP_FOLDER."data/apps/ and all enclosed files and folders to 0777.</h3>");
}

$core = new Core();
$core->render(URL);