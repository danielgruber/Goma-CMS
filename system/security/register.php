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
 * @version		2.0
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
		 * add custom actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array(
			"register"
		);
		
		/**
		 * register custom method
		 *
		 *@name extra_methods
		*/
		public static $extra_methods = array("register");
		
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
				
				// check if registering is not available on this page
				} else if(!self::$enabled) {
					return "<div class=\"notice\">" . lang("register_disabled", "You cannot register on this site!") . "</div>";
					
				// great, let's show a form
				} else {
					$user = new user();
					return $user->controller()->form(false, false, array(), false, "doregister");
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