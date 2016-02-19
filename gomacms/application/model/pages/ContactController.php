<?php
defined("IN_GOMA") OR die();

/**
 * Controls sending contact-emails.
 *
 * @package    goma cms
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author    Goma-Team
 * @Version    1.5
 */
class ContactController extends PageController
{
    /**
     * @var string
     */
    public $mailTemplate = "contact/mail.html";

    /**
     * sends out the mail
     * @param array $data
     * @param string $from
     * @throws Exception
     */
    public function send($data, $from = null)
    {
        if(!isset($from)) {
            $from = $this->modelInst()->email;
        }

        $mail = new Mail("noreply@" . $this->request->getServerName(), true, $data["email"]);

        $model = new ViewAccessableData($data);

        if (!$mail->sendHTML($from, lang("contact"), $model->renderWith($this->mailTemplate))) {
            throw new Exception(lang("mail_not_sent", "There was an error transmitting the data."));
        }
    }

    /**
     * @param array $data
     * @param string $from
     */
    public function submitAndSend($data, $from = null) {
        try {
            $this->send($data, $from);

            AddContent::addSuccess(lang("mail_successful_sent"));
        } catch(Exception $e) {
            log_exception($e);
            AddContent::addError($e->getMessage());
        }

        $this->redirectback();
    }
}
