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
     * @return array
     * @throws Exception
     * @throws PermissionException
     * @throws SQLException
     */
    protected function writeChangedRecords($forceInsert, $forceWrite, $snap_priority) {
        $writeFields = array();

        $records = $this->getWritableRecords();

        if(empty($records)) {
            return array();
        }

        $updateLastModifiedIDs = array();
        $sort = 0;
        /** @var DataObject $record */
        foreach($records as $record) {
            if(!isset($writeFields[$record->versionid]) || $record->id == 0) {
                // write
                if($record->hasChanged()) {
                    $record->writeToDB($forceInsert, $forceWrite, $snap_priority);
                } else {
                    $updateLastModifiedIDs[] = $record->versionid;
                }

                $ownerSortField = $this->relationShip->getOwnerSortField();
                $writeFields[$record->versionid] = array(
                    $this->relationShip->getOwnerField() => $this->ownValue,
                    $this->relationShip->getTargetSortField() => $sort,
                    $this->relationShip->getTargetField() => $record->versionid,
                    $ownerSortField => isset($record->$ownerSortField) ? $record->$ownerSortField : 0
                );

                // add extra fields
                if($extraFields = $this->relationShip->getExtraFields()) {
                    foreach ($extraFields as $field => $char) {
                        if (isset($record[$field])) {
                            $writeFields[$record->versionid][$field] = $record->$field;
                        }
                    }
                }

                $sort++;
            }
        }

        return $writeFields;
    }

    /**
     * returns existing ManyMany-Data.
     */
    protected function getExistingManyManyData() {
        // check for existing entries
        $query = new SelectQuery($this->relationShip->getTableName(),
            array("*"),
            array($this->relationShip->getOwnerField() => $this->ownValue));

        if($query->execute()) {
            $existingFields = array();
            while($row = $query->fetch_assoc()) {

                $targetValue = $row[$this->relationShip->getTargetField()];
                $existingFields[$targetValue] = array(
                    $this->relationShip->getOwnerField() => $this->ownValue,
                    $this->relationShip->getTargetField() => $targetValue,
                    $this->relationShip->getOwnerSortField() => $row[$this->relationShip->getOwnerSortField()],
                    $this->relationShip->getTargetSortField() => $row[$this->relationShip->getTargetSortField()],
                    "id"    => $row["id"]
                );

                // add extra fields, which exist
                foreach($this->relationShip->getExtraFields() as $field => $char) {
                    $existingFields[$targetValue][$field] = $row[$field];
                }
            }

            return $existingFields;
        } else {
            throw new SQLException();
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

        $writeData = $this->writeChangedRecords($forceInsert, $forceWrite, $snap_priority);

        // check if nothing is writable.
        if(empty($writeData)) {
            return;
        }

        $existingFields = $this->getExistingManyManyData();

        $manipulation = array(
            "insert" => array(
                "command"	=> "insert",
                "table_name"=> $this->relationShip->getTableName(),
                "fields"	=> array(

                ),
                "ignore"	=> true
            )
        );

        foreach($writeData as $id => $data) {
            if(!isset($existingFields[$id])) {
                $manipulation["insert"]["fields"][] = $data;
            } else {
                if($writeData[$id] != $existingFields[$id]) {

                    if($writeData[$id][$this->relationShip->getOwnerSortField()] == 0) {
                        if(isset($existingFields[$id][$this->relationShip->getOwnerSortField()])) {
                            $writeData[$id][$this->relationShip->getOwnerSortField()] = $existingFields[$id][$this->relationShip->getOwnerSortField()];
                        }
                    }

                    $manipulation[] = array(
                        "command"	=> "update",
                        "table_name"=> $this->relationShip->getTableName(),
                        "id"		=> $existingFields[$id]["id"],
                        "fields"	=> $writeData[$id]
                    );
                }
            }
            unset($existingFields[$id]);
        }

        if(count($existingFields) > 0) {
            $ids = array_map(function($record){
                return $record["id"];
            }, $existingFields);
            
            $manipulation["delete"] = array(
                "command"	=> "delete",
                "table_name"=> $this->relationShip->getTableName(),
                "where"	=> array(
                    "id"    => $ids
                )
            );
        }

        $this->dataobject->onBeforeManipulateManyMany($manipulation, $this, $writeData);
        $this->dataobject->callExtending("onBeforeManipulateManyMany", $manipulation, $this, $writeData);
        if(!SQL::manipulate($manipulation)) {
            throw new LogicException("Could not manipulate Database. Manipulation corrupted. " . print_r($manipulation, true));
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
            $record->write(false, true);
            $manipulation = array(
                array(
                    "command"	=> "insert",
                    "table_name"=> $this->relationShip->getTableName(),
                    "fields"	=> array(
                        array(
                            $this->relationShip->getOwnerField()        => $this->ownValue,
                            $this->relationShip->getTargetField()	    => $record->versionid,
                            $this->relationShip->getOwnerSortField()    => $this->Count(),
                            $this->relationShip->getTargetSortField()   => 10000
                        )
                    )
                )
            );
            SQL::manipulate($manipulation);
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
        $record = parent::removeRecord($record);
        if($write) {
            $manipulation = array(
                array(
                    "command"	=> "delete",
                    "table_name"=> $this->relationShip->getTableName(),
                    "where"		=> array(
                        $this->relationShip->getTargetField() 	=> $record->versionid,
                        $this->relationShip->getOwnerField()    => $this->ownValue
                    )
                )
            );
            SQL::manipulate($manipulation);
        }
        return $record;
    }
}