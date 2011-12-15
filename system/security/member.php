<?php
/**
  * you can use this class for some information about the current user
  * the following information is shown:
  * nickname
  * name
  * email
  * id
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 20.11.2011
  * $Version 003
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class member extends object
{
		/**
		 * this var contains an errormessage, if an error occured
		 *@name error
		 *@access public
		 *@var array
		*/
		public static $error = "";
		/**
		 * this userid
		 *@name id
		 *@access public
		*/
		public static $id = "";
		/**
		 * the nickname
		 *@name nickname
		 *@access public
		*/
		public static $nickname = "";
		/**
		 * the name
		 *@name name
		 *@access public
		*/
		public static $name = "";
		/**
		 * the email
		 *@name email
		 *@access public
		*/
		public static $email = "";
		/**
		 * current rights as numeric
		 *@name rights
		 *@access public
		 *@var num
		*/
		public static $rights;
		/**
		 * current groupid
		*/
		public static $groupid;
		/**
		 * Methods
		*/
		public static function groupids() {
			if(isset($_SESSION["user_id"])) {
				
				if(Permission::$currrights  !== false) {
					$ids = array_keys(Permission::$currrights);
				} else {
					$user = DataObject::get("user", array("id" => $_SESSION["user_id"]));
					$group = $user->group(array());
					if($group->rights == 10) {
						$rights = 10;
						$groups = array($group->id => 10);
						return true;
					} else {
						Permission::$currrights = array($group->id => $group->rights);
						foreach($user->groups(array(), array("rights")) as $group) {
							$groups[$group->id] = $group->rights;
						}		
					}
					Permission::$currrights = $groups;
					arsort(Permission::$currrights);
					$ids = array_keys(Permission::$currrights);
					unset($groups);
					
				}
				return $ids;
			} else {
				return array(0);
			}
		}
		/**
		 * logs a user off
		 *
		 *@name doLogout
		 *@access public
		*/
		public function doLogout() {
			if(isset($_SESSION["user_counted"]))
				$counted = true;
					
			if(isset($_SESSION["lang"]))
				$lang = $_SESSION["lang"];
			
			session_unset();
				
			if(isset($counted))
				$_SESSION["user_counted"] = true;
				
			if(isset($lang))
				$_SESSION["lang"] = $lang;
				
		}
		/**
		 * checks if an user is login
		 *@name login
		 *@access public
		 *@return bool
		*/
		public static function login()
		{
				return right(2);
		}
		/**
		 * checks if an user have the rights
		 *@name right
		 *@access public
		 *@param string|numeric - if numeric: the rights from 1 - 10, if string: the advanced rights
		 *@return bool
		*/
		public static function right($name)
		{
				return right($name);
		}
		/**
		 * login an user with the params
		 * if the params are incorrect, it returns false
		 *@name doLogin
		 *@access public
		 *@param string - nickname
		 *@param string - password
		 *@return bool
		*/
		public static function doLogin($user, $pwd)
		{
				
				$data = DataObject::get_one("user", array("nickname" => array("LIKE", $user)));
				
				if($data)
				{
						$d = $data->ToArray();
						
						if(Hash::checkHashMatches($pwd, $d["password"])) {
							$_SESSION['user_group'] = $d['groupid'];
							if(($d['status'] != 2 && $d["status"] != 0) || Permission::check(10))
							{
									$_SESSION['user_nickname'] = $d['nickname'];
									$_SESSION['user_name'] = $d['name'];
									$_SESSION["user_group"]	= $d["groupid"];
									$_SESSION['user_email'] = $d['email'];
									$_SESSION['user_id'] = $d['id'];
									
									$rights = DataObject::get_one("group", array("id" => $d["groupid"]))->rights;
									$_SESSION["user_rights"] = $rights;
									$data->phpsess = session_id();
									$data->code = randomString(20);
									$data->write(false, true);
									return true;
							} else if($d["status"] == 0) {
								
							} else
							{
									unset($_SESSION['user_group']);
									addcontent::add($GLOBALS['lang']['login_locked']);
									return false;
							}
						} else {
							logging("Login with wrong Password for User ".$d["nickname"]." with IP: ".$_SERVER["REMOTE_ADDR"].""); // just for security
							addcontent::add($GLOBALS['lang']['wrong_login']);
							return false;
						}
				} else
				{
						logging("Login with wrong Username/Password with IP: ".$_SERVER["REMOTE_ADDR"].""); // just for security
						addcontent::add($GLOBALS['lang']['wrong_login']);
						return false;
				}
		}
		/**
		 * checks the vars
		 *@name checkvars
		 *@access public
		*/
		public static function checkvars()
		{
				if(isset($_SESSION['user_nickname']))
				{
						self::$nickname = $_SESSION['user_nickname'];
						self::$name = $_SESSION['user_name'];
						self::$email = $_SESSION['user_email'];
						self::$id = $_SESSION['user_id'];
						self::$rights = $_SESSION["user_rights"];
						self::$groupid = $_SESSION["user_group"];
						
						if(!self::$rights) {
							self::$rights = DataObject::get_one('group', array("id" => $_SESSION["user_group"]))->rights + 1; // rights of current user
						}
				}
		}
		/**
		 * require login
		 *
		 *@name require_Login
		 *@access public
		*/
		public function require_login() {
			if(!self::login()) {
				AddContent::addError(lang("require_login"));
				HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "profile/login/?redirect=" . $_SERVER["REQUEST_URI"]);
			}
			return true;
		}
}

