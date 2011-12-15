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
	"filetype_failure"	=> "Dieser Dateityp ist an dieser Stelle nicht erlaubt!",
	"filesize_failure"	=> "Diese Datei ist f&uuml;r diesen Ort zu gro&szlig;!",
	"upload_failure"	=> "Die Datei konnte nicht auf den Server geladen werden.",
	"browse"			=> "Durchsuchen"
);

foreach($files_lang as $key => $value)
{
	$GLOBALS['lang']['files.'.$key] = $value;
}

