<?php defined("IN_GOMA") OR die();
/**
 * timezone-field
 */
class TimeZone extends DBField {
    /**
     * gets the field-type
     *
     *@name getFieldType
     *@access public
     */
    static public function getFieldType($args = array()) {
        return 'enum("'.implode('","', i18n::$timezones).'")';
    }

    /**
     * generatesa a numeric field
     *@name formfield
     *@access public
     *@param string - title
     */
    public function formfield($title = null)
    {
        return new Select($this->name, $title, ArrayLib::key_value(i18n::$timezones), $this->value);
    }
}