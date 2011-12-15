<?php
/**
  * this class let you know much about other classes or your class
  * you can get childs or other things
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 23.06.2011
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
		public function __construct($from = null, $html = true, $reply = true, $sendername = null)
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
				$header = "";
				
				if(!empty($this->senderemail))
				{
						$header = "From: ".$this->sendername." <" . $this->senderemail . ">\r\n";
						if($this->reply)
						{
								$header .= "Reply-To: ".$this->sendername." <" . $this->senderemail . ">\r\n";
						}
				}
				if($this->html)
				{
						$header .= "Content-Type: text/html\r\n";
				}
				$header .= 'X-Mailer: Goma '.GOMA_VERSION.' - '.BUILD_VERSION.'';
				
				
				if(mail($adresse, $subject, $message, $header))
				{
						return true;
				}
				
				return false;
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
				$template = new Template();
				$template->assign("subject", $subject);
				$template->assign("message", $message);
				$text = $template->display('mail.html');
				return $this->send($adresse,$subject, $text);
		}
}