<?php defined('IN_GOMA') OR die();
/**
 * manages connection to Database.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 *
 * last modified: 06.06.2015
 */
class ModelRepository extends IModelRepository {

    /**
     * reads from a given model class.
     */
    public function read() {
        throw new RuntimeException("Not implemented, yet.");
    }

    /**
     * deletes a record.
     * @param DataObject $record
     */
    public function delete($record) {
        throw new RuntimeException("Not implemented, yet.");
    }

    /**
     * returns old record if one is existing.
     * @param DataObject $model
     * @return DataObject|null
     */
    protected function getOldRecord($model) {
        if($model->versionid != 0) {
            return DataObject::get_one($model, array("versionid" => $model->versionid));
        }

        return null;
    }

    /**
     * returns write-object for given parameters.
     *
     * @param DataObject $record
     * @param int $commandType
     * @param DataObject|null $oldRecord
     * @param iDataBaseWriter|null $dbWriter
     * @return ModelWriter
     */
    protected function getWriter($record, $commandType = -1, $oldRecord = null, $dbWriter = null) {
        if($commandType < 1) {
            $old = (isset($oldRecord)) ? $oldRecord : $this->getOldRecord($record);
            $command = isset($old) ? self::COMMAND_TYPE_UPDATE : self::COMMAND_TYPE_INSERT;

            return new ModelWriter($record, $command, $old, $this, $dbWriter);
        }

        return new ModelWriter($record, $commandType, $oldRecord, $this, $dbWriter);
    }

    /**
     * writes a record in repository. it decides if record exists or not and updates or inserts.
     *
     * @param DataObject $record
     * @param bool $forceWrite if to override permissions
     * @param bool $silent if to not update last-modified and editorid
     * @param bool $overrideCreated if to not force created and autorid to not be changed
     * @throws PermissionException
     */
    public function write($record, $forceWrite = false, $silent = false, $overrideCreated = false) {

        $writer = $this->buildWriter($record, -1, $silent, $overrideCreated);

        if(!$forceWrite) {
            $writer->validatePermission();
        }

        $writer->write();
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
    public function writeState($record, $forceWrite = false, $silent = false, $overrideCreated = false) {
        $writer = $this->buildWriter($record, -1, $silent, $overrideCreated, self::WRITE_TYPE_SAVE);

        if(!$forceWrite) {
            $writer->validatePermission();
        }

        $writer->write();
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
    public function add($record, $forceInsert = false, $silent = false, $overrideCreated = false) {
        $writer = $this->buildWriter($record, self::COMMAND_TYPE_INSERT, $silent, $overrideCreated);

        if(!$forceInsert) {
            $writer->validatePermission();
        }

        $writer->write();
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
    public function addState($record, $forceInsert = false, $silent = false, $overrideCreated = false) {
        $writer = $this->buildWriter($record, self::COMMAND_TYPE_INSERT, $silent, $overrideCreated, self::WRITE_TYPE_SAVE);

        if(!$forceInsert) {
            $writer->validatePermission();
        }

        $writer->write();
    }

    /**
     * builds up writer by parameters.
     *
     * @param DataObject $record
     * @param int $command
     * @param bool $silent
     * @param bool $overrideCreated
     * @param int $writeType
     * @param iDataBaseConnector|null $dbWriter
     * @return ModelWriter
     */
    public function buildWriter($record, $command, $silent, $overrideCreated, $writeType = self::WRITE_TYPE_PUBLISH, $dbWriter = null) {
        $writer = $this->getWriter($record, $command, null, $dbWriter);

        $writer->setUpdateCreated($overrideCreated);
        $writer->setSilent($silent);
        $writer->setWriteType($writeType);

        return $writer;
    }
}
