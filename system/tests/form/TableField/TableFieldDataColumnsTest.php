<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for one TableField-Component.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class TableFieldDataColumnsTest extends TableComponentFieldTests
{
    /**
     * internal name.
     */
    public $name = "TableFieldDataColumns";

    public function GetField()
    {
        $field = new TableFieldDataColumns();
        $field->setDisplayFields(array(
            "test" => "blah"
        ));
        return $field;
    }
}