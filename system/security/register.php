<?php
/**
 * @package		Goma\Security\Users
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined('IN_GOMA') OR die();

/**
 * extends the user-class with a registration-form.
 *
 * @package		Goma\Security\Users
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		2.3
 */
class RegisterExtension extends ControllerExtension
{
		
		/**
		 * some settings
		*/
		
		/**
		 * a bool which indicates whether registration is enabled or disabled
		 *
		 *@name enabled
		 *@access public
		*/
		public static $enabled = false;
		
		/**
		 * a bool which indicates whether a new user needs to validate his email-adresse or not
		 *
		 *@name validateMail
		 *@access public
		*/
		public static $validateMail = true;
		
		/**
		 * registration code, if set to null or "" no code is required
		 *
		 *@name registerCode
		 *@access public
		*/
		public static $registerCode;

		/**
		 * set to true when a new user must be validated by the administrator.
		*/
		public static $mustBeValidated = false;

		/**
		 * email to notify when a user registers that should be validated.
		 * also allowed is an array or commma-seperated value.
		 * if set to 0, every user with the permission to validate can validate users.
		*/
		public static $validationMail = null;
		
		/**
		 * add custom actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array(
			"register", "resendActivation", "activate"
		);
		
		/**
		 * register custom method
		 *
		 *@name extra_methods
		*/
		public static $extra_methods = array("register", "doRegister", "resendActivation", "activate");
		
		/**
		 * add custom method to handle the action
		 *
		 *@name register
		 *@access public
		*/
		public function register()
		{
			// define title of this page
			Core::setTitle(lang("register"));
			Core::addBreadCrumb(lang("register"), "profile/register/");
			
			// check if logged in
			if(member::login()) {
				HTTPResponse::Redirect(BASE_URI);
				exit;
				
			// check if link from e-mail
			} else if(isset($_GET["activate"])) {
				$data = DataObject::get("user", array("code" => $_GET["activate"]));
				
				if($data->count() > 0 && $data->status != 2) {
					$data->code = randomString(10); // new code
					if(self::$mustBeValidated) {
						$data->status = 3; // activation
						$this->sendMailToAdmins($data);
					} else {
						$data->status = 1; // activation
					}
					
					
					if($data->write(false, true)) {
						if(self::$mustBeValidated) {
							return '<div class="success">'.lang("register_requre_acitvation").'</div>';
						} else {
							return '<div class="success">'.lang("register_ok").'</div>';
						}
					} else {
						throw new Exception("Could not save data");
					}
				} else {
					// pssst ;)
					return '<div class="success">'.lang("register_ok").'</div>';
				}
			
			// check if registering is not available on this page
			} else if(!self::$enabled) {
				return "<div class=\"notice\">" . lang("register_disabled", "You cannot register on this site!") . "</div>";
				
			// great, let's show a form
			} else {
				$user = new user();
				return $user->controller($this->getOwner())->form(false, false, array(), false, "doregister");
			}
		}

		/**
		 * resends the activation mail.
		*/
		public function resendActivation() {
			if($this->getParam("email")) {
				if($data = DataObject::get_one("user", array("email" => $this->getParam("email")))) {
					$this->sendMail($data);
					return lang("register_resend");
				} else {
					return "";
				}
			} else {
				$this->redirectBack();
			}
		}
		
		/**
		 * sends activation mail.
		*/
		public function sendMail($data) {
			$email = "";
			$email .= lang("hello") . " ".convert::raw2text($data["nickname"])."<br />\n<br />\n";
			$email .= lang("thanks_for_register") . "<br />\n<br />\n";
			$email .= lang("account_activate") . "<br />\n";
			$email .= '<a target="_blank" href="'. BASE_URI . BASE_SCRIPT . "profile/register/?activate=" . $data["code"] .'">' . BASE_URI . BASE_SCRIPT . "profile/register/?activate=" . $data["code"] . "</a><br />\n<br />\n";
			$email .= lang("register_greetings");
			$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"]);
			if(!$mail->sendHTML($data["email"], lang("register"), $email)) {
				throw new Exception("Could not send mail.");
			}

			return true;
		}
		
		/**
		 * sends activation mail.
		*/
		public function sendMailToAdmins($user) {
			// first step: get emails that we want to send to.

			if(self::$validationMail == null) {
				// get from permissions
				$emails = "";

				// get group ids that have the permission USERS_MANAGE
				$data = DataObject::get("group", array("permissions" => array("name" => "USERS_MANAGE")));
				$groupids = $data->fieldToArray("id");

				$users = DataObject::get("user", array("groups" => array("id" => $groupids)));
				
				$emails = implode(",", $users->fieldToArray("email"));

			} else {
				if(is_array(self::validationMail)) {
					$emails = implode(",", self::$validateMail);
				} else {
					$emails = self::$validateMail;
				}
			}

			if(!is_object($user)) {
				$user = new User($user);
			}

			$view = $user 	->customise(array("activateLink" => BASE_URI . BASE_SCRIPT . "profile/activate" . URLEND . "?activate=" . $user["code"]))
							->renderWith("mail/activate_account_admin.html");

			$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"]);
			if(!$mail->sendHTML($emails, lang("user_activate"), $view)) {
				throw new Exception("Could not send mail.");
			}

			return true;
		}

		/**
		 * activation method for admins.
		*/
		public function activate() {
			if(!Permission::check("USERS_MANAGE")) {
				member::redirectToLogin();
			}

			if(isset($_GET["activate"]) && $data = DataObject::get_one("user", array("code" => $_GET["activate"]))) {
				if($this->getOwner()->confirm(lang("user_activate_confirm"), lang("yes"), null, $data->generateRepresentation(true))) {
					$data->status = 1;
					$data->code = randomString(10);

					$view = $data 	->customise()
									->renderWith("mail/account_activated.html");

					$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"]);
					if(!$mail->sendHTML($data->email, lang("user_activate_subject"), $view)) {
						throw new Exception("Could not send mail.");
					}

					if($data->write(false, true)) {
						AddContent::addSuccess(lang("user_activated_subject"));
						$this->getOwner()->redirectBack();
					} else {
						throw new Exception("Could not save data.");
					}
				}
			} else {
				$this->getOwner()->redirectBack();
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
			if(self::$validateMail) {
				$data["status"] = 0;
				$data["code"] = randomString(10);
				
				// send mail
				$this->sendMail($data);

				if($this->getOwner()->save($data))	{		
					return '<div class="success">' . lang('register_ok_activate', "User successful created. Please visit your e-mail-provider to check out the e-mail we sent to you.") . '</div>';		
				}

			} else if(self::$mustBeValidated) {

				$data["status"] = 3;
				$data["code"] = randomString(10);
				// send mail
				$this->sendMailToAdmins($data);

				if($this->getOwner()->save($data))	{		
					return '<div class="success">' . lang('register_wait_for_activation', "The account was sucessfully registered, but an administrator needs to activate it. You'll be notified by email.") . '</div>';		
				}
			} else {
				if($this->getOwner()->save($data)) {	
					return '<div class="success">' . lang('register_ok', "Ready to login! Thanks for using this Site!") . '</div>';		
				}
			}
		}
}

Object::extend("ProfileController", "RegisterExtension");