<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 02.07.2010
*/   
$form_lang = array(
	'email_not_valid'			=> 'Please enter a valid email-adresse.',
	'required_fields'			=> 'Please enter text in all mandatory fields:',
	'edit_data'					=> "edit data",
	'required_field'			=> "This field is a mandatory field!",
	'too_long'					=> "Your input is too long. Please check the field  ",
	'no_number'					=> "Your input is not numeric. Please check the field ",
	"dropdown_nothing_select"	=> "Nothing selected",
	"number_not_valid"			=> "Please insert a valid number!",
	"bad_pagetype"				=> "Please set a valid pagetype!",
	"not_saved_yet"				=> "The data was successfully saved.!"
);
foreach($form_lang as $key => $value)
{
	$GLOBALS['lang']['form_'.$key] = $value;
}
