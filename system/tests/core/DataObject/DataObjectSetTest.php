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

        $this->daniel->queryVersion = $this->kathi->queryVersion = $this->patrick->queryVersion = $this->janine->queryVersion =
            $this->nik->queryVersion = $this->julian->queryVersion = DataObject::VERSION_PUBLISHED;
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

        $mockInExp = new MockDataObjectForDataObjectSet();
        $mockInExp->inExpansion = "blah";
        $this->unittestAssignFields($mockInExp, "blah");
        $set = new DataObjectSet();
        $set->setDbDataSource(new MockIDataObjectSetDataSource("User", "tja"));
        $this->assertEqual($set->inExpansion, "tja");

        $this->unittestAssignFields(new MockIDataObjectSetDataSource("User", "blub"), "blub");

        $set = $this->unittestAssignFields(array($source = new MockIDataObjectSetDataSource(), $model = new MockIModelSource()));

        $this->assertEqual($set->getDbDataSource(), $source);
        $this->assertEqual($set->getModelSource(), $model);

        $set = $this->unittestAssignFields("DumpDBElementPerson");
        $this->assertIsA($set->getDbDataSource(), "MockIDataObjectSetDataSource");
        $this->assertIsA($set->getModelSource(), "MockIModelSource");
    }

    public function unittestAssignFields($object, $inExpansion = null) {
        $set = new DataObjectSet($object);
        $this->assertIsA($set->getModelSource(), "IDataObjectSetModelSource");
        $this->assertIsA($set->getDbDataSource(), "IdataObjectSetDataSource");
        $this->assertEqual($set->inExpansion, $inExpansion);

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
        $this->assertEqual($object->first(), null);
        $this->assertEqual($object->last(), null);
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
        $set->add($user1 = new User());
        $set->add($user2 = new User());

        $this->assertEqual($set->count(), 2);
    }

    public function testcreateFromCodeDuplicate()
    {
        $set = new DataObjectSet("user");
        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
        $set->add($user1 = new User());
        $set->add($user2 = new User());

        $this->assertThrows(function() use ($set, $user1) {
            $set->add($user1);
        }, "LogicException");

        $this->assertEqual($set->count(), 2);
    }

    public function testSearch() {
        $data = DataObject::get("user");
        $clone = clone $data;

        $count = $data->count();
        $first = $data->first();

        $data->search("daniel");
        $this->assertNotEqual($count, $data->count());
        $this->assertIsA($data->first(), "DataObject");
        $this->assertEqual($clone->count(), $count);

        $this->assertEqual($clone->first(), $first);
    }

    public function testFirstLast() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

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

    /**
     *
     */
    public function testFirstLastWithPersistence() {

    }

    public function testPagination() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

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
        $this->assertEqual($set->count(), 2);
        $this->assertEqual($set->first(), $this->julian);
        $this->assertEqual($set->last(), $this->daniel);

        $i = 0;
        foreach($set as $record) {
            $this->assertEqual($source->records[$i]->ToArray(), $record->ToArray());
            $i++;
        }

        $this->assertEqual($i, 2);

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

    public function testEmptyPagination() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array();

        $set->activatePagination(null, 2);

        $this->assertEqual($set->count(), 0);
        $this->assertEqual($set->getPageCount(), 0);
    }

    public function testStaging() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

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
        $set->setVersion(DataObject::VERSION_PUBLISHED);

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

    public function testCustomised() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        );

        $set->customise(array(
            "blub" => 123
        ));

        foreach($set as $record) {
            $this->assertEqual($record->blub, 123);
        }

        $this->assertEqual($set->blub, 123);
        $this->assertEqual($set[3]->blub, 123);

        $objectWithoutCustomisation = $set->getObjectWithoutCustomisation();
        $this->assertEqual($objectWithoutCustomisation[3]->blub, null);
    }

    public function testRanges() {
        $set = new DataObjectSet("DumpDBElementPerson");

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        );

        $this->assertIsA($set->getRange(0, 1), "DataSet");
        $this->assertIsA($set->getRange(0, 1)->ToArray(), "array");
        $this->assertIsA($set->getArrayRange(0, 1), "array");
    }

    public function testRangesNew() {
        $set = new DataObjectSet();
        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);

        $this->assertIsA($set->getRange(0, 1), "DataSet");
        $this->assertIsA($set->getRange(0, 1)->ToArray(), "array");
        $this->assertIsA($set->getArrayRange(0, 1), "array");

        $set->add($this->janine);

        $this->assertIsA($set->getRange(0, 1), "DataSet");
        $this->assertIsA($set->getRange(0, 1)->ToArray(), "array");
        $this->assertIsA($set->getArrayRange(0, 1), "array");
    }

    public function testObjectPersistence() {
        $this->unittestObjectPersistence(array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        ));
        $this->unittestObjectPersistence(array(
            array("name" => "janine"),
            array("name" => "daniel"),
            array("name" => "julian"),
            array("name" => "kathi"),
        ));
    }

    public function unittestObjectPersistence($records) {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = $records;

        $cacheMethod = new ReflectionMethod("DataObjectSet", "clearCache");
        $cacheMethod->setAccessible(true);

        $this->assertTrue($set[0] === $set->first());
        $this->assertTrue($set[count($records) - 1] === $set->last());

        $this->assertTrue($set->first() === $set[0]);
        $this->assertTrue($set->last() === $set[count($records) - 1]);

        $i = 0;
        foreach($set as $record) {
            if($i == 0) {
                $this->assertTrue($record === $set->first());
            } else {
                $this->assertFalse($record === $set->first());
            }

            if($i == count($records) - 1) {
                $this->assertTrue($record === $set->last());
            } else {
                $this->assertFalse($record === $set->last());
            }
            $i++;
        }

        $set->activatePagination(1, 2);

        if(is_array($records[1])) {
            $this->assertEqual($set[1]->ToArray(), $records[1]);
            $this->assertEqual($set->last()->ToArray(), $records[1]);
        } else {
            $this->assertEqual($set[1], $records[1]);
            $this->assertEqual($set->last(), $records[1]);
        }
        $i = 0;
        foreach($set as $record) {
            if($i == 0) {
                $this->assertTrue($record === $set->first());
            } else {
                $this->assertFalse($record === $set->first());
            }

            if($i == 1) {
                $this->assertTrue($record === $set->last());
            } else {
                $this->assertFalse($record === $set->last());
            }
            $i++;
        }

        $cacheMethod->invoke($set);
        $i = 0;
        foreach($set as $record) {
            if($i == 0) {
                $this->assertTrue($record === $set->first());
            } else {
                $this->assertFalse($record === $set->first());
            }

            if($i == 1) {
                $this->assertTrue($record === $set->last());
            } else {
                $this->assertFalse($record === $set->last());
            }
            $i++;
        }
    }

    public function findTest() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        );

        $this->assertEqual($set->find("name", "julian"), $this->julian);
        $set->forceData();
        $this->assertEqual($set->find("name", "julian"), $this->julian);

        $this->assertEqual($set->find("age", "19"), $this->janine);
        $this->assertEqual($set->find("name", "JULIAN", false), null);
        $this->assertEqual($set->find("name", "JULIAN", true), $this->julian);
    }

    public function findTestNew() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setVersion(DataObject::VERSION_PUBLISHED);

        /** @var MockIDataObjectSetDataSource $source */
        $source = $set->getDbDataSource();

        $source->records = array(
            $this->julian,
            $this->daniel,
            $this->janine,
            $this->kathi
        );

        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
        $this->assertEqual($set->ToArray(), array());

        $this->assertEqual($set->find("name", "julian"), null);
        $set->forceData();
        $this->assertEqual($set->find("name", "julian"), null);

        $set->add($this->julian);
        $this->assertEqual($set->find("name", "julian"), $this->julian);
        $set->forceData();
        $this->assertEqual($set->find("name", "julian"), $this->julian);
        $set->add($this->janine);

        $this->assertEqual($set->find("age", "19"), $this->janine);
        $this->assertEqual($set->find("name", "JULIAN", false), null);
        $this->assertEqual($set->find("name", "JULIAN", true), $this->julian);
    }

    public function testCommitStaging() {
        $set = new DataObjectSet("DumpDBElementPerson");
        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);

        $set->commitStaging();
        $this->assertEqual($set->getFetchMode(), DataObjectSet::FETCH_MODE_EDIT);

        $set->add($this->janine);
        try {
            $set->commitStaging();
            $this->assertEqual(true, false);
        } catch(Exception $e) {
            $this->assertEqual($e->getMessage(), "1 could not be written.");
        }
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
    public $table;

    public function __construct($dataClass = "", $exp = null)
    {
        $this->_dataClass = $dataClass;
        $this->inExpansion = $exp;
    }

    protected function getListBy($records, $filter, $sort, $limit) {
        $copyRecords = array();
        foreach($records as $record) {
            $copyRecords[] = is_object($record) ? clone $record : $record;
        }

        $list = new ArrayList($copyRecords);
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

    /**
     * @return string
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function baseTable()
    {
        return $this->table;
    }

    /**
     * @param array $manipulation
     * @param ManyMany_DataObjectSet $set
     * @param array $writeData array of versionid => boolean
     * @return mixed
     */
    public function onBeforeManipulateManyMany(&$manipulation, $set, $writeData)
    {
        // TODO: Implement onBeforeManipulateManyMany() method.
    }

    /**
     * @return void
     */
    public function clearCache()
    {
        // TODO: Implement clearCache() method.
    }

    /**
     * @param array $manipulation
     * @return bool
     */
    public function manipulate($manipulation)
    {
        // TODO: Implement manipulate() method.
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
        return isset($this->model) ? $this->model : new ViewAccessableData($data);
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
    public function __construct($name = null, $age = null, $gender = null)
    {
        parent::__construct();

        if(is_array($name)) {
            $this->name = isset($name["name"]) ? $name["name"] : null;
            $this->age = isset($name["age"]) ? $name["age"] : null;
            $this->gender = isset($name["gender"]) ? $name["gender"] : null;
        }

        $this->name = $name;
        $this->age = $age;
        $this->gender = $gender;
    }

    public function ToArray($additional_fields = array())
    {
        return array_merge(parent::ToArray($additional_fields), array(
            "name"      => $this->name,
            "age"       => $this->age,
            "gender"    => $this->gender
        ));
    }

    public function writeToDBInRepo($repository, $forceInsert = false, $forceWrite = false, $snap_priority = 2, $history = true, $silent = false, $overrideCreated = false)
    {
        throw new Exception("Should not be written.");
    }
}
