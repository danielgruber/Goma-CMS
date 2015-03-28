<?php defined('IN_GOMA') OR die();


/**
 * Wrapper-Class to reflect some data of the logged-in user.
 *
 * @package		Goma\Security\Users
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version		1.4
 */
class Member extends Object {
	/**
	 * id of the current logged in user
	 *
	 *@name id
	 *@access public
	*/
	public static $id;
	
	/**
	 * nickname of the user logged in
	 *
	 *@name nickname
	 *@access public
	*/
	public static $nickname;
	
	/**
	 * this var reflects the status of the highest group in which the user is
	 *
	 *@name groupType
	 *@access public
	 *@var enum(0,1,2)
	*/
	public static $groupType = 0;
	
	/**
	 * set of groups of this user
	 *
	 *@name groups
	 *@access public
	*/
	public static $groups = array();
	
	/**
	 * default-admin
	 *
	 *@name default_admin
	 *@access public
	*/
	public static $default_admin;
	
	/**
	 * object of logged in user
	 *
	 *@name loggedIn
	 *@access public
	*/
	public static $loggedIn;
	
	/**
	 * checks for default admin and basic groups
	 *
	 *@name checkDefaults
	 *@access public
	*/
	static function checkDefaults() {
		
		$cacher = new Cacher("groups-checkDefaults");
		if($cacher->checkValid()) {
		
		} else {
			if(DataObject::count("group", array("type" => 2)) == 0) {
				$group = new Group();
				$group->name = lang("admins", "admin");
				$group->type = 2;
				$group->permissions()->add(Permission::forceExisting("superadmin"));
				$group->permissions()->write(false, true, 2);
				$group->write(true, true, 2, false, false);
			}
			
			if(DataObject::count("group", array("type" => 1)) == 0) {
				$group = new Group();
				$group->name = lang("user", "users");
				$group->type = 1;
				$group->write(true, true, 2, false, false);
			}
			
			if(isset(self::$default_admin) && DataObject::count("user") == 0) {
				$user = new User();
				$user->nickname = self::$default_admin["nickname"];
				$user->password = self::$default_admin["password"];
				$user->write(true, true);
				$user->groups()->add(DataObject::get_one("group", array("type" => 2)));
				$user->groups()->write(false, true);
			}
			
			$cacher->write(true, 3600);
		}
		
		
	}
	
	/**
	 * checks the login and writes the types
	 *
	 * @name 	Init
	 * @access 	public
	 * @return 	boolean	true if logged in
	*/
	static function Init() {
		if(PROFILE) Profiler::mark("member::Init");
		if(isset(self::$id)) {
			return true;
		}
		
		self::checkDefaults();
		
		if($data = self::getUserObject()) {
			if($data["timezone"]) {
				Core::setCMSVar("TIMEZONE", $data["timezone"]);
				date_default_timezone_set(Core::getCMSVar("TIMEZONE"));
			}
			
			self::$id = $data->id;
			self::$nickname = $data->nickname;
			
			self::forceGroups($data);
			
			self::$groupType = self::$groups->first()->type;
			
			// every group has at least the type 1, 0 is just for guests
			if(self::$groupType == 0) {
				self::$groupType = 1;
				self::$groups->first()->type = 1;
				self::$groups->first()->write(false, true, 2, false, false);
			}
			
			self::$loggedIn = $data;
			if(PROFILE) Profiler::unmark("member::Init");
			return true;
		} else {
			if(PROFILE) Profiler::unmark("member::Init");
			return false;
		}
	}

	/**
	 * looks for logged in user and validates session.
	*/
	public static function getUserObject() {
		if(isset($_SESSION["g_userlogin"])) {
			if($data = DataObject::get_one("user", array("id" => $_SESSION["g_userlogin"]))) {
				$currsess = session_id();

				if($data['phpsess'] == $currsess)
				{
					return $data;
				} else {
					self::doLogout();
				}
			}
		}
	}

	/**
	 * forces groups to be existing or creates them.
	 *
	 * @param 	DataObject of Type User
	*/
	public static function forceGroups($data) {

		self::$groups = $data->groups(null, "type DESC");

		// if no group is set, set default group user
		if(self::$groups->forceData()->Count() == 0) {

			$group = self::getDefaultGroup();
			
			self::$groups->add($group);
			self::$groups->write(false, true, 2, false, false);
		}
	}

	/**
	 * returns a group which any user can be assigned safetly to based on permissions.
	 *
	 * @name 	getDefaultGroup
	 * @return 	Group
	*/
	public static function getDefaultGroup() {
		// check for default user group			
		$defaultGroup = DataObject::get_one("group", array("usergroup" => 1));
		if(!$defaultGroup) {
	
			// check if any group exists, which a user can be safely asigned to without giving him admin permission
			$groupCount = DataObject::count("group", array("type" => 1));

			// validate group and permissions
			if($groupCount == 0 || ($groupCount == 1 && DataObject::get_one("group", array("type" => 1))->permissions()->Count() > 0)) {

				// create new
				$defaultGroup = new Group(array("name" => lang("user"), "type" => 1, "usergroup" => 1));
				$defaultGroup->write(true, true, 2, false, false);
			} else {

				// iterate trough all groups with type 1 and set default group to the first one without permissions
				foreach(DataObject::get("group", array("type" => 1)) as $defaultGroup) {
					if($defaultGroup->permissions()->count() == 0) {
						$defaultGroup->usergroup = 1;
						$defaultGroup->write(false, true, 2, true, false);
						break;
					} else {
						unset($defaultGroup);
					}
				}
				
				if(!isset($defaultGroup)) {
					$defaultGroup = new Group(array("name" => lang("user"), "type" => 1, "usergroup" => 1));
					$defaultGroup->write(true, true, 2, false, false);
				}
			}
		}

		return $defaultGroup;
	}
	
