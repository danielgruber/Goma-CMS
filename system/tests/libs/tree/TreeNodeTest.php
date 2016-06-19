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

    protected $count;

    public function testCreate() {
        $this->unittestCreate("test", 2, "blub", "user", null, DataObject::get_by_id("user", 2));
        $this->unittestCreate("test", 2, "blub", "user", null, DataObject::get_by_id("user", 2), array(
            new TreeNode(1),
            new TreeNode(2)
        ));

        $user = DataObject::get_one("user");
        $this->unittestCreate("test", $user->id, "blub", "user", null, $user);
        $this->unittestCreate("test", $user->id, "blub", "user", null, $user, array(
            new TreeNode(1),
            new TreeNode(2)
        ));
    }

    public function unittestCreate($nodeid, $recordid, $title, $class_name, $icon, $record = null, $children = null) {
        if(!isset($children)) $children = array();

        $node = new TreeNode($nodeid, $recordid, $title, $class_name, $icon);

        $node->setChildren($children);

        $this->assertEqual($node->nodeid, $nodeid);
        $this->assertEqual($node->recordid, $recordid);
        $this->assertEqual($node->record(), $record);
        $this->assertEqual($node->title, $title);
        $this->assertEqual($node->treeclass, $class_name);
        $this->assertEqual($node->icon, $icon);

        $this->assertEqual($node->getChildren(), $children);
        $this->assertEqual($node->forceChildren(), $children);

        return $node;
    }

    public function testForceChildren() {
        $this->unittestForceChildren("124", array(
            new TreeNode(1),
            new TreeNode(2)
        ));
        $this->unittestForceChildren("124", array());
    }

    public function unittestForceChildren($nodeid, $children) {
        $this->count = 0;
        $node = new TreeNode($nodeid);
        $node->setChildCallback(function() use($children) {
            $this->count++;
            return $children;
        });

        $this->assertEqual($this->count, 0);

        $this->assertEqual($node->getChildren(), array());
        $this->assertEqual($node->forceChildren(), $children);
        $this->assertEqual($this->count, 1);

        $this->assertEqual($node->forceChildren(), $children);
        $this->assertEqual($this->count, 1);

        return $node;
    }
}
