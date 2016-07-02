<?php defined("IN_GOMA") OR die();

/**
 * DataSet for has-many-relationships.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2.1
 */
class HasMany_DataObjectSet extends RemoveStagingDataObjectSet {

    /**
     * field for the relationship-info.
     *
     * @var ModelHasManyRelationShipInfo
     */
    protected $relationShipInfo;

    /**
     * @var int
     */
    protected $relationShipValue;

    /**
     * @var string
     */
    protected $relationShipField;

    /**
     * sets the relation-props
     *
     * @param ModelHasManyRelationShipInfo $relationShipInfo
     * @param int $value
     */
    public function setRelationENV($relationShipInfo, $value) {
        if(!isset($relationShipInfo)) {
            throw new InvalidArgumentException("First argument of setRelationENV needs to be type of ModelHasManyRelationShipInfo. Null given.");
        }

        $this->relationShipInfo = $relationShipInfo;
        $this->relationShipValue = $value;
        $this->relationShipField = $relationShipInfo->getInverse() . "id";

        if($this->getFetchMode() != self::FETCH_MODE_CREATE_NEW && $this->first() && $this->first()->{$this->relationShipField} != $this->relationShipValue) {
            throw new InvalidArgumentException("You cannot move HasManyRelationship to another object. Please copy data by yourself.");
        }

        foreach($this->staging as $record) {
            $record->{$this->relationShipField} = $this->relationShipValue;
        }
    }

    /**
     * get the relation-props
     */
    public function getRelationENV() {
        return array("info" => $this->relationShipInfo, "value" => $this->relationShipValue);
    }

    /**
     * generates a form
     *
     * @param string $name
     * @param bool $edit if edit form
     * @param bool $disabled
     * @param null $request
     * @param null $controller
     * @param null $submission
     * @return Form
     */
    public function generateForm($name = null, $edit = false, $disabled = false, $request = null, $controller = null, $submission = null) {
        $form = parent::generateForm($name, $edit, $disabled, $request, $controller, $submission);

        if(($id = $this->getRelationID()) !== null) {
            $form->add(new HiddenField($this->relationShipField, $id));
        }

        return $form;
    }

    /**
     * @param bool $forceInsert
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param null|IModelRepository $repository
     */
    public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2, $repository = null)
    {
        if($this->fetchMode == DataObjectSet::FETCH_MODE_CREATE_NEW) {
            $records = $this->dbDataSource()->getRecords($this->version, array(
                $this->relationShipField => $this->relationShipValue,
                "recordid NOT in ('".implode("','", array_merge($this->staging->fieldToArray("id"), $this->removeStaging->fieldToArray("id")))."')"
            ));

            foreach($records as $record) {
                $this->removeStaging->add($record);
            }
        }

        if(($id = $this->getRelationID()) !== null) {
            foreach($this->staging as $record) {
                $record->{$this->relationShipField} = $this->getRelationID();
            }
        }

        parent::commitStaging($forceInsert, $forceWrite, $snap_priority, $repository);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createNewModel($data = array())
    {
        $record = parent::createNewModel($data);

        if($this->relationShipField) {
            $record->{$this->relationShipField} = $this->getRelationID();
        }

        return $record;
    }

    /**
     * @param DataObject $record
     * @param bool $write
     * @return $this
     */
    public function push($record, $write = false)
    {
        if(($id = $this->getRelationID()) !== null) {
            $record->{$this->relationShipField} = $id;
        }

        return parent::push($record, $write);
    }

    /**
     * gets id.
     *
     * @return null|int
     */
    protected function getRelationID() {
        if(isset($this->relationShipValue)) {
            return $this->relationShipValue;
        } else if(isset($this->filter[$this->relationShipField]) && (is_string($this->filter[$this->relationShipField]) || is_int($this->filter[$this->relationShipField]))) {
            return $this->filter[$this->relationShipField];
        }
    }

    /**
     * @param IModelRepository $repository
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param IModelRepository|null $repository
     * @return mixed
     * @throws MySQLException
     * @throws PermissionException
     */
    public function commitRemoveStaging($repository, $forceWrite = false, $snap_priority = 2, $repository = null) {
        /** @var DataObject $item */
        foreach ($this->removeStaging as $item) {
            if($this->relationShipInfo()->shouldRemoveData()) {
                $item->remove($forceWrite);
            } else {
                $item->{$this->relationShipField} = 0;
                $item->writeToDBInRepo($repository, false, $forceWrite, $snap_priority);
            }
        }
    }

    /**
     * @return array
     */
    public function getFilterForQuery()
    {
        $filter = parent::getFilterForQuery();

        if(($id = $this->getRelationID()) !== null) {
            $filter[$this->relationShipField] = $id;
        } else {
            throw new InvalidArgumentException("HasMany_DataObjectSet needs relationship-info for query.");
        }

        return $filter;
    }

    /**
     * @param array|string $filter
     * @return array
     */
    protected function argumentFilterForHidingRemovedStageForQuery($filter) {
        if($ids = $this->removeStaging->fieldToArray("id")) {
            if (!is_array($filter)) {
                $filter = (array)$filter;
            }

            $filter[] = $this->dbDataSource()->table() . ".recordid NOT IN ('" . implode("','", $this->removeStaging->fieldToArray("id")) . "') ";
        }

        return $filter;
    }

    /**
     * @return ModelHasManyRelationShipInfo
     */
    protected function relationShipInfo()
    {
        if(!isset($this->relationShipInfo)) {
            throw new InvalidArgumentException("You have to set RelationshipInfo if you want to make changes on this relationship.");
        }

        return $this->relationShipInfo;
    }
}
