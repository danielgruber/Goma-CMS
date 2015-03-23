<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for HTMLNode-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class HTMLNodeTests extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "HTML";

	/**
	 * internal name.
	*/
	public $name = "HTMLNode";

	public function testImgNodeAttr() {
		$node = new HTMLNode("IMG", array(
			"src" => "test.png",
			"alt" => "blah"
		));
		$this->assertEqual($node->getTag(), "img");
		$this->assertEqual($node->src, "test.png");
		$this->assertEqual($node->render(), '<img src="test.png" alt="blah" />');
		$this->assertEqual((string) $node, '<img src="test.png" alt="blah" />');

		$node->alt = "blub";
		$this->assertEqual((string) $node, '<img src="test.png" alt="blub" />');

		$node->title = "test";
		$this->assertEqual((string) $node, '<img src="test.png" alt="blub" title="test" />');

		unset($node->alt);
		$this->assertEqual((string) $node, '<img src="test.png" title="test" />');
	}
}