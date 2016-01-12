<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for one TableField-Component.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

abstract class TableComponentFieldTests extends GomaUnitTest {
    /**
     * area
     */
    static $area = "TableField";

    /**
     * internal name.
     */
    public $name = "Component";

    public abstract function GetField();

    /**
     * tests TableField_ColumnProvider interface.
     */
    public function testTableField_ColumnProvider() {
        $field = $this->GetField();

        $tableField = new TableField("test", "blah", new ViewAccessableData());

        if($field instanceof TableField_ColumnProvider) {
            $columns = array();

            $field->augmentColumns($tableField, $columns);

            foreach($columns as $column) {
                $info = $field->getColumnMetadata($tableField, $column);
                $this->assertTrue(isset($info["title"]));

                $view = new ViewAccessableData(array(
                    $column => 1
                ));

                $this->assertNotEqual($field->getColumnContent($tableField, $view, $column), "");
                $this->assertThrows(function() use ($field, $tableField, $column, $view) {
                    $field->getColumnContent($tableField, $column, $view);
                }, "InvalidArgumentException");
            }
        }
    }
}
