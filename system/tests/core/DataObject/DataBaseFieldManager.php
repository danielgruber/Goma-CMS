<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Field-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class DatabaseFieldManagerTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "DataObject";

    /**
     * internal name.
     */
    public $name = "DatabaseFieldManagerTest";

    /**
     * tests if fillFieldArray works correctly.
     */
    public function testFillFieldArray() {
        $this->unittestFillFieldArray(array("test" => 1), array("test" => 1));
        $this->unittestFillFieldArray(array("test" => false), array("test" => 0));
        $this->unittestFillFieldArray(array("test" => "blah", "blub" => 3), array("test" => "blah", "blub" => 3));

        $this->unittestFillFieldArray(array("o" => new TestObjectToRaw(123)), array("o" => 123));
    }

    public function unittestFillFieldArray($array, $expected) {
        /** @var DataObject $m */
        $m = new MyTestModelForDataObjectFieldWrite($array);

        $expected["last_modified"] = time();

        if(!isset($array["blub"])) {
            $expected["blub"] = 2;
        }
        $array["blub"] = "1";
        $array["last_modified"] = 1;

        $this->assertEqual(
            DataBaseFieldManager::fillFieldArray($array, $m->toArray(), $m->classname, true, true),
            $expected,
            "Expected ".print_r($expected, true). " %s"
        );
    }
}

class TestObjectToRaw {
    public $val;

    public function __construct($val) {
        $this->val = $val;
    }

    public function raw() {
        return $this->val;
    }
}
