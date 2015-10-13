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
 * last modified: 30.05.2015
 */
abstract class IModelRepository {

    const WRITE_TYPE_AUTOSAVE = 0;
    const WRITE_TYPE_SAVE = 1;
    const WRITE_TYPE_PUBLISH = 2;

    const COMMAND_TYPE_UPDATE = 2;
    const COMMAND_TYPE_INSERT = 1;
    const COMMAND_TYPE_DELETE = 3;
    const COMMAND_TYPE_PUBLISH = 4;
    const COMMAND_TYPE_UNPUBLISH = 5;

    /**
     * reads from a given model class.
     */
    public abstract function read();

    /**
     * deletes a record.
     * @param DataObject $record
     */
    public abstract function delete($record);

    /**
     * writes a record in repository. it decides if record exists or not and updates or inserts.
     *
     * @param DataObject $record
     * @param bool if $forceWrite if to override permissions
     * @param bool $silent if to not update last-modified and editorid
     * @param bool $overrideCreated if to not force created and autorid to not be changed
     * @throws PermissionException
     */
    public abstract function write($record, $forceWrite = false, $silent = false, $overrideCreated = false);

    /**
     * writes a record in repository as state. it decides if record exists or not and updates or inserts.
     *
     * @param DataObject $record
     * @param bool|if $forceWrite if to override permissions
     * @param bool $silent if to not update last-modified and editorid
     * @param bool $overrideCreated if to not force created and autorid to not be changed
     * @throws PermissionException
     */
    public abstract function writeState($record, $forceWrite = false, $silent = false, $overrideCreated = false);

    /**
     * inserts record as new record.
     *
     * @param DataObject $record
     * @param bool $forceInsert
     * @param bool $silent
     * @param bool $overrideCreated
     * @throws PermissionException
     */
    public abstract function add($record, $forceInsert = false, $silent = false, $overrideCreated = false);

    /**
     * inserts record as new record, but does not publish.
     *
     * @param DataObject $record
     * @param bool $forceInsert
     * @param bool $silent
     * @param bool $overrideCreated
     * @throws PermissionException
     */
    public abstract function addState($record, $forceInsert = false, $silent = false, $overrideCreated = false);

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
    public abstract function buildWriter($record, $command, $silent, $overrideCreated, $writeType = self::WRITE_TYPE_PUBLISH, $dbWriter = null);
}