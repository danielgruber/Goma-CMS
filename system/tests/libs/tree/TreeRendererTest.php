<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for TreeRenderer-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class TreeRendererTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Tree";

    /**
     * internal name.
     */
    public $name = "TreeRenderer";

    public function testCreate() {
        $renderer = new TreeRenderer($node1 = new TreeNode(1));

        $this->assertEqual($renderer->tree, $node1);

        $renderer2 = new TreeRenderer($node2 = new TreeNode(1));
        $this->assertEqual($renderer2->tree, $node2);
    }
}
