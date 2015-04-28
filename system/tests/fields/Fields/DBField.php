<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for HTMLText-Field.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class DBFieldTest extends GomaUnitTest implements TestAble {
    /**
     * area
     */
    static $area = "DBField";

    /**
     * internal name.
     */
    public $name = "DBField";

    /**
     * tests size-matching
     *
     *@name testSizeMatching
     */
    public function testParseCasting() {
        foreach(ClassInfo::getChildren("DBField") as $child) {
            $expected = array(
                "class" => $child,
                "args"  => array(1,2)
            );

            if(ClassInfo::hasInterface($child, "DefaultConvert")) {
                $expected["convert"] = true;
            }

            $this->assertEqual(DBField::parseCasting($child . "(1,2)"), $expected, "DBField Check $child %s");

            $expected["method"] = randomString(3);
            $this->assertEqual(DBField::parseCasting($child . "(1,2)->" . $expected["method"]), $expected, "DBField Check with method $child %s");
        }
    }
}