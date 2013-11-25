<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for saving Dates with times as a timestamp.
 *
 * @package		Goma\Core\Model
 * @version		1.5
 */
class DateTimeSQLField extends DBField {
	
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return "int(30)";
	}
	
	/**
	 * converts every type of time to a date fitting in this object.
	*/
	public function __construct($name, $value, $args = array())
	{
			
			if(preg_match('/^[0-9]+$/', trim($value))) {
				$value = trim($value);
			} else {
				$time = strtotime($value);
				$value = $time;
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
	 *@name date
	 *@access public
	*/
	public function date($format =	DATE_FORMAT)
	{	
		return goma_date($format, $this->value);
	}
	
	/**
	 * returns raw-data.
	*/
	public function raw() {
		return date(DATE_FORMAT, $this->value);
	}
	
	/**
	 * for db.
	*/
	public function forDB() {
		return $this->value;
	}
	
	
	
	/**
	 * returns date as ago
	 *
	 *@name ago
	 *@access public
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
						$diffRound = round($diff, 1);
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
			if(preg_match("/^[0-9]+$/", trim($value))) {
				$value = trim($value);
			} else {
				$time = strtotime($value);
				$value = mktime(0,0,0, date("n", $time), date("j", $time), date("Y", $time));
			}
			
			parent::__construct($name, $value, $args);
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
			return new DateField($this->name, $title, date(DATE_FORMAT_DATE, $this->value));
	}
	
	/**
	 * returns raw-data.
	*/
	public function raw() {
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
	*/
	public function date($format = DATE_FORMAT_DATE) {
		return goma_date($format, $this->value);
	}
}

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
			$value = date("H:i:s", strtotime(str_replace(".", ":", $value)));
			parent::__construct($name, $value, $args);
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
