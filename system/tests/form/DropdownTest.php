<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Dropdown.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class DropdownTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "Dropdown";

    /**
     * gets key from info.
     */
    public function testGetKeyFromInfo() {
        $this->assertEqual($this->unitTestGetKeyFromInfo(array("abc", "cde"), 0, "abc"), "abc");
        $this->assertEqual($this->unitTestGetKeyFromInfo(array(1 => "abc", 2 => "cde"), 1, "abc"), 1);
        $this->assertEqual($this->unitTestGetKeyFromInfo(array(
            array("title" => "abc", "ccc" => "abc"),
            array("title" => "deg", "ccc" => "def")
        ), 0, array("title" => "abc", "ccc" => "abc")), "abc");
        $this->assertEqual($this->unitTestGetKeyFromInfo(array(
            1 => array("title" => "abc", "ccc" => "abc"),
            2 => array("title" => "deg", "ccc" => "def")
        ), 1, array("title" => "abc", "ccc" => "abc")), 1);

        $source = new DataObjectSet("user");
        $this->assertEqual($this->unitTestGetKeyFromInfo($source, 0, $source->first()), $source->first()->id);
    }

    protected function unitTestGetKeyFromInfo($dataSource, $key, $value) {
        $dropdown = new DropDown();
        $method = new ReflectionMethod("Dropdown", "getKeyFromInfo");
        $method->setAccessible(true);

        return $method->invoke($dropdown, $dataSource, $key, $value);
    }
}
