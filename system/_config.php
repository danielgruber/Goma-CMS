<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 03.09.2012
  * $Version 2.0.2
*/

Core::addRules(array(
	"dev" 											=> "dev",
	'admin//$item' 									=> "adminController",
	'system/ajax//link/$id'							=> 'ajaxlink',
	'system/ajax//popup/$id'						=> 'ajaxlink',
	"api/v1//\$ClassName!"							=> "RestfulServer",
	"treeserver"									=> "TreeServer",
	'uploaded/images/resampled/$width!/$height!' 	=> "imageResize",
	'uploaded/images/resampled/$width!' 			=> "imageResize",
	'images/resampled/$width!/$height!'				=> "imageResize",
	'images/resampled/$width!'						=> "imageResize",
	'profile//$Action'								=> "ProfileController",
	'member/$id!'				 					=> "ProfileController",
	"uploads"										=> "UploadController",
	"gloader"										=> "Gloader",
	"system/help"									=> "HelpController",
	"pusher"										=> "PushController"
), 10);

Core::addRules(array(
	"system"						=> "SystemController",
), 9);

Core::addRules(array(
	''							   => "HomePageController"
), 1);

// gloader
gloader::addLoadAble("dialog", "system/libs/javascript/bluebox.min.js", array("draggable"));
gloader::addLoadAble("draggable", "system/libs/javascript/ui/draggable.js");
gloader::addLoadAble("dropable", "system/libs/javascript/ui/droppable.js");
gloader::addLoadAble("sortable", "system/libs/javascript/ui/sortable.js");
gloader::addLoadAble("selectable", "system/libs/javascript/ui/selectable.js");
gloader::addLoadAble("resizable", "system/libs/javascript/ui/resizable.js");
gloader::addLoadAble("accordion", "system/libs/javascript/ui/accordion.js");
gloader::addLoadAble("autocomplete", "system/libs/javascript/ui/autocomplete.js");
gloader::addLoadAble("button", "system/libs/javascript/ui/button.js");
gloader::addLoadAble("uidialog", "system/libs/javascript/ui/dialog.js", array("button", "resizable", "draggable"));
gloader::addLoadAble("slider", "system/libs/javascript/ui/slider.js");
gloader::addLoadAble("tabs", "system/libs/javascript/ui/tabs.js");
gloader::addLoadAble("gtabs", "system/libs/tabs/tabs.js");
gloader::addLoadAble("progessbar", "system/libs/javascript/ui/progessbar.js");
gloader::addLoadAble("tree", "system/libs/javascript/tree.js");
gloader::addLoadAble("datepicker", "system/libs/javascript/ui/datepicker.js");
gloader::addLoadAble("uiEffects", "system/libs/javascript/ui/effects.js");
gloader::addLoadAble("touch", "system/libs/javascript/ui/jquery.ui.touch.js");
gloader::addLoadAble("jquery.scale.rotate", "system/libs/javascript/jquery.scale.rotate.js");
gloader::addLoadAble("dropdownDialog", "system/libs/javascript/dropdownDialog.js");
gloader::addLoadAble("ajaxupload", "system/libs/ajax/ajaxupload.js");
gloader::addLoadAble("htmllib", "system/libs/javascript/html.js");
gloader::addLoadAble("history", "system/libs/javascript/history/history.js");
gloader::addLoadAble("notifications", "system/libs/notifications/notifications.js");
gloader::addLoadAble("json", "system/libs/javascript/json.js");

/**
 * here you can define the seperator for the creadcrumbs
*/
define('BREADCRUMB_SEPERATOR',' &raquo; ');