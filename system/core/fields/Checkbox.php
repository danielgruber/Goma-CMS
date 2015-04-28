<?php
/**
 * Created by PhpStorm.
 * User: D
 * Date: 17.04.15
 * Time: 11:04
 */
class CheckBoxSQLField extends DBField {
    /**
     * gets the field-type
     *
     * @name getFieldType
     * @access public
     * @return string
     */
    static public function getFieldType($args = array()) {
        return 'enum("0","1")';
    }

    /**
     * generatesa a switch.
     *
     * @name formfield
     * @access public
     * @param string $title
     * @return Checkbox|FormField
     */
    public function formfield($title = null)
    {
        return new Checkbox($this->name, $title, $this->value);
    }
}

class SwitchSQLField extends CheckBoxSQLField {}