member::checkvars();


class userController extends Controller
{
		/**
		 * checks user-login and logout and so on
		 *@name execute
		*/
		public static function execute()
		{			
				if(isset($_SESSION['user_nickname']))
				{
						
						$row = dataobject::get("user", array('id' => $_SESSION['user_id']));
						
						if($row)
						{			
								$currsess = session_id();
								
								if($row['phpsess'] != $currsess)
								{
										session_unset();
								}
								if($row["timezone"]) {
									Core::setCMSVar("TIMEZONE", $row["timezone"]);
									date_default_timezone_set(Core::getCMSVar("TIMEZONE"));
								}
						} else
						{
								member::doLogout();				

						}
				}	
				
						
		}
		/**
		 * gets userdata
		 *@name getuserdata
		 *@param id - userid
		 *@return array
		*/
		public function getuserdata($id)
		{
				$d = DataObject::get($this, array('id' => $id));
				if(!$d) {
					session_unset();
				}
				$r = $d->group(array(), array("rights"));
				$arr = $d->to_array();
				$arr['rights'] = $r['rights'];
				return $arr;
		}
		
		/**
		 * saves the user-pwd
		 *@access public
		 *@name savepwd
		*/
		public function pwdsave($result)
		{
				addcontent::add('<div class="success">'.lang("successful_saved", "The data was successfully written!").'</div>');
				DataObject::update("user", array("password" => Hash::getHashFromDefaultFunction($result["password"])), array('id' => $result["id"]));
				$this->redirectback();
		}
		/**
		 * saves the user-pwd
		 *@access public
		 *@name savepwd
		*/
		public function ajaxpwdsave($result, $response)
		{
				$user = DataObject::get("user", array("id" => $result["id"]));
				$user->password = $result["password"];
				$user->write(false, true);			
				$response->exec(dialog::closeById($_GET["boxid"]));
				return $response->render();
		}
		
}


class User extends DataObject
{
		public $name = '{$_lang_user}';
		public $db_fields = array(		'nickname'		=> 'varchar(200)',
										'name'			=> 'varchar(200)',
										'email'			=> 'varchar(200)',
										'password'		=> 'varchar(200)',
										'avatar'		=> 'Image',
										'signatur'		=> 'text',
										'status'		=> 'int(2)',
										'phpsess'		=> 'varchar(200)',
										"code"			=> "varchar(200)",
										"timezone"		=> "timezone");
		/**
		 * the table_name is users not user
		 *@name table_name
		*/
		public $table_name = "users";
		/**
		 * every user has one group
		*/
		public $has_one = array('group' => 'group'); 
		/**
		 * every user has additional groups
		*/
		public $many_many = array("groups" => "group");
		/**
		 * users are activated by default
		*/
		public $defaults = array(
				'status'	=> '1'
		);
		/**
		 * we add an index to username and password, because of logins
		*/
		public $indexes = array(
			"login"	=> array("type"	=> "INDEX", "fields" => 'nickname, password')
		);
		
		public $searchable_fields = array(
			"nickname", "name", "email", "signatur"
		);
		
		public $insertRights = 1;
		/**
		 * gets all groups if a object
		 *
		 *@name getAllGroups
		 *@access public 
		*/
		public function getAllGroups() {
			$groups = $this->groups();
			$groups->dataset = true;
			$groups->addRecord($this->group());
			return $groups;
		}
		
		/**
		 * rights
		*/
		
