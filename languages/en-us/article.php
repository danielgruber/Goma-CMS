<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 16.07.2011
*/   
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

$ar_lang = array(
	'no_article'      	=> "<div class=\"error\"><strong>Fehler</strong><br />The article wasn't found!</div>" ,
	"rate"            	=> "rate article",
	"articles"        	=> "articles",
	'article'         	=> "article",
	"description"     	=> "description",
	"title"           	=> "title",
	"comments"        	=> "allow comments",
	"written_by"      	=> "written by",
	"browse"          	=> "browser server",
	"active"          	=> "active",
	'page_article'		=> "article",
	'page_category'	 	=> "article-category",
	'allow_rate'		=> 'allow rating',
	"read_more"		 	=> "read more"
);
foreach($ar_lang as $key => $value){
	$GLOBALS['lang']['ar_'.$key] = $value;
}

