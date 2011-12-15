<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 09.04.2011
*/

$preview_lang = array(
	"file"			=> "File",
	"folder"		=> "Folder"
);

foreach($preview_lang as $key => $value) {
	$GLOBALS["lang"]["preview." . $key] = $value;
}

