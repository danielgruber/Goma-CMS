<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 13.03.2010
*/   
$se_lang = array(
	"search"              	=> "Suchen",
	"result"             	=> "Suchergebniss",
	"no_word"             	=> "Bitte geben Sie einen Suchbegriff ein!",
	'no_title'            	=> "Kein Titel",
	'no'                  	=> "Ihre suche ergab 0 Treffer!",
	'search_sites'        	=> "Suchen..." ,
	'pages'               	=> "Seiten",
	'results'			 	=> "Ergebnisse"
);
foreach($se_lang as $key => $value){
$GLOBALS['lang']['search.'.$key] = $value;
}
