<?php defined("IN_GOMA") OR die();

/**
 * DataSet for has-many-relationships.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
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
     *@name form
     *@access public
     *@param string - name
     *@param bool - edit-form
     *@param bool - disabled
     */
    public function generateForm($name = null, $edit = false, $disabled = false) {

        if(isset($this[$this->field])) {
            $this->dataobject[$this->field] = $this[$this->field];
        } else if(isset($this->filter[$this->field]) && is_string($this->filter[$this->field]) || is_int($this->filter[$this->field])) {
            $this->dataobject[$this->field] = $this->filter[$this->field];
        }

        $form = parent::generateForm($name, $edit, $disabled);

        if(isset($this[$this->field])) {
            $form->add(new HiddenField($this->field, $this[$this->field]));
        } else if(isset($this->filter[$this->field]) && is_string($this->filter[$this->field]) || is_int($this->filter[$this->field])) {
            $form->add(new HiddenField($this->field, $this->filter[$this->field]));
        }
        return $form;
    }

    /**
     * sets the has-one-relation when adding to has-many-set
     *
     *@name push
     */
    public function push(DataObject $record, $write = false) {
        if($this->classname == "hasmany_dataobjectset") {
            if(isset($this[$this->field])) {
                $record[$this->field] = $this[$this->field];
            } else if(isset($this->filter[$this->field]) && (is_string($this->filter[$this->field]) || is_int($this->filter[$this->field]))) {
                $record[$this->field] = $this->filter[$this->field];
            }
        }

        $return = parent::push($record);
        if($write) {
            $record->write(false, true);
        }
        return $return;
    }

    /**
     * removes the relation on writing
     *
     *@name removeRecord
     */
    public function removeRecord($record, $write = false) {
        $record = parent::removeRecord($record);
        if($write) {
            $record[$this->field] = 0;
            if(!$record->write()) {
                throwError(6, "Permission-Error", "Could not remove Relation from Record ".$record->classname.": ".$record->ID."");
            }
        }
        return $record;
    }
}
