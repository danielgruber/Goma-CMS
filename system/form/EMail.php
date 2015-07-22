<?php
defined("IN_GOMA") OR die();

/**
 * An email text field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0.3
 */
class Email extends TextField {
	/**
	 * this function generates some JavaScript for the validation of the field
	 *@name jsValidation
	 *@access public
	 */
	public function jsValidation() {
		return '
							var regexp = /^([a-zA-Z0-9\-\._]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9]{2,9})$/;
							if(regexp.test($("#' . $this->ID() . '").val()))
							{
									
							} else
							{
									$("#' . $this->divID() . '").find(".err").remove();
									$("#' . $this->ID() . '").after("<div class=\"err\" style=\"color: #ff0000;\">' . lang("form_email_not_valid", "Please enter a correct E-Mail-Adresse") . '</div>");
									return false;
							}
						';
	}

	/**
	 * this is the validation for this field if it is required
	 *
	 * @name validation
	 * @access public
	 * @return bool|string
	 */
	public function validate($value) {
		if(RegexpUtil::isEmail($value)) {
			return true;
		} else {
			return lang("form_email_not_valid", "Please enter a correct E-Mail-Adresse");
		}
	}

}
