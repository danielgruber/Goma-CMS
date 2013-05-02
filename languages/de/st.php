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
	"today"       => "Heute",
	'yesterday'   => "Gestern",
	'2 hours ago' => "In den Letzten 2 Stunden",
	"last 30 days"=> "In den letzten 30 Tage",
	'whole'       => "Gesamt",
	'stats'       => "Statistiken",
	'visitors'    => "Besucher",
	'online'      => "Besucher online"
);
foreach($st_lang as $key => $value) 
{
	$GLOBALS['lang']['st_'.$key] = $value;
}
