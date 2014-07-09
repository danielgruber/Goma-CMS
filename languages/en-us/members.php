<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@author Goma-Team
  * last modified: 12.07.2010
  * $Version 2.0.0 - 001
*/   

 
defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

$mem_lang = array(
	"members"		=> "Members",
	"member"		=> "Members",
	"send_message"	=> "Send message",
	"group"			=> "User-group"
);
foreach($mem_lang as $key => $value)
{
	$GLOBALS["lang"]["mem_".$key] = $value;
}
