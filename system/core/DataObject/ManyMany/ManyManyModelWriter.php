<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Many-Many-Relationships of Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 *
 * @method ModelWriter getOwner()
 */
class ManyManyModelWriter extends Extension {

    /**
     * many-many-objects.
     */
    protected $many_many_objects;

    /**
     * many-many-relationships.
     */
    protected $many_many_relationships;

    /**
     * called when data was written so we have new versionid, but transaction is still on stage.
     *
     * @param array $manipulation
     */
    public function onBeforeWriteData(&$manipulation) {
        $data = $this->getOwner()->getData();

        $many_many = $this->getOwner()->getModel()->ManyManyRelationships();

        // many-many
        if ($many_many) {
            /** @var ModelManyManyRelationshipInfo $relationShip */
            foreach($many_many as $name => $relationShip) {

                /** @var ModelManyManyRelationShipInfo $relationShip */
                $relationShip = $this->getOwner()->getModel()->getManyManyInfo($name);

                /** @var ManyMany_DataObjectSet $set */
                if(isset($data[$name]) && is_a($data[$name], "ManyMany_DataObjectSet")) {
                    $set = $data[$name];
                    $set->setRelationENV($relationShip, $this->getOwner()->getModel());
                    $set->commitStaging(false, true, $this->getOwner()->getWriteType(), $this->getOwner()->getRepository(), $this->getOwner()->getOldId());
                } else {
                    $set = $this->getOwner()->getModel()->getManyMany($name);
                    $set->setRelationENV($relationShip, $this->getOwner()->getModel());
                    $set->commitStaging(false, true, $this->getOwner()->getWriteType(), $this->getOwner()->getRepository(), $this->getOwner()->getOldId());
                }
            }
        }

        // add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
        if ($this->getOwner()->getOldId() != 0) {
            $manipulation = $this->moveManyManyExtra($manipulation, $this->getOwner()->getOldId());
        }

        $this->getOwner()->setData($data);
    }

    /**
     * is used for writing many-many-relations in DataObject::write
     *
     * @param DataObject $ownerModel
     * @param array $manipulation
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param array $data ids to write
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param null|IModelRepository $repository
     * @return array
     */
    public static function set_many_many_manipulation(
        $ownerModel, $manipulation, $relationShip,
        $data, $forceWrite = false, $snap_priority = 2, $repository = null
    )
    {
        $existing = $ownerModel->getManyManyRelationShipData($relationShip);

        // calculate maximum target sort.
        $maxTargetSort = $ownerModel->maxTargetSort($relationShip, $existing);

        $manipulation = self::createManyManyManipulation(
            $manipulation,
            $data,
            $ownerModel,
            $relationShip,
            $forceWrite,
            $snap_priority,
            $existing,
            $maxTargetSort,
            $repository
        );

        // if owner and target are the same we have to put everything twice in inverted order
        if(ClassManifest::classesRelated($relationShip->getTargetClass(), $relationShip->getOwner())) {
            $invertedRelationship = $relationShip->getInverted();
            $invertedExisting = $ownerModel->getManyManyRelationShipData($invertedRelationship);

            $manipulation = self::createManyManyManipulation(
                $manipulation,
                $data,
                $ownerModel,
                $invertedRelationship,
                $forceWrite,
                $snap_priority,
                $invertedExisting,
                $maxTargetSort,
                $repository
            );
        }

        return $manipulation;
    }

    /**
     * creates manipulation out of gotten data.
     *
     * @param array $manipulation
     * @param array $data
     * @param DataObject $ownerModel
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param array $existing
     * @param int $maxTargetSort
     * @param IModelRepository|null $repository
     * @return array
     */
    public static function createManyManyManipulation($manipulation, $data, $ownerModel, $relationShip, $forceWrite, $snap_priority, $existing, $maxTargetSort, $repository = null) {
        $mani_delete = array(
            "table_name"	=> $relationShip->getTableName(),
            "command"   	=> "delete",
            "where"		    => array()
        );
        $mani_insert = array(
            "table_name"	=> $relationShip->getTableName(),
            "command"   	=> "insert",
            "ignore"		=> true,
            "fields"		=> array()
        );

        $i = 0;
        foreach($data as $key => $info) {
            if(is_array($info)) {
                $id = self::getRelationShipRecordFromRecord($relationShip, $key, $info, $forceWrite, $snap_priority, true, $repository);

                $targetSort = isset($existing[$id][$relationShip->getTargetSortField()]) ?
                    $existing[$id][$relationShip->getTargetSortField()] :
                    ++$maxTargetSort;

                if(count($mani_delete["where"]) > 0) {
                    $mani_delete["where"][] = "OR";
                }

                $mani_delete["where"][] = array(
                    $relationShip->getOwnerField() 		=> $ownerModel->versionid,
                    $relationShip->getTargetField() 	=> $id
                );

                $mani_insert["fields"][$id] = array(
                    $relationShip->getOwnerField() 		=> $ownerModel->versionid,
                    $relationShip->getTargetField() 	=> $id,
                    $relationShip->getOwnerSortField()  => $i,
                    $relationShip->getTargetSortField() => $targetSort
                );

                foreach($relationShip->getExtraFields() as $field => $type) {
                    if(isset($info[$field])) {
                        $mani_insert["fields"][$id][$field] = $info[$field];
                    }
                }
            } else {
                if(!isset($existing[$info])) {
                    $mani_insert["fields"][$info] = array(
                        $relationShip->getOwnerField() 		=> $ownerModel->versionid,
                        $relationShip->getTargetField() 	=> $info,
                        $relationShip->getOwnerSortField()  => $i,
                        $relationShip->getTargetSortField() => ++$maxTargetSort
                    );
                }
            }
            $i++;
        }

        $mani_insert["fields"] = array_values($mani_insert["fields"]);

        $table = $relationShip->getTableName();

        if(count($mani_delete["where"]) > 0) {
            $manipulation[$table . "_delete"] = $mani_delete;
        }

        if(isset($manipulation[$table . "_insert"])) {
            $manipulation[$table . "_insert"]["fields"] = array_merge($manipulation[$table . "_insert"]["fields"], $mani_insert["fields"]);
        } else {
            $manipulation[$table . "_insert"] = $mani_insert;
        }

        return $manipulation;
    }

