<?php
/**
 * Created by PhpStorm.
 * User: D
 * Date: 18.05.15
 * Time: 00:43
 */

class MySQLWriterImplementation implements iDataBaseWriter {

    /**
     * writer.
     *
     * @var ModelWriter
     */
    protected $writer;

    /**
     * new generated version.
     */
    protected $newVersion;

    /**
     * sets Writer-Object.
     *
     * @param ModelWriter $writer
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;
    }

    /**
     * writes data to Database.
     */
    public function write($data)
    {

        // generate has-one-data
        $data = $this->checkForWritableHasOne($data);

        $many_many_objects = array();
        $many_many_relationships = array();

        // here the magic for many-many happens
        if ($many_many = $this->model()->ManyManyRelationships()) {
            foreach($many_many as $key => $value) {
                if (isset($data[$key]) && is_object($data[$key]) && is_a($data[$key], "ManyMany_DataObjectSet")) {
                    $many_many_objects[$key] = $data[$key];
                    $many_many_relationships[$key] = $value;
                    unset($data[$key]);
                }
                unset($key, $value);
            }
        }

        $baseClass = $this->model()->baseClass();

        $this->newVersion = $this->insertBaseClassAndGetVersionId($data);

        $manipulation = array();

        if ($dataClasses = ClassInfo::DataClasses($baseClass))
        {
            foreach($dataClasses as $class => $table)
            {
                $this->generateTableManipulation($data, $class, $manipulation, $this->newVersion);
            }
        }

        // relation-data

        /** @var ManyMany_DataObjectSet $object */
        foreach($many_many_objects as $key => $object) {
            $object->setRelationENV($many_many_relationships[$key], $this->newVersion);
            $object->writeToDB(false, true, $this->writer->getWriteType());
            unset($data[$key . "ids"]);
        }

        $many_many = $this->model()->ManyManyRelationships();

        // many-many
        if ($many_many) {
            /** @var ModelManyManyRelationshipInfo $relationShip */
            foreach($many_many as $name => $relationShip)
            {
                if(isset($data[$name]) && is_array($data[$name])) {
                    $manipulation = $this->set_many_many_manipulation($manipulation, $name, $data[$name], $this->permissionsOverridden, $this->getWriteType(), $history);
                } else if (isset($data[$name . "ids"]) && is_array($data[$name . "ids"]))
                {
                    $manipulation = $this->set_many_many_manipulation($manipulation, $name, $data[$name . "ids"], $this->permissionsOverridden, $this->getWriteType(), $history);
                }

            }
        }

        // has-many
        if ($this->model()->hasMany())
            foreach($this->model()->hasMany() as $name => $class)
            {
                if (isset($data[$name]) && is_object($data[$name]) && is_a($data[$name], "HasMany_DataObjectSet")) {
                    $key = array_search($this->model()->classname, ClassInfo::$class_info[$class]["has_one"]);
                    if ($key === false)
                    {
                        $currentClass = $this->model()->classname;
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

                    $data[$name]->setRelationENV($name, $key . "id", $this->recordid);
                    $data[$name]->writeToDB(false, $this->permissionsOverridden, $this->writer->getWriteType());
                } else {
                    if (isset($data[$name]) && !isset($data[$name . "ids"]))
                        $data[$name . "ids"] = $data[$name];
                    if (isset($data[$name . "ids"]) && is_array($data[$name . "ids"]))
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

                        foreach($data[$name . "ids"] as $id) {
                            $editdata = DataObject::get($class, array("id" => $id));
                            $editdata[$key . "id"] = $this->recordid;
                            $editdata->write(false, true, $this->writer->getCommandType());
                            unset($editdata);
                        }
                    }
                }
            }



        // add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
        if ($this->getOldId() != 0) {
            $manipulation = $this->moveManyManyExtra($manipulation, $this->getOldId());
        }

        self::$datacache[$this->model()->baseClass()] = array();

        // get correct oldid for history
        if ($data = DataObject::get_one($this->model->classname, array("id" => $this->recordid))) {
            $historyOldID = ($data["publishedid"] == 0) ? $data["publishedid"] : $data["stateid"];
        } else {
            $historyOldID = $this->getOldId();
        }

        // fire events!
        $this->model()->onBeforeWriteData();
        $this->model()->callExtending("onBeforeWriteData");
        $this->model()->onBeforeManipulate($manipulation, $b = "write");
        $this->model()->callExtending("onBeforeManipulate", $manipulation, $b = "write");

        // fire manipulation to DataBase
        if (SQL::manipulate($manipulation)) {

            if ($this->newVersion == 0) {
                if (PROFILE) Profiler::unmark("DataObject::write");
                throw new LogicException("There's no versionid defined.");
            }


            if($this->writer->getWriteType() == ModelRepository::WRITE_TYPE_PUBLISH) {
                $this->model()->onBeforePublish();

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

            $this->model()->onBeforeManipulate($manipulation, $b = "write_state");
            $this->model()->callExtending("onBeforeManipulate", $manipulation, $b = "write_state");
            if (SQL::manipulate($manipulation)) {

                if (StaticsManager::getStatic($this->model->classname, "history") && $history) {

                    $command = $this->writer->getCommandType();
                    if($command != ModelRepository::COMMAND_TYPE_INSERT &&
                        $this->writer->getWriteType() == ModelRepository::WRITE_TYPE_PUBLISH) {
                        $command = "publish";
                    }

                    History::push($this->model()->classname, $this->getOldId(), $this->newVersion, $this->recordid, $command);
                }
                unset($manipulation);

                $this->model()->onAfterWrite();
                $this->model()->callExtending("onAfterWrite");

                // HERE CLEAN-UP for non-versioned-tables happens
                // if we don't version this dataobject, we need to delete the old record
                if (!self::Versioned($this->model()->classname) && $this->getOldId() && $this->getCommandType() != ModelRepository::COMMAND_TYPE_INSERT) {
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
                    foreach($this->model()->ManyManyTables() as $data) {
                        $manipulation[$data["table"]] = array(
                            "table" 	=> $data["table"],
                            "command"	=> "delete",
                            "where"		=> array(
                                $data["field"] => $this->oldId
                            )
                        );
                    }

                    $this->model()->callExtending("deleteOldVersions", $manipulation, $this->oldId);

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
     * inserts data into table of base-class and gets new generated versionid back.@global
     *
     * @param array $data
     * @return int
     */
    protected function insertBaseClassAndGetVersionId($data) {
        // generate the write-manipulation
        $manipulation = array();

        $this->generateTableManipulation($data, $this->model()->baseClass(), $manipulation);

        if(!SQL::manipulate($manipulation)) {
            throw new LogicException("Manipulation malformed. " . print_r($manipulation, true));
        }

        return SQL::Insert_ID();
    }

    /**
     * generates manipulation for given ModelClass with given data.
     *
     * @param array $data
     * @param string $class
     * @param array $manipulation to edit
     * @param int $versionId when set to 0 new record is generated
     */
    protected function generateTableManipulation($data, $class, &$manipulation, $versionId = 0) {

        $fields = array_merge(array(
            "class_name"	=> $this->model()->classname,
            "last_modified" => NOW
        ), DataBaseFieldManager::getFieldValues($class,
            $data,
            $this->writer->getCommandType() == ModelRepository::COMMAND_TYPE_INSERT,
            !$this->writer->getSilent()
        ));

        if($versionId != 0) {
            $manipulation[$class . "_clean"] = array(
                "command"	=> "delete",
                "table_name"=> ClassInfo::$class_info[$class]["table"],
                "id"		=> $versionId
            );

            $fields["id"] = $versionId;
        }

        $manipulation[$class] = array(
            "command"	=> "insert",
            "fields"	=> $fields
        );
    }

    /**
     * iterates through has-one-relationships and checks if there is something to write.
     */
    protected function checkForWritableHasOne($data) {
        if ($has_one = $this->model()->hasOne()) {
            foreach($has_one as $key => $value) {
                if (isset($data[$key]) && is_object($data[$key]) && is_a($data[$key], "DataObject")) {
                    /** @var DataObject $record */
                    $record = $data[$key];

                    // check for write
                    if($this->writer->getCommandType() == ModelRepository::COMMAND_TYPE_INSERT || $record->wasChanged()) {
                        ModelRepository::write($record, true, $this->writer->getSilent(), $this->writer->getUpdateCreated());
                    }

                    // get id from object
                    $data[$key . "id"] = $record->id;
                    unset($data[$key]);
                }
            }
        }

        return $data;
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
        $currentClass = $this->model()->classname;
        while($currentClass != null && !ClassInfo::isAbstract($currentClass)) {
            if (isset(ClassInfo::$class_info[$currentClass]["many_many_relations_extra"])) {
                foreach(ClassInfo::$class_info[$currentClass]["many_many_relations_extra"] as $info) {

                    $relationShip = $this->model()->getManyManyInfo($info[1], $info[0])->getInverted();
                    $existingData = $this->model()->getManyManyRelationShipData($relationShip, null, $oldId);

                    if(!empty($existingData)) {

                        $manipulation[$relationShip->getTableName()] = array(
                            "command"   => "insert",
                            "table_name"=> $relationShip->getTableName(),
                            "fields"    => array()
                        );

                        foreach ($existingData as $data) {
                            $newRecord = $data;
                            $newRecord[$relationShip->getOwnerField()] = $this->newVersion;
                            $newRecord[$relationShip->getTargetField()] = $newRecord["versionid"];

                            unset($newRecord["versionid"], $newRecord["relationShipId"]);
                            $manipulation[$relationShip->getTableName()]["fields"][] = $newRecord;
                        }
                    }
                }
            }
            $currentClass = ClassInfo::getParentClass($currentClass);
        }

        return $manipulation;
    }

    /**
     * validates write.
     * throws exception when having problems.
     */
    public function validate() {
        if (!defined("CLASS_INFO_LOADED")) {
            throw new LogicException("Calling ModelWriter::write without loaded ClassInfo is not allowed.");
        }

        if(!is_object($this->writer->getModel())) {
            throw new InvalidArgumentException("Model must be a DataObject.");
        }

        ModelBuilder::checkForTableExisting($this->writer->getModel());
    }


    /**
     * tries to find recordid in versions of state-table.
     *
     * @param int $recordid
     * @return Tuple<publishedid, stateid>
     * @throws SQLException
     */
    public function findStateRow($recordid) {
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
     * forces recordid is represented in state-table.
     * it may change recordid.
     */
    protected function forceRecordId() {
        if ($this->writer->getCommandType() == ModelRepository::COMMAND_TYPE_INSERT) {
            $this->insertIntoStateTable(array(
                "stateid" => 0,
                "publishedid" => 0
            ));

            $id = sql::insert_id();
            $this->recordid = $id;
        } else if (!isset($data["publishedid"])) {
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
     * inserts or updates data in state-table.
     *
     * @param array $fields
     * @param string $command
     */
    protected function insertIntoStateTable($fields, $command = "insert") {
        $manipulation = array(
            "state" => array(
                "table_name"=> $this->writer->getModel()->baseTable . "_state",
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
     * returns model.
     *
     * @return DataObject
     */
    protected function model() {
        return $this->writer->getModel();
    }

}