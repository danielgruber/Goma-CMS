<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 23.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class RegisterExtension extends ControllerExtension
{
		
		
		/**
		 * add custom actions
		*/
		public $allowed_actions = array(
			"register"
		);
		
		/**
		 * register custom method
		*/
		public static $extra_methods = array("register");
		
		/**
		 * add custom method
		*/
		public function register()
		{
				Core::setTitle(lang("register"));
				Core::addBreadCrumb(lang("register"), "profile/register/");
				if(member::login()) {
					HTTPResponse::Redirect(BASE_URI);
					exit;
				} else if(isset($_GET["activate"])) {
					$data = DataObject::_get("user", array("code" => $_GET["activate"]));
					
					if($data->_count() > 0 && $data->status != 2) {
						$data->status = 1; // activation
						$data->code = randomString(10); // new code
						if($data->write(false, true)) {
							return '<div class="success">'.lang("register_ok").'</div>';
						} else {
							throwError(6, 'Server-Error', 'Could not save data.');
						}
					} else {
						// pssst ;)
						return '<div class="success">'.lang("register_ok").'</div>';
					}
				} else if(!SettingsController::get("register_enabled")) {
					return "<div class=\"notice\">" . lang("register_disabled", "You cannot register on this site!") . "</div>";
				} else {
					$this->model_inst = new user();
					return $this->form(false, false, array(), false, "doregister");
				}
		}
		/**
		 * registers the user
		 * we don't use register, because of constructor
		 *
		 *@name doRegister
		 *@access public
		*/
		public function doregister($data)
		{
				if(settingsController::get("register_email")) {
					$data["status"] = 0;
					$data["code"] = randomString(10);
					// send out mail
					$email = "";
					$email .= lang("hello") . " ".text::protect($data["nickname"])."<br />\n<br />\n";
					$email .= lang("thanks_for_register") . "<br />\n<br />\n";
					$email .= lang("account_activate") . "<br />\n";
					$email .= '<a target="_blank" href="'. BASE_URI . BASE_SCRIPT . "profile/register/?activate=" . $data["code"] .'">' . BASE_URI . BASE_SCRIPT . "profile/register/?activate=" . $data["code"] . "</a><br />\n<br />\n";
					$email .= lang("register_greetings");
					$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"]);
					if(!$mail->sendHTML($data["email"], lang("register"), $email)) {
						throwError(6, 'Server-Error', 'Could not send out mail.');
					}
					if($this->save($data))			
						return '<div class="success">' . lang('register_ok_activate', "User successful created. Please visit your e-mail-provider to check out the e-mail we sent to you.") . '</div>';		
					else
						throwError(6, 'Server-Error', 'Could not save data.');

				} else {
					if($this->save($data))			
						return '<div class="success">' . lang('register_ok', "Ready to login! Thanks for using this Site!") . '</div>';		
					else
						throwError(6, 'Server-Error', 'Could not save data.');
				}
		}
}

Object::extend("ProfileController", "RegisterExtension");