<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 26.06.2012
  * $Version 2.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Mail extends Object
{
		/**
		 * This var contains the email of the sender of the mail
		 *@name sendermail
		 *@access public
		 *@var string
		**/
		public $senderemail;
		
		/**
		 * the name of the sender
		 *
		 *@name sendername
		 *@access public
		*/
		public $sendername;
		
		/**
		 * This var defines if the message is html
		 *@name html
		 *@access public
		 *@var bool
		**/
		public $html;
		
		/**
		 * This var defines if the message schould be replied
		 *@name reply
		 *@access public
		 *@var bool
		**/
		public $reply;
		
		/**
		 * sets $addressor, $html, $reply
		 *@name __construct
		 *@param string - addressor 
		 *@param bool - message format
		 *@param bool - reply
		 *@access public
		 *@return bool
		**/
		public function __construct($from = null, $html = true, $reply = null, $sendername = null)
		{
				parent::__construct();
				
				/* --- */
				
				if($from === null) {
					$from = "noreply@" . $_SERVER["SERVER_NAME"];
				}
				
				if(!empty($from))
				{
						$this->senderemail = $from;
						if($sendername === null)
							$this->sendername = $from;
						else
							$this->sendername = $from;
				}
				$this->html = $html;
				$this->reply = $reply;
				return true;
		}
		
		/**
		 * sends a mail
		 *@name send
		 *@param string - adresse
		 *@param string - subject	 
		 *@param string - text
		 *@access public
		 *@return bool
		**/
		public function send($adresse, $subject, $message)
		{
				$mail = new libMail();
				
				if(!empty($this->senderemail))
				{
						$mail->from($this->senderemail);
						if($this->reply)
						{
								$mail->ReplyTo($this->reply);
						}
				}
				if($this->html)
				{
						$mail->Html($message, "UTF-8");
				} else {
						$mail->text($message, "UTF-8");
				}
				
				$mail->Subject($subject);
				$mail->To($adresse);
				
				return $mail->send();
		}
		/**
		 * sends HTML with predefined template
		 *@name sendHTML
		 *@access public
		 *@param string - addresse
		 *@param string - subject
		 *@param string - message
		*/
		public function sendHTML($adresse,$subject,$message)
		{
				$this->html = true;
				$template = new Template();
				$template->assign("subject", $subject);
				$template->assign("message", $message);
				$text = $template->display('mail.html');
				
				return $this->send($adresse,$subject, $text);
		}
}