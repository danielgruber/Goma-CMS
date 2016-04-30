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
class ManyMany_DataObjectSet extends RemoveStagingDataObjectSet {
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
     * @param mixed $item
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
     * write to DB
     *
     * @param bool $forceInsert to force insert
     * @param bool $forceWrite to force write
     * @param int $snap_priority of the snapshop: autosave 0, save 1, publish 2
     * @param null|IModelRepository $repository
     * @throws MySQLException
     */
    public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2, $repository = null) {

        $updateLastModified = array();
        $writeData = $this->writeChangedRecords($forceInsert, $forceWrite, $snap_priority, $updateLastModified);

        // check if nothing is writable.
        if(empty($writeData) && empty($updateLastModified)) {
            return;
        }

        $manipulation = array();

        // generate manipulation
        /** @var DataObject $owner */
        $owner = gObject::instance($this->relationShip->getOwner());
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
        $baseClassTarget = ClassInfo::$class_info[$this->relationShip->getTarget()]["baseclass"];
        DataObject::update($baseClassTarget, array("last_modified" => NOW), array("id" => $updateLastModified));

        $this->dataobject->onBeforeManipulateManyMany($manipulation, $this, $writeData);
        $this->dataobject->callExtending("onBeforeManipulateManyMany", $manipulation, $this, $writeData);
        if(!SQL::manipulate($manipulation)) {
            throw new LogicException("Could not manipulate Database. Manipulation corrupted. <pre>" . print_r($manipulation, true) . "</pre>");
        }
    }

    /**
     * joins stuff.
     */
    public function getJoinForQuery()
    {
        $join = parent::getJoinForQuery();

        if($this->relationShip->getExtraFields() && $this->ownValue != 0) {
            $relationTable = $this->relationShip->getTableName();
            // search second join
            foreach((array) $join as $table => $data) {
                if(strpos($data, $relationTable)) {
                    unset($join[$table]);
                }
            }

            $join[$relationTable] = " INNER JOIN " . DB_PREFIX . $relationTable . " AS " .
                $relationTable . " ON " . $relationTable . "." . $this->relationShip->getTargetField() . " = " . $this->dbDataSource()->table() . ".id AND " .
                $relationTable . "." . $this->relationShip->getOwnerField() . " = '" . $this->ownValue . "'";
        }

        return $join;
    }

    /**
     * @param null|IModelRepository $repository
     * @param bool $forceWrite
     * @param int $snap_priority
     * @return mixed
     */
    public function commitRemoveStaging($repository, $forceWrite = false, $snap_priority = 2)
    {
        foreach($this->removeStaging as $record) {
            //if($this->relationShip->)
        }
    }

    /**
     * @param $filter
     * @return array
     */
    protected function argumentFilterForHidingRemovedStageForQuery($filter)
    {
        if($ids = $this->removeStaging->fieldToArray("id")) {
            if (!is_array($filter)) {
                $filter = (array)$filter;
            }

            $filter[] = $this->dbDataSource()->table() . ".recordid NOT IN ('" . implode("','", $this->removeStaging->fieldToArray("id")) . "') ";
        }

        return $filter;
    }
}
