<?php defined("IN_GOMA") OR die();

/**
 * contact form as page.
 *
 * @package    goma cms
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author    Goma-Team
 * @Version    1.5
 */
class Contact extends Page
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
    static $db = array('email' => 'varchar(200)'/*, "requireemailfield" => "Checkbox"*/);

    /**
     * defaults
     */
    static $default = array(
        "requireemailfield" => 1
    );

    /**
     * generate the extended form
     *
     * @name getForm
     * @access public
     * @param object - FORM
     */
    public function getForm(&$form)
    {
        parent::getForm($form);

        $form->add($email = new TextField('email', lang("email")), null, "content");
        //$form->add(new AutoFormField("requireemailfield", lang("requireEmailField", "email is required")), 0, "content");
        $form->add(new HTMLEditor('data', lang("text")), null, "content");

        $email->info = lang("email_info", "e-mail-info");
    }

    /**
     * gets the content of this page
     *
     * @return string
     */
    public function getContent()
    {
        $form = new Form($this->controller(), "mailer", array(
            new TextField('name', lang("name")),
            new TextField('subject', lang("subject")),
            new email("email", lang("email")),
            new textarea("text", lang("text"), null, "300px"),
            new captcha("captcha")
        ),
            array(
                new FormAction("submit", lang("lp_submit"))
            ));


        $form->setSubmission("send");
        if ($this->requireEmailField) {
            $form->addValidator(new RequiredFields(array("name", "text", "email")), "Required Fields");
        } else {
            $form->addValidator(new RequiredFields(array("name", "text")), "Required Fields");
        }
        return $this->data["data"] . $form->render();
    }
}
