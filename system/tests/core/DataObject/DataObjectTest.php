<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for DataObject-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class DataObjectTests extends GomaUnitTest
{
    static $area = "Model";
    /**
     * name
     */
    public $name = "DataObject";

    /**
     * write-test
     */
    public function testWriteToDB() {
        $this->unitTestWriteToDB(array(), "write");
        $this->unitTestWriteToDB(array(false, false, 2), "write");
        $this->unitTestWriteToDB(array(false, false, 1), "writeState");
        $this->unitTestWriteToDB(array(true, false, 1), "addState");
        $this->unitTestWriteToDB(array(true, false, 2), "add");

        $this->unitTestWriteToDB(array(false, true, 2), "write");
        $this->unitTestWriteToDB(array(false, true, 1), "writeState");
    }

    public function unitTestWriteToDB($args, $expectedCall) {
        $oldRepo = Core::repository();
        $fakeRepo = new fakeRepo();
        Core::__setRepo($fakeRepo);

        $record = new MockWriteEntity();
        call_user_func_array(array($record, "writeToDB"), $args);

        $this->assertEqual($fakeRepo->lastMethod, $expectedCall);

        Core::__setRepo($oldRepo);
    }
}

class MockWriteEntity extends DataObject {}

class fakeRepo extends  IModelRepository {

    /**
     * last method info.
     */
    public $lastMethod;

    /**
     * reads from a given model class.
     */
    public function read()
    {
        // TODO: Implement read() method.
        $this->lastMethod = "read";
    }

    /**
     * deletes a record.
     *
     * @param DataObject $record
     */
    public function delete($record)
    {
        // TODO: Implement delete() method.
        $this->lastMethod = "delete";
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
        $this->lastMethod = "write";
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
        $this->lastMethod = "writeState";
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
        $this->lastMethod = "add";
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
        $this->lastMethod = "addState";
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
        $this->lastMethod = "buildWriter";
    }
}