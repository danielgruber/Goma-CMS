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

    const WRITE_TYPE_AUTOSAVE = 0;
    const WRITE_TYPE_SAVE = 1;
    const WRITE_TYPE_PUBLISH = 2;

    const COMMAND_TYPE_UPDATE = 2;
    const COMMAND_TYPE_INSERT = 1;
    const COMMAND_TYPE_ANY = 0;

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
    protected $writeType = self::WRITE_TYPE_PUBLISH;

    /**
     * type of command. you can force insert here.
     *
     * @var int
     */
    protected $commandType = self::COMMAND_TYPE_ANY;

    /**
     * set of data which can be written to DataBase.
     *
     * @var array
     */
    private $data;

    /**
     * current recordid of model.
     */
    private $recordid;

    /**
     * old version id of model.
     */
    private $oldId;

    /**
     * new version id of record.
     */
    private $newVersion;

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
     * overrides permissions.
     *
     * @var bool
     */
    private $permissionsOverridden = false;

    /**
     * creates write.
     *
     * @param DataObject $model
     */
    public function __construct($model) {
        parent::__construct();

        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param boolean $permissionsOverridden
     * @return $this
     */
    public function setPermissionsOverridden($permissionsOverridden = true)
    {
        $this->permissionsOverridden = $permissionsOverridden;
        return $this;
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
     * @return mixed
     */
    public function getRecordid()
    {
        if(!isset($this->oldId)) {
            $this->generateOldAndRecordId();
        }

        return $this->recordid;
    }

    /**
     * @return mixed
     */
    public function getOldId()
    {
        if(!isset($this->oldId)) {
            $this->generateOldAndRecordId();
        }

        return $this->oldId;
    }

    /**
     * generates oldid and recordid.
     */
    private function generateOldAndRecordId() {
        if($this->getCommandType() == self::COMMAND_TYPE_INSERT) {
            $this->oldId = 0;
            $this->recordid = 0;
        } else {
            $this->oldId = $this->model->versionid;
            $this->recordid = $this->model->id;
        }
    }

    /**
     * validates write.
     * throws exception when having problems.
     */
    protected function validate() {
        if (!defined("CLASS_INFO_LOADED")) {
            throw new LogicException("Calling ModelWriter::write without loaded ClassInfo is not allowed.");
        }

        if(!is_object($this->model)) {
            throw new InvalidArgumentException("Model must be a DataObject.");
        }

        ModelBuilder::checkForTableExisting($this->model);
    }

    /**
     * returns what type of command is used.
     *
     * @return int
     */
    protected function getCommandType() {
        if ($this->commandType != self::COMMAND_TYPE_ANY) {
            return $this->commandType;
        }

        $this->commandType = $this->getObjectToUpdate() ? self::COMMAND_TYPE_UPDATE : self::COMMAND_TYPE_INSERT;

        return $this->commandType;
    }

    /**
     * returns current data-record or null if data should be inserted.
     *
     * @return DataObject
     */
    protected function getObjectToUpdate() {
        if(!isset($this->updatableModel)) {
            if($record = DataObject::get_one($this->model->BaseClass(), array("versionid" => $this->versionid))) {
                $this->updatableModel = $record;
            } else {
                $this->commandType = self::COMMAND_TYPE_INSERT;
                return null;
            }
        }

        return $this->updatableModel;
    }

    /**
     * can-wrapper.
     * @param string $perm
     * @return bool
     */
    protected function can($perm) {
        return $this->model->can($perm, $this->model);
    }

    /**
     * validates permission.
     *
     * @return bool
     */
    protected function validatePermission() {

        if(!$this->permissionsOverridden) {

            if ($this->getCommandType() == self::COMMAND_TYPE_INSERT) {
                if (!$this->can("Insert")) {
                    return false;
                }
            } else {
                if (!$this->can("Write")) {
                    return false;
                }
            }


            if ($this->getWriteType() == self::WRITE_TYPE_PUBLISH) {
                if (!$this->can("Publish")) {
                    return false;
                }
            }
        }

        return true;
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
     * @param bool $overrideCreated
     * @return bool
     * @throws PermissionException
     */
    protected function gatherDataToWriteAndValidatePermission() {

        $this->data = array();

        $objectForUpdate = $this->getObjectToUpdate();
        if(!$this->validatePermission()) {
            throw new PermissionException("You don't have the Permission to write objects of type ".$this->model->classname.".");
        }

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
     * tries to find recordid in versions of state-table.
     *
     * @param int $recordid
     * @return Tuple<publishedid, stateid>
     * @throws SQLException
     */
    protected function findStateRow($recordid) {
        $query = new SelectQuery($this->baseTable . "_state", array("publishedid", "stateid"), array("id" => $recordid));
        if ($query->execute()) {
            if($row = $query->fetch_object()) {
                return new Tuple($row->publishedid, $row->stateid);
            } else {
                return new Tuple(0, 0);
            }
        } else {
            throw new MySQLException();
        }
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

        if(!$this->recordid) {
            return true;
        }

        // first check if this record is important
        if (!$this->model->isField("stateid") || !$this->model->isField("publishedid")) {
            $info = $this->findStateRow($this->recordid);

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
     * inserts or updates data in state-table.
     *
     * @param array $fields
     * @param string $command
     */
    protected function insertIntoStateTable($fields, $command = "insert") {
        $manipulation = array(
            "state" => array(
                "table_name"=> $this->model->baseTable . "_state",
                "command"	=> $command,
                "fields"	=> $fields
            )
        );

        if($command == "update") {
            if(isset($fields["id"])) {
                $manipulation["state"]["id"] = $fields["id"];
            } else {
                throw new LogicException("Updating State-Table requires an ID.");
            }
        }

        SQL::writeManipulation($manipulation);
    }

    /**
     * forces recordid is represented in state-table.
     * it may change recordid.
     */
    protected function forceRecordId() {
        if ($this->getCommandType() == self::COMMAND_TYPE_INSERT) {
            $this->insertIntoStateTable(array(
                "stateid" => 0,
                "publishedid" => 0
            ));

            $id = sql::insert_id();
            $this->recordid = $id;
        } else if (!isset($this->data["publishedid"])) {
            $query = new SelectQuery($this->baseTable . "_state", array("id"), array("id" => $this->recordid));
            if ($query->execute()) {
                $data = $query->fetch_assoc();

                // check if record was found.
                if (!isset($data["id"])) {
                    $this->insertIntoStateTable(array(
                        "id" => $this->recordid
                    ));
                }

            } else {
                throw new SQLException();
            }
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
                    if($this->getCommandType() == self::COMMAND_TYPE_INSERT || $this->data[$key]->wasChanged()) {
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
    public function write($history = true) {
        $this->validate();

        if ($this->data === null) {
            return;
        }

        // TODO: clear cache

        $this->gatherDataToWriteAndValidatePermission();

        // find out if we should write data
        if ($this->getCommandType() != self::COMMAND_TYPE_INSERT && !$this->permissionsOverridden) {
            if (!$this->checkForChange($this->getWriteType(), $this->data, $changed)) {
                return;
            }
        }

        $this->forceRecordId();

        // generate has-one-data
        $this->checkForWritableHasOne();

        $many_many_objects = array();
        $many_many_relationships = array();

        // here the magic for many-many happens
        if ($many_many = $this->model->ManyManyRelationships()) {
            foreach($many_many as $key => $value) {
                if (isset($this->data[$key]) && is_object($this->data[$key]) && is_a($this->data[$key], "ManyMany_DataObjectSet")) {
                    $many_many_objects[$key] = $this->data[$key];
                    $many_many_relationships[$key] = $value;
                    unset($this->data[$key]);
                }
                unset($key, $value);
            }
        }

        $baseClass = $this->model->baseClass();

        // generate the write-manipulation
        $manipulation = array(
            $baseClass => array(
                "command"	=> "insert",
                "fields"	=> array_merge(array(
                    "class_name"	=> $this->classname,
                    "last_modified" => NOW
                ), DataBaseFieldManager::getFieldValues($this->model->baseClass(),
                    $this->data,
                    $this->getCommandType() == self::COMMAND_TYPE_INSERT,
                    !$this->updateLastModified
                ))
            )
        );

        if(!SQL::manipulate($manipulation)) {
            throw new LogicException("Manipulation malformed. " . print_r($manipulation, true));
        }

        $this->newVersion = SQL::Insert_ID();

        $manipulation = array();

        if ($dataClasses = ClassInfo::DataClasses($baseClass))
        {
            foreach($dataClasses as $class => $table)
            {
                $manipulation[$class . "_clean"] = array(
                    "command"	=> "delete",
                    "table_name"=> ClassInfo::$class_info[$class]["table"],
                    "id"		=> $this->newVersion
                );

                $manipulation[$class] = array(
                    "command"	=> "insert",
                    "fields"	=> array_merge(array(
                        "id" 			=> $this->newVersion
                    ), DataBaseFieldManager::getFieldValues($class,
                        $this->data,
                        $this->getCommandType() == self::COMMAND_TYPE_INSERT,
                        !$this->updateLastModified
                    ))
                );

            }
        }

        // relation-data

        /** @var ManyMany_DataObjectSet $object */
        foreach($many_many_objects as $key => $object) {
            $object->setRelationENV($many_many_relationships[$key], $this->newVersion);
            $object->writeToDB(false, true, $this->getWriteType());
            unset($this->data[$key . "ids"]);
        }

        $many_many = $this->ManyManyRelationships();

        // many-many
        if ($many_many) {
            /** @var ModelManyManyRelationshipInfo $relationShip */
            foreach($many_many as $name => $relationShip)
            {
                if(isset($this->data[$name]) && is_array($this->data[$name])) {
                    $manipulation = $this->set_many_many_manipulation($manipulation, $name, $this->data[$name], $this->permissionsOverridden, $this->getWriteType(), $history);
                } else if (isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
                {
                    $manipulation = $this->set_many_many_manipulation($manipulation, $name, $this->data[$name . "ids"], $this->permissionsOverridden, $this->getWriteType(), $history);
                }

            }
        }

        // has-many
        if ($this->hasMany())
            foreach($this->hasMany() as $name => $class)
            {
                if (isset($this->data[$name]) && is_object($this->data[$name]) && is_a($this->data[$name], "HasMany_DataObjectSet")) {
                    $key = array_search($this->model->classname, ClassInfo::$class_info[$class]["has_one"]);
                    if ($key === false)
                    {
                        $currentClass = $this->model->classname;
                        while($currentClass = strtolower(get_parent_class($currentClass)))
                        {
                            if ($key = array_search($currentClass, ClassInfo::$class_info[$class]["has_one"]))
                            {
                                break;
                            }
                        }
                    }
                    if ($key === false)
                    {
                        if (PROFILE) Profiler::unmark("DataObject::write");
                        throw new LogicException("Could not find relation for ".$name."ids.");
                    }

                    $this->data[$name]->setRelationENV($name, $key . "id", $this->recordid);
                    $this->data[$name]->writeToDB(false, $this->permissionsOverridden, $this->getWriteType());
                } else {
                    if (isset($this->data[$name]) && !isset($this->data[$name . "ids"]))
                        $this->data[$name . "ids"] = $this->data[$name];
                    if (isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
                    {
                        // find field
                        $key = array_search($this->model->classname, ClassInfo::$class_info[$class]["has_one"]);
                        if ($key === false)
                        {
                            $currentClass = $this->model->classname;
                            while($currentClass = strtolower(get_parent_class($currentClass)))
                            {
                                if ($key = array_search($currentClass, ClassInfo::$class_info[$class]["has_one"]))
                                {
                                    break;
                                }
                            }
                        }

                        if ($key === false)
                        {
                            throw new LogicException("Could not find relation for ".$name."ids.");
                        }

                        foreach($this->data[$name . "ids"] as $id) {
                            $editdata = DataObject::get($class, array("id" => $id));
                            $editdata[$key . "id"] = $this->recordid;
                            $editdata->write(false, true, $this->getCommandType());
                            unset($editdata);
                        }
                    }
                }
            }



        // add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
        if ($this->getOldId() != 0) {
            $manipulation = $this->moveManyManyExtra($manipulation, $this->getOldId());
        }

        self::$datacache[$this->model->baseClass()] = array();

        // get correct oldid for history
        if ($data = DataObject::get_one($this->model->classname, array("id" => $this->recordid))) {
            $historyOldID = ($data["publishedid"] == 0) ? $data["publishedid"] : $data["stateid"];
        } else {
            $historyOldID = $this->getOldId();
        }

        // fire events!
        $this->model->onBeforeWriteData();
        $this->model->callExtending("onBeforeWriteData");
        $this->model->onBeforeManipulate($manipulation, $b = "write");
        $this->model->callExtending("onBeforeManipulate", $manipulation, $b = "write");

        // fire manipulation to DataBase
        if (SQL::manipulate($manipulation)) {

            if ($this->newVersion == 0) {
                if (PROFILE) Profiler::unmark("DataObject::write");
                throw new LogicException("There's no versionid defined.");
            }


            if($this->getWriteType() == self::WRITE_TYPE_PUBLISH) {
                $this->onBeforePublish();

                $this->insertIntoStateTable(array(
                    "id"            => $this->recordid,
                    "publishedid"   => $this->newVersion,
                    "stateid"       => $this->newVersion
                ), "update");
            } else {
                $this->insertIntoStateTable(array(
                    "id"            => $this->recordid,
                    "stateid"       => $this->newVersion
                ), "update");
            }

            $this->model->onBeforeManipulate($manipulation, $b = "write_state");
            $this->model->callExtending("onBeforeManipulate", $manipulation, $b = "write_state");
            if (SQL::manipulate($manipulation)) {

                if (StaticsManager::getStatic($this->model->classname, "history") && $history) {

                    $command = $this->getCommandType();
                    if($command != self::COMMAND_TYPE_INSERT && $this->getWriteType() == self::WRITE_TYPE_PUBLISH) {
                        $command = "publish";
                    }

                    History::push($this->model->classname, $this->getOldId(), $this->newVersion, $this->recordid, $command);
                }
                unset($manipulation);

                $this->model->onAfterWrite();
                $this->model->callExtending("onAfterWrite");

                // HERE CLEAN-UP for non-versioned-tables happens
                // if we don't version this dataobject, we need to delete the old record
                if (!self::Versioned($this->model->classname) && $this->getOldId() && $this->getCommandType() != self::COMMAND_TYPE_INSERT) {
                    $manipulation = array(
                        $baseClass => array(
                            "command"	=> "delete",
                            "where" 	=> array(
                                "id" => $this->oldId
                            )
                        )
                    );

                    if ($dataClasses = ClassInfo::DataClasses($baseClass))
                    {
                        foreach(array_keys($dataClasses) as $class)
                        {
                            $manipulation[$class] = array(
                                "command"	=> "delete",
                                "where" 	=> array(
                                    "id" => $this->oldId
                                )
                            );
                        }
                    }

                    // clean-up-many-many
                    foreach($this->model->ManyManyTables() as $data) {
                        $manipulation[$data["table"]] = array(
                            "table" 	=> $data["table"],
                            "command"	=> "delete",
                            "where"		=> array(
                                $data["field"] => $this->oldId
                            )
                        );
                    }

                    $this->model->callExtending("deleteOldVersions", $manipulation, $this->oldId);

                    SQL::manipulate($manipulation);
                }
            } else {
                throw new SQLException();
            }

        } else {
            throw new SQLException();
        }
    }


    /**
     * moves extra many-many-relations.
     *
     * @param array $manipulation
     * @param int $oldId
     * @return array
     * @throws SQLException
     */
    protected function moveManyManyExtra($manipulation, $oldId) {
        $currentClass = $this->classname;
        while($currentClass != null && !ClassInfo::isAbstract($currentClass)) {
            if (isset(ClassInfo::$class_info[$currentClass]["many_many_relations_extra"])) {
                foreach(ClassInfo::$class_info[$currentClass]["many_many_relations_extra"] as $info) {

                    $relationShip = $this->model->getManyManyInfo($info[1], $info[0])->getInverted();
                    $existingData = $this->model->getManyManyRelationShipData($relationShip, null, $oldId);

                    $manipulation[$relationShip->getTableName()] = array(
                        "command"   => "insert",
                        "table_name"=> $relationShip->getTableName(),
                        "fields"    => array(

                        )
                    );
                    foreach($existingData as $data) {
                        $newRecord = $data;
                        $newRecord[$relationShip->getOwnerField()] = $this->newVersion;
                        $newRecord[$relationShip->getTargetField()] = $newRecord["versionid"];

                        unset($newRecord["versionid"], $newRecord["relationShipId"]);
                        $manipulation[$relationShip->getTableName()]["fields"][] = $newRecord;
                    }
                }
            }
            $currentClass = ClassInfo::getParentClass($currentClass);
        }

        return $manipulation;
    }
}