<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 13.03.2010
*/   
$se_lang = array(
	"search"              	=> "search",
	"result"              	=> "result",
	"no_word"             	=> "Please enter a search-query!",
	'no_title'            	=> "no title",
	'no'                  	=> "There is no result!",
	'search_sites'        	=> "search..." ,
	'pages'               	=> "pages",
	'results'			 	=> "Results"
);
foreach($se_lang as $key => $value){
$GLOBALS['lang']['search.'.$key] = $value;
}
