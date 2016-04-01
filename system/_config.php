<?php
/*
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Framework
 * @version 2.0.6 
 */

Director::addRules(array(
	'dev' 							=> 'dev',
	'admin//$item' 					=> 'adminController',

	'api/stats' 					=> 'StatController',
	'api/v2'						=> 'GomaRestfulService',
	'api/v1//$ClassName!' 			=> 'RestfulServer',

	'images/resampled' 				=> 'imageResize',

	'profile//$Action' 				=> 'ProfileController',
	'member/$id!' 					=> 'ProfileController',

	'uploads' 						=> 'UploadController',
	'gloader' 						=> 'Gloader',
	'pusher' 						=> 'PushController',

	'treecallback'					=> 'TreeCallbackUrl',
	'treeserver' 					=> 'TreeServer',

	'system/cron'					=> 'CronController',
	'system/help' 					=> 'HelpController',
	'system/smtp'					=> 'SMTPConnector',
	"system/livecounter"			=> 'liveCounterController',
	'system/ajax//link/$id' 		=> 'ajaxlink',
	'system/ajax//popup/$id' 		=> 'ajaxlink',
	'system//ck_uploader'			=> 'CKEditorUploadsController',
	'system//ck_imageuploader'		=> 'CKEditorUploadsController'
), 10);

Director::addRules(array('system' => 'SystemController', ), 9);

// gloader
gloader::addLoadAble("dialog", "system/libs/javascript/bluebox.min.js", array("draggable"));
gloader::addLoadAble("draggable", "system/libs/javascript/ui/draggable.js");
gloader::addLoadAble("pageslider", "system/libs/javascript/slider.js");
gloader::addLoadAble("dropable", "system/libs/javascript/ui/droppable.js");
gloader::addLoadAble("sortable", "system/libs/javascript/ui/sortable.js");
gloader::addLoadAble("selectable", "system/libs/javascript/ui/selectable.js");
gloader::addLoadAble("resizable", "system/libs/javascript/ui/resizable.js");
gloader::addLoadAble("accordion", "system/libs/javascript/ui/accordion.js");
gloader::addLoadAble("autocomplete", "system/libs/javascript/ui/autocomplete.js", array("menu"));
gloader::addLoadAble("menu", "system/libs/javascript/ui/menu.js");
gloader::addLoadAble("button", "system/libs/javascript/ui/button.js");
gloader::addLoadAble("uidialog", "system/libs/javascript/ui/dialog.js", array(
	"button",
	"resizable",
	"draggable"
));
gloader::addLoadAble("hammer", "system/libs/javascript/hammer.js");
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
gloader::addLoadAble("jquery-color", "system/libs/thirdparty/jquery-color/jquery.color.min.js");
gloader::addLoadAble("helpbox", "system/libs/javascript/helpBox.js");

// Breadcrump seperator
define('BREADCRUMB_SEPERATOR', ' &raquo; ');

// uncomment this line to active REST-Support in Goma.
//gObject::extend("Controller", "RestControllerExtension");
