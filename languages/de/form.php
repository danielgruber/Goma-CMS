<?php
/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 10.02.2013
 */
$form_lang = array(
	'email_not_valid'			=> 'Bitte geben Sie eine g&uuml;ltige Email-Adresse in das Feld ein',
	'phone_not_valid'			=> 'Bitte geben Sie eine g&uuml;ltige Telefonnummer ein.',
	'required_fields'			=> 'Sie haben nicht alle Pflichtfelder ausgef&uuml;llt. Bitte f&uuml;llen Sie folgende Felder aus:',
	'edit_data'					=> "Datensatz bearbeiten",
	'required_field'			=> "Dieses Feld ist obligatorisch.",
	"not_matching"				=> "Die Eingabe hat das falsche Format.",
	'too_long'					=> "Ihre Eingaben sind zu lang. Bitte &uuml;berpr&uuml;fen Sie das Feld ",
	'no_number'					=> "Ihr Eingabe ist keine Zahl. Bitte &uuml;berpr&uuml;fen Sie das Feld ",
	"number_wrong_area"			=> "Die Zahl liegt nicht in dem vorgegebenen Bereich. Bitte &uuml;berpr&uuml;fen Sie das Feld ",
	"dropdown_nothing_select"	=> "Nichts ausgew&auml;hlt",
	"click_to_select"			=> "Zum auswählen klicken",
	"number_not_valid"			=> "Bitte geben Sie eine Zahl ein.",
	"bad_pagetype"				=> "Bitte geben Sie einen korrekten Seitentype ein,",
	"not_saved_yet"				=> "Ihre Legitimierung für diese Aktion ist ausgelaufen. Bitte wiederholen Sie die Aktion.",

	"tablefield.reset"			=> "Zurücksetzen",
	"tablefield.filterBy"		=> "Suche nach "
);
foreach($form_lang as $key => $value)
{
	$GLOBALS['lang']['form_'.$key] = $value;
}
