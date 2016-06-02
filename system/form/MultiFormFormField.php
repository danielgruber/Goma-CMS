<?php
defined("IN_GOMA") OR die();

/**
 * Combines multiple forms to a combined field.
 * It requires a Set of DataObjects which have getForm or getEditForm.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class MultiFormFormField extends ClusterFormField {
    /**
     * @var bool
     */
    protected $useEditFormMethod = false;

    /**
     * @var bool|string
     */
    protected $allowAddOfKind = false;

    /**
     * validates value.
     */
    public function getValue() {
        parent::getValue();

        if(!is_a($this->value, "DataSet") || !is_a($this->value, "DataObjectSet")) {
            throw new InvalidArgumentException("Value for MultiFormFormField must be DataSet or DataObjectSet.");
        }

        /** @var DataObject $record */
        foreach($this->value as $record) {
            $field = new ClusterFormField(
                $this->name . "_" . $record->versionid,
                ""
            );

            if($this->useEditFormMethod) {
                $record->getEditForm($field);
            } else {
                $record->getForm($field);
            }

            $this->add($field);
        }
    }

    /**
     * @return boolean
     */
    public function isUseEditFormMethod()
    {
        return $this->useEditFormMethod;
    }

    /**
     * @param boolean $useEditFormMethod
     * @return $this
     */
    public function setUseEditFormMethod($useEditFormMethod)
    {
        $this->useEditFormMethod = $useEditFormMethod;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getAllowAddOfKind()
    {
        return $this->allowAddOfKind;
    }

    /**
     * @param bool|string $allowAddOfKind
     * @return $this
     */
    public function setAllowAddOfKind($allowAddOfKind)
    {
        $this->allowAddOfKind = $allowAddOfKind;
        return $this;
    }
}
