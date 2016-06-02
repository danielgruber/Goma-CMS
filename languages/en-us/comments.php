<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 14.07.2010
*/
$co_lang = array(
	"add_comment"       => "Add a comment",
	"of"                => "from",
	'on'                => "at",
	"comments"			=> "Comments",
	"edit"				=> "edit comment",
);
foreach($co_lang as $key => $value)
{
	$GLOBALS['lang']['co_'.$key] = $value;
}
