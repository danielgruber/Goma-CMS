<?php defined("IN_GOMA") OR die();

/**
 * Date-Field for SQL-Date.
 *
 * @package	Goma\Forms
 * @link	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 2.0
 */
class DateField extends FormField
{
	/**
	 * generates this field.
	 *
	 * @name    __construct
	 * @param    string $name name
	 * @param    string $title title
	 * @param    string $value value
	 * @param    array $between key 0 for start and key 1 for end and key 2 indicates whether to allow the values given
	 * @param    object $form
	 */
	public function __construct($name = null, $title = null, $value = null, $between = null, $form = null)
	{
		$this->between = is_int($between) ? array($between, PHP_INT_MAX) : $between;
		parent::__construct($name, $title, $value, $form);
	}

	/**
	 * creates the field.
	 *
	 * @name createNode
	 * @access public
	 * @return HTMLNode
	 */
	public function createNode()
	{
		$node = parent::createNode();
		$node->type = "text";
		$node->addClass("datepicker");
		return $node;
	}

	/**
	 * validate
	 *
	 * @param string $value
	 * @return bool
	 * @throws FormInvalidDataException
	 */
	public function validate($value)
	{
		if (($timestamp = strtotime($value)) === false) {
			throw new FormInvalidDataException($this->name, lang("no_valid_date", "No valid date."));
		} else {
			if ($this->between && is_array($this->between)) {
				$this->validateTimestamp($timestamp);
			}
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function getModel()
	{
		$model = parent::getModel();
		if(RegexpUtil::isNumber($model)) {
			return date(DATE_FORMAT_DATE, $model);
		}

		return $model;
	}

	/**
	 * @param int $timestamp
	 * @throws FormInvalidDataException
	 */
	protected function validateTimestamp($timestamp) {
		$between = array_values($this->between);

		if (!preg_match("/^[0-9]+$/", trim($between[0]))) {
			$start = strtotime($between[0]);
		} else {
			$start = $between[0];
		}

		if (!preg_match("/^[0-9]+$/", trim($between[1]))) {
			$end = strtotime($between[1]);
		} else {
			$end = $between[1];
		}

		if(
			((!isset($between[2]) || $between[2] === false) && ($start >= $timestamp || $timestamp >= $end)) ||
			(isset($between[2]) && $between[2] === true && ($start > $timestamp && $timestamp > $end))
		) {
			$err = lang("date_not_in_range", "The given time is not between the range \$start and \$end.");
			$err = str_replace('$start', date(DATE_FORMAT_DATE, $start), $err);
			$err = str_replace('$end', date(DATE_FORMAT_DATE, $end), $err);
			throw new FormInvalidDataException($this->name, $err);
		}
	}

	/**
	 * @param FormFieldRenderData $info
	 * @param bool $notifyField
	 */
	public function addRenderData($info, $notifyField = true)
	{
		parent::addRenderData($info, $notifyField);

		$info->addJSFile("system/libs/thirdparty/moment/moment.min.js");
		$info->addJSFile("system/libs/thirdparty/jquery-daterangepicker/daterangepicker.js");
		$info->addCSSFile("system/libs/thirdparty/jquery-daterangepicker/daterangepicker.css");
	}

	/**
	 * @param string $php_format
	 * @return string
	 */
	public static function dateformat_PHP_to_DatePicker($php_format)
	{
		$SYMBOLS_MATCHING = array(
			// Day
			'd' => 'DD',
			'D' => 'D',
			'j' => 'D',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week
			'W' => '',
			// Month
			'F' => 'MM',
			'm' => 'MM',
			'M' => 'M',
			'n' => 'M',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'YYYY',
			'y' => 'YY',
			// Time
			'a' => '',
			'A' => '',
			'B' => '',
			'g' => '',
			'G' => '',
			'h' => '',
			'H' => '',
			'i' => '',
			's' => '',
			'u' => ''
		);
		$jqueryui_format = "";
		$escaping = false;
		for($i = 0; $i < strlen($php_format); $i++)
		{
			$char = $php_format[$i];
			if($char === '\\') // PHP date format escaping character
			{
				$i++;
				if($escaping) $jqueryui_format .= $php_format[$i];
				else $jqueryui_format .= '\'' . $php_format[$i];
				$escaping = true;
			}
			else
			{
				if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
				if(isset($SYMBOLS_MATCHING[$char]))
					$jqueryui_format .= $SYMBOLS_MATCHING[$char];
				else
					$jqueryui_format .= $char;
			}
		}
		return $jqueryui_format;
	}

	/**
	 * render JavaScript
	 */
	public function JS()
	{
		return '$(\'#'.$this->ID().'\').daterangepicker('.json_encode($this->getDatePickerOptions()).');';
	}

	/**
	 * @return array
	 */
	protected function getDatePickerOptions() {
		/** @var string[] $calendar */
		require (ROOT . LANGUAGE_DIRECTORY . Core::$lang . "/calendar.php");


		return array(
			"singleDatePicker" => true,
			"showDropdowns"	=> true,
			"showWeekNumbers"	=> true,
			"autoApply"	=> true,
			"minDate"	=> isset($this->between[0]) ? date("d/m/Y", $this->between[0]) : null,
			"maxDate"	=> isset($this->between[1]) ? date("d/m/Y", $this->between[1]) : null,
			"applyClass"=> "btn-success button green",
			"locale"	=> array(
				"format"		=> self::dateformat_PHP_to_DatePicker(DATE_FORMAT_DATE),
				"seperator"		=> " - ",
				"applyLabel"	=> lang("save"),
				"cancelLabel"	=> lang("cancel"),
				"fromLabel"		=> lang("fromLabel"),
				"toLabel"		=> lang("toLabel"),
				"customRangeLabel"	=> lang("customLabel"),
				"daysOfWeek"	=> array(
					$calendar["Sun"],
					$calendar["Mon"],
					$calendar["Tue"],
					$calendar["Wed"],
					$calendar["Thu"],
					$calendar["Fri"],
					$calendar["Sat"]
				),
				"monthNames"	=> array(
					$calendar["January"],
					$calendar["February"],
					$calendar["March"],
					$calendar["April"],
					$calendar["May"],
					$calendar["June"],
					$calendar["July"],
					$calendar["August"],
					$calendar["September"],
					$calendar["October"],
					$calendar["November"],
					$calendar["December"]
				),
				"firstDay"	=> 1
			)
		);
	}
}
