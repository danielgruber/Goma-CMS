<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 02.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

$bb_lang = array(
	'has_written'        => "wrote:",
	"quote"              => "Quote:",
	"code"               => "sourcecode:",
	"img"                => "picture",
	'link'               => "URL",
	"headers"            => "headings",
	"colors"             => "colors",
	"lists"              => "lists",
	"lists_ol"           => "list with numbers",
	"lists_ul"           => "list with bullet-points",
	"lists_li"           => "bullet-point"
);
foreach($bb_lang as $key => $value) {
	$GLOBALS['lang']['bb.'.$key] = $value;
}
