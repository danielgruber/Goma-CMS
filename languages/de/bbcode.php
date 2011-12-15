<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 14.01.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

$bb_lang = array(
	'has_written'        => "hat geschrieben:",
	"quote"              => "Zitat:",
	"code"               => "Quellcode:",
	"img"                => "Bild",
	'link'               => "Link",
	"headers"            => "&Uuml;berschriften",
	"colors"             => "Farben",
	"lists"              => "Listen",
	"lists_ol"           => "Liste mit Zahlen",
	"lists_ul"           => "Liste mit Punkten",
	"lists_li"           => "Listenpunkt"
);
foreach($bb_lang as $key => $value) {
	$GLOBALS['lang']['bb.'.$key] = $value;
}
