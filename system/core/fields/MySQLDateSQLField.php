<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for Saving Date as SQL-Type Date.
 *
 * @package		Goma\Core\Model
 * @version		1.0.1
 */
class MySQLDateSQLField extends DBField {

    /**
     * gets the field-type
     * @return string
     */
    static function getFieldType($args = array()) {
        return "Date";
    }

    /**
     * converts every type of time to a date fitting in this object.
     */
    public function __construct($name, $value, $args = array())
    {
        if($value !== null) {
            if(preg_match("/^[0-9]+$/", trim($value))) {
                $time = (int) trim($value);
            } else {
                $time = strtotime($value);
            }

            $value = date("Y-m-d", $time);
        }

        parent::__construct($name, $value, $args);
    }

    /**
     * generatesa a date-field.
     *
     * @param string $title
     * @return DateField|FormField
     */
    public function formfield($title = null)
    {
        return new DateField($this->name, $title, date(DATE_FORMAT_DATE, strtotime($this->value)));
    }

    /**
     * returns raw-data.
     */
    public function raw() {
        if($this->value === null) {
            return null;
        }

        return date(DATE_FORMAT_DATE, strtotime($this->value));
    }

    /**
     * for db.
     */
    public function forDB() {
        return $this->value;
    }

    /**
     * default convert
     *
     * @param string $format
     * @return bool|mixed|null|string
     */
    public function date($format = DATE_FORMAT_DATE) {
        return goma_date($format, strtotime($this->value));
    }
}
