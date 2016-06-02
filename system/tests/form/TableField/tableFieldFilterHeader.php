<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for one TableField-Component.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class TableFieldFilterHeaderTest extends GomaUnitTest
{
    static $area = "TableField";

    /**
     * internal name.
     */
    public $name = "TableFieldFilterHeader";

    /**
     * @return TableFieldFilterHeader
     */
    public function GetField()
    {
        $field = new TableFieldFilterHeader();
        return $field;
    }

    public function testTableField_FilterHeader() {
        $set = new AddFilterMockSet();

        $tableField = new TableField("test", "blah", new ViewAccessableData());
        $form = new Form(new controller(), "form", array(
            $tableField
        ));

        $field = $this->GetField();

        $field->Init($tableField);
        $field->manipulate($tableField, $set);

        $this->assertEqual($set->filter, array());
        $this->assertFalse($form->state->tablefieldtest->tableFieldFilterHeader->visible);

        $post = array(
            "test" => "123",
            "blub" => "blah"
        );
        $form->getRequest()->post_params["filter"] = $post;

        $field->manipulate($tableField, $set);

        $this->assertEqual($set->filter, array(
            array("test" => array("LIKE", "%123%")),
            array("blub" => array("LIKE", "%blah%"))
        ));
        $this->assertTrue($form->state->tablefieldtest->tableFieldFilterHeader->visible);

        $set->filter = array();

        $this->assertEqual($set->filter, array());

        $field->handleAction($tableField, "reset", null, array());
        $field->manipulate($tableField, $set);

        $this->assertEqual($set->filter, array());
        $this->assertFalse($form->state->tablefieldtest->tableFieldFilterHeader->visible);
        $this->assertFalse($form->state->tablefieldtest->tableFieldFilterHeader->reset);
        $this->assertEqual($form->getRequest()->post_params["filter"], $post);

        $field->handleAction($tableField, "resetfields", "blub", array());
        $field->manipulate($tableField, $set);

        $this->assertEqual($set->filter, array(
            array("test" => array("LIKE", "%123%"))
        ));
        $this->assertTrue($form->state->tablefieldtest->tableFieldFilterHeader->visible);
    }
}

class AddFilterMockSet extends ViewAccessableData {
    public $filter = array();

    public function addFilter($filter) {
        $this->filter[] = $filter;
    }
}
