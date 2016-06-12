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

		$this->caseFieldAccessable("test", new FieldSet("test", array(), "blub"));
		$this->caseFieldAccessable("blah", new FieldSet("BLAH", array(), "blub"));

		$this->caseFieldAccessable("TEST", new FieldSet("test", array(), "blub"));
		$this->caseFieldAccessable("Blah", new FieldSet("BLAH", array(), "blub"));

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
	 * @param string $name
	 * @param AbstractFormComponent $field
	 */
	public function caseFieldAccessable($name, $field) {
		$this->assertEqual(strtolower($field->getName()), strtolower($name));

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

		$form->getRequest()->post_params = array(
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

	protected $fieldValue1;
	protected $fieldValue2;
	protected $handlerCalled;
	protected $validationCalled;

	public function testDataHandlerAndValidation() {
		$this->unittestDataHandlerAndValidation("123", "456");
		$this->unittestDataHandlerAndValidation(null, null);
		$this->unittestDataHandlerAndValidation("abc", "efg");
	}

	public function unittestDataHandlerAndValidation($fieldValue1, $fieldValue2) {
		$this->fieldValue1 = $fieldValue1;
		$this->fieldValue2 = $fieldValue2;

		$form = new Form(new Controller(), "testData", array(
			new TextField("field1", "123"),
			new TextField("field2", "123")
		));

		$form->addDataHandler(array($this, "transformFields"));
		$form->addValidator(new FormValidator(array($this, "validateFieldsActive")), "validate");

		$this->validationCalled = $this->handlerCalled = false;

		$form->getRequest()->post_params = array(
			"field1" => $this->fieldValue1,
			"field2" => $this->fieldValue2
		);

		$this->assertEqual($form->gatherResultForSubmit(), array(
			"field1" => $this->fieldValue2,
			"field2" => $this->fieldValue2,
			"field3" => $this->fieldValue1
		));

		$this->assertTrue($this->handlerCalled);
		$this->assertTrue($this->validationCalled);
	}

	/**
	 * @param FormValidator $obj
     */
	public function validateFieldsActive($obj) {
		$this->validationCalled = true;
		$result = $obj->getForm()->result;

		$this->assertEqual($result["field1"], $this->fieldValue2);
		$this->assertEqual($result["field2"], $this->fieldValue2);
		$this->assertEqual($result["field3"], $this->fieldValue1);
	}

	public function transformFields($result) {
		$this->handlerCalled = true;

		$data = $result["field1"];
		$result["field1"] = $result["field2"];
		$result["field3"] = $data;
		return $result;
	}

	public function testValidationError() {
		$form = new Form(new Controller(), "testData", array(
			new TextField("field1", "123")
		));

		$form->addValidator(new FormValidator(array($this, "validateAndThrow")), "validate");

		$form->post = array(
			"field1" => "123"
		);

		/** @var FormNotValidException $e */
		try {
			$form->gatherResultForSubmit();

			$this->assertFalse(true);
		} catch(Exception $e) {
			$this->assertIsA($e, "FormNotValidException");

			$errors = $e->getErrors();
			$this->assertIsA($errors[0], "Exception");
			$this->assertEqual($errors[0]->getMessage(), "problem");
		}
	}

	public function validateAndThrow() {
		throw new Exception("problem");
	}

	/**
	 * @param $data
	 */
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

	public function testcheckForRestore() {
		$session = GlobalSessionManager::globalSession();
		$mock = new MockSessionManager();
		GlobalSessionManager::__setSession($mock);

		$form = new Form(new Controller(), "blub");
		$this->assertEqual($mock->functionCalls, array(
			array("hasKey", array("form_restore.blub")),
			array("hasKey", array("form_state_blub"))
		));

		$this->assertIsA($form->state, "FormState");

		$mock->functionCalls = array();
		$mock->session["form_state_blah"] = array(
			"test" => 1
		);
		$form = new Form(new Controller(), "blah");
		$this->assertEqual($mock->functionCalls, array(
			array("hasKey", array("form_restore.blah")),
			array("hasKey", array("form_state_blah")),
			array("get", array("form_state_blah"))
		));
		$this->assertIsA($form->state, "FormState");
		$this->assertEqual($form->state->test, 1);

		GlobalSessionManager::__setSession($session);
	}
}
