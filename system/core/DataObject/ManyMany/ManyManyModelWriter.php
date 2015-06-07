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
    public function onBeforeWrite() {

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
                if(isset($data[$name]) && is_array($data[$name])) {
                    $manipulation = $this->set_many_many_manipulation($manipulation, $name, $data[$name], true, $owner->getWriteType());
                } else if (isset($data[$name . "ids"]) && is_array($data[$name . "ids"]))
                {
                    $manipulation = $this->set_many_many_manipulation($manipulation, $name, $data[$name . "ids"], true, $owner->getWriteType());
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
     * @param array $manipulation
     * @param string $relationShipName
     * @param array $data
     * @param bool $forceWrite
     * @param int $snap_priority
     * @return array
     * @throws PermissionException
     */
    protected function set_many_many_manipulation($manipulation, $relationShipName, $data, $forceWrite = false, $snap_priority = 2)
    {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        /** @var ModelManyManyRelationShipInfo $relationShip */
        $relationShip = $owner->getModel()->getManyManyInfo($relationShipName);

        $existing = $owner->getModel()->getManyManyRelationShipData($relationShip);

        // calculate maximum target sort.
        $maxTargetSort = $owner->getModel()->maxTargetSort($relationShip, $existing);

        $mani_insert = array(
            "table_name"	=> $relationShip->getTableName(),
            "command"   	=> "insert",
            "ignore"		=> true,
            "fields"		=> array(

            )
        );

        $i = 0;
        foreach($data as $key => $info) {
            if(is_array($info)) {
                $id = $owner->getModel()->getRelationShipIdFromRecord($relationShip, $key, $info, $forceWrite, $snap_priority);

                $targetSort = isset($existing[$id][$relationShip->getTargetSortField()]) ?
                    $existing[$id][$relationShip->getTargetSortField()] :
                    ++$maxTargetSort;

                $mani_insert["fields"][$id] = array(
                    $relationShip->getOwnerField() 		=> $owner->getModel()->versionid,
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
                        $relationShip->getOwnerField() 		=> $owner->getModel()->versionid,
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

        $currentClass = $owner->getModel()->classname;
        while($currentClass != null && !ClassInfo::isAbstract($currentClass)) {
            if (isset(ClassInfo::$class_info[$currentClass]["many_many_relations_extra"])) {
                foreach(ClassInfo::$class_info[$currentClass]["many_many_relations_extra"] as $info) {

                    /** @var ModelManyManyRelationShipInfo $relationShip */
                    $relationShip = $owner->getModel()->getManyManyInfo($info[1], $info[0])->getInverted();
                    $existingData = $owner->getModel()->getManyManyRelationShipData($relationShip, null, $oldId);

                    if(!empty($existingData)) {

                        $manipulation[$relationShip->getTableName()] = array(
                            "command"   => "insert",
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
                }
            }
            $currentClass = ClassInfo::getParentClass($currentClass);
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
        foreach($owner->getModel()->ManyManyTables() as $data) {
            $manipulation[$data["table"]] = array(
                "table" 	=> $data["table"],
                "command"	=> "delete",
                "where"		=> array(
                    $data["field"] => $oldId
                )
            );
        }

    }
}

Object::extend("ModelWriter", "ManyManyModelWriter");