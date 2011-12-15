<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 26.10.2011
*/   

$install_lang = array(
	"install_goma"		=> "Install Goma",
	"restore_app"		=> "Restore",
	"browse_apps"		=> "Browse Apps",
	"welcome"			=> "Welcome to the Goma System tool. Please select, what you want to do.",
	"select_app"		=> "Please select the application, which you want to install!",
	"app"				=> "Application",
	"select"			=> "Select",
	"install"			=> "Install",
	"no_app_found"		=> "There is no application. Please browse the Goma-apps to install an application.",
	"folder"			=> "Install-Folder",
	"folder_info"		=> "Goma created a folder on your server, where the files will be stored. The name of that directory should only contain lowercase letters.",
	"db_user"			=> "Database-User",
	"db_host"			=> "Database-Server",
	"db_host_info"		=> "Mostly localhost",
	"db_name"			=> "Database-Name",
	"db_password"		=> "Database-Password",
	"table_prefix"		=> "Table-prefix",
	"folder_error"		=> "The folder already exists or is not valid.",
	"sql_error"			=> "The Database-Server denied the query."
);

foreach($install_lang as $key => $value)
{
	$GLOBALS['lang']['install.'.$key] = $value;
}
