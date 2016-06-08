<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for TreeNode-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class TreeNodeTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Tree";

    /**
     * internal name.
     */
    public $name = "TreeNode";

    public function testCreate() {
        $this->unittestCreate("test", 2, "blub", "user", null, DataObject::get_by_id("user", 2));

        $user = DataObject::get_one("user");
        $this->unittestCreate("test", $user->id, "blub", "user", null, $user);
    }

    public function unittestCreate($nodeid, $recordid, $title, $class_name, $icon, $record = null) {
        $node = new TreeNode($nodeid, $recordid, $title, $class_name, $icon);

        $this->assertEqual($node->nodeid, $nodeid);
        $this->assertEqual($node->recordid, $recordid);
        $this->assertEqual($node->record(), $record);
        $this->assertEqual($node->title, $title);
        $this->assertEqual($node->treeclass, $class_name);
        $this->assertEqual($node->icon, $icon);
    }
}
