<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Model-Writer.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ModelWriterTests extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "Transactions";

    /**
     * internal name.
     */
    public $name = "ModelWriter";

    /**
     * tests if change-property is correctly evaluated.
     */
    public function testChanged() {
        $this->assertTrue($this->unitTestChanged(array(
            "test" => 1,
            "last_modified" => NOW - 10,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => 3
        ), array(
            "test" => 1,
            "last_modified" => NOW,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => 3
        )));

        $this->assertFalse($this->unitTestChanged(array(
            "test" => 1,
            "last_modified" => NOW - 10,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => 3
        ), array(
            "test" => "1",
            "last_modified" => NOW - 10,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => "3"
        )));

        $this->assertFalse($this->unitTestChanged(array(
            "test" => 1,
            "last_modified" => NOW - 10,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => 3
        ), array(
            "test" => "1",
        )));

        $this->assertTrue($this->unitTestChanged(array(
            "test" => 1,
            "last_modified" => NOW - 10,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => 3
        ), array(
            "test" => "2",
        )));

        $this->assertTrue($this->unitTestChanged(array(
            "test" => 1,
            "last_modified" => NOW - 10,
            "created"       => NOW - 20,
            "autorid"       => 2,
            "editorid"      => 3
        ), array(
            "test" => "1",
            "autorid" => 3
        )));
    }

    protected function unitTestChanged($mockData, $newData) {
        $mockObject = new MockUpdatableGObject();
        $mockObject->data = $mockData;

        $newDataObject = new MockUpdatableGObject();
        $newDataObject->data = $newData;

        $writer = new ModelWriter($newDataObject, ModelRepository::COMMAND_TYPE_UPDATE, $mockObject, new MockDBRepository(), new MockDBWriter());

        $reflectionMethod = new ReflectionMethod("ModelWriter", "checkForChanges");
        $reflectionMethod->setAccessible(true);

        $reflectionMethodData = new ReflectionMethod("ModelWriter", "gatherDataToWrite");
        $reflectionMethodData->setAccessible(true);
        $reflectionMethodData->invoke($writer);

        $this->assertEqual($newDataObject->onBeforeWriteFired, 0);

        return $reflectionMethod->invoke($writer);
    }

    /**
     * tests write-method once.
     */
    public function testWrite() {
        $mockData = array("test" => 1);
        $newData = array("test" => 2);
        $mockObject = new MockUpdatableGObject();
        $mockObject->checkLogic = true;
        $mockObject->data = $mockData;

        $newDataObject = new MockUpdatableGObject();
        $mockObject->checkLogic = false;
        $newDataObject->data = $newData;

        $writer = new ModelWriter($newDataObject, ModelRepository::COMMAND_TYPE_UPDATE, $mockObject, new MockDBRepository(), new MockDBWriter());
        ModelWriterTestExtensionForEvents::$checkLogic = true;
        ModelWriterTestExtensionForEvents::clear();
        $this->assertEqual(ModelWriterTestExtensionForEvents::$onBeforeWriteFired, 0);
        $writer->write();

        $this->assertEqual($mockObject->onBeforeWriteFired, 0);
        $this->assertEqual($mockObject->onAfterWriteFired, 0);

        $this->assertEqual($newDataObject->onBeforeWriteFired, 1);
        $this->assertEqual($newDataObject->onAfterWriteFired, 1);

        /** @var ModelWriterTestExtensionForEvents $extInstance */
        $this->assertEqual(ModelWriterTestExtensionForEvents::$onBeforeWriteFired, 1);
        $this->assertEqual(ModelWriterTestExtensionForEvents::$onAfterWriteFired, 1);
        $this->assertEqual(ModelWriterTestExtensionForEvents::$onBeforeDBWriterFired, 1);
        $this->assertEqual(ModelWriterTestExtensionForEvents::$gatherDataToWrite, 1);
    }

    /**
     * tests if valueMatches works.
     */
    public function testvalueMatches() {
        $this->assertTrue($this->unittestvalueMatches("1", 1));
        $this->assertTrue($this->unittestvalueMatches("1", "1"));
        $this->assertTrue($this->unittestvalueMatches("2", "2"));
        $this->assertTrue($this->unittestvalueMatches(2, "2"));
        $this->assertTrue($this->unittestvalueMatches(1, true));
        $this->assertTrue($this->unittestvalueMatches(0, false));
        $this->assertTrue($this->unittestvalueMatches(0, ""));
        $this->assertTrue($this->unittestvalueMatches(false, ""));
        $this->assertTrue($this->unittestvalueMatches(true, "2"));

        $this->assertFalse($this->unittestvalueMatches(22, "2"));
        $this->assertFalse($this->unittestvalueMatches(1, ""));
        $this->assertFalse($this->unittestvalueMatches(new StdClass("2"), "2"));
    }

    protected function unittestvalueMatches($var1, $var2) {
        $reflectionMethod = new ReflectionMethod("ModelWriter", "valueMatches");
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke(null, $var1, $var2);
    }

    public function testPermissionCalling() {
        $this->assertEqual(
            $this->unittestPermissionCalling(IModelRepository::COMMAND_TYPE_INSERT, IModelRepository::WRITE_TYPE_PUBLISH),
            array(ModelPermissionManager::PERMISSION_TYPE_INSERT, ModelPermissionManager::PERMISSION_TYPE_PUBLISH)
        );

        $this->assertEqual(
            $this->unittestPermissionCalling(IModelRepository::COMMAND_TYPE_PUBLISH, IModelRepository::WRITE_TYPE_PUBLISH),
            array(ModelPermissionManager::PERMISSION_TYPE_WRITE, ModelPermissionManager::PERMISSION_TYPE_PUBLISH)
        );

        $this->assertEqual(
            $this->unittestPermissionCalling(IModelRepository::COMMAND_TYPE_INSERT, IModelRepository::WRITE_TYPE_SAVE),
            array(ModelPermissionManager::PERMISSION_TYPE_INSERT)
        );

        $this->assertEqual(
            $this->unittestPermissionCalling(IModelRepository::COMMAND_TYPE_UPDATE, IModelRepository::WRITE_TYPE_PUBLISH),
            array(ModelPermissionManager::PERMISSION_TYPE_WRITE, ModelPermissionManager::PERMISSION_TYPE_PUBLISH)
        );

        $this->assertEqual(
            $this->unittestPermissionCalling(IModelRepository::COMMAND_TYPE_UPDATE, IModelRepository::WRITE_TYPE_SAVE),
            array(ModelPermissionManager::PERMISSION_TYPE_WRITE)
        );

        try {
            $this->unittestPermissionCalling(IModelRepository::COMMAND_TYPE_UPDATE, IModelRepository::WRITE_TYPE_SAVE, false);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertIsA($e, "PermissionException");
        }
    }

    public function unittestPermissionCalling($commandType, $writeType, $validate = true) {
        $model = new MockUpdatableGObject();
        $model->validate = $validate;
        $modelWriter = new ModelWriter($model, $commandType, $model, new MockDBRepository(), new MockDBWriter());
        $modelWriter->setWriteType($writeType);

        $modelWriter->validatePermission();

        return $model->getCalledPermissions();
    }
}

