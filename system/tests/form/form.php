<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Form.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class FormTest extends GomaUnitTest implements TestAble {
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
		$form = new Form($c = new Controller(), "test");

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
        $this->caseFieldAccessable("test", new TextField("test", "blub"));

		$this->caseFieldAccessable("test", new FieldSet("test", "blub"));
		$this->caseFieldAccessable("blah", new FieldSet("BLAH", "blub"));

		$this->caseFieldAccessable("TEST", new FieldSet("test", "blub"));
		$this->caseFieldAccessable("Blah", new FieldSet("BLAH", "blub"));

		$this->caseFieldAccessable("TEST", new RadioButton("test", "blub"));
		$this->caseFieldAccessable("Blah", new RadioButton("BLAH", "blub"));

		$this->caseFieldAccessable("TEST", new FormAction("test", "blub"));
		$this->caseFieldAccessable("Blah", new FormAction("BLAH", "blub"));

		$form = new Form(new Controller(), "test", array(
			$set = new FieldSet("BLAH", array(
				$t = new FormField("test"),
				$b = new FormField("Blub")
			))
		));

		$this->assertEqual($form->blah, $set);
		$this->assertEqual($form->Test, $t);
		$this->assertEqual($form->BLub, $b);
	}

	/**
	 * case.
	*/
	public function caseFieldAccessable($name, $field) {

		$this->assertEqual(strtolower($field->name), strtolower($name));

		$form = new Form(new Controller(), "test", array(
			$field
		));

		$this->assertEqual($form->$name, $field, "Check if field with name $name is accessable. %s");
	}

	protected static $testCalled = false;

	public function testNullResult() {
		$form = new Form(new Controller(), "test" ,array(
			new TextField("test", "test")
		), array(
			$action = new FormAction("save", "save")
		));

		$form->setSubmission(array($this, "_testNull"));

		$form->saveToSession();

		self::$testCalled = false;
		$this->assertFalse(self::$testCalled);

		$form->post = array(
			"test" => null,
			$action->PostName() => 1
		);
		$form->trySubmit();
		$this->assertTrue(self::$testCalled);

		self::$testCalled = false;
		$this->assertFalse(self::$testCalled);
		$form->setSubmission(array($this, "_exceptionSubmit"));

		$form->saveToSession();

		$this->assertNotEqual($form->trySubmit(), "");
		$this->assertEqual(self::$testCalled, 2);
	}

	public function _testNull($data) {
		self::$testCalled = 1;
		$this->assertNull($data["test"]);
	}

	public function _exceptionSubmit() {
		self::$testCalled = 2;
		throw new Exception("Problem");
	}

	public function testTemplateExists() {
		foreach(ClassInfo::getChildren("FormField") as $field) {
			if(!ClassInfo::isAbstract($field)) {
				$reflectionClass = new ReflectionClass($field);
				$inst = $reflectionClass->newInstance();

				if($reflectionClass->hasProperty("template")) {
					$reflectionProp = $reflectionClass->getProperty("template");
					$reflectionProp->setAccessible(true);
					$tpl = $reflectionProp->getValue($inst);

					if ($tpl) {
						$this->assertTrue(tpl::getFilename($tpl), "Template $tpl for class $field %s");
					}
				}
			}
		}
	}
}
