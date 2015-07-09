<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Pages.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class PagesTest extends GomaUnitTest implements TestAble {


    static $area = "cms";
    /**
     * name
     */
    public $name = "pages";

    /**
     * tests if permissions are instantly written.
     */
    public function testAddPermissionWithoutWriting() {
        $page = new Page();
        $perm = new Permission();

        $page->addPermission($perm, "read_permission", false);

        $this->assertEqual($perm->id, 0);
        $this->assertEqual($page->id, 0);

        //$this->assertEqual($page->read_permission, $perm);
    }

    /**
     * tests parent-type.
     */
    public function testParentType() {

    }

    public function unitTestParentType($page, $expected) {

    }

    public function testFilterParents() {
        $pages = new pages();
        $reflectionMethod = new ReflectionMethod("pages", "filterParents");
        $reflectionMethod->setAccessible(true);

        $allowParents1 = array("test", "abc");
        $this->assertEqual($reflectionMethod->invoke($pages, $allowParents1, array()), $allowParents1);
        $this->assertEqual($reflectionMethod->invoke($pages, $allowParents1, array("abc")), array("abc"));
        $this->assertEqual($reflectionMethod->invoke($pages, $allowParents1, array("test")), array("test"));
        $this->assertEqual($reflectionMethod->invoke($pages, array(), array("abc")), array());
    }
}