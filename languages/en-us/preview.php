<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 09.04.2011
*/

$preview_lang = array(
	"file"			=> "File",
	"folder"		=> "Folder"
);

foreach($preview_lang as $key => $value) {
	$GLOBALS["lang"]["preview." . $key] = $value;
}

