<?php defined("IN_GOMA") OR die();

/**
 * Date-Range-Field
 * It replies with date - date
 * it uses the same as values.
 *
 * @package	Goma\Forms
 * @link	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 */
class DateRangeField extends DateField {

    /**
     * @param string $value
     * @return bool
     * @throws FormInvalidDataException
     */
    public function validate($value)
    {
        if(strpos($value, " - ")) {
            $parts = explode(" - ", $value);

            if(count($parts) == 2) {
                $ts1 = strtotime($parts[0]);
                $ts2 = strtotime($parts[1]);

                if($ts1 !== false && $ts2 !== false) {
                    $this->validateTimestamp($ts1);
                    $this->validateTimestamp($ts2);

                    return true;
                }
            }
        }

        throw new FormInvalidDataException($this->name, lang("no_valid_date", "No valid date."));
    }

    /**
     * @return array
     */
    public function getDatePickerOptions()
    {
        $options = parent::getDatePickerOptions();

        $options["singleDatePicker"] = false;

        return $options;
    }
}
