<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.09.2011
  * $Version 2.0.0 - 001
*/   

 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


$pm_lang = array(
	"from"              => "From",
	"inbox"             => "Inbox",
	"no_message"        => "There are no new messages!",
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
	"no_messages"		=> "Sie haben keine Nachrichten."
);
foreach($pm_lang as $key => $value)
{
	$GLOBALS['lang']['pm_'.$key] = $value;
}
