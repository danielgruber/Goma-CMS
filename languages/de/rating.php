<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 30.10.2011
*/   

$rating_l = array(
	"vote"              => "Stimme",
	"votes"             => "Stimmen",
	"rated"             => "Sie haben bereits abgestimmt!",
	"thanks_for_voting" => "Danke f&uuml;r ihre Stimme",
	"perms_delete"		=> "Bewertungen zur&uuml;cksetzen"
);
foreach($rating_l as $key => $value)
{
	$GLOBALS['lang']['rating.'.$key] = $value;
}