class MockUpdatableGObject extends gObject {
    public $data;

    public $stateid = 1;
    public $publishedid = 1;
    public $versionid = 1;
    public $id = 1;
    public $onBeforeWriteFired = 0;
    public $onAfterWriteFired = 0;
    public $checkLogic = false;
    protected $calledPermissions = array();
    public $validate = true;

    public function can($permission) {
        $this->calledPermissions[] = strtolower($permission);
        return $this->validate;
    }

    /**
     * @return array
     */
    public function getCalledPermissions()
    {
        return $this->calledPermissions;
    }

    public function clearPermissions() {
        $this->calledPermissions = array();
    }

    public function __get($k) {
        return "";
    }

    public function ToArray() {
        return $this->data;
    }

    public function onBeforeWrite() {
        $this->onBeforeWriteFired++;
    }

    public function onAfterWrite() {
        if($this->checkLogic && $this->onBeforeWriteFired == $this->onAfterWriteFired) {
            throw new LogicException("OnBeforeWrite must be fired before onAfterWrite");
        }
        $this->onAfterWriteFired++;
    }

    public function workWithExtensionInstance() {

    }

    public function __call($key, $val) {
        return array();
    }
}

class ModelWriterTestExtensionForEvents extends Extension {
    public static $onBeforeWriteFired = 0;
    public static $onAfterWriteFired = 0;
    public static $gatherDataToWrite = 0;
    public static $onBeforeDBWriterFired = 0;
    public static $checkLogic = false;
    protected $calledPermissions = array();

