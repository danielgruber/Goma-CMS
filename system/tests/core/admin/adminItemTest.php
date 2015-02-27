<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for AdminItem.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class AdminItemTest extends GomaUnitTest implements TestAble {

	/**
	 * area
	*/
	static $area = "Admin";

	/**
	 * internal name.
	*/
	public $name = "AdminItem";

	/**
	 * setup.
	*/
	public function setUp() {
		$this->item = new AdminItem();
		$this->item->models = array("Uploads");
	}

	/**
	 * destruct.
	*/
	public function tearDown() {
		unset($this->item);
	}

	public function testModelControllerSystem() {
		$this->assertIsA($this->item->getControllerInst(), "Controller");
		$this->assertEqual($this->item->model(), "uploads");

		$this->assertNotNull($this->item->modelInst()->adminURI);

		// check if we can call controller functions
		$this->assertTrue($this->item->__cancall("handlerequest"));
	}
}