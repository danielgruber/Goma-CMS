<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Form.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class LangSelectTest extends GomaUnitTest implements TestAble {
	/**
	 * area
	*/
	static $area = "Form";

	/**
	 * internal name.
	*/
	public $name = "Form";

	/**
	 * tests form RequestHandler connection.
	*/
	public function testFormRequestHandler() {
		$form = new Form($c = new StdClass(), "test");

		$this->assertEqual($form->name(), "test");
		$this->assertEqual($form->controller, $c);
		$this->assertTrue((boolean) $form->render());
	}

	/**
	 * tests if fields are accessable by name.
	*/
	public function testFieldAccessable() {
		$this->caseFieldAccessable("name", new TextField("name", "name"));
		$this->caseFieldAccessable("surname", new TextField("surname", "name"));
		$this->caseFieldAccessable("address1", new TextField("address1", "name"));
		$this->caseFieldAccessable("_1", new TextField("_1", "name"));
	}

	/**
	 * case.
	*/
	public function caseFieldAccessable($name, $field) {

		$this->assertEqual($field->name, $name);

		$form = new Form(new StdClass(), "test", array(
			$field
		));

		$this->assertEqual($form->$name, $field, "Check if field with name $name is accessable. %s");
	}
}