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
class HasMany_DataObjectSet extends DataObjectSet {

    /**
     * field for the relation according to this set, for example: pageid or groupid
     *
     *@name field
     *@access protected
     */
    protected $field;

    /**
     * name of the relation
     *
     *@name relationName
     *@access protected
     */
    protected $relationName;

    /**
     * sets the relation-props
     *
     *@name setRelationENV
     *@access public
     *@param string - name
     *@param string - field
     */
    public function setRelationENV($name = null, $field = null, $id = null) {
        if(isset($name))
            $this->relationName = $name;
        if(isset($field))
            $this->field = $field;

        if(isset($id))
            foreach($this as $record)
                $record[$field] = $id;
    }

    /**
     * get the relation-props
     *
     *@name getRelationENV
     *@access public
     *@return array
     */
    public function getRelationENV() {
        return array("name" => $this->name, "field" => $this->field);
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

        if(isset($this[$this->field])) {
            $this->dataobject[$this->field] = $this[$this->field];
        } else if(isset($this->filter[$this->field]) && is_string($this->filter[$this->field]) || is_int($this->filter[$this->field])) {
            $this->dataobject[$this->field] = $this->filter[$this->field];
        }

        $form = parent::generateForm($name, $edit, $disabled, $request, $controller, $submission);

        if(isset($this[$this->field])) {
            $form->add(new HiddenField($this->field, $this[$this->field]));
        } else if(isset($this->filter[$this->field]) && is_string($this->filter[$this->field]) || is_int($this->filter[$this->field])) {
            $form->add(new HiddenField($this->field, $this->filter[$this->field]));
        }
        return $form;
    }

    /**
     * removes the relation on writing
     *
     * @param DataObject $record
     * @param bool $write
     * @return DataObject record
     * @internal param $removeRecord
     */
    public function removeRecord($record, $write = false) {
        /** @var DataObject $record */
        $record = parent::removeRecord($record);

        if(isset($this->filter["id"]) && is_array($this->filter["id"]) && $record->id != 0) {
            $key = array_search($record->id, $this->filter["id"]);
            unset($this->filter["id"][$key]);
        }

        if($write) {
            $record[$this->field] = 0;
            $record->writeToDB(false, true);
        }
        return $record;
    }
}
