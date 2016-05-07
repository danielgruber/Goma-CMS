<?php defined("IN_GOMA") OR die();

/**
 * @package goma form framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 *
 * last modified: 04.12.2015
 * $Version 1.3
 */
class NumberField extends FormField {
	/**
	 * start of range
	 *
	 *@name rangeStart
	 *@access protected
	 */
	protected $rangeStart;

	/**
	 * end of range
	 *
	 *@name rangeEnd
	 *@access protected
	 */
	protected $rangeEnd;

	/**
	 * @var string
	 */
	protected $regexp = RegexpUtil::NUMBER_REGEXP;

	/**
	 * @var string
	 */
	protected $regexpError = "form_no_number";

	/**
	 * @param string $name
	 * @param string $title
	 * @param int $start
	 * @param int|null $end
	 * @param int|null $value
	 * @param Form|null $parent
	 * @return NumberField
	 */
	public static function createWithRange($name, $title, $start, $end = null, $value = null, $parent = null)
	{
		/** @var NumberField $field */
		$field = parent::create($name, $title, $value, $parent);
		$field->setRange($start, $end);
		return $field;
	}

	/**
	 * NumberField constructor.
	 * @param string $name
	 * @param string $title
	 * @param int $value
	 * @param int $maxLength
	 * @param Form|null $parent
	 */
	public function __construct($name = null, $title = null, $value = null, $maxLength = null, $parent = null) {
		parent::__construct($name, $title, $value, $parent);

		$this->maxLength = $maxLength;
	}

	/**
	 * more of validation.
	 *
	 * @param $value
	 * @return bool|string
	 */
	public function validate($value) {
		if(isset($this->rangeStart) && $value < $this->rangeStart) {
			return lang("form_number_wrong_area") . '"' . $this->title . '" ';
		}

		if(isset($this->rangeEnd) && $value > $this->rangeEnd) {
			return lang("form_number_wrong_area") . '"' . $this->title . '" ';
		}

		return parent::validate($value);
	}

	/**
	 * sets the range
	 *
	 * @param null|int $start
	 * @param null|int $end
	 * @access public
	 */
	public function setRange($start = null, $end = null) {
		$this->rangeStart = $start;
		$this->rangeEnd = $end;
	}
}
