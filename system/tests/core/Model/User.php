<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for User-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class UserTests extends GomaUnitTest
{

    static $area = "User";
    /**
     * name
     */
    public $name = "User";

    /**
     * tests if we can get password.
     */
    public function testGetPassword() {
        $user = new User();

        $user->password = "1234";

        $this->assertEqual($user->password, "");
        $this->assertNotEqual($user->fieldGet("password"), "1234");
    }

    /**
     * checks if validatecode works correctly.
     */
    public function testValidateCode() {
        $obj = new FormValidatorMock();
        $obj->form  = new StdClass();
        $obj->form->result = array(
            "code" => ""
        );

        RegisterExtension::$registerCode = "";
        $this->assertTrue(User::_validateCode($obj));

        RegisterExtension::$registerCode = randomString(3);
        $this->assertEqual(User::_validateCode($obj), lang("register_code_wrong", "The Code was wrong!"));

        $obj->form->result["code"] = RegisterExtension::$registerCode;
        $this->assertTrue(User::_validateCode($obj));
    }

    /**
     * tests generate-code.
     */
    public function testGenerateCode() {
        $user = new User();

        $user->code = "123";
        $user->generateCode(false);

        $this->assertNotEqual($user->code, "123");
        $this->assertFalse($user->code_has_sent);

        $c = $user->code;

        $user->generateCode(true);
        $this->assertNotEqual($user->code, "123");
        $this->assertNotEqual($user->code, $c);
        $this->assertTrue($user->code_has_sent);
    }

    /**
     * tests image.
     */
    public function testImage() {
        $user = new User(array("email" => "webmaster@goma-cms.org"));

        $this->assertIsA($user->getImage(), "GravatarImageHandler");

        $user->avatar = new ImageUploads();

        $this->assertIsA($user->getImage(), "ImageUploads");
    }

    /**
     * tests password-validation.
     */
    public function testPasswordValidation() {
        $k = randomString(10);
        $this->unitValidatePwd($k, $k, true);
        $this->unitValidatePwd("", "", lang("password_cannot_be_empty"));
        $this->unitValidatePwd("", $k, lang("password_cannot_be_empty"));
        $this->unitValidatePwd($k, "", lang("passwords_not_match"));
        $this->unitValidatePwd($k, $k . " ", lang("passwords_not_match"));
    }

    public function unitValidatePwd($new, $repeat, $expected) {
        $obj = new FormValidatorMock();
        $obj->form  = new StdClass();
        $obj->form->result = array(
            "password"  => $new,
            "repeat"    => $repeat
        );



        $this->assertEqual(User::validateNewAndRepeatPwd($obj), $expected, "Validating ".var_export($new, true)." and ".var_export($repeat, true).". %s");
    }
}

class FormValidatorMock {
    public $form;

    public function getForm() {
        return $this->form;
    }
}
