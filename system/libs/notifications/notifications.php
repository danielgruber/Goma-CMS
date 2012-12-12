<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 12.12.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

interface Notifier {
	/**
	 * returns information about notification-settings of this class
	 * these are:
	 * - title
	 * - icon
	 * this API may extended with notification settings later
	 * 
	 *@name NotifySettings
	 *@access public
	*/
	public static function NotifySettings();
}

class Notification extends Object {
	/**
	 * notify a user about anything
	 *
	 *@name notify
	 *@access public
	 *@param string - class-name
	 *@param string - title - with language-tags of the notification
	 *@param string - text of the notification
	 *@param string - type
	*/
	public static function notify($class, $text = "", $type = null) {
		if(!isset($type))
			$type = "notification";
		
		Resources::add("system/libs/notifications/notifications.js", "js", "script");
		Resources::add("notifications.css", "css");
		
		if(ClassInfo::hasInterface($class, "Notifier")) {
			$data = call_user_func_array(array($class, "NotifySettings"), array());
			if(isset($data["title"], $data["icon"])) {
				$title = $data["title"];
				$icon = ClassInfo::findFile($data["icon"], $class);
			}
		}
		
		if(!isset($title, $icon)) {
			$title = lang("notification", "notification");
			$icon = "images/icons/modernui/dark/48x48/appbar.notification.multiple.png";
		}
		
		if($type == "notification") {
			Resources::addJS("$(function(){ Notifications.notify(".var_export($class, true).",".var_export(parse_lang($title), true).", ".var_export($icon, true).", ".var_export($text, true)."); });");
		} else {
			// other types are unsupported right now
			throwError(6, "PHP-Error", "Unsupported notification type " . $type);
		}
	}
}