    /**
     * returns versionid from given relationship-info-array. it creates new record if no one can be found.
     * it also updates the record.
     *
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param int $key
     * @param array $record
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param bool $history
     * @param IModelRepository|null $repository
     * @return int
     */
    protected static function getRelationShipRecordFromRecord($relationShip, $key, $record, $forceWrite = false, $snap_priority = 2, $history = true, $repository = null) {
        $repository = isset($repository) ? $repository : Core::repository();

        // validate versionid
        if(isset($record["versionid"])) {
            $id = $record["versionid"];
        } else if(DataObject::count($relationShip->getTargetClass(), array("versionid" => $key)) > 0) {
            $id = $key;
        }

        // did not find versionid, so generate one
        if(!isset($id) || $id == 0) {

            $target = $relationShip->getTargetClass();
            /** @var DataObject $dataObject */
            $dataObject = new $target(array_merge($record, array("id" => 0, "versionid" => 0)));
            $dataObject->writeToDBInRepo($repository, true, $forceWrite, $snap_priority, $forceWrite, $history);

            return $dataObject->versionid;
        } else {

            // we want to update many-many-extra
            $databaseRecord = null;
            $db = gObject::instance($relationShip->getTargetClass())->DataBaseFields(true);

            // just find out if we may be update the record given.
            foreach($record as $field => $v) {
                if(isset($db[strtolower($field)]) && !in_array(strtolower($field), array("versionid", "id", "recordid"))) {
                    if(!isset($databaseRecord)) {
                        $databaseRecord = DataObject::get_one($relationShip->getTargetClass(), array("versionid" => $id));
                    }

                    $databaseRecord[$field] = $v;
                }
            }

            // we found many-many-extra which can be updated so write please
            if(isset($databaseRecord)) {
                $databaseRecord->writeToDBInRepo($repository, false, $forceWrite, $snap_priority, $forceWrite, $history);
                return $databaseRecord->versionid;
            }

            return $id;
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
        $dataClasses = array_merge(
            array($this->getOwner()->getModel()->BaseClass()),
            ClassInfo::DataClasses($this->getOwner()->getModel()->classname)
        );

        foreach($dataClasses as $dataClass) {
            if (isset(ClassInfo::$class_info[$dataClass]["many_many_relations_extra"])) {
                foreach(ClassInfo::$class_info[$dataClass]["many_many_relations_extra"] as $info) {
                    $manipulation = $this->moveManyManyExtraForRelationShip($manipulation, $oldId, $info);
                }
            }
        }

        return $manipulation;
    }

    /**
     * moves many-many-extra for a specific class.
     *
     * @param array $manipulation
     * @param int $oldId
     * @param array $info
     * @return array
     */
    protected function moveManyManyExtraForRelationShip($manipulation, $oldId, $info) {
        /** @var ModelManyManyRelationShipInfo $relationShip */
        $relationShip = $this->getOwner()->getModel()->getManyManyInfo($info[1], $info[0])->getInverted();
        $existingData = $this->getOwner()->getModel()->getManyManyRelationShipData($relationShip, null, $oldId);

        if(!empty($existingData)) {
            $manipulation[$relationShip->getTableName()] = array(
                "command"   => "insert",
                "ignore"	=> true,
                "table_name"=> $relationShip->getTableName(),
                "fields"    => array()
            );

            foreach ($existingData as $data) {
                $newRecord = $data;
                $newRecord[$relationShip->getOwnerField()] = $this->getOwner()->getModel()->versionid;
                $newRecord[$relationShip->getTargetField()] = $newRecord["versionid"];

                unset($newRecord["versionid"], $newRecord["relationShipId"]);
                $manipulation[$relationShip->getTableName()]["fields"][] = $newRecord;
            }
        }

        return $manipulation;
    }

    /**
     * delete old versions.
     * @param array $manipulation
     * @param int $oldId
     */
    public function deleteOldVersions(&$manipulation, $oldId) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        // clean-up-many-many
        /** @var ModelManyManyRelationShipInfo $relationship */
        foreach($owner->getModel()->ManyManyRelationships() as $relationship) {
            $manipulation[$relationship->getTableName()] = array(
                "table" 	=> $relationship->getTableName(),
                "command"	=> "delete",
                "where"		=> array(
                    $relationship->getOwnerField() => $oldId
                )
            );
        }
    }
}

gObject::extend("ModelWriter", "ManyManyModelWriter");
