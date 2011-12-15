<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 26.10.2011
*/   

$install_lang = array(
	"install_goma"		=> "Goma installieren",
	"restore_app"		=> "Installation wiederherstellen",
	"browse_apps"		=> "Apps durchsuchen",
	"welcome"			=> "Willkommen in der Systemverwaltung. Bitte w&auml;hlen Sie, was Sie tun m&ouml;chten.",
	"select_app"		=> "Bitte w&auml;hlen Sie die Applikation aus, die Sie installieren m&ouml;chten!",
	"app"				=> "Applikation",
	"select"			=> "Ausw&auml;hlen",
	"install"			=> "Installieren",
	"no_app_found"		=> "Es wurde leider keine Applikation gefunden, die installiert werden kann. Bitte suchen Sie im Goma-Applikations-Bereich.",
	"folder"			=> "Installationsverzeichnis",
	"folder_info"		=> "Goma legt auf der Festplatte ihres Servers ein Verzeichnis mit diesem Namen an, um die App in dieses Verzeichnis zu schreiben",
	"db_user"			=> "Datenbankbenutzer",
	"db_host"			=> "Datenbankserver",
	"db_host_info"		=> "Meist localhost",
	"db_name"			=> "Datenbankname",
	"db_password"		=> "Datenbankpasswort",
	"table_prefix"		=> "Tabellen-Pr&auml;fix",
	"folder_error"		=> "Der Ordner existiert bereits oder ist auf Liste der nicht erlaubten Ordnernamen.",
	"sql_error"			=> "Die Datenbankeinstellungen scheinen nicht korrekt zu sein."
);

foreach($install_lang as $key => $value)
{
	$GLOBALS['lang']['install.'.$key] = $value;
}
