<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 16.04.2012
  * $Version 2.0.1
*/   

 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


$pm_lang = array(
	"from"              => "From",
	"inbox"             => "Inbox",
	"sent"           	=> "The message was successfully sent!",
	"reply"           	=> "answer",
	"add_sig"       	=> "add signature",
	"picture"           => "picture",
	"url"               => "URL",
	"subject"           => "Subject",
	"send"              => "Send",
	"read"              => "read message",
	"no_subject"        => "No Subject",
	"to"				=> "To",
	"delete"			=> "delete Message",
	"no_messages"		=> "There are no messages.",
	
	"earlier"			=> "Earlier messages",
	"later"				=> "Newer messages",
	"compose"			=> "Compose message"
);
foreach($pm_lang as $key => $value)
{
	$GLOBALS['lang']['pm_'.$key] = $value;
}
