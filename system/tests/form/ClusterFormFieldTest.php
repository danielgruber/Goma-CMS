<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for ClusterFormField-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ClusterFormFieldTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "ClusterFormField";

    /**
     * tests cluster-form-field.
     */
    public function testCreate() {
        $clusterFormField = new ClusterFormField("test", "blah", array(
            $field = new TextField("text1", "text1")
        ), array(
            "text1" => "123"
        ));

        $this->assertEqual($clusterFormField->form(), $clusterFormField);
        $this->assertEqual($clusterFormField->getField("text1")->getModel(), "123");
        $this->assertNotEqual($field->overridePostName, "");

        $request = new Request("get", "blub");
        $request->post_params = array(
            $field->overridePostName => "234",
            "text1" => "456"
        );
        $clusterFormField->setRequest($request);

        $this->assertEqual($clusterFormField->getField("text1")->result(), "234");

        $this->assertEqual($clusterFormField->result(), array(
            "text1" => "234"
        ));

        $stdClass = new User();
        $clusterFormField->setModel($stdClass);
        $newClass = clone $stdClass;
        $newClass->text1 = "234";

        $this->assertEqual($clusterFormField->result(), $newClass);
    }
}
