<?php defined("IN_GOMA") OR die();

/**
 * Push-Controller.
 *
 * @package     Goma\Push
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0.10
 */
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

	protected static $key;
	private static $hasBeenInited;

	/**
	 * inits the push-controller
	 * @param string $key
	 * @param string $secret
	 * @param string $app_id
	 */
	static function initPush($key, $secret, $app_id) {
		self::$key = $key;
		self::$pusher = new Pusher($key, $secret, $app_id);
		if(Core::globalSession()->get("pushActive") && !self::$hasBeenInited) {
			self::initJS();
		}
	}

	static function enablePush() {
		if(!self::$hasBeenInited) {
			self::initJS();
		}

		GlobalSessionManager::globalSession()->set("pushActive", true);
	}

	static function disablePush() {
		GlobalSessionManager::globalSession()->set("pushActive", false);
	}

	protected static function initJS() {
		self::$hasBeenInited = true;
		Resources::addData("goma.Pusher.init('" . self::$key . "');var uniqueID = " . var_export(member::uniqueID(), true) . ";");

		Resources::add("notifications.css", "css");
		gloader::load("notifications");
	}

	/**
	 * triggers event
	 *
	 * @name trigger
	 * @access public
	 * @return bool
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
	 * @name auth
	 * @access public
	 * @return string
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
		return "Forbidden";
	}
}
