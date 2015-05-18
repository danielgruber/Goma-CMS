<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class ModelWriter extends Object {

    /**
     * DataObject to write.
     *
     * @var DataObject
     */
    protected $model;

    /**
     * type of write.
     *
     * @var int
     */
    protected $writeType = ModelRepository::WRITE_TYPE_PUBLISH;

    /**
     * type of command. you can force insert here.
     *
     * @var int
     */
    protected $commandType;

    /**
     * Database-writer.
     *
     * @var iDataBaseWriter
     */
    protected $databaseWriter;

    /**
     * set of data which can be written to DataBase.
     *
     * @var array
     */
    private $data;

    /**
     * record for updating.
     */
    private $updatableModel;

    /**
     * defines if we should update editorid and last_modified.
     */
    private $updateLastModified = true;

    /**
     * defines if to get autorid and created from old object.
     */
    private $moveAutorAndCreatedFromOld = true;

    /**
     * creates write.
     *
     * @param DataObject $model new version
     * @param int $commandType
     * @param DataObject $objectToUpdate old version
     * @param iDataBaseWriter $writer
     */
    public function __construct($model, $commandType, $objectToUpdate, $writer = null) {
        parent::__construct();

        $this->model = $model;
        $this->commandType = $commandType;
        $this->updatableModel = $objectToUpdate;
        $this->databaseWriter = isset($writer) ? $writer : new MySQLWriterImplementation();
        $this->databaseWriter->setWriter($this);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param bool $silent
     */
    public function setSilent($silent)
    {
        $this->updateLastModified = $silent;
    }

    /**
     * @param bool $created
     */
    public function setUpdateCreated($created) {
        $this->moveAutorAndCreatedFromOld = !$created;
    }

    /**
     * @return int
     */
    public function getWriteType()
    {
        return $this->writeType;
    }

    /**
     * @param int $writeType
     * @return $this
     */
    public function setWriteType($writeType)
    {
        $this->writeType = $writeType;
        return $this;
    }

    /**
     * @param int $commandType
     * @return $this
     */
    public function setCommandType($commandType)
    {
        $this->commandType = $commandType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewVersion()
    {
        return $this->newVersion;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecordid()
    {
        return $this->getModel()->id;
    }

    /**
     * @return int
     */
    public function getOldId()
    {
        return $this->getObjectToUpdate() ? $this->getObjectToUpdate()->versionid : 0;
    }
    /**
     * returns what type of command is used.
     *
     * @return int
     */
    protected function getCommandType() {
        return $this->commandType;
    }

    /**
     * returns current data-record or null if data should be inserted.
     *
     * @return DataObject
     */
    protected function getObjectToUpdate() {
        return $this->updatableModel;
    }

    /**
     * updates fields like author or last-modified when required.
     */
    protected function updateStatusFields() {

        if($this->moveAutorAndCreatedFromOld || !isset($this->data["created"])) {
            $this->data["created"] = $this->getObjectToUpdate() ? $this->getObjectToUpdate()->created : NOW;
        }

        if($this->moveAutorAndCreatedFromOld || !isset($this->data["autorid"])) {
            $this->data["autorid"] = $this->getObjectToUpdate() ? $this->getObjectToUpdate()->autorid : member::$id;
        }

        if($this->updateLastModified || !isset($this->data["last_modified"])) {
            $this->data["last_modified"] = NOW;
            $this->data["editorid"] = member::$id;
        }

        $this->data["snap_priority"] = $this->getWriteType();
        $this->data["class_name"] = $this->model->isField("class_name") ? $this->model->fieldGET("class_name") : $this->model->classname;
    }

    /**
     * prepares data to write and validates if a write is required.
     *
     * @return bool
     * @throws PermissionException
     */
    protected function gatherDataToWrite() {

        $this->data = array();

        $objectForUpdate = $this->getObjectToUpdate();

        $this->model->onBeforeWrite();

        if($objectForUpdate) {
            $this->data = array_merge($objectForUpdate->ToArray(), $this->data);

            // copy many-many-relations
            foreach($this->ManyManyRelationships() as $name => $relationShip) {
                if (!isset($this->data[$name . "ids"]) && !isset($this->data[$name])) {
                    $this->data[$name] = $this->getRelationData($name);
                } else if (!isset($this->data[$name . "ids"]) && is_array($this->data[$name])) {
                    unset($this->data[$name . "ids"]);
                }
            }
        } else {
            $this->data = $this->model->toArray();
        }

        $this->updateStatusFields();

        $this->callExtending("gatherDataToWrite");
    }

    /**
     * returns true when version differs, so you really know that these are different versions.
     *
     * @param DataObject $model
     * @return bool
     */
    protected function versionDiffers($model) {
        return (
            $model->publishedid == 0 ||
            $model->stateid == 0 ||
            ($model->stateid != $this->getOldId() && $this->getWriteType() == self::WRITE_TYPE_SAVE) ||
            ($model->publishedid != $this->getOldId() && $this->getWriteType() == self::WRITE_TYPE_PUBLISH));
    }

    /**
     * compares two values and also types, but it is implemented, that comparable types like
     * int and string are equal when holding equal values.
     *
     * @param mixed $var1
     * @param mixed $var2
     * @return bool
     */
    protected static function valueMatches($var1, $var2) {
        $comparableTypes = array("boolean", "integer", "string", "double");
        if (in_array(gettype($var1), $comparableTypes) && in_array(gettype($var2), $comparableTypes))
        {
            if ($var1 != $var2) {
                return false;
            }
        } else if (gettype($var1) != gettype($var2) || $var1 != $var2) {
            return false;
        }

        return true;
    }

    /**
     * updates changed-array and $forceChanged when relationship has changed.
     *
     * @param array names of relationship
     * @param bool $useIds
     * @param string $useObject
     * @return bool
     */
    protected function checkForChangeInRelationship($relationShips, $useIds = true, $useObject = null) {
        foreach ($relationShips as $name) {

            if ($useIds && (isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))) {
                return true;
            }

            if($useObject) {
                if((isset($this->data[$name]))) {
                    if($useObject === true) {
                        if(is_array($this->data[$name])) {
                            return true;
                        }
                    } else if(is_a($this->data[$name], $useObject)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * checks if data should be written or it is the same than the data which is already existing.
     *
     * @return bool
     * @throws MySQLException
     */
    protected function checkForChanges() {

        if(!$this->getObjectToUpdate()) {
            return true;
        }

        // first check if this record is important
        if (!$this->model->isField("stateid") || !$this->model->isField("publishedid")) {
            $info = $this->databaseWriter->findStateRow($this->model->id);

            $this->model->stateid = $info->getSecond();
            $this->model->publishedid = $info->getFirst();
        }

        $oldData = $this->getObjectToUpdate()->ToArray();

        // try and find out whether to write cause of state
        if (!$this->versionDiffers($this->model)) {

            // first check for raw data.
            foreach($oldData as $key => $val) {
                if (!self::valueMatches($val, $this->data[$key]) && $key != "last_modified" && $key != "editorid") {
                    return true;
                }
            }

            // has-one
            if ($has_one = $this->model->hasOne()) {
                if($this->checkForChangeInRelationship(array_keys($has_one), false, "DataObject")) {
                    return true;
                }
            }

            // many-many
            if ($relationShips = $this->model->ManyManyRelationships()) {
                if($this->checkForChangeInRelationship(array_keys($relationShips), true, "ManyMany_DataObjectSet")) {
                    return true;
                }
            }

            // has-many
            if ($has_many = $this->model->hasMany()) {
                if($this->checkForChangeInRelationship(array_keys($has_many), true, true)) {
                    return true;
                }
            }

            return false;
        } else {
            return true;
        }
    }

    /**
     * iterates through has-one-relationships and checks if there is something to write.
     */
    protected function checkForWritableHasOne() {
        if ($has_one = $this->model->hasOne()) {
            foreach($has_one as $key => $value) {
                if (isset($this->data[$key]) && is_object($this->data[$key]) && is_a($this->data[$key], "DataObject")) {

                    // check for write
                    if($this->getCommandType() == ModelRepository::COMMAND_TYPE_INSERT || $this->data[$key]->wasChanged()) {
                        $this->data[$key]->writeToDB(false, $this->permissionsOverridden, $this->getWriteType());
                    }

                    // get id from object
                    $this->data[$key . "id"] = $this->data[$key]->id;
                    unset($this->data[$key]);
                }
            }
        }
    }

    /**
     * writes generated data to DataBase.
     */
    public function write() {
        $this->validate();

        if ($this->data === null) {
            return;
        }

        ModelTransactionCache::clear();

        $this->gatherDataToWrite();

        // find out if we should write data
        if ($this->getCommandType() != ModelRepository::COMMAND_TYPE_INSERT) {
            if (!$this->checkForChanges()) {
                return;
            }
        }

        $this->databaseWriter->write($this->data);
    }

    /**
     * validates permission.
     */
    public function validatePermission() {

        if ($this->commandType == self::COMMAND_TYPE_INSERT) {
            if (!$this->model->can("Insert")) {
                throw new PermissionException("Record {$this->model->id} of type {$this->model->classname} can't" .
                    "be inserted cause of missing insert permissions.",
                    ExceptionManager::PERMISSION_ERROR,
                    "Insert");
            }
        } else {
            if (!$this->model->can("Write")) {
                throw new PermissionException("Record {$this->model->id} of type {$this->model->classname} can't" .
                    "be updated cause of missing writing permissions.",
                    ExceptionManager::PERMISSION_ERROR,
                    "Write");
            }
        }


        if ($this->commandType  == self::WRITE_TYPE_PUBLISH) {
            if (!$this->model->can("Publish")) {
                throw new PermissionException("Record {$this->model->id} of type {$this->model->classname} can't" .
                    "be published cause of missing publish permissions.",
                    ExceptionManager::PERMISSION_ERROR,
                    "Publish");
            }
        }
    }
}