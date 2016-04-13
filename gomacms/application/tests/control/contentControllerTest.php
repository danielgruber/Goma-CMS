<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for contentController.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class contentControllerTest extends GomaUnitTest
{


    static $area = "cms";
    /**
     * name
     */
    public $name = "contentController";

    /**
     * tests checkForReadPermission
     */
    public function testcheckForReadPermission() {
        $this->assertIdentical($this->unitTestCheckForReadPermission("all", null, false), true);
        $this->assertIdentical($this->unitTestCheckForReadPermission("all", "blub", true), true);

        // this method should ONLY check for Password
        $this->assertIdentical($this->unitTestCheckForReadPermission("admin", null, false), true);
        $this->assertIdentical($this->unitTestCheckForReadPermission("admin", "blah", true), true);

        $this->assertIdentical($this->unitTestCheckForReadPermission("password", "test", true), true);
        $this->assertIdentical($this->unitTestCheckForReadPermission("password", "test12345   ", true), true);
        $this->assertEqual($this->unitTestCheckForReadPermission("password", "test", false), array("test"));
        $this->assertEqual($this->unitTestCheckForReadPermission("password", "12345  ", false), array("12345  "));
    }

    public function unitTestCheckForReadPermission($readPermissionType, $password, $shouldBeInKeychain) {
        $page = new Page();
        $page->read_permission = new Permission(array(
            "type" => $readPermissionType,
            "password" => $password
        ));

        $controller = new ContentController();
        $controller->setModelInst($page);
        if($shouldBeInKeychain) {
            $controller->keyChainAdd($password);
        } else {
            $controller->keyChainRemove($password);
        }

        return $controller->checkForReadPermission();
    }
}