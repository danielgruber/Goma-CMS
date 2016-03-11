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
     * on before write.
     */
    public function gatherDataToWrite() {

        /** @var ModelWriter $owner */
        $owner = $this->getOwner();
        $data = $owner->getData();

        $this->many_many_objects = array();
        $this->many_many_relationships = array();

        // here the magic for many-many happens
        if ($many_many = $owner->getModel()->ManyManyRelationships()) {
            foreach($many_many as $key => $value) {
                if (isset($data[$key]) && is_object($data[$key]) && is_a($data[$key], "ManyMany_DataObjectSet")) {
                    $this->many_many_objects[$key] = $data[$key];
                    $this->many_many_relationships[$key] = $value;
                    unset($data[$key]);
                }
                unset($key, $value);
            }
        }

        $owner->setData($data);
    }

    /**
     * called when data was written so we have new versionid, but transaction is still on stage.
     *
     * @param array $manipulation
     */
    public function onBeforeWriteData(&$manipulation) {

        /** @var ModelWriter $owner */
        $owner = $this->getOwner();
        $data = $owner->getData();

        /** @var ManyMany_DataObjectSet $object */
        foreach($this->many_many_objects as $key => $object) {
            $object->setRelationENV($this->many_many_relationships[$key], $owner->getModel()->versionid);
            $object->writeToDB(false, true, $owner->getWriteType());
            unset($data[$key . "ids"]);
        }

        $many_many = $owner->getModel()->ManyManyRelationships();

        // many-many
        if ($many_many) {
            /** @var ModelManyManyRelationshipInfo $relationShip */
            foreach($many_many as $name => $relationShip)
            {

                /** @var ModelManyManyRelationShipInfo $relationShip */
                $relationShip = $owner->getModel()->getManyManyInfo($name);

                // it is supported to have extra-fields in this array
                if(isset($data[$name]) && is_array($data[$name])) {
                    $manipulation = self::set_many_many_manipulation(
                        $owner->getModel(), $manipulation,
                        $relationShip, $data[$name],
                        true, $owner->getWriteType()
                    );
                } else if (isset($data[$name . "ids"]) && is_array($data[$name . "ids"]))
                {
                    $manipulation = self::set_many_many_manipulation(
                        $owner->getModel(), $manipulation,
                        $relationShip, $data[$name . "ids"],
                        true, $owner->getWriteType()
                    );
                }

            }
        }

        // add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
        if ($owner->getOldId() != 0) {
            $manipulation = $this->moveManyManyExtra($manipulation, $owner->getOldId());
        }

        $owner->setData($data);
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
     * @return array
     * @throws PermissionException
     */
    public static function set_many_many_manipulation($ownerModel, $manipulation, $relationShip,
                                                      $data, $forceWrite = false, $snap_priority = 2)
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
            $maxTargetSort
        );

        // if owner and target are the same we have to put everything twice in inverted order
        if(ClassManifest::classesRelated($relationShip->getTarget(), $relationShip->getOwner())) {
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
                $maxTargetSort
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
     * @return array
     */
    public static function createManyManyManipulation($manipulation, $data, $ownerModel, $relationShip, $forceWrite, $snap_priority, $existing, $maxTargetSort) {

        $mani_insert = array(
            "table_name"	=> $relationShip->getTableName(),
            "command"   	=> "insert",
            "ignore"		=> true,
            "fields"		=> array()
        );

        $i = 0;
        foreach($data as $key => $info) {
            if(is_array($info)) {
                $id = $ownerModel->getRelationShipIdFromRecord($relationShip, $key, $info, $forceWrite, $snap_priority);

                $targetSort = isset($existing[$id][$relationShip->getTargetSortField()]) ?
                    $existing[$id][$relationShip->getTargetSortField()] :
                    ++$maxTargetSort;

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
        if(isset($manipulation[$table . "_insert"])) {
            $manipulation[$table . "_insert"]["fields"] = array_merge($manipulation[$table . "_insert"]["fields"], $mani_insert["fields"]);
        } else {
            $manipulation[$table . "_insert"] = $mani_insert;
        }

        return $manipulation;
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

        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        $dataClasses = array_merge(
            array($owner->getModel()->BaseClass()),
            ClassInfo::DataClasses($owner->getModel()->classname)
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

        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        /** @var ModelManyManyRelationShipInfo $relationShip */
        $relationShip = $owner->getModel()->getManyManyInfo($info[1], $info[0])->getInverted();
        $existingData = $owner->getModel()->getManyManyRelationShipData($relationShip, null, $oldId);

        if(!empty($existingData)) {
            $manipulation[$relationShip->getTableName()] = array(
                "command"   => "insert",
                "ignore"	=> true,
                "table_name"=> $relationShip->getTableName(),
                "fields"    => array()
            );

            foreach ($existingData as $data) {
                $newRecord = $data;
                $newRecord[$relationShip->getOwnerField()] = $owner->getModel()->versionid;
                $newRecord[$relationShip->getTargetField()] = $newRecord["versionid"];

                unset($newRecord["versionid"], $newRecord["relationShipId"]);
                $manipulation[$relationShip->getTableName()]["fields"][] = $newRecord;
            }
        }

        return $manipulation;
    }

    /**
     * delete old versions.
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

    /**
     * translates versionids to active versionids.
     *
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param array $ids
     * @return array
     */
    public function translateVersionIDs($relationShip, $ids) {
        $newIds = array();

        // first check if records are up 2 date.
        /** @var DataObject $targetObject */
        $targetObject = gObject::instance($relationShip->getTarget());
        $selectQuery = new SelectQuery($targetObject->BaseTable(),
            array(
                $targetObject->BaseTable() . ".recordid",
                $targetObject->BaseTable() . ".id",
                $targetObject->BaseTable() . ".snap_priority",
                $targetObject->BaseClass() . "_state.id AS stateid"
            ),
            array(
                $targetObject->BaseTable() . ".id" => $ids
            )
        );

        // left join state table so we see what kind of version it is or was not.
        $selectQuery->leftJoin($targetObject->BaseClass() . "_state",
            ' ON (s.publishedid = ' . $targetObject->BaseTable() . '.id ' .
            'AND ' . $targetObject->BaseTable() . '.snap_priority = 2) OR ' .
            '(s.stateid = ' . $targetObject->BaseTable() . '.id ' .
            'AND ' . $targetObject->BaseTable() . '.snap_priority < 2)', "s");

        print_r($selectQuery->build());
        exit;

        return $newIds;
    }
}

gObject::extend("ModelWriter", "ManyManyModelWriter");