<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 - 2012 Goma-Team
  * last modified: 15.03.2012
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
	"lists_li"           => "bullet-point",
	
	"bold"				 => "bold",
	"italic"			 => "italic",
	"underlined"		 => "underlined",
	
	"img_prompt"		 => "Please insert the URL to the image you want to add.",
	"link_prompt"		 => "Please insert the URL you want to add.",
	"link_prompt_title"	 => "Please define the title of the link.",
	"code"				 => "Code",
	"quote"				 => "Quotation"
);
foreach($bb_lang as $key => $value) {
	$GLOBALS['lang']['bb.'.$key] = $value;
}
