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
        $this->relationShipInfo = $relationShipInfo;
        $this->relationShipValue = $value;
        $this->relationShipField = $relationShipInfo->getRelationShipName() . "id";

        if($this->getFetchMode() != self::FETCH_MODE_CREATE_NEW && $this->first()->{$this->relationShipField} != $this->relationShipValue) {
            throw new InvalidArgumentException("You cannot move HasManyRelationship to another object. Please copy data by yourself.");
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

        if($id = $this->getRelationID()) {
            $form->add(new HiddenField($this->relationShipField, $id));
        }

        return $form;
    }

    /**
     * @param bool $forceInsert
     * @param bool $forceWrite
     * @param int $snap_priority
     * @throws DataObjectSetCommitException
     */
    public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2)
    {
        if($id = $this->getRelationID()) {
            foreach($this->staging as $record) {
                $record->{$this->relationShipField} = $this->getRelationID();
            }
        }

        parent::commitStaging($forceInsert, $forceWrite, $snap_priority);
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
     * gets id.
     *
     * @return null|int
     */
    protected function getRelationID() {
        if(isset($this->relationShipValue)) {
            return $this->relationShipValue;
        } else if(isset($this->first()->{$this->relationShipField})) {
            return $this->first()->{$this->relationShipField};
        } else if(isset($this->filter[$this->relationShipField]) && (is_string($this->filter[$this->relationShipField]) || is_int($this->filter[$this->relationShipField]))) {
            return $this->filter[$this->relationShipField];
        }
    }

    /**
     * @param bool $forceWrite
     * @param int $snap_priority
     * @return mixed
     * @throws MySQLException
     */
    public function commitRemoveStaging($forceWrite = false, $snap_priority = 2) {
        /** @var DataObject $item */
        foreach ($this->removeStaging as $item) {
            if($this->relationShipInfo()->shouldRemoveData()) {
                $item->remove($forceWrite);
            } else {
                $item->{$this->relationShipField} = 0;
                $item->writeToDB(false, $forceWrite, $snap_priority);
            }
        }
    }

    /**
     * @param $filter
     * @return array
     */
    protected function argumentFilterForHidingRemovedStageForQuery($filter)
    {
        if(!is_array($filter)) {
            $filter = (array) $filter;
        }

        $filter[] = " id NOT IN ('".implode("','", $this->removeStaging->fieldToArray("id"))."') ";

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
