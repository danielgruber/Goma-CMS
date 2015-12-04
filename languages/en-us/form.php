<?php
/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 10.02.2013
 */
$form_lang = array(
	'email_not_valid'			=> 'Please enter a valid email-address.',
	'phone_not_valid'			=> 'Please enter a valid phone-number.',
	'required_fields'			=> 'Please enter text in all mandatory fields:',
	'edit_data'					=> "edit data",
	'required_field'			=> "This field is required.",
	"not_matching"				=> "Your value is not valid.",
	'too_long'					=> "Your input is too long. Please check the field  ",
	'no_number'					=> "Your input is not numeric. Please check the field ",
	"number_wrong_area"			=> "The number is not within the given range. Please check the field ",
	"dropdown_nothing_select"	=> "Nothing selected",
	"click_to_select"			=> "Click to select",
	"number_not_valid"			=> "Please insert a valid number!",
	"bad_pagetype"				=> "Please set a valid pagetype!",
	"not_saved_yet"				=> "The action could not be completed for security reason, yet. Please repeat it!",

	"tablefield.reset"			=> "Reset",
	"tablefield.filterBy"		=> "filter by "
);
foreach($form_lang as $key => $value)
{
	$GLOBALS['lang']['form_'.$key] = $value;
}
