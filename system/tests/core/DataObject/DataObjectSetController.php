<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Controller-Relationship.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class DataObjectSetControllerTest extends GomaUnitTest implements TestAble {

	/**
	 * tests if DataObjectSet behaves right.
	*/
	public function testControllerWithDataObjectSet() {
		$set = new DataObjectSet(new MyTestModelForDataObjectSet());
		$this->assertIsA($set->controller(), "Controller");
		$this->assertIsA($set->controller()->modelInst(), "DataObjectSet");
		$this->assertEqual($set->controller()->model(), "mytestmodelfordataobjectset");
	}

	public function testssetModelInst() {
		$c = new MyTestControllerForDataObjectSet();
		$c->setModelInst(new Uploads);
		$this->assertEqual($c->model, "uploads");
		$this->assertIsA($c->modelInst(), "uploads");
	}
}

class MyTestModelForDataObjectSet extends DataObject {
	public function controller() {
		$c = new MyTestControllerForDataObjectSet();
		$c->model_inst = $this;
		$c->model = $this->classname;
		return $c;
	}
}

class MyTestControllerForDataObjectSet extends Controller {

}