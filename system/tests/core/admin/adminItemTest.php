<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for AdminItem.
 *
 * @property 	AdminItem item
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

		// we have admin-items, that manage models with controller...
		$this->item = new AdminItem();
		$this->item->models = array("Uploads");

		// ... and without
		$this->itemWithoutController = new AdminItem();
		$this->itemWithoutController->models = array("Group");
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
		$this->assertTrue($this->item->__cancall("form"));
		$this->assertFalse($this->item->__cancall("myverystupidneverexistingfunction"));

		// checks for adminitems without controller
		$this->assertEqual($this->itemWithoutController->model(), "group");
		$this->assertFalse($this->itemWithoutController->getControllerInst());
		$this->assertFalse($this->itemWithoutController->__cancall("handlerequest"));
	}
}