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
        $this->assertNull(User::_validateCode($obj));

        RegisterExtension::$registerCode = randomString(3);
        $this->assertThrows(function() use ($obj) {
            User::_validateCode($obj);
        }, "FormInvalidDataException");

        $obj->form->result["code"] = RegisterExtension::$registerCode;
        $this->assertNull(User::_validateCode($obj));
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
        $this->unitValidatePwd("", "", "password_cannot_be_empty");
        $this->unitValidatePwd("", $k, "password_cannot_be_empty");
        $this->unitValidatePwd($k, "", "passwords_not_match");
        $this->unitValidatePwd($k, $k . " ", "passwords_not_match");
    }

    public function unitValidatePwd($new, $repeat, $expected) {
        $obj = new FormValidatorMock();
        $obj->form  = new StdClass();
        $obj->form->result = array(
            "password"  => $new,
            "repeat"    => $repeat
        );


        if($expected === true) {
            $this->assertNull(User::validateNewAndRepeatPwd($obj));
        } else {
            /** @var FormInvalidDataException $e */
            try {
                User::validateNewAndRepeatPwd($obj);
                $this->assertFalse(true);
            } catch(Exception $e) {
                $this->assertIsA($e, "FormInvalidDataException");
                $this->assertEqual($e->getMessage(), $expected);
                $this->assertEqual($e->getField(), "password");
            }
        }
    }
}

class FormValidatorMock {
    public $form;

    public function getForm() {
        return $this->form;
    }
}
