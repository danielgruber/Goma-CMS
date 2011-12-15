<?php
/*****************************************************************
 * Goma - Open Source Content Management System
 * if you see this text, please install PHP 5.2 or higher        *
 *****************************************************************
*/
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 04.05.2010
*/  

// test
// php version

if(strtok(phpversion(),'.') < 5)
{
		header("HTTP/1.1 500 Server Error");
		echo file_get_contents(dirname(__FILE__) . "/includes/framework/templates/php5.html");
		die();
}

define("BASE_SCRIPT", "index.php/");
define("MOD_REWRITE", false);

$url = $_SERVER["REQUEST_URI"];
if(preg_match('/index\.php$/', $url))
{
		$url = preg_replace('/index\.php/', '', $url, 1);
} else
{
		$url = preg_replace('/index\.php\//', '', $url, 1);
}

while(preg_match('/\/\//',$url))
{
		$url = str_replace('//','/',$url);
}

require("system/application.php");