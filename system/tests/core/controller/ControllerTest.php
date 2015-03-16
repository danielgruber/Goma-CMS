<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Controller-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ControllerTest extends GomaUnitTest {

	static $area = "Controller";
	/**
	 * name
	*/
	public $name = "Controller";

	public function testModelSaveManagementWithArray() {
		$view = new ViewAccessableData(array("test" => 3));

		$this->assertEqual($view->test, 3);

		$c = new Controller();

		$model = $c->getSafableModel(array("test" => 1, "blah" => 2, "blub" => "test"), $view);

		$this->assertEqual($view->test, 3);
		$this->assertEqual($model->test, 1);
		$this->assertEqual($model->blah, 2);
		$this->assertEqual($model->blub, "test");
	}

	public function testModelSaveManagementWithObject() {
		$view = new ViewAccessableData(array("test" => 3));
		$data = new ViewAccessableData(array("test" => 1, "blah" => 2, "blub" => "test"));

		$this->assertEqual($view->test, 3);

		$c = new Controller();

		$model = $c->getSafableModel($data, $view);

		$this->assertEqual($view->test, 3);
		$this->assertEqual($model->test, 1);
		$this->assertEqual($data->test, 1);
		$this->assertEqual($model->blah, 2);
		$this->assertEqual($model->blub, "test");
	}
}