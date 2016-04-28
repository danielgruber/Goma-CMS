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
     * @var string
     */
    protected $field;

    /**
     * name of the relation
     *
     * @var string
     */
    protected $relationName;

    /**
     * @var int
     */
    protected $value;

    /**
     * sets the relation-props
     *
     * @param string $name
     * @param string $field
     * @param int $id
     */
    public function setRelationENV($name = null, $field = null, $id = null) {
        if(isset($name))
            $this->relationName = $name;
        if(isset($field))
            $this->field = $field;

        if(isset($id)) {
            $this->value = $id;

            foreach ($this as $record) {
                $record[$field] = $id;
            }
        }
    }

    /**
     * get the relation-props
     */
    public function getRelationENV() {
        return array("name" => $this->relationName, "field" => $this->field, "value" => $this->value);
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
            $form->add(new HiddenField($this->field, $id));
        }

        return $form;
    }

    /**
     * @param DataObject $record
     * @param bool $write
     * @return DataObjectSet
     */
    public function push(DataObject $record, $write = false)
    {
        if($id = $this->getRelationID()) {
            $record->{$this->field} = $this->getRelationID();
        }

        return parent::push($record, $write);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createNewModel($data = array())
    {
        $record = parent::createNewModel($data);

        $record->{$this->field} = $this->getRelationID();

        return $record;
    }

    /**
     * gets id.
     *
     * @return null|int
     */
    protected function getRelationID() {
        if(isset($this->value)) {
            return $this->value;
        } else if(isset($this->first()->{$this->field})) {
            return $this->first()->{$this->field};
        } else if(isset($this->filter[$this->field]) && (is_string($this->filter[$this->field]) || is_int($this->filter[$this->field]))) {
            return $this->filter[$this->field];
        }
    }
}
