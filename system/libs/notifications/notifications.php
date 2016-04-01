<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 12.12.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Notification extends gObject {
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
	public static function notify($class, $text = "", $title = null, $type = null) {
		if(!isset($type))
			$type = "notification";
		
		$type = strtolower($type);
		
		gloader::load("notifications");
		Resources::add("notifications.css", "css");
		
		if(ClassInfo::hasInterface($class, "Notifier")) {
			$data = call_user_func_array(array($class, "NotifySettings"), array());
			if(isset($data["title"], $data["icon"])) {
				$title = isset($title) ? $title : $data["title"];
				$icon = ClassInfo::findFile($data["icon"], $class);
			}
		}
		
		if(!isset($title, $icon)) {
			$title = lang("notification", "notification");
			$icon = "images/icons/modernui/dark/48x48/appbar.notification.multiple.png";
		}
		
		if($type == "notification") {
			Resources::addJS("$(function(){ goma.ui.Notifications.notify(".var_export($class, true).",".var_export(parse_lang($title), true).", ".var_export($icon, true).", ".var_export($text, true)."); });");
		} else if($type == "pushnotification" && PushController::$pusher) {
			PushController::triggerToUser("notification", array($class, parse_lang($title), $icon, $text));
		} else {
			// other types are unsupported right now
			throw new InvalidArgumentException("Not supported notification-type.");
		}
	}
}