	/**
	 * returns the groupids of the groups of the user
	 *
	 *@name groupids
	 *@access public
	*/
	static function groupIDs() {
		if(is_array(self::$groups)) {
			return self::$groups;
		}
		return self::$groups->fieldToArray("id");
	}
	
	/**
	 * returns if the user is logged in
	 *
	 *@name login
	 *@access public
	*/
	static function login() {
		return (self::$groupType > 0);
	}
	
	/**
	 * returns if the user is an admin
	 *
	 *@name admin
	 *@access public
	*/
	public function admin() {
		return (self::$groupType == 2);
	}
	
	/**
	 * checks if an user have the rights
	 *@name right
	 *@access public
	 *@param string|numeric - if numeric: the rights from 1 - 10, if string: the advanced rights
	 *@return bool
	*/
	static function right($name)
	{
			return right($name);
	}
	
	/**
	 * login an user with the params
	 * if the params are incorrect, it returns false.
	 *
	 * @name 	doLogin
	 * @access 	public
	 * @param 	string - nickname
	 * @param 	string - password
	 *@ return 	bool
	*/
	static function doLogin($user, $pwd)
	{
		self::checkDefaults();

		try {
			self::checkLogin($user, $pwd);

			return true;
		} catch(LoginInvalidException $e) {

			// credentials wrong
			logging("Login with wrong Username/Password with IP: ".$_SERVER["REMOTE_ADDR"].""); // just for security
			AddContent::addError(lang("wrong_login"));
		} catch(LoginUserLockedException $e) {

			// user is locked
			AddContent::addError(lang("login_locked"));
		} catch(LoginUserMustUnlockException $e) {

			// user must activate account
			$add = "";
			if(ClassInfo::exists("registerExtension")) {
				$add = ' <a href="profile/resendActivation/?email=' . urlencode($data->email) . '">'.lang("register_resend_title").'</a>';
			}
			AddContent::addError(lang("login_not_unlocked") . $add);
		}

		return false;
	}

	/**
	 * performs a login and throws an exception if login cannot be validates.
	*/
	public static function checkLogin($user, $pwd) {
		self::checkDefaults();

		$data = DataObject::get_one("user", array("nickname" => trim(strtolower($user)), "OR", "email" => array("LIKE", $user)));
		
		if($data) {
			// check password
			if(Hash::checkHashMatches($pwd, $data->fieldGet("password"))) {
				if($data->status == 1) {
					// register login
					$_SESSION["g_userlogin"] = $data->id;
					
					$data->phpsess = session_id();
					$data->performLogin();
					
					return true;
				} else if($data->status == 0) {
					throw new LoginUserMustUnlockException();
				} else {
					throw new LoginUserLockedException();
				}
			} else {
				throw new LoginInvalidException();
			}
		} else {
			throw new LoginInvalidException();
		}
	}
	
	/**
	 * forces a logout
	 *
	 *@name doLogout
	 *@access public
	*/
	public function doLogout() {
		$data = DataObject::get_by_id("user", $_SESSION["g_userlogin"]);
		if($data) {
			$data->performLogout();
		}
		unset($_SESSION["g_userlogin"]);
	}
	
	/**
	 * require login
	 *
	 *@name require_Login
	 *@access public
	*/
	public function require_login() {
		if(!self::login()) {
			AddContent::addNotice(lang("require_login"));
			self::redirectToLogin();
		}
		return true;
	}

	public function redirectToLogin() {
		HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "profile/login/?redirect=" . $_SERVER["REQUEST_URI"]);
		exit;
	}
	
	/**
	 * unique identifier of this user.
	*/
	public function uniqueID() {
		if(isset($_SESSION["uniqueID"])) {
			return $_SESSION["uniqueID"];
		} else {
			if(self::$loggedIn) {
				$_SESSION["uniqueID"] = self::$loggedIn->uniqueID();
				return $_SESSION["uniqueID"];
			} else {
				$_SESSION["uniqueID"] = md5(randomString(20));
				return $_SESSION["uniqueID"];
			}
		}
	}
}

class LoginInvalidException extends LogicException {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::LOGIN_INVALID, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}
}

class LoginUserLockedException extends LogicException {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::LOGIN_USER_LOCKED, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}
}

class LoginUserMustUnlockException extends LogicException {
	/**
	 * constructor.
	 */
	public function __construct($m = "", $code = ExceptionManager::LOGIN_USER_MUST_UNLOCK, Exception $previous = null) {
		parent::__construct($m, $code, $previous);
	}
}