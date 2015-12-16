<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for LostPassword-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class LostPasswordTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "User";

    /**
     * internal name.
     */
    public $name = "LostPassword";

    /**
     * tests exceptions.
     */
    public function testTPL() {
        $this->assertNotEqual(lost_passwordExtension::LOST_PASSWORD_MAIL, lost_passwordExtension::LOST_PASSWORD_SENT);

        $this->assertNotEqual(tpl::render(lost_passwordExtension::LOST_PASSWORD_MAIL), tpl::render(lost_passwordExtension::LOST_PASSWORD_SENT));
    }

    public function testAccessMethod() {
        $profile = new ProfileController();

        $this->assertNotEqual($profile->lost_password(), "");

        $request = new Request("get", "lost_password");

        $this->assertNotEqual($profile->handleRequest($request), "");
    }

    public function testForms() {
        $object = new lost_passwordExtension();

        $form1 = $object->getLostPwdForm();
        $this->assertIsA($form1, "Form");
        $this->assertNotEqual($form1->render(), "");

        $form2 = $object->getEditPasswordForm(new User());
        $this->assertIsA($form2, "Form");
        $this->assertNotEqual($form2->render(), "");
    }

    public function testValidate() {
        $object = new lost_passwordExtension();
        $std = new StdClass();
        $std->result = array("email" => 123);

        $validator = new LostPasswordMockForm($std);
        $this->assertEqual($object->validate($validator), lang("lp_not_found", "There is no E-Mail-Adresse for your data."));

        $std->result = array("email" => "");

        $this->assertEqual($object->validate($validator), lang("lp_not_found", "There is no E-Mail-Adresse for your data."));
    }
}

class LostPasswordMockForm {
    protected $form;

    public function __construct($form) {
        $this->form = $form;
    }

    public function getForm() {
        return $this->form;
    }
}
