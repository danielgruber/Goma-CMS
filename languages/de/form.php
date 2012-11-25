<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 25.11.2010
*/   
$form_lang = array(
	'email_not_valid'			=> 'Bitte geben Sie eine g&uuml;ltige Email-Adresse in das Feld ein',
	'required_fields'			=> 'Sie haben nicht alle Pflichtfelder ausgef&uuml;llt. Bitte f&uuml;llen Sie folgende Felder aus:',
	'edit_data'					=> "Datensatz bearbeiten",
	'required_field'			=> "Dieses Feld ist obligatorisch!",
	'too_long'					=> "Ihre Eingaben sind zu lang! Bitte &uuml;berpr&uuml;fen Sie das Feld ",
	'no_number'					=> "Ihr Eingabe ist keine Zahl! Bitte &uuml;berpr&uuml;fen Sie das Feld ",
	"dropdown_nothing_select"	=> "Nichts ausgew&auml;hlt",
	"number_not_valid"			=> "Bitte geben Sie eine g&uuml;ltige Zahl ein!",
	"bad_pagetype"				=> "Bitte geben Sie einen korrekten Seitentype ein!",
	"not_saved_yet"				=> "Die Aktion konnte leider aus Sicherheitsgründen nicht ausgeführt werden. Bitte versuchen Sie es noch einmal!",
	
	"tablefield.reset"			=> "Zurücksetzen",
	"tablefield.filterBy"		=> "Sortieren nach "
);
foreach($form_lang as $key => $value)
{
	$GLOBALS['lang']['form_'.$key] = $value;
}
