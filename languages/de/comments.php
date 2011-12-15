<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 14.07.2010
*/
$co_lang = array(
	"add_comment"       => "Kommentar hinzuf&uuml;gen",
	"of"                => "von",
	'on'                => "am",
	"comments"			=> "Kommentare",
	"edit"				=> "Kommentar bearbeiten",
	"comment"			=> "Kommentar"
);
foreach($co_lang as $key => $value)
{
	$GLOBALS['lang']['co_'.$key] = $value;
}
