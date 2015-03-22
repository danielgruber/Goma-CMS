<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Form-Generation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class DataObjectFormGenerationTest extends GomaUnitTest implements TestAble {
	/**
	 * area
	*/
	static $area = "HTML";

	/**
	 * internal name.
	*/
	public $name = "HTMLField";

	/**
	 * tests form-generation.
	*/
	public function testFormGeneration() {
		$o = new TestDataObjectForForm();
		$form = $o->generateForm("test", false, false, null, $this);

		$this->assertEqual($form->name(), "test");
		$this->assertEqual($form->model, $o);
		$this->assertEqual($form->controller, $this);

		$set = new DataObjectSet($o);
		$form2 = $set->generateForm("test", false, false, null, $this);

		$this->assertEqual($form2->name(), "test");
		$this->assertIsA($form2->model, "TestDataObjectForForm");
		$this->assertEqual($form2->controller, $this);

		$formSubmit = $set->generateForm("test", false, false, null, $this, "submit");
		$this->assertEqual($formSubmit->getSubmission(), "submit");

		$formSubmit2 = $o->generateForm("test", false, false, null, $this, "submit");
		$this->assertEqual($formSubmit2->getSubmission(), "submit");
	}

	public function submit_form() {

	}

	public function submit() {

	}
}

class TestDataObjectForForm extends DataObject {}