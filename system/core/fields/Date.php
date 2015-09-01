<?php defined("IN_GOMA") OR die();
/**
 * Base-Class for saving Dates as a timestamp. It will translate it to a timestamp for the date dd.mm.yyyy 00:00.
 *
 * @package		Goma\Core\Model
 * @version		1.0.1
 */
class DateSQLField extends DateTimeSQLField {

    /**
     * converts every type of time to a date fitting in this object.
     */
    public function __construct($name, $value, $args = array())
    {
        if($value !== null) {

            if(preg_match("/^[0-9]+$/", trim($value))) {
                $value = trim($value);
            } else {
                $time = strtotime($value);
                $value = mktime(0,0,0, date("n", $time), date("j", $time), date("Y", $time));
            }
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
        return new DateField($this->name, $title, date(DATE_FORMAT_DATE, $this->value));
    }

    /**
     * returns raw-data.
     */
    public function raw() {
        if($this->value === null) {
            return null;
        }

        return date(DATE_FORMAT_DATE, $this->value);
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
        return goma_date($format, $this->value);
    }
}
