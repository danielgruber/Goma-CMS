<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 15.09.2012
  * $Version 1.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class contact extends Page
{
		public $can_parent = "all";
		/**
		 * the icon for this page
		*/
		public static $icon = "images/icons/fatcow-icons/16x16/email.png";
		
		/**
		 * we need an e-mail-adress
		*/
		public $db_fields = array('email'	=> 'varchar(200)', "requireemailfield" => "Checkbox");
		
		/**
		 * defaults
		*/
		public $defaults = array(
			"requireemailfield" => 1
		);
		
		/**
		 * the name of this page
		*/
		public $name = '{$_lang_contact}';
		
		/**
		 * generate the extended form
		 *
		 *@name getForm
		 *@access public
		 *@param object - FORM
		*/
		public function getForm(&$form)
		{
				parent::getForm($form);
				
				$form->add($email = new TextField('email',  $GLOBALS["lang"]["email"]),0, "content");
				$form->add(new AutoFormField("requireemailfield", lang("requireEmailField", "email is required")), 0, "content");
				$form->add(new HTMLEditor('data', $GLOBALS["lang"]["text"]),0, "content");
				
				$email->info = lang("email_info", "e-mail-info");
		}
		
		/**
		 * gets the content of this page
		 *
		 *@name getContent
		 *@access public
		*/
		public function getContent()
		{
				$form = new Form($this->controller(), "mailer", array(
					new TextField('name', $GLOBALS["lang"]["name"]),
					new TextField('subject', $GLOBALS["lang"]["subject"]),
					new email("email",  $GLOBALS["lang"]["email"]),
					new textarea("text", $GLOBALS["lang"]["text"], null, "300px"),
					new captcha("captcha")
				),
				array(
					new FormAction("submit",$GLOBALS["lang"]["save"])
				));
				
				
				
				$form->setSubmission("send");
				if($this->requireEmailField) {
					$form->addValidator(new RequiredFields(array("name", "text", "email")), "Required Fields");
				} else {
					$form->addValidator(new RequiredFields(array("name", "text")), "Required Fields");
				}
				return $this->data["data"] . $form->render();
		}
		
}

class contactController extends PageController
{
		/**
		 * sends out the mail
		 *
		 *@name send
		 *@access public
		 *@param data
		*/
		public function send($data)
		{
				$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"], true, true);
				$message = lang("contact_introduction", "Dear Site Owner,<br /><br />\nThe following data was submitted with the contact form:<br /><br />\n\n");
				$message .= "<table width=\"100%\">
								<tr>
									<td>
										".$GLOBALS["lang"]["name"]."
									</td>
									<td>
										".convert::raw2text($data["name"])."
									</td>
								</tr>
								<tr>
									<td>
										".$GLOBALS["lang"]["subject"]."
									</td>
									<td>
										".convert::raw2text($data["subject"])."
									</td>
								</tr>
								<tr>
									<td>
										".$GLOBALS["lang"]["email"]."
									</td>
									<td>
										".convert::raw2text($data["email"])."
									</td>
								</tr>
								<tr>
									<td>
										".$GLOBALS["lang"]["text"]."
									</td>
									<td>
										".convert::raw2text($data["text"])."
									</td>
								</tr>
							</table>
							<hr />";
				$message .= lang("contact_greetings", "Kind Regards<br />\n
							Goma Auto-Messenger");
							
				if($mail->sendHTML($this->model_inst->email, lang("contact"),$message))
					AddContent::addSuccess(lang("successful_saved"));
				else
					AddContent::addError(lang("mail_not_sent", "There was an error transmitting the data."));
				$this->redirectback();
		}
}