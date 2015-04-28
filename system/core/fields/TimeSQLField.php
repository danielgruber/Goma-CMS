<?php defined("IN_GOMA") OR die();
/**
 * Base-Class for saving times in db.
 *
 * @package		Goma\Core\Model
 * @version		1.0
 */
class TimeSQLField extends DBField {

    /**
     * gets the field-type
     *
     *@name getFieldType
     *@access public
     */
    static public function getFieldType($args = array()) {
        return "time";
    }

    /**
     * converts every type of time to a date fitting in this object.
     */
    public function __construct($name, $value, $args = array())
    {
        if($value !== null) {
            $value = date("H:i:s", strtotime(str_replace(".", ":", $value)));
        }

        parent::__construct($name, $value, $args);
    }

    /**
     * converts this with date
     *
     * @param String format optional
     */
    public function date($format =	DATE_FORMAT)
    {
        if($this->value === null)
            return null;

        return goma_date($format, strtotime($this->value));
    }

    /**
     * returns time with format given.
     *
     * @param String format
     */
    public function timeWithFormat($format) {
        return $this->date($format);
    }

    /**
     * default convert
     */
    public function forTemplate() {
        return $this->value;
    }

    /**
     * generatesa a date-field.
     *
     *@name formfield
     *@access public
     *@param string - title
     */
    public function formfield($title = null)
    {
        return new TimeField($this->name, $title, $this->value);
    }
}
