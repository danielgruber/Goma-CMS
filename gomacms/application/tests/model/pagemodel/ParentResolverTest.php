<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ParentResolver.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ParentResolverTest extends GomaUnitTest implements TestAble {


    static $area = "cms";
    /**
     * name
     */
    public $name = "ParentResolver";

    public function testFilterParents() {
        $pages = new ParentResolver();
        $reflectionMethod = new ReflectionMethod("ParentResolver", "filterParents");
        $reflectionMethod->setAccessible(true);

        $allowParents1 = array("test", "abc");
        $this->assertEqual($reflectionMethod->invoke($pages, $allowParents1, array()), $allowParents1);
        $this->assertEqual($reflectionMethod->invoke($pages, $allowParents1, array("abc")), array("abc"));
        $this->assertEqual($reflectionMethod->invoke($pages, $allowParents1, array("test")), array("test"));
        $this->assertEqual($reflectionMethod->invoke($pages, array(), array("abc")), array());
    }
}