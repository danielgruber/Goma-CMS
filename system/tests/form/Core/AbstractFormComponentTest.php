<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for AbstractFormComponent-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class AbstractFormComponentTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "AbstractFormComponent";

    /**
     *
     */
    public function testCreate() {
        $this->unitTestCreate("test", "blah", null);
        $this->unitTestCreate("TEST  ", "BLAHJ ", null);

        $set = new FieldSet();
        $this->unitTestCreate("TEST", "blah", $set);

        $controller = new Controller();
        $controller->setRequest(new Request("get", "blah"));
        $form = new Form($controller, "blah", array(
            $set
        ));

        $this->unitTestCreate("TEST", "123", $set);
        $this->unitTestCreate("TEST", "123", $form);
    }

    /**
     * @param $name
     * @param $model
     * @param AbstractFormComponentWithChildren $parent
     * @return MockAbstractFormComponentImpl
     */
    public function unitTestCreate($name, $model, $parent) {
        $field = new MockAbstractFormComponentImpl($name, $model, $parent);

        $this->assertEqual($field->getName(), $name);
        $this->assertEqual($field->getDbname(), strtolower(trim($name)));
        $this->assertEqual($field->PostName(), $field->getDbname());
        $this->assertEqual($field->getModel(), $model);
        if($parent) {
            $this->assertEqual($field->form(), $parent->form());
            $this->assertEqual($field->getParent(), $parent);
            $this->assertEqual($field->getRequest(), $parent->getRequest());
        } else {
            $this->assertThrows(function() use($field) {
                $field->form();
            }, "LogicException");
            $this->assertNull($field->getParent(), null);
            $this->assertEqual($field->getRequest(), null);
        }

        return $field;
    }

    /**
     * tests get model.
     */
    public function testGetModel() {
        $field = new MockAbstractFormComponentImpl("test");

        $controller = new Controller();
        $controller->setRequest($request = new Request("get", "blah"));
        $form = new Form($controller, "blah", array(
            $field
        ));

        $this->assertEqual($field->getModel(), null);
        $request->post_params["test"] = 123;
        $this->assertEqual($field->getModel(), 123);
        $request->post_params["test"] = null;
        $this->assertEqual($field->getModel(), null);

        $this->assertEqual($form->getFieldPost("test"), null);

        $set = new FieldSet("set1", array(
            $field
        ));
        $form = new Form($controller, "blah", array(
            $set
        ));

        $this->assertEqual($field->getModel(), null);
        $this->assertEqual($set->getModel(), null);

        $form->setModel($model = array(
            "lala" => 1
        ));
        $this->assertEqual($set->getModel(), $model);
        $this->assertEqual($field->getModel(), null);

        $form->setModel($model = array(
            "lala" => 1,
            "test" => 345
        ));
        $this->assertEqual($set->getModel(), $model);
        $this->assertEqual($field->getModel(), 345);

        $field->overridePostName = "lala";
        $this->assertEqual($field->PostName(), "lala");
        $this->assertEqual($set->getFieldValue("lala"), 1);
        $this->assertEqual($field->getModel(), 345);

        $request->post_params["lala"] = 2;
        $this->assertEqual($field->getModel(), 2);
    }

    /**
     * tests get result.
     */
    public function testGetResult() {
        $data = array();

        $field = new MockAbstractFormComponentImpl("test");

        $controller = new Controller();
        $controller->setRequest($request = new Request("get", "blah"));
        $form = new Form($controller, "blah", array(
            $field
        ));

        $field->argumentResult($data);
        $this->assertEqual($data["test"], null);

        $request->post_params["test"] = 123;
        $field->argumentResult($data);
        $this->assertEqual($data["test"], 123);

        $request->post_params["test"] = 0;
        $field->argumentResult($data);
        $this->assertEqual($data["test"], 0);

        $request->post_params["test"] = null;
        $field->argumentResult($data);
        $this->assertEqual($data["test"], null);
    }

    /**
     * tests get result.
     */
    public function testGetResultWithObject() {
        $data = new User();

        $field = new MockAbstractFormComponentImpl("test");

        $controller = new Controller();
        $controller->setRequest($request = new Request("get", "blah"));
        $form = new Form($controller, "blah", array(
            $field
        ));

        $field->argumentResult($data);
        $this->assertEqual($data["test"], null);
        $this->assertEqual($data->test, null);

        $request->post_params["test"] = 123;
        $field->argumentResult($data);
        $this->assertEqual($data["test"], 123);
        $this->assertEqual($data->test, 123);
    }
}

class MockAbstractFormComponentImpl extends AbstractFormComponent {

    public $fieldCallable;
    public $jsCallable;

    /**
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        if(is_callable($this->fieldCallable)) {
            return call_user_func_array($this->fieldCallable, array($info));
        }
        return null;
    }

    /**
     * @return string
     */
    public function js()
    {
        if(is_callable($this->jsCallable)) {
            return call_user_func_array($this->jsCallable, array());
        }

        return null;
    }
}
