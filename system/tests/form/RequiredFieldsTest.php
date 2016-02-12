<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for RequiredFields.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class RequiredFieldsTest extends GomaUnitTest implements TestAble {
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "RequiredFieldsTest";

    /**
     * tests fields.
     */
    public function testRequiredFields() {
        $this->assertNull($this->unitTestRequiredFields(array(
            new TextField("test", "test")
        ), array(
            "test" => 1
        ), array("test")));

        $this->assertNull($this->unitTestRequiredFields(array(
            new TextField("test", "test")
        ), array(
            "test" => 1
        ), array("TEST")));

        $this->assertNull($this->unitTestRequiredFields(array(
            new TextField("test", "test")
        ), array(
            "test" => 1
        ), array("TEST1")));

        $this->assertThrows(function() {
            $this->unitTestRequiredFields(array(
                new TextField("test", "test")
            ), array(
                "test" => ""
            ), array("TEST"));
        }, "FormMultiFieldInvalidDataException");

        $this->assertThrows(function() {
            $this->unitTestRequiredFields(array(
                new TextField("test", "test")
            ), array(
                "test" => 0
            ), array("TEST"));
        }, "FormMultiFieldInvalidDataException");

        $this->assertThrows(function() {
            $this->unitTestRequiredFields(array(
                new TextField("test", "test")
            ), array(
                "test" => array()
            ), array("TEST"));
        }, "FormMultiFieldInvalidDataException");

        $this->assertThrows(function() {
            $this->unitTestRequiredFields(array(
                new TextField("test", "test")
            ), array(
                "test" => new ViewAccessableData()
            ), array("TEST"));
        }, "FormMultiFieldInvalidDataException");

        $this->assertNull($this->unitTestRequiredFields(array(
            new TextField("test", "test")
        ), array(
            "test" => new ViewAccessableData(array(
                "test" => 1
            ))
        ), array("TEST")));

        $this->assertThrows(function() {
            $this->unitTestRequiredFields(array(
                new TextField("test", "test")
            ), array(
                "test" => new BoolTestClass(false)
            ), array("TEST"));
        }, "FormMultiFieldInvalidDataException");

        $this->assertNull($this->unitTestRequiredFields(array(
            new TextField("test", "test")
        ), array(
            "test" => new BoolTestClass(true)
        ), array("TEST")));

        try {
            $this->unitTestRequiredFields(array(
                new TextField("test0", "test1"),
                new TextField("test1", "test2"),
                new TextField("test2", "test3"),
                new TextField("test3", "test4")
            ), array(
                "test0" => 0,
                "test1" => "",
                "test2" => "",
                "test3" => ""
            ), array("TEST1", "test0", "test2", "test3"));

            $this->assertTrue(false);
        } catch(FormMultiFieldInvalidDataException $e) {
            $keys = array_keys($e->getFieldsMessages());
            array_shift($keys);
            $this->assertEqual($keys, array("TEST1", "test0", "test2", "test3"));
        }

        try {
            $this->unitTestRequiredFields(array(), array(), "");

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }
    }

    /**
     * unit-test.
     *
     * @param array $fields
     * @param array $result
     * @param array $requiredFields
     * @return bool|string
     */
    protected function unitTestRequiredFields($fields, $result, $requiredFields) {
        $form = new Form(new RequestHandler(), "test", $fields);

        $form->addValidator($required = new RequiredFields($requiredFields), "require");
        $form->result = $result;

        $required->validate();
    }
}

class BoolTestClass {
    protected $bool;

    public function __construct($bool) {
        $this->bool = $bool;
    }

    public function bool() {
        return $this->bool;
    }
}