    public static function clear() {
        self::$onBeforeWriteFired = 0;
        self::$onAfterWriteFired = 0;
        self::$gatherDataToWrite = 0;
        self::$onBeforeDBWriterFired = 0;
    }

    public function gatherDataToWrite() {
        if(self::$checkLogic && self::$gatherDataToWrite == self::$onBeforeWriteFired) {
            throw new LogicException("onBeforeWrite must be fired before onGatherDataToWrite");
        }
        self::$gatherDataToWrite++;
    }

    public function onBeforeWrite() {
        self::$onBeforeWriteFired++;
    }

    public function onBeforeDBWriter() {
        if(self::$checkLogic && self::$onBeforeDBWriterFired == self::$gatherDataToWrite) {
            throw new LogicException("gatherDataToWrite must be fired before onBeforeDBWrite");
        }
        self::$onBeforeDBWriterFired++;
    }

    public function onAfterWrite() {
        if(self::$checkLogic && self::$onBeforeDBWriterFired == self::$onAfterWriteFired) {
            throw new LogicException("onBeforeDBWriter must be fired before onAfterWrite");
        }
        self::$onAfterWriteFired++;
    }
}
gObject::extend("ModelWriter", "ModelWriterTestExtensionForEvents");

class MockDBRepository extends  IModelRepository {

    /**
     * reads from a given model class.
     */
    public function read()
    {
        // TODO: Implement read() method.
    }

    /**
     * deletes a record.
     *
     * @param DataObject $record
     */
    public function delete($record)
    {
        // TODO: Implement delete() method.
    }

    /**
     * writes a record in repository. it decides if record exists or not and updates or inserts.
     *
     * @param DataObject $record
     * @param bool if $forceWrite if to override permissions
     * @param bool $silent if to not update last-modified and editorid
     * @param bool $overrideCreated if to not force created and autorid to not be changed
     * @throws PermissionException
     */
    public function write($record, $forceWrite = false, $silent = false, $overrideCreated = false)
    {
        // TODO: Implement write() method.
    }

    /**
     * writes a record in repository as state. it decides if record exists or not and updates or inserts.
     *
     * @param DataObject $record
     * @param bool|if $forceWrite if to override permissions
     * @param bool $silent if to not update last-modified and editorid
     * @param bool $overrideCreated if to not force created and autorid to not be changed
     * @throws PermissionException
     */
    public function writeState($record, $forceWrite = false, $silent = false, $overrideCreated = false)
    {
        // TODO: Implement writeState() method.
    }

    /**
     * inserts record as new record.
     *
     * @param DataObject $record
     * @param bool $forceInsert
     * @param bool $silent
     * @param bool $overrideCreated
     * @throws PermissionException
     */
    public function add($record, $forceInsert = false, $silent = false, $overrideCreated = false)
    {
        // TODO: Implement add() method.
    }

    /**
     * inserts record as new record, but does not publish.
     *
     * @param DataObject $record
     * @param bool $forceInsert
     * @param bool $silent
     * @param bool $overrideCreated
     * @throws PermissionException
     */
    public function addState($record, $forceInsert = false, $silent = false, $overrideCreated = false)
    {
        // TODO: Implement addState() method.
    }

    /**
     * builds up writer by parameters.
     *
     * @param DataObject $record
     * @param int $command
     * @param bool $silent
     * @param bool $overrideCreated
     * @param int $writeType
     * @param iDataBaseWriter $dbWriter
     * @return ModelWriter
     */
    public function buildWriter($record, $command, $silent, $overrideCreated, $writeType = self::WRITE_TYPE_PUBLISH, $dbWriter = null)
    {
        // TODO: Implement buildWriter() method.
    }}

class MockDBWriter implements iDataBaseWriter {

    /**
     * sets Writer-Object.
     *
     * @param ModelWriter $writer
     */
    public function setWriter($writer)
    {
        // TODO: Implement setWriter() method.
    }

    /**
     * writes data of Writer to Database.
     */
    public function write()
    {
        // TODO: Implement write() method.
    }

    /**
     * validates.
     */
    public function validate()
    {
        return true;
    }

    /**
     * tries to find recordid in versions of state-table.
     *
     * @param int $recordid
     * @return Tuple<publishedid, stateid>
     */
    public function findStateRow($recordid)
    {
        return new Tuple(1, 1);
    }

    /**
     * publish.
     */
    public function publish()
    {
        // TODO: Implement publish() method.
    }
}
