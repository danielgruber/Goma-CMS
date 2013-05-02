<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 02.05.2013
  * $Version 1.1.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class contact extends Page
{
		/**
		 * the name of this page
		*/
		static $cname = '{$_lang_contact}';
		
		/**
		 * the icon for this page
		*/
		static $icon = "images/icons/fatcow16/email.png";
		
		/**
		 * we need an e-mail-adress
		*/
		static $db = array('email'	=> 'varchar(200)'/*, "requireemailfield" => "Checkbox"*/);
		
		/**
		 * defaults
		*/
		static $default = array(
			"requireemailfield" => 1
		);
		
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
				
				$form->add($email = new TextField('email',  lang("email")), null, "content");
				//$form->add(new AutoFormField("requireemailfield", lang("requireEmailField", "email is required")), 0, "content");
				$form->add(new HTMLEditor('data', lang("text")), null, "content");
				
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
					new TextField('name', lang("name")),
					new TextField('subject', lang("subject")),
					new email("email",  lang("email")),
					new textarea("text", lang("text"), null, "300px"),
					new captcha("captcha")
				),
				array(
					new FormAction("submit", lang("lp_submit"))
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
				$mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"], true, $data["email"]);
				$message = lang("contact_introduction", "Dear Site Owner,<br /><br />\nThe following data was submitted with the contact form:<br /><br />\n\n");
				$message .= "<table width=\"100%\">
								<tr>
									<td>
										".lang("name")."
									</td>
									<td>
										".convert::raw2xml($data["name"])."
									</td>
								</tr>
								<tr>
									<td>
										".lang("subject")."
									</td>
									<td>
										".convert::raw2xml($data["subject"])."
									</td>
								</tr>
								<tr>
									<td>
										".lang("email")."
									</td>
									<td>
										".convert::raw2xml($data["email"])."
									</td>
								</tr>
								<tr>
									<td>
										".lang("text")."
									</td>
									<td>
										".convert::raw2xmlLines($data["text"])."
									</td>
								</tr>
							</table>
							<hr />";
				$message .= lang("contact_greetings", "Kind Regards<br />\n
							Goma Auto-Messenger");
							
				if($mail->sendHTML($this->model_inst->email, lang("contact"),$message))
					AddContent::addSuccess(lang("mail_successful_sent"));
				else
					AddContent::addError(lang("mail_not_sent", "There was an error transmitting the data."));
				$this->redirectback();
		}
}
