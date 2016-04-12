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

    public function testDataObject() {
        $object = new HasMany_DataObjectSet();
        $this->assertNull($object->first());

        $object = new HasMany_DataObjectSet("user");
        $object->setData(array());
        $this->assertIsA($object->first(), "user");
        $this->assertNull($object->first(false));
    }

    public function testcreateFromCode()
    {
        $set = new DataObjectSet("user");
        $set->setData();
        $set->add(new User());
        $set->add(new User());

        $this->assertEqual($set->count(), 2);
    }

    public function testSearch() {
        $data = DataObject::get("user");
        $clone = clone $data;

        $count = $data->count();
        $first = $data->first();

        // TODO: Test with foreach

        $data->search("123");
        $this->assertNotEqual($count, $data->count());
        $this->assertIsA($data->first(), "DataObject");
        $this->assertEqual($clone->count(), $count);

        $this->assertEqual($clone->first(), $first);
    }
}
