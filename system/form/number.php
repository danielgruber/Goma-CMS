<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 27.12.2012
  * $Version 1.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class NumberField extends FormField {
	/**
	 * maximum length of the number
	 *
	 *@name maxlength
	 *@access public
	*/
	public $maxlength;
	
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
	 *@name __construct
	 *@param string - name
	 *@param string - title
	 *@param mixed - value
	 *@param object - form
	*/	
	public function __construct($name = null, $title = null, $value = null, $maxlength = null, $parent = null) {
		$this->maxlength = $maxlength;
		parent::__construct($name, $title, $value, $parent);
	}
	
	/**
	 * this is the validation for this field if it is required
	 *@name validation
	 *@access public
	*/
	public function validate($value)
	{
		if($this->maxlength !== null) {
			if(strlen($value) > $this->maxlength)
				return lang("form_too_long") . '"' . $this->title . '"';
		}
		
		if(!is_int($value) || !_ereg('^[0-9\.\-\s\,]+$', $value)) {
			
			return lang("form_no_number") . '"' . $this->title . '"';
		}
		
		if(isset($this->rangeStart) && $value < $this->rangeStart) {
			return lang("form_number_wrong_area") . '"' . $this->title . '"';
		}
		
		if(isset($this->rangeEnd) && $value > $this->rangeEnd) {
			return lang("form_number_wrong_area") . '"' . $this->title . '"';
		}
		
		return true;
	}
	
	/**
	 * sets the range
	 *
	 *@name setRange
	 *@access public
	*/
	public function setRange($start = null, $end = null) {
		$this->rangeStart = $start;
		$this->rangeEnd = $end;
	}
	
	/**
	 * this is the validation for this field if it is required
	 *@name validation
	 *@access public
	*/
	public function JSValidation()
	{
		return '
							var regexp = /^[0-9\.\-\s\,]+$/;
							if(regexp.test($("#'.$this->ID().'").val()))
							{
									
							} else
							{
									$("#'.$this->divID().'").find(".err").remove();
									$("#'.$this->ID().'").before("<div class=\"err\" style=\"color: #ff0000;\">'.lang("form_number_not_valid", "Please enter a valid Number").'</div>");
									return false;
							}
						';
	}
}

