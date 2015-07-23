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
        $mockObject = new MockUpdatableObject();
        $mockObject->data = $mockData;

        $newDataObject = new MockUpdatableObject();
        $newDataObject->data = $newData;

        $writer = new ModelWriter($newDataObject, ModelRepository::COMMAND_TYPE_UPDATE, $mockObject, new MockDBRepository(), new MockDBWriter());

        $reflectionMethod = new ReflectionMethod("ModelWriter", "checkForChanges");
        $reflectionMethod->setAccessible(true);

        $reflectionMethodData = new ReflectionMethod("ModelWriter", "gatherDataToWrite");
        $reflectionMethodData->setAccessible(true);
        $reflectionMethodData->invoke($writer);

        return $reflectionMethod->invoke($writer);
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
}

class MockUpdatableObject {
    public $data;

    public $stateid = 1;
    public $publishedid = 1;
    public $versionid = 1;
    public $id = 1;

    public function __get($k) {
        return "";
    }

    public function ToArray() {
        return $this->data;
    }

    public function __call($key, $val) {
        return array();
    }
}


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
}