<?php defined("IN_GOMA") OR die();
/**
 * Basic Mail-Class for Goma.
 *
 * @package		Goma\Mail
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class Mail
{
		/**
		 * the sender.
		 *
		 * @name 	sendermail
		 * @access 	public
		 * @var 	string
		**/
		public $senderemail;
		
		/**
		 * the name of the sender
		 *
		 * @name 	sendername
		 * @access 	public
		*/
		public $sendername;
		
		/**
		 * This var defines if the message is html
		 *
		 * @name 	html
		 * @access 	public
		 * @var 	bool
		**/
		public $html;
		
		/**
		 * This var defines if the message schould be replied
		 *
		 * @name 	reply
		 * @access 	public
		 * @var 	bool
		**/
		public $reply;
		
		/**
	 	 * subject.
		*/
		public $subject;

		/**
	  	 * receiver-list.
		*/
		public $address;

		/**
	   	 * body.
		*/
		public $body;

		/**
		 * sets $sender, $html, $reply
		 *
		 * @name 	__construct
		 * @param 	string - address of sender
		 * @param 	bool - message format
		 * @param 	bool - reply
		 * @access 	public
		 * @return 	bool
		**/
		public function __construct($from = null, $html = true, $reply = null, $sendername = null)
		{
			if($from === null) {
				$from = "noreply@" . $_SERVER["SERVER_NAME"];
			}
			
			if(!empty($from))
			{
				$this->senderemail = $from;
				if($sendername === null) {
					$this->sendername = $from;
				} else {
					$this->sendername = $from;
				}
			}
			$this->html = $html;
			$this->reply = $reply;
			return true;
		}
		
		/**
		 * sends a mail
		 *
		 * @name 	send
		 * @param 	string - adresse
		 * @param 	string - subject	 
		 * @param 	string - text
		 * @access 	public
		 * @return 	bool
		**/
		public function send($address, $subject, $message)
		{
			$this->address = $address;
			$this->subject = $subject;
			$this->body = $message;
			
			$mail = $this->prepareMail();

			return $mail->send();
		}

		/**
	 	 * builds PHP-Mailer-Object and calls two hooks.
	 	 *
		 * @hook 	mail_prepareMailer($this, $mailer) 	called before data like sender or receiver is set
		 * @hook 	mail_prepareSend($this, $mailer) 	called immediatly before preperation is done.
		 * @name 	prepareMail
		*/
		public function prepareMail() {
			$mail = new PHPMailer;

			Core::CallHook("mail_prepareMailer", $this, $mail);


			if(!empty($this->senderemail)) {
				$mail->From = $this->senderemail;

				if($this->reply) {
					$mail->addReplyTo($this->reply);
				}
			}

			if($this->html) {
				$mail->isHTML(true);
			} else {
				$mail->isHTML(false);
			}
			
			$mail->Subject = $this->subject;
			$mail->Body = $this->body;
			
			if(trim($this->address) == "") {
				throw new InvalidArgumentException("You need at least one recipent in your list of receivers.");
			}

			foreach($this->parseAddress($this->address) as $addAddr) {
				if(is_array($addAddr)) {
					$mail->addAddress($addAddr[0], $addAddr[1]);
				} else {
					$mail->addAddress($addAddr);
				}
			} 

			Core::callHook("mail_prepareSend", $this, $mail);

			return $mail;
		}

		/**
	 	 * parses address for PHP-Mailer.
		*/
		public function parseAddress($address) {
			$parts = explode(",", $address);
			$mails = array();

			foreach($parts as $part) {
				if(strpos($part, "@") === false) {
					throw new InvalidArgumentException("Address $part is not valid.");
				}

				if(preg_match('/\^(.+)<(.*)\>$/', $part, $matches)) {
					$mails[] = array(trim($matches[1]), trim($matches[2]));
				} else {
					$mails[] = $part;
				}
			}

			return $mails;
		}

		/**
		 * sends HTML with predefined template
		 *
		 * @name 	sendHTML
		 * @access 	public
		 * @param 	string 	addresse
		 * @param 	string 	subject
		 * @param 	string 	message
		*/
		public function sendHTML($adresse, $subject, $message)
		{
				$this->html = true;
				$template = new Template();
				$template->assign("subject", $subject);
				$template->assign("message", $message);
				$text = $template->display('mail.html');
				
				return $this->send($adresse,$subject, $text);
		}
}