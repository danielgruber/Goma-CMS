<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for FormField.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class FormFieldTest extends GomaUnitTest implements TestAble {
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "FormField";

    public function testCreate() {
        $form = new Form(new Controller(), "test");

        $this->unitTestCreate("test", "blah", "blub", null);
        $this->unitTestCreate("test2", "blah", "blub", $form);

        $this->assertEqual($form->test2->name, "test2");
    }

    public function unitTestCreate($name, $title, $value, $parent) {
        $field = FormField::create($name, $title, $value, $parent);

        $this->assertEqual($field->name, $name);
        $this->assertEqual($field->value, $value);
        $this->assertEqual($field->getTitle(), $title);

        if($parent != null) {
            $this->assertEqual($field->form(), $parent);
        } else {
            $this->assertThrows(function() use($field) {
                $field->form();
            }, "LogicException");
        }

        $this->assertEqual($field->PostName(), $name);
        $this->assertEqual($field->disabled, false);
        $this->assertIsA($field->container, "HTMLNode");
        $this->assertIsA($field->input, "HTMLNode");
        $this->assertEqual($field->input->name, $name);
    }

    public function testDisable() {
        $field = new FormField();
        $this->assertFalse($field->disabled);

        $field->disable();
        $this->assertTrue($field->disabled);
        $this->assertEqual($field->input->disabled, "disabled");

        $field->enable();
        $this->assertFalse($field->disabled);
        $this->assertEqual($field->input->disabled, null);
    }

    public function testResult() {
        $form = new Form(new Controller(), "test");

        $field = new FormField("test", "", "1234", $form);

        $form->post = array(
            "test" => "123"
        );

        $this->assertEqual($field->result(), "123");

        $field->disable();
        $this->assertEqual($field->result(), "1234");

        $field->enable();
        $this->assertEqual($field->result(), "123");

        $form->disabled = true;
        $this->assertEqual($field->result(), "1234");

        $form->disabled = false;
        $this->assertEqual($field->result(), "123");

        $prop = new ReflectionProperty("FormField", "POST");
        $prop->setAccessible(true);
        $prop->setValue($field, false);

        $this->assertEqual($field->result(), "1234");
    }

    public function testgetValue() {
        $form = new Form(new Controller(), "test");

        $field = new FormField("test", "", "1234", $form);

        $field->getValue();
        $this->assertEqual($field->value, 1234);

        $form->result = new ViewAccessableData();
    }
}
