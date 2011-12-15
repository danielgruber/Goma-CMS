<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 11.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class NumberField extends FormField {
	
	/**
	 *@name __construct
	 *@param string - name
	 *@param string - title
	 *@param mixed - value
	 *@param object - form
	*/	
	public function __construct($name = null, $title = null, $value = null, $parent = null, $maxlength = null) {
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
				return lang("form_too_short") . '"' . $this->title . '"';
		}
		if(!is_int($value) || !_ereg('^[0-9\.\-\s\,]+$', $value)) {
			
			return lang("form_no_number") . '"' . $this->title . '"';
		}
		return true;
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

