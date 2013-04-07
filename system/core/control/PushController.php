<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
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
		Resources::addJS("$(function(){goma.Pusher.init('".$key."');goma.Pusher.subscribe('presence-goma');});");
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
	 * make auth
	 *
	 *@name auth
	 *@access public
	*/
	public function auth() {
		if(member::login()) {
			if(self::$pusher && isset($_POST['channel_name'], $_POST['socket_id'])) {
				echo self::$pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], member::$loggedIn->id, member::$loggedIn->toArray());
				exit;
			}
		} 
		
		header('', true, 403);
		echo "Forbidden";
		
	}
}