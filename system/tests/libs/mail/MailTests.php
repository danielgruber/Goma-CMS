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

	public $hookPrepareCalled = 0;
	public $hookSendCalled = 0;

	/**
	 * setup event-handler.
	*/
	public function setUp() {
		$that = $this;
		Core::addToHook("mail_prepareMailer", function($mail, $mailer) use($that) {
			$that->hookPrepareCalled++;
			$that->assertIsA($mail, "Mail");
			$that->assertIsA($mailer, "PHPMailer");

			$mail->debug = 1;
		});

		Core::addToHook("mail_prepareSend", function($mail, $mailer) use ($that) {
			$that->hookSendCalled++;
			$that->assertIsA($mail, "Mail");
			$that->assertIsA($mailer, "PHPMailer");

			$mail->debugSend = 1;
		});
	}

	/**
	 * checks for Events.
	*/
	public function testcheckEvents() {
		$this->unitCheckForEvents("noreply@goma-cms.org", "test@ibpg.eu", "test", "bub");
		$this->unitCheckForEvents("noreply@goma-cms.org", "test@ibpg.eu<Max Mustermann>", "test", "bub");
		$this->unitCheckForEvents("noreply@goma-cms.org", "test@ibpg.eu<Max Mustermann>,test@ibpg.eu", "test", "bub");
	}

    /**
     * tests for default sender.
     */
    public function testDefaultSender() {
        $mail = new Mail();
        $mail->address = "test@goma-cms.org";

        $phpmailer = $mail->prepareMail();

        $this->assertEqual($phpmailer->From, "noreply@" . $_SERVER["SERVER_NAME"]);
        $this->assertEqual($phpmailer->FromName, "noreply@" . $_SERVER["SERVER_NAME"]);
    }

    /**
     * tests parsing of addresses.
     */
    public function testAddressParsing() {
        $this->unitTestAddressParsing("test@ibpg.eu", array("test@ibpg.eu", "test@ibpg.eu"));
        $this->unitTestAddressParsing("test@ibpg.eu<Max Mustermann>", array("test@ibpg.eu", "Max Mustermann"));
    }

    /**
     * @param string $address
     * @param string $expected
     */
    public function unitTestAddressParsing($address, $expected) {
        $this->assertEqual(Mail::parseSingleAddress($address), $expected);
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

	/**
	 * test exception.
	*/
	public function testException() {
		try {
			$mail = new Mail("noreply@test.de");
			$mail->address = "";

			$mail->prepareMail();
			$this->fail("InvalidArgumentException expected");
		} catch(InvalidArgumentException $e) {
			$this->assertEqual($e->getCode(), ExceptionManager::EMAIL_INVALID);
		}
	}

	/**
	 * test exception.
	*/
	public function testExceptionMultiAddr() {
		try {
			$mail = new Mail("noreply@test.de");
			$mail->address = "daniel@ibpg.eu, danielibpg.eu";

			$mail->prepareMail();
			$this->fail("InvalidArgumentException expected");
		} catch(InvalidArgumentException $e) {
			$this->assertEqual($e->getCode(), ExceptionManager::EMAIL_INVALID);
		}
	}
}
