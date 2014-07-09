<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 19.03.2013
  * $Version 1.4.9
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class PushController extends Controller {
	/**
	 * pusher
	*/
	static $pusher;
	
	/**
	 * allowed actions
	 *
	 *@name allowed_actions
	*/
	public $allowed_actions = array(
		"auth"
	);
	
	/**
	 * inits the push-controller
	 *
	 *@name init
	 *@access public
	*/
	static function initPush($key, $secret, $app_id) {
		self::$pusher = new Pusher($key, $secret, $app_id);
		Resources::addData("goma.Pusher.init('".$key."');var uniqueID = ".var_export(member::uniqueID(), true).";");
		
		Resources::add("notifications.css", "css");
		gloader::load("notifications");
	}
	
	/**
	 * triggers event
	 *
	 *@name trigger
	 *@access public
	*/
	static function trigger($event, $data) {
		if(isset(self::$pusher)) {
			return self::$pusher->trigger("presence-goma", $event, $data);
		} else {
			return false;
		}
	}
	
	/**
	 * triggers a event to the currently logged-in user.
	*/
	static function triggerToUser($event, $data) {
		if(isset(self::$pusher)) {
			return self::$pusher->trigger("private-" . member::uniqueID(), $event, $data);
		} else {
			return false;
		}
	}
	
	/**
	 * make auth
	 *
	 *@name auth
	 *@access public
	*/
	public function auth() {
		if(isset($_POST['channel_name']) && preg_match('/^presence\-/', $_POST['channel_name']) && member::login()) {
			if(self::$pusher && isset($_POST['socket_id'])) {
				echo self::$pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], member::$loggedIn->id, member::$loggedIn->toArray());
				exit;
			}
		} else if(isset($_POST['channel_name']) && preg_match('/^private\-/', $_POST['channel_name'])) {
			if(self::$pusher && isset($_POST['socket_id']) && $_POST["channel_name"] == "private-" . member::uniqueID()) {
				echo self::$pusher->socket_auth($_POST['channel_name'], $_POST['socket_id']);
				exit;
			}
		}
		
		header('', true, 403);
		echo "Forbidden";
		
	}
}