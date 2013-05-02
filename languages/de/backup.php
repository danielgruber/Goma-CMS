<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 - 2012 Goma-Team
  * last modified: 31.03.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

$db_lang = array(
	"exports"           	=> "Sicherungen",
	'restore'      			=> "Wiederherstellen",
	'info'              	=> "Informationen",
	"exported"          	=> "Die Datenbank wurde erfolgreich exportiert!",
	"delete_confirm"		=> "Wollen Sie dieses Backup wirklich l&ouml;schen?",
	"restore_confirm"		=> "Wollen Sie Ihre Installation wirklich auf dieses Backup wiederherstellen?",
	'upload'				=> "Sicherung hochladen",
	"create_date"			=> "Erstellungsdatum",
	"restore_success"		=> "Die Datenbank wurde erfolgreich wiederhergestellt!", 
	"create_complete"		=> "Komplett-Sicherung erstellen",
	"create_sql"			=> "Datenbank sichern",
	"restorable"			=> "Wiederherstellbar",
	"exclude_files"			=> "Dateien aus dem Backup ausschliessen",
	"write_error"			=> "Konnte Datei nicht schreiben.",
	"db"					=> "Datenbanksicherung",
	"full"					=> "Komplett-Sicherung",
	"type"					=> "Sicherungstyp"
);
foreach($db_lang as $key => $value) {
	$GLOBALS['lang']['backup_'.$key] = $value;
}