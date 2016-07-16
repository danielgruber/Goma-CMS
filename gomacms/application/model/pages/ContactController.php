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
     * index.
     */
    public function index() {
        $form = new Form($this, "mailer", array(
            new TextField('name', lang("name")),
            new TextField('subject', lang("subject")),
            new email("email", lang("email")),
            new textarea("text", lang("text"), null, "300px"),
            new captcha("captcha")
        ),
            array(
                new FormAction("submit", lang("lp_submit"))
            ));

        $form->setModel(array(

        ));

        $form->setSubmission("submitAndSend");
        if ($this->modelInst()->requireEmailField) {
            $form->addValidator(new RequiredFields(array("name", "text", "email")), "Required Fields");
        } else {
            $form->addValidator(new RequiredFields(array("name", "text")), "Required Fields");
        }

        $renderedForm = $form->render();
        if($renderedForm->shouldServe()) {
            $this->tplVars["content"] = $this->modelInst()->data()->forTemplate() . $renderedForm->getResponseBodyString();
            $renderedForm->setBodyString(parent::index());
        }

        return $renderedForm;
    }

    /**
     * sends out the mail
     * @param array $data
     * @param string $from
     * @return GomaResponse
     * @throws Exception
     */
    public function send($data, $from = null)
    {
        if(!is_string($from)) {
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
     * @return GomaResponse
     */
    public function submitAndSend($data, $from = null) {
        $this->send($data, $from);

        AddContent::addSuccess(lang("mail_successful_sent"));

        return GomaResponse::redirect(BASE_URI . $this->namespace);
    }
}
