<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 15.03.2011
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
	"lists_li"           => "Listenpunkt",
	
	"bold"				 => "Fett",
	"italic"			 => "Kursiv",
	"underlined"		 => "Unterstrichen",
	
	"img_prompt"		 => "Bitte geben Sie den Pfad zum Bild an.",
	"link_prompt"		 => "Bitte geben Sie die URL an.",
	"link_prompt_title"	 => "Bitte geben Sie den Titel des Links an.",
	"code"				 => "Quellcode",
	"quote"				 => "Zitat"
);
foreach($bb_lang as $key => $value) {
	$GLOBALS['lang']['bb.'.$key] = $value;
}
