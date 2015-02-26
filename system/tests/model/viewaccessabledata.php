<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ViewAccessableData.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ViewAccessableDataTest extends GomaUnitTest implements TestAble {
	
	/**
	 * area
	*/
	static $area = "viewmodel";

	/**
	 * internal name.
	*/
	public $name = "ViewAccessableData";

	/**
	 * checks for data
	*/
	public function testCheckData() {
		$view = new viewaccessableData(array("id" => 1, "data" => 2, "title" => 3));
		$this->assertTrue($view->bool());
	}
	
	/**
	 * checks for no data
	*/
	public function testCheckNoData() {
		$view = new viewaccessableData();
		$this->assertFalse($view->bool());
	}

	/**
	 * test customise.
	*/
	public function testCustomise() {
		$data = new ViewAccessableData(array("blah" => "blub"));
		$this->assertEqual($data->blah, "blub");
		$this->assertEqual($data->blah_cust, null);
		$this->assertEqual($data->blub, null);

		// customise this
		$customised = $data->customise(array("blah_cust" => "test"));
		$this->assertEqual($data->blah_cust, "test");
		$this->assertEqual($customised->blah_cust, "test");
		$this->assertEqual($customised->blub, null);

		$newCustomised = $data->customisedObject(array("blub" => "blah"));
		$this->assertEqual($data->blah_cust, "test");
		$this->assertEqual($data->blub, null);
		$this->assertEqual($newCustomised->blah_cust, "test");
		$this->assertEqual($newCustomised->blub, "blah");
	}
	
	/**
	 * checks for reset
	*/
	public function testReset() {
		$view = new viewaccessableData(array("id" => 1, "data" => 2, "title" => 3));
		$this->assertTrue($view->bool());
		$view->reset();
		$this->assertFalse($view->bool());
	}
	
	/**
	 * test for ToArray
	*/
	public function testToArray() {
		$data = array("id" => 1, "data" => 2, "title" => 3);
		$view = new viewaccessableData($data);
		$this->assertEqual($view->toArray(), $data);
	}
	
	/**
	 * checks if changes work
	*/
	public function testChanged() {
		$data = array("id" => 1, "data" => 2, "title" => 3);
		$view = new ViewAccessableData($data);
		$this->assertFalse($view->wasChanged());
		$view->title = 4;
		$view->assertTrue($view->wasChanged());
	}
	
	/**
	 * tests doObject
	*/
	public function testDoObject() {
		$data = array("id" => 1, "data" => 2, "title" => 3);
		$view = new ViewAccessableData($data);
		$this->assertIsA($view->doObject("data"), "DBField");
	}
}