<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Mail-Class.
 *
 * @package		Goma\Mail
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class MailTests extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "Mail";

	/**
	 * internal name.
	*/
	public $name = "Mail";

	protected $hookPrepareCalled = 0;
	protected $hookSendCalled = 0;

	/**
	 * setup event-handler.
	*/
	public function setUp() {
		Core::addToHook("mail_prepareMailer", function($mail, $mailer){
			$this->hookPrepareCalled++;
			$this->assertIsA($mail, "Mail");
			$this->assertIsA($mailer, "PHPMailer");

			$mail->debug = 1;
		});

		Core::addToHook("mail_prepareSend", function($mail, $mailer){
			$this->hookSendCalled++;
			$this->assertIsA($mail, "Mail");
			$this->assertIsA($mailer, "PHPMailer");

			$mail->debugSend = 1;
		});
	}

	/**
	 * checks for Events.
	*/
	public function testcheckEvents() {
		$this->unitCheckForEvents("noreply@goma-cms.org", "daniel@ibpg.eu", "test", "bub");
		$this->unitCheckForEvents("noreply@goma-cms.org", "daniel@ibpg.eu<Daniel Gruber>", "test", "bub");
		$this->unitCheckForEvents("noreply@goma-cms.org", "daniel@ibpg.eu<Daniel Gruber>,test@ibpg.eu", "test", "bub");
	}

	/**
	 * @param string $sender
	 * @param string $address
	 * @param string $subject
	 * @param string $body
	 */
	public function unitCheckForEvents($sender, $address, $subject, $body) {
		$mail = new Mail($sender);
		$mail->address = $address;
		$mail->subject = $subject;
		$mail->body = $body;

		$this->hookPrepareCalled = 0;
		$this->hookSendCalled = 0;
		$mailer = $mail->prepareMail();

		$this->assertEqual($this->hookPrepareCalled, 1);
		$this->assertEqual($this->hookSendCalled, 1);
		$this->assertEqual($mail->debug, 1);
		$this->assertEqual($mail->debugSend, 1);

		$this->assertEqual($mailer->Subject, $subject);
		$this->assertEqual($mailer->Body, $body);
	}
}
