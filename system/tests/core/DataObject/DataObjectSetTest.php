<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for ManyManyRelationShipInfo-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class DataObjectSetTests extends GomaUnitTest
{

    static $area = "Model";
    /**
     * name
     */
    public $name = "DataObjectSet";

    /**
     * relationship env.
     */
    public function testCount() {

    }

    public function setDataTest() {
        $object = new HasMany_DataObjectSet("user");
        $object->setData(array());
        $this->assertEqual($object->count(), 0);
    }
}