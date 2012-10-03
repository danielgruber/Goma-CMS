<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 08.12.2011
  * 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

$files_lang = array(
	"filetype_failure"	=> "This filetype isn't allowed here!",
	"filesize_failure"	=> "This file is to big for this place!",
	"upload_failure"	=> "The file couldn't uploaded to the server.",
	"browse"			=> "Browse",
	"replace"			=> "Replace file",
	"delete"			=> "Delete file",
	"filename"			=> "filename",
	"upload"			=> "upload",
	"no_file"			=> "There is not file visible here",
	"upload_success"	=> "The file was uploaded successfully!",
	"size"				=> "filesize"
);

foreach($files_lang as $key => $value)
{
	$GLOBALS['lang']['files.'.$key] = $value;
}

