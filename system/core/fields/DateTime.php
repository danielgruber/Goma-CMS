<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for saving Dates with times as a timestamp.
 *
 * @package		Goma\Core\Model
 * @version		1.5.3
 */
class DateTimeSQLField extends DBField {

	/**
	 * gets the field-type
	 *
	 * @param array $args
	 * @return string
	 */
	static public function getFieldType($args = array()) {
		return "int(30)";
	}

	/**
	 * converts every type of time to a date fitting in this object.
	 * @param string $name
	 * @param string $value
	 * @param array $args
	 */
	public function __construct($name, $value, $args = array())
	{
		if($value !== null) {
			if(preg_match('/^[0-9]+$/', trim($value))) {
				$value = trim($value);
			} else {
				$time = strtotime($value);
				$value = $time;
			}
		}

		parent::__construct($name, $value, $args);
	}

	/**
	 * default convert
	 */
	public function forTemplate() {
		if(isset($this->args[0]))
			return $this->date($this->args[0]);
		else
			return $this->date();
	}

	/**
	 * converts this with date
	 * @param string $format
	 * @return bool|mixed|null|string
	 */
	public function date($format = DATE_FORMAT)
	{
		if($this->value === null)
			return null;

		return goma_date($format, $this->value);
	}

	/**
	 * returns date with format given.
	 *
	 * @param String $format
	 * @return bool|mixed|null|string
	 */
	public function dateWithFormat($format) {
		return $this->date($format);
	}

	/**
	 * returns raw-data.
	 */
	public function raw() {
		if($this->value === null)
			return null;

		return date(DATE_FORMAT, $this->value);
	}

	/**
	 * @return int
	 */
	public function toTimestamp() {
		return (int) $this->value;
	}

	/**
	 * for db.
	 */
	public function forDB() {
		return $this->value;
	}

	/**
	 * returns date as ago
	 * @param bool $fullSentence
	 * @return string
	 */
	public function ago($fullSentence = true) {
		if(NOW - $this->value < 60) {
			return '<span title="'.$this->forTemplate().'" class="ago-date" data-date="'.$this->value.'">' . sprintf(lang("ago.seconds", "about %d seconds ago"), round(NOW - $this->value)) . '</span>';
		} else if(NOW - $this->value < 90) {
			return '<span title="'.$this->forTemplate().'" class="ago-date" data-date="'.$this->value.'">' . lang("ago.minute", "about one minute ago") . '</span>';
		} else {
			$diff = NOW - $this->value;
			$diff = $diff / 60;
			if($diff < 60) {
				return '<span title="'.$this->forTemplate().'" class="ago-date" data-date="'.$this->value.'">' . sprintf(lang("ago.minutes", "%d minutes ago"), round($diff)) . '</span>';
			} else {
				$diff = round($diff / 60);
				if($diff == 1) {
					return '<span title="'.$this->forTemplate().'" class="ago-date" data-date="'.$this->value.'">' . lang("ago.hour", "about one hour ago") . '</span>';
				} else {
					if($diff < 24) {
						return '<span title="'.$this->forTemplate().'" class="ago-date" data-date="'.$this->value.'">' . sprintf(lang("ago.hours", "%d hours ago"), round($diff)) . '</span>';
					} else {
						$diff = $diff / 24;
						if($diff <= 1.1) {
							return '<span title="'.$this->forTemplate().'" class="ago-date" data-date="'.$this->value.'">' . lang("ago.day", "about one day ago") . '</span>';
						} else if($diff <= 7) {
							$pre = ($fullSentence) ? lang("version_at") . " " : "";
							return '<span title="'.$this->forTemplate().'" data-date="'.$this->value.'">' . $pre . sprintf(lang("ago.weekday", "%s at %s"), $this->date("l"), $this->date("H:i")) . '</span>';
						} else {
							if($fullSentence)
								return lang("version_at") . " " . $this->forTemplate();
							else
								return $this->forTemplate();
						}
					}
				}
			}
		}
	}

	/**
	 * form
	 */
	public function form() {

	}
}
