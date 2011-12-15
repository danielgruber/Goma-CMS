<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 11.12.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class contact extends Page
{
		/**
		 * the icon for this page
		*/
		public static $icon = "images/icons/fatcow-icons/16x16/email.png";
		/**
		 * we need an e-mail-adress
		*/
		public $db_fields = array('email'	=> 'varchar(200)');
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
				$form->add(new HTMLEditor('data', $GLOBALS["lang"]["text"]),0, "content");
				
				$form->remove("pagecomments");
				$form->remove("rating");
				
				$email->info = lang("email_info", "e-mail-info");
		}
		/**
		 * gets the content of this page
		*/
		public function getContent()
		{
				$form = new Form($this->controller(), "mailer", array(
					new TextField('name', $GLOBALS["lang"]["name"]),
					new TextField('subject', $GLOBALS["lang"]["subject"]),
					new email("email",  $GLOBALS["lang"]["email"]),
					new textarea("text", $GLOBALS["lang"]["text"]),
					new captcha("captcha")
				),
				array(
					new FormAction("submit",$GLOBALS["lang"]["save"])
				));
				
				
				
				$form->setSubmission("send");
				$form->addValidator(new RequiredFields(array("name", "text", "email")), "Required Fields");
				
				return $this->data["data"] . $form->render();
		}
		
}

class contactController extends PageController
{
		/**
		 * sends out the mail
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
										".text::protect($data["name"])."
									</td>
								</tr>
								<tr>
									<td>
										".$GLOBALS["lang"]["subject"]."
									</td>
									<td>
										".text::protect($data["subject"])."
									</td>
								</tr>
								<tr>
									<td>
										".$GLOBALS["lang"]["email"]."
									</td>
									<td>
										".text::protect($data["email"])."
									</td>
								</tr>
								<tr>
									<td>
										".$GLOBALS["lang"]["text"]."
									</td>
									<td>
										".text::protect($data["text"])."
									</td>
								</tr>
							</table>
							<hr />";
				$message .= lang("contact_greetings", "Kind Regards<br />\n
							Goma Auto-Messenger");
							
				if($mail->sendHTML($this->model_inst->email, lang("contact"),$message))
					AddContent::addSuccess(lang("successful_saved"));
				else
					AddContent::addError(lang("Error"));
				$this->redirectback();
		}
}