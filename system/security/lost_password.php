<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 04.04.2013
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class lost_passwordExtension extends ControllerExtension
{

	
		/**
		 * add action
		*/
		public $allowed_actions = array("lost_password");
		
		/**
		 * register method
		*/
		static $extra_methods = array("lost_password");
		
		/**
		 * renders the action
		*/
		public function lost_password()
		{
				Core::setTitle(lang("lost_password", "lost password"));
				Core::addBreadCrumb(lang("lost_password", "lost password"), URL . URLEND);
				if(member::login())
				{
					return "<h1>".lang("lost_password", "lost password")."</h1>" . lang("lp_know_password", "You know your password, else you would not be logged in!");
				}
				if(isset($_GET["code"]) && $_GET["code"] != "")
				{
						$code = $_GET["code"];
						if(DataObject::count("user", array("code" => $code)) > 0)
						{
								if(isset($_GET["deny"])) {
									DataObject::update("user", array("code" => ""), array("code" => $code));
									return lang("lp_deny_okay");	
								}							
								$data = DataObject::_get("user", array("code" => $code), array("id"));
								$pwdform = new Form($this, "editpwd", array(
									new HTMLField("heading","<h1>".lang("lost_password", "lost password")."</h1>"),
									new HiddenField("id", $data["id"]),
									new PasswordField("password",$GLOBALS["lang"]["new_password"]),
									new PasswordField("repeat", $GLOBALS["lang"]["repeat"])
								));
								$pwdform->addValidator(new FormValidator(array($this, "validatepwd")), "pwdvalidator");
								$pwdform->addAction(new FormAction("update", lang("save", "save"),"pwdsave"));
								return $pwdform->render();
						}
				}
				
				
				$form = new Form($this, "lost_password", array(
					new HTMLField("heading","<h1>".lang("lost_password", "lost password")."</h1>"),
					new TextField("email", lang("lp_email_or_user", "E-Mail or Username"))
				), array(
					new FormAction("lp_submit", lang("lp_submit", "Send"))
				));
				$form->setSubmission("Submit");
				$form->addValidator(new FormValidator(array($this,"validate"), array($this, "Validate")), "validate");
				return $form->render();
		}
		
		/**
		 * validates the password
		 *@name validatepwd
		 *@access public
		*/
		public function validatepwd($obj)
		{
				if($obj->form->result["password"] == $obj->form->result["repeat"])
				{
						return true;
				} else
				{
						return $GLOBALS["lang"]["passwords_not_match"];
				}
		}
		/**
		 * saves new password
		 *@name pwdsave
		 *@access public
		*/
		public function pwdsave($data)
		{
				$user = DataObject::get_by_id("User", array("id" => $data["id"]));
				$user->password = $data["password"];
				$user->code = randomString(20);
				if($user->write(false, true))
				{
						return "<h1>".lang("lost_password", "lost password")."</h1>" . lang("lp_update_ok", "Your password was updated successful!");
				} else
				{
						throwErrorByID(3);
				}
				
		}
		/**
		 * validates data
		 *@name validate
		 *@access public
		*/
		public function validate($obj)
		{
				$data = $obj->form->result["email"];
				if(!$data)
					return lang("lp_not_found", "There is no E-Mail-Adresse for your data.");
				
				$object = DataObject::get("user", array("nickname" => array("LIKE", $data), "OR", "email" => $data));
				if($object->Count() > 0 && $object->email) {
					return true;
				} else {
					return lang("lp_not_found", "There is no E-Mail-Adresse for your data.");
				}
		}
		
		public function submit($data)
		{
				$data = DataObject::get_one("user", array("nickname" => $data["email"], "OR", "email" => $data["email"]));
				
				// update code
				$key = randomString(20);
				$data->code = $key;
				$data->write(false, true);
				
				$id = $data["id"];
				$email = $data["email"];
				
				$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"], true, true);
				$text = "<p>" . lang("hello", "Hello")." ".convert::raw2xml($data["title"])."!</p>
				
				<p>".lang("lp_text")."</p>
				<p><a target=\"_blank\" href=\"".BASE_URI.BASE_SCRIPT."profile/lost_password".URLEND."?code=".$key."\">".BASE_URI.BASE_SCRIPT."profile/lost_password".URLEND."?code=".$key."</a></p>
				<p>
				".lang("lp_deny") . "</p>
				<p><a target=\"_blank\" href=\"".BASE_URI.BASE_SCRIPT."profile/lost_password".URLEND."?code=".$key."&amp;deny=1\">".BASE_URI.BASE_SCRIPT."profile/lost_password".URLEND."?code=".$key."&deny=1</a> </p>

				<br />
				<p>".lang("lp_mfg", "Kind Regards")."</p>";
				
				if($mail->sendHTML($email, lang("lost_password"), $text))
				{
						return "<h1>".lang("lost_password", "lost password")."</h1>" . lang("lp_mail_sent", "The E-mail was sent!");
				} else
				{
						return lang("mail_not_sent", "Mail couldn't be transmitted.");
				}
				
		}
}

Object::extend("ProfileController", "lost_passwordExtension");