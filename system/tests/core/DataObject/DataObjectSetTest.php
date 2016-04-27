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

    static $area = "NModel";
    /**
     * name
     */
    public $name = "DataObjectSet";

    /**
     * relationship env.
     */
    public function testCount() {
        $data = DataObject::get("user");
        $count = $data->count();

        $data->add(new User());
        $this->assertEqual($data->count(), $count + 1);
    }

    public function testDataClass() {
        $this->unittestDataClass("123");
        $this->unittestDataClass("blub");
        $this->unittestDataClass(null);
    }

    public function unittestDataClass($class) {
        $mockDataSource = new MockIDataObjectSetDataSource();
        $mockModelSource = new MockIModelSource();

        $mockModelSource->_dataClass = $class;
        $mockDataSource->_dataClass = $class;

        $set = new DataObjectSet($mockDataSource);
        $this->assertEqual($set->DataClass(), $class);

        $set->setModelSource($mockModelSource);
        $this->assertEqual($set->DataClass(), $class);

        $secondSet = new DataObjectSet();
        $secondSet->setModelSource($mockModelSource);
        $this->assertEqual($secondSet->DataClass(), $class);

        $mockModelSource->_dataClass = $class . "_";
        $this->assertEqual($secondSet->DataClass(), $class . "_");
        $this->assertEqual($set->DataClass(), $class);
    }

    public function setDataTest() {
        $object = new HasMany_DataObjectSet("user");
        $object->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
        $this->assertEqual($object->count(), 0);
    }

    public function testDataObject() {
        $object = new HasMany_DataObjectSet();
        $this->assertThrows(function() use($object) {
            $object->first();
        }, "InvalidArgumentException");

        $object = new HasMany_DataObjectSet("user");
        $object->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
        $this->assertNull($object->first());
    }

    public function testcreateFromCode()
    {
        $set = new DataObjectSet("user");
        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
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

        $data->search("daniel");
        $this->assertNotEqual($count, $data->count());
        $this->assertIsA($data->first(), "DataObject");
        $this->assertEqual($clone->count(), $count);

        $this->assertEqual($clone->first(), $first);
    }
}

class MockIDataObjectSetDataSource implements IDataObjectSetDataSource {

    public $records = array();
    public $aggregate;
    public $group = array();
    public $canFilterBy = true;
    public $canSortBy = true;
    public $_dataClass;
    public $inExpansion;

    public function getRecords($version, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array())
    {
        return $this->records;
    }

    /**
     * gets specific aggregate like max, min, count, sum
     *
     * @param string $version
     * @param string|array $aggregate
     * @param string $aggregateField
     * @param bool $distinct
     * @param array $filter
     * @param array $sort
     * @param array $limit
     * @param array $joins
     * @param array $search
     * @param array $groupby
     * @return mixed
     */
    public function getAggregate($version, $aggregate, $aggregateField = "*", $distinct = false, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array(), $groupby = array())
    {
        return $this->aggregate;
    }

    public function getGroupedRecords($version, $groupField, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array())
    {
        return $this->group;
    }

    public function canFilterBy($field)
    {
        return $this->canFilterBy;
    }

    public function canSortBy($field)
    {
        return $this->canSortBy;
    }

    public function DataClass()
    {
        return $this->_dataClass;
    }

    public function getInExpansion()
    {
        return $this->inExpansion;
    }
}

class MockIModelSource implements IDataObjectSetModelSource {

    public $model;
    public $formCallback;
    public $getEditFormCallback;
    public $getActionsCallback;
    public $_dataClass;

    public function createNew($data = array())
    {
        return $this->model;
    }

    public function getForm(&$form)
    {
        if(is_callable($this->formCallback)) {
            call_user_func_array($this->formCallback, array($form));
        }
    }

    public function getEditForm(&$form)
    {
        if(is_callable($this->getEditFormCallback)) {
            call_user_func_array($this->getEditFormCallback, array($form));
        }
    }

    public function getActions(&$form)
    {
        if(is_callable($this->getActionsCallback)) {
            call_user_func_array($this->getActionsCallback, array($form));
        }
    }

    public function DataClass()
    {
        return $this->_dataClass;
    }

    public function callExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null)
    {
        // TODO: Implement callExtending() method.
    }
}
