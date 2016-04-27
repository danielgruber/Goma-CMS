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

    protected $daniel;
    protected $kathi;
    protected $patrick;
    protected $janine;
    protected $nik;
    protected $julian;

    public function setUp()
    {
        $this->daniel =  new DumpDBElementPerson("Daniel", 20, "M");
        $this->kathi = new DumpDBElementPerson("Kathi", 22, "W");
        $this->patrick = new DumpDBElementPerson("Patrick", 16, "M");
        $this->janine = new DumpDBElementPerson("Janine", 19, "W");
        $this->nik = new DumpDBElementPerson("Nik", 21, "M");
        $this->julian = new DumpDBElementPerson("Julian", 20, "M");
    }

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

    public function testAssignFields() {
        $this->unittestAssignFields("MockDataObjectForDataObjectSet");
        $this->unittestAssignFields(new MockDataObjectForDataObjectSet());
        $this->unittestAssignFields(new MockIDataObjectSetDataSource("User"));
        $set = $this->unittestAssignFields(array($source = new MockIDataObjectSetDataSource(), $model = new MockIModelSource()));
        $this->assertEqual($set->getDbDataSource(), $source);
        $this->assertEqual($set->getModelSource(), $model);

        $set = $this->unittestAssignFields("DumpDBElementPerson");
        $this->assertIsA($set->getDbDataSource(), "MockIDataObjectSetDataSource");
        $this->assertIsA($set->getModelSource(), "MockIModelSource");
    }

    public function unittestAssignFields($object) {
        $set = new DataObjectSet($object);
        $this->assertIsA($set->getModelSource(), "IDataObjectSetModelSource");
        $this->assertIsA($set->getDbDataSource(), "IdataObjectSetDataSource");

        return $set;
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

    public function testFirstLast() {
        $set = new DataObjectSet("DumpDBElementPerson");

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();
        $source->records = array(
            $this->janine,
            $this->daniel
        );

        $this->assertEqual($set->first(), $this->janine);
        $this->assertEqual($set->last(), $this->daniel);
        $this->assertEqual($set->forceData()->count(), 2);

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine
        );

        $this->assertEqual($set->first(), $this->janine);
        $this->assertEqual($set->last(), $this->daniel);
        $set->filter(array("name" => "blah"));
        $this->assertEqual($set->first(), null);
        $this->assertEqual($set->last(), null);

        $set->filter(array());

        $this->assertEqual($set->first(), $this->julian);
        $this->assertEqual($set->last(), $this->janine);

        $this->assertEqual($set[1], $this->daniel);
        $this->assertEqual($set[2], $this->janine);
        $this->assertEqual($set[3], null);
    }

    public function testPagination() {
        $set = new DataObjectSet("DumpDBElementPerson");

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi,
            $this->patrick,
            $this->nik
        );

        $set->activatePagination(null, 2);
        $this->assertEqual($set->first(), $this->julian);
        $this->assertEqual($set->last(), $this->daniel);

        $set->activatePagination(2);
        $this->assertEqual($set->getPage(), 2);

        $this->assertEqual($set->first(), $this->janine);
        $this->assertEqual($set->last(), $this->kathi);

        $set->activatePagination(3);
        $this->assertEqual($set->getPage(), 3);

        $this->assertEqual($set->first(), $this->patrick);
        $this->assertEqual($set->last(), $this->nik);
        $this->assertEqual($set[1], $this->nik);

        $set->disablePagination();

        $this->assertEqual($set[4], $this->patrick);
    }

    public function testStaging() {
        $set = new DataObjectSet("DumpDBElementPerson");

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        );

        $this->assertEqual($set->count(), 4);
        $set->add($this->patrick);

        $this->assertEqual($set->count(), 5);
        $this->assertEqual($set[4], $this->patrick);
        $this->assertEqual($set->last(), $this->patrick);

        try {
            $set->commitStaging();
        } catch(Exception $e) {
            $this->assertIsA($e, "DataObjectSetCommitException");
            $this->assertEqual($set->getStaging()->find("name", "patrick", true), $this->patrick);
        }

        $set->removeFromStage($this->patrick);
        $this->assertEqual($set[4], null);
        $this->assertEqual($set->last(), $this->kathi);
    }

    public function testStagingMulti() {
        $set = new DataObjectSet("DumpDBElementPerson");

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        );

        $this->assertEqual($set->count(), 4);
        $set->add($this->patrick);
        $set->add($this->nik);

        $this->assertEqual($set->count(), 6);
        $this->assertEqual($set[4], $this->patrick);
        $this->assertEqual($set->last(), $this->nik);
        $this->assertEqual($set[3], $this->kathi);

        $set->removeFromStage($this->patrick);
        $this->assertEqual($set[4], $this->nik);
        $this->assertEqual($set->count(), 5);
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

    public function __construct($dataClass = "")
    {
        $this->_dataClass = $dataClass;
    }

    protected function getListBy($records, $filter, $sort, $limit) {
        $list = new ArrayList($records);
        if($filter) {
            $list = $list->filter($filter);
        }

        if($sort) {
            $list = $list->sort($sort);
        }

        if(isset($limit[0], $limit[1])) {
            $list = $list->getRange($limit[0], $limit[1]);
        }

        return $list;
    }

    public function getRecords($version, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array())
    {
        return $this->getListBy($this->records, $filter, $sort, $limit)->ToArray();
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
        if(strtolower($aggregate) == "count" && !isset($this->aggregate)) {
            return $this->getListBy($this->records, $filter, $sort, $limit)->count();
        }

        return $this->aggregate;
    }

    public function getGroupedRecords($version, $groupField, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array())
    {
        return $this->getListBy($this->group, $filter, $sort, $limit)->ToArray();
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

    public function __construct($dataClass = "")
    {
        $this->_dataClass = $dataClass;
    }

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

class MockDataObjectForDataObjectSet extends DataObject {}

class DumpDBElementPerson extends DataObject {

    public static function getDbDataSource($class)
    {
        return new MockIDataObjectSetDataSource($class);
    }

    public static function getModelDataSource($class)
    {
        return new MockIModelSource($class);
    }

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $age;

    /**
     * @var string 'M' or 'W'
     */
    public $gender;

    /**
     * DumpElementPerson constructor.
     * @param string $name
     * @param int $age
     * @param string $gender 'M' or 'W'
     */
    public function __construct($name, $age, $gender)
    {
        parent::__construct();

        $this->name = $name;
        $this->age = $age;
        $this->gender = $gender;
    }

    public function writeToDB($forceInsert = false, $forceWrite = false, $snap_priority = 2, $forcePublish = false, $history = true, $silent = false, $overrideCreated = false)
    {
        throw new Exception("Should not be written.");
    }
}
