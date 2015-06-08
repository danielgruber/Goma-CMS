<?php defined("IN_GOMA") OR die();

/**
 * for many-many-relation
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
 */
class ManyMany_DataObjectSet extends DataObjectSet {

    /**
     * value of $ownField
     *
     *@name ownValue
     *@access protected
     */
    protected $ownValue;

    /**
     * relationship for this DataSet.
     *
     * @var ModelManyManyRelationShipInfo
     */
    protected $relationShip;

    /**
     * sets the relation-props
     *
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param int $ownValue
     */
    public function setRelationENV($relationShip, $ownValue) {

        if(!is_a($relationShip, "ModelManyManyRelationShipInfo")) {
            throw new InvalidArgumentException("Relationship-Info must be type of ModelManyManyRelationShipInfo");
        }

        $this->relationShip = $relationShip;
        $this->ownValue = $ownValue;

        if($this->relationShip->getExtraFields() && $this->ownValue != 0) {
            $relationTable = $this->relationShip->getTableName();
            // search second join
            foreach((array) $this->join as $table => $data) {
                if(strpos($data, $relationTable)) {
                    unset($this->join[$table]);
                }
            }

            $this->join[$relationTable] = " INNER JOIN " . DB_PREFIX . $relationTable . " AS " .
                $relationTable . " ON " . $relationTable . "." . $this->relationShip->getTargetField() . " = " . $this->dataobject->table() . ".id AND " .
                $relationTable . "." . $this->relationShip->getOwnerField() . " = '" . $this->ownValue . "'";
        }
    }

    /**
     * get the relation-props
     *
     *@name getRelationENV
     *@access public
     *@return ModelManyManyRelationShipInfo
     */
    public function getRelationShip() {
        return $this->relationShip;
    }

    /**
     * returns value of field for this relationship.
     *
     * @return int
     */
    public function getRelationOwnValue() {
        return $this->ownValue;
    }


    /**
     * converts the item to the right format
     *
     * @name getConverted
     * @access protected
     * @param various - data
     * @return ViewAccessableData
     */
    public function getConverted($item) {
        $item = parent::getConverted($item);

        if(isset($this->relationShip)) {
            $item->extendedCasting = array_merge($item->extendedCasting, $this->relationShip->getExtraFields());
        }

        return $item;
    }

    /**
     * sets the variable join
     *
     * @name join
     * @access public
     * @return $this
     */
    public function join($join) {
        if(isset($join)) {
            $this->join = $join;
            if(isset($this->relationShip)) {
                if ($this->relationShip->getExtraFields()) {
                    $this->join[$this->relationShip->getTableName()] = "";
                }
            }

            $this->purgeData();
        }
        return $this;
    }

    /**
     * returns all Records that can be written.
     *
     * @return array<DataObject>
     */
    protected function getWritableRecords() {
        if(count($this->data) > 0) {
            $arr = array();
            foreach($this as $record) {
                if(is_object($record)) {
                    $arr[] = $record;
                }
            }

            return $arr;
        } else if(is_object($this->dataobject) && $this->dataobject->wasChanged()) {
            return array($this->dataobject);
        } else {
            return array();
        }
    }

    /**
     * writes changed records and returns many-many-table-information to insert.
     *
     * @param bool $forceInsert
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param array $updateLastModifiedIDs
     * @return array
     */
    protected function writeChangedRecords($forceInsert, $forceWrite, $snap_priority, &$updateLastModifiedIDs) {
        $writeFields = array();

        $records = $this->getWritableRecords();

        if(empty($records)) {
            return array();
        }

        /** @var DataObject $record */
        foreach($records as $record) {
            if(!isset($writeFields[$record->versionid]) || $record->id == 0) {
                // write
                if($record->hasChanged()) {
                    $record->writeToDB($forceInsert, $forceWrite, $snap_priority);
                } else {
                    $updateLastModifiedIDs[] = $record->versionid;
                }

                $this->createWriteFieldInfo($writeFields, $this->relationShip, $record);
            }
        }

        return $writeFields;
    }

    /**
     * creates write-field information.
     *
     * @param array $writeFields
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param DataObject $record
     */
    protected function createWriteFieldInfo(&$writeFields, $relationShip, $record) {
        $writeFields[$record->versionid] = array(
            "versionid" => $record->versionid
        );

        // add extra fields
        if($extraFields = $relationShip->getExtraFields()) {
            foreach ($extraFields as $field => $char) {
                if (isset($record[$field])) {
                    $writeFields[$record->versionid][$field] = $record->$field;
                }
            }
        }
    }

    /**
     *  writes to db without throwing exceptions. it returns true or false.
     *
     * @param bool $forceInsert to force insert
     * @param bool $forceWrite to force write
     * @param int $snap_priority of the snapshop: autosave 0, save 1, publish 2
     * @return bool
     */
    public function write($forceInsert = false, $forceWrite = false, $snap_priority = 2) {
        try {
            $this->writeToDB($forceInsert, $forceWrite, $snap_priority);
        } catch(Exception $e) {
            log_exception($e);
            return false;
        }
    }

    /**
     * write to DB
     *
     * @param bool $forceInsert to force insert
     * @param bool $forceWrite to force write
     * @param int $snap_priority of the snapshop: autosave 0, save 1, publish 2
     * @return void
     * @throws Exception
     */
    public function writeToDB($forceInsert = false, $forceWrite = false, $snap_priority = 2) {

        $updateLastModified = array();
        $writeData = $this->writeChangedRecords($forceInsert, $forceWrite, $snap_priority, $updateLastModified);

        // check if nothing is writable.
        if(empty($writeData) && empty($updateLastModified)) {
            return;
        }

        $manipulation = array();

        // generate manipulation
        /** @var DataObject $owner */
        $owner = Object::instance($this->relationShip->getOwner());
        $owner->versionid = $this->ownValue;
        $manipulation = ManyManyModelWriter::set_many_many_manipulation(
            $owner,
            $manipulation,
            $this->relationShip,
            $writeData,
            $forceWrite,
            $snap_priority
        );

        // update not written records to indicate changes
        DataObject::update($this->relationShip->getTarget(), array("last_modified" => NOW), array("id" => $updateLastModified));

        $this->dataobject->onBeforeManipulateManyMany($manipulation, $this, $writeData);
        $this->dataobject->callExtending("onBeforeManipulateManyMany", $manipulation, $this, $writeData);
        if(!SQL::manipulate($manipulation)) {
            throw new LogicException("Could not manipulate Database. Manipulation corrupted. <pre>" . print_r($manipulation, true) . "</pre>");
        }


    }

    /**
     * writes the many-many-relation immediatly if writing
     *
     * @name push
     * @return bool
     */
    public function push($record, $write = false) {
        if(!$record instanceof DataObject) {
            throw new InvalidArgumentException("Argument 1 of ManyMany_DataObjectSet must be instance of DataObject.");
        }

        $return = parent::push($record);
        if($write) {
            $this->writeToDB(false, true);
        }
        return $return;
    }

    /**
     * removes the relation on writing
     *
     * @param DataObject $record
     * @param bool $write
     * @return DataObject record
     */
    public function removeRecord($record, $write = false) {
        /** @var DataObject $record */
        $record = parent::removeRecord($record);
        if($write) {
            $this->writeToDB(false, true);
        }
        return $record;
    }
}