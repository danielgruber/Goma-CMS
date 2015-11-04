<?php
defined('IN_GOMA') OR die();

/**
 * Extension to have a lost password page.
 *
 * @package		Goma\Security\Users
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		2.3.2
 */
class lost_passwordExtension extends ControllerExtension
{

    /**
     * add url-handler
     */
    public $url_handlers = array(
        "lost_password"	=> "lost_password"
    );

    /**
     * add action
     */
    public $allowed_actions = array("lost_password");

    /**
     * register method
     */
    static $extra_methods = array("lost_password");

    /**
     * renders the action
     */
    public function lost_password()
    {
        Core::setTitle(lang("lost_password", "lost password"));
        Core::addBreadCrumb(lang("lost_password", "lost password"), URL . URLEND);
        if(member::login())
        {
            return "<h1>".lang("lost_password", "lost password")."</h1>" . lang("lp_know_password", "You know your password, else you would not be logged in!");
        }

        if(isset($_GET["code"]) && ($_GET["code"] != "" || isset($_GET["deny"])))
        {
            $code = $_GET["code"];
            if(DataObject::count("user", array("code" => $code)) > 0)
            {
                /** @var User $data */
                $data = DataObject::get_one("user", array("code" => $code), array("id"));

                if(isset($_GET["deny"])) {
                    $data->generateCode(false, true);
                    return lang("lp_deny_okay");
                }

                return $this->getEditPasswordForm($data)->render();
            } else {
                $view = new ViewAccessableData();
                return $view->customise(array("codeWrong" => true))->renderWith("mail/lostPasswordSent.html");
            }
        }

        return $this->getLostPwdForm()->render();

    }

    /**
     * generates lost password form.
     *
     * @return Form
     */
    public function getLostPwdForm() {
        $form = new Form($this, "lost_password", array(
            new HTMLField("heading","<h3>".lang("lost_password", "lost password")."</h3>"),
            new TextField("email", lang("lp_email_or_user", "E-Mail or Username"))
        ), array(
            new FormAction("lp_submit", lang("lp_submit", "Send"))
        ));
        $form->setSubmission("Submit");
        $form->addValidator(new FormValidator(array($this,"validate"), array($this, "Validate")), "validate");
        return $form;
    }

    /**
     * generates edit password form.
     *
     * @param User $user
     * @return Form
     */
    public function getEditPasswordForm($user) {

        $pwdform = new Form($this, "editpwd", array(
            new HTMLField("heading","<h3>".lang("lost_password", "lost password")."</h3>"),
            new HiddenField("id", $user->id),
            new PasswordField("password",lang("NEW_PASSWORD")),
            new PasswordField("repeat", lang("REPEAT"))
        ));
        $pwdform->addValidator(new FormValidator(array("User", "validateNewAndRepeatPwd")), "pwdvalidator");
        $pwdform->addAction(new FormAction("update", lang("save", "save"),"pwdsave"));

        return $pwdform;
    }

    /**
     * saves new password
     *
     * @name pwdsave
     * @access public
     * @return string
     */
    public function pwdsave($data)
    {
        $user = DataObject::get_by_id("User", array("id" => $data["id"]));
        $user->password = $data["password"];
        $user->code = randomString(20);
        if($user->write(false, true))
        {
            return "<h1>".lang("lost_password", "lost password")."</h1>" . lang("lp_update_ok", "Your password was updated successful!");
        } else
        {
            throwErrorByID(3);
        }

    }

    /**
     * validates data
     * @name validate
     * @param FormValidator $obj
     * @access public
     * @return bool|string
     */
    public function validate($obj)
    {
        $data = $obj->getForm()->result["email"];
        if(!$data) {
            return lang("lp_not_found", "There is no E-Mail-Adresse for your data.");
        }

        $object = DataObject::get("user", array("nickname" => array("LIKE", $data), "OR", "email" => $data));
        if($object->Count() > 0 && $object->email) {
            return true;
        } else {
            return lang("lp_not_found", "There is no E-Mail-Adresse for your data.");
        }
    }

    public function submit($data)
    {
        /** @var User $data */
        $data = DataObject::get_one("user", array("nickname" => $data["email"], "OR", "email" => $data["email"]));

        // update code
        $key = $data->generateCode(true, true);

        $email = $data["email"];

        $mail = new Mail("noreply@" . $_SERVER["SERVER_NAME"], true, true);

        $text = $data->customise(array(
            "key" => $key
        ))->renderWith("mail/lostPassword.html");

        if($mail->sendHTML($email, lang("lost_password"), $text))
        {
            return $data->renderWith("mail/lostPasswordSent.html");
        } else
        {
            return lang("mail_not_sent", "Mail couldn't be transmitted.");
        }
    }
}

Object::extend("ProfileController", "lost_passwordExtension");
Object::extend("AdminController", "lost_passwordExtension");
