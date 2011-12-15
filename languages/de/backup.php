<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 31.10.2011
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
	"create_sql"			=> "Datenbank sichern"
);
foreach($db_lang as $key => $value) {
	$GLOBALS['lang']['backup_'.$key] = $value;
}