		public function canWrite($data)
		{
				$groupid = (isset($data["groupid"])) ? $data["groupid"] : $this->groupid;
				if($data["id"] == member::$id && member::login())
				{
						if(isset($data["groupid"]) && $data["groupid"] == member::$groupid)
								return true;
						else if(!isset($data["groupid"]))
								return true;
						else
								return false;
				} else
				{
						if(parent::canWrite($data))
						{
								$groupid = (isset($data["groupid"])) ? $data["groupid"] : $this->groupid;
								$rights = DataObject::get_obe("group", array("id" => $data["groupid"]))->rights;
								if($rights <= member::$rights)
										return true;
								else
										return false;
						}
						
						return false;
						
						
				}
		}
		
		public function canInsert($data)
		{
				if(parent::canInsert($data))
				{					
						$groupid = (isset($data["groupid"])) ? $data["groupid"] : $this->groupid;
						$rights = DataObject::get_one("group", array("id" => $groupid))->rights;
						if($rights <= 4 || right(10))
								return true;
						else
								return false;
				}
				
				return false;
		}
		
		/**
		 * forms
		*/
		public function getForm(&$form)
		{
				// add default tab
				$form->add(new TabSet("tabs", array(
					$general = new Tab("general",array(
							new TextField("nickname", $GLOBALS["lang"]["username"]),
							new TextField("name",$GLOBALS["lang"]["name"]),
							new TextField("email", $GLOBALS["lang"]["email"]),
							new PasswordField("password", $GLOBALS["lang"]["password"], ""),
							new PasswordField("repeat", $GLOBALS["lang"]["repeat"], "")
						),$GLOBALS["lang"]["general"])
				)));
				
				if($this->id != member::$id && right(7))
				{
					
					$form->add(new Tab("admin", array(
						new HasOneDropDown("group", lang("group", "Group"), "name", ' `rights` <= "'.member::$rights.'"'),
						new Manymanydropdown("groups", lang("groups", "Groups"), "name", '`rights` < '.member::$rights)
					), $GLOBALS["lang"]["administration"]),0, "tabs");
				} else
				{
						$group = DataObject::getone("group", array("rights" => 4));
						
						$form->add(new HiddenField("groupid", $group["id"]));
				}
				
				if(!member::login())
				{
						$code = settingsController::get("register");
						if($code != "")
						{
								$general->add(new TextField("code", lang("register_code", "Code")));
								$form->addValidator(new FormValidator(array($this, 'validatecode')), "validatecode");
						}
				}
				
				$form->addValidator(new RequiredFields(array("nickname", "password", "repeat", "groupid")), "required_users");
				$form->addValidator(new FormValidator(array($this, '_validateuser')), "validate_user");
				
				// if we send out activation-mails, we need to have a email-adresse
				if(settingsController::get("register_email") && !right(2)) {
					$form->addValidator(new RequiredFields(array("email")), "email");
				}
				
				if(defined("IS_BACKEND")) {
					
					$form->addAction(new AjaxSubmitButton("submit", $GLOBALS["lang"]["save"], "ajaxsave"));
					$form->addAction(new Button("cancel", lang("cancel"), "LoadTreeItem(0);"));
				} else {
					$form->addAction(new FormAction("submit", $GLOBALS["lang"]["save"]));
				}
				
				
				
		}
		/**
		 * gets the edit-form for profile-edit or admin-edit
		 *
		 *@name getEditForm
		 *@access public
		 *@param object - form
		*/
		public function getEditForm(&$form)
		{
				
				unset($form->result["password"]);
				
				$form->add(new TabSet("tabs", array(
					new Tab("general",array(
							new HtmlField("username",lang("username", "Username") . "<br /><strong>".text::protect($this["nickname"])."</strong>"),
							new TextField("name",  $GLOBALS["lang"]["name"]),
							new TextField("email", $GLOBALS["lang"]["email"]),
							$this->doObject("timezone")->formfield(lang("timezone")),
							// password management in external window
							new ajaxexternalform("passwort", $GLOBALS["lang"]["password"], '**********', $this->pwdform($this->id)),
							new ImageUpload("avatar", $GLOBALS["lang"]["pic"]),
							new TextArea("signatur", $GLOBALS["lang"]["signatur"], null, "100px")
							
						), $GLOBALS["lang"]["general"])			
				)));
				
				if($this["id"] != $_SESSION["user_id"] && right(DataObject::get("group", array("id" => $this["groupid"]))->rights))
				{
					
					// if a user is not activated by mail, admin should have a option to activate him manually
					if($this->status == 0) {
						$status = new radiobutton("status", lang("status", "Status"), array(0 => lang("not_unlocked", "Not activated yet"),1 => lang("not_locked", "Unlocked"), 2 => lang("locked", "Locked")));
					} else {
						$status = new radiobutton("status", lang("status", "Status"), array(1 => lang("not_locked", "Unlocked"), 2 => lang("locked", "Locked")));
					}
					
					$form->add(new Tab("admin", array(
						new HasOneDropdown("group", lang("group", "Group"), "name", 'rights < '.member::$rights.''),
						new ManyManyDropdown("groups", lang("groups", "Groups"), "name", 'rights < '.member::$rights),
						$status
					), $GLOBALS["lang"]["administration"]),0,"tabs");
				}
				
				$form->addValidator(new RequiredFields(array("nickname", "groupid")), "requirefields");
				
				
				if(right(10) && defined("IS_BACKEND"))
				{
						$form->addAction(new HTMLAction("delete", '<a href="'.ROOT_PATH.'admin/usergroup/model/user/'.$this->id.'/delete'.URLEND.'?redirect='.urlencode(ROOT_PATH . "admin/usergroup/").'" rel="ajaxfy" class="button red">'.lang("delete", "Delete").'</a>'));
				}
				
				if(defined("IS_BACKEND")) {
					$form->addAction(new Button("cancel", $GLOBALS["lang"]["cancel"], 'LoadTreeItem(0);'));
					$form->addAction(new AjaxSubmitButton("saveuser", $GLOBALS["lang"]["save"], "ajaxsave"));
				} else {
					$form->addAction(new LinkAction("cancel", $GLOBALS["lang"]["cancel"], "profile/"));
					$form->addAction(new FormAction("submit", $GLOBALS["lang"]["save"]));
				}		
		}
		/**
		 * form for password-edit
		 *@name pwdform
		*/
		public function pwdform($id)
		{
				$pwdform = new Form($this->controller(), "editpwd", array(
					new HiddenField("id", $id),
					new PasswordField("oldpwd", $GLOBALS["lang"]["old_password"]),
					new PasswordField("password",$GLOBALS["lang"]["new_password"]),
					new PasswordField("repeat", $GLOBALS["lang"]["repeat"])
				));
				$pwdform->addValidator(new FormValidator(array($this, "validatepwd")), "pwdvalidator");
				$pwdform->addAction(new AjaxSubmitButton("submit", lang("save", "save"), "ajaxpwdsave", "pwdsave"));
				return $pwdform;
		}
		/**
		 * validates an new user
		 *@name validateuser
		 *@access public
		*/
		public function _validateuser($obj)
		{
				if($obj->form->result["password"] == $obj->form->result["repeat"])
				{
						// check if username is unique
						if(DataObject::count("user", array("nickname" => $obj->form->result["nickname"])) > 0)
						{
								return lang("register_username_bad", "The username is already taken.");
						}
						return true;
				} else
				{
						return $GLOBALS["lang"]["passwords_not_match"];
				}
		}
		/**
		 * sets the password with md5
		 *@name setpassword
		 *@access public
		*/
		public function setpassword($value)
		{
				$this->setField("password", Hash::getHashFromDefaultFunction($value));
				return true;
		}
		/**
		 * valdiates code
		 *@name validatecode
		 *@access public
		 *@param string - value
		 *@return true|string
		*/
		public function validateCode($value)
		{
				if(is_array($value)) {
					return true;
				}
				if(!defined("IS_BACKEND")) {
						$code = settingsController::get("register");
						if($code != "")
								return ($code == $value) ? true : lang("register_code_wrong", "The Code was wrong!");
						else
								return true;
				}
				return true;
		}
		/**
		 * gets the password
		 *@name setpassword
		 *@access public
		*/
		public function getpassword($value)
		{
				return "";
		}
		/**
		 * validates the pwd
		 *@name validatepwd
		 *@access public		 
		*/
		public function validatepwd($obj)
		{
				if(isset($obj->form->result["oldpwd"]))
				{
						$data = DataObject::get("user", array("id" => $obj->form->result["id"]))->ToArray();
						$pwd = $data["password"];
						if(Hash::checkHashMatches($obj->form->result["oldpwd"], $pwd))
						{
								if(isset($obj->form->result["password"], $obj->form->result["repeat"]) && $obj->form->result["password"] != "")
								{
										if($obj->form->result["password"] == $obj->form->result["repeat"])
										{
												return true;
										} else
										{
												return $GLOBALS["lang"]["passwords_not_match"];
										}
								} else
								{
										return $GLOBALS["lang"]["passwords_not_match"];
								}
						} else
						{
								return $GLOBALS["lang"]["password_wrong"];
						}
				} else
				{
						return $GLOBALS["lang"]["password_wrong"];
				}
		}
		
	
}