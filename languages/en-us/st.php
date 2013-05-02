<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 16.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

$st_lang = array(
	"today"       => "Today",
	'yesterday'   => "Yesterday",
	'2 hours ago' => "In the last two hours",
	"last 30 days"=> "In the last 30 days",
	'whole'       => "overall",
	'stats'       => "Statistics",
	'visitors'    => "Visitors",
	'online'      => "visitors online"
);
foreach($st_lang as $key => $value) 
{
	$GLOBALS['lang']['st_'.$key] = $value;
}
