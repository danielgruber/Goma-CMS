<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Controller-Relationship.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ControllerResolverTest extends GomaUnitTest implements TestAble {

	/**
	 * tests if DataObjectSet behaves right.
	*/
	public function testControllerWithDataObjectSet() {
		$set = new DataObjectSet(new MyTestModelForDataObjectSet());
		$this->assertIsA(ControllerResolver::instanceForModel($set), "Controller");
		$this->assertIsA(ControllerResolver::instanceForModel($set)->modelInst(), "DataObjectSet");
		$this->assertEqual(ControllerResolver::instanceForModel($set)->model(), "mytestmodelfordataobjectset");
	}

	public function testssetModelInst() {
		$dataObjectSet = new MyTestControllerForDataObjectSet();
		$dataObjectSet->setModelInst(new Uploads());
		$this->assertEqual($dataObjectSet->model, "uploads");
		$this->assertIsA($dataObjectSet->modelInst(), "uploads");
	}
}

class MyTestModelForDataObjectSet extends DataObject {

	static $controller = "MyTestControllerForDataObjectSet";
}

class MyTestControllerForDataObjectSet extends Controller {

}