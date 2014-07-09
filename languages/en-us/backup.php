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
	"exports"           	=> "Backups",
	'restore'      			=> "Restore",
	'info'              	=> "Information",
	"exported"          	=> "The database was successfully backed up!",
	"delete_confirm"		=> "Do you really want to delete this backup?",
	"restore_confirm"		=> "Do you really want to restore to this backup?",
	'upload'				=> "Upload a backup..",
	"create_date"			=> "Creation date",
	"restore_success"		=> "The database was restored successfully!", 
	"create_complete"		=> "Create complete Backup",
	"create_sql"			=> "Create Database-Backup",
	"restorable"			=> "restoreable",
	"exclude_files"			=> "Exclude files from backup",
	"write_error"			=> "Could not write to filesystem.",
	"db"					=> "Database-Backup",
	"full"					=> "Full backup",
	"type"					=> "Backuptype"
);
foreach($db_lang as $key => $value) {
	$GLOBALS['lang']['backup_'.$key] = $value;
}