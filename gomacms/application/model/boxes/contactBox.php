<?php defined("IN_GOMA") OR die();

/**
 * contact form as box.
 *
 * @property    boolean useCaptcha
 * @property    string email
 *
 * @package 	goma cms
 * @link 		http://goma-cms.org
 * @license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @Version 	1.5
 */
class ContactBox extends Box
{
    /**
     * title of this dataobject
     */
    public static $cname = '{$_lang_contact}';

    /**
     * additional database-fields needed for this box
     *
     *@name db
     */
    static $db = array
    (
        "email"         => "varchar(200)",
        "useCaptcha"    => "Switch"
    );

    /**
     * gets checkboxes for editing
     *
     * @param Form $form
     */
    public function getEditForm(&$form)
    {
        parent::getEditForm($form);

        $form->add(
            InfoTextField::createFieldWithInfo(
                new Email("email", lang("email")),
                lang("email_info", "e-mail-info")
            ), null, "content"
        );

        $form->add(new HTMLEditor("text", lang("content"), null, null, $this->width), null, "content");

        $form->add(new CheckBox("useCaptcha", lang("useCaptcha")), null, "content");
    }

    /**
     * renders the whole box
     *
     * @return string
     */
    public function getContent()
    {
        $form = new Form(new Controller(), "mailer", array(
            new TextField('name', lang("name")),
            new TextField('subject', lang("subject")),
            new email("email",  lang("email")),
            new textarea("text", lang("text"), null, "100px")
        ),
            array(
                new AjaxSubmitButton("submit", lang("lp_submit"), array($this, "ajaxSubmit"), array($this, "submit"))
            ));

        if($this->useCaptcha) {
            $form->add(new captcha("captcha"));
        }

        $form->addValidator(new RequiredFields(array("name", "text")), "Required Fields");

        return $form->renderPrependString($this->text);
    }

    /**
     * @param array $data
     * @param FormAjaxResponse $response
     * @return FormAjaxResponse
     */
    public function ajaxSubmit($data, $response) {
        $controller = new contactController();
        try {
            $controller->send($data, $this->email);

            $response->addSuccess(lang("mail_successful_sent"));
            $response->resetForm();
        } catch(Exception $e) {
            log_exception($e);
            $response->addError($e->getMessage());
        }

        return $response;
    }

    /**
     * @param array $data
     */
    public function submit($data) {
        $controller = new contactController();
        $controller->submitAndSend($data, $this->email);
    }
}
