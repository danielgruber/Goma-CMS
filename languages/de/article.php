<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 01.01.2011
*/   
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

     $ar_lang = array(
       'no_article'      => "<div class=\"error\"><strong>Fehler</strong><br />Der Artikel wurde nicht gefunden!</div>" ,
       "rate"            => "Artikel bewerten",
       "articles"        => "Artikel",
       'article'         => "Artikel",
       "description"     => "Beschreibung",
       "title"           => "Titel",
       "comments"        => "Kommentare erlauben",
       "written_by"      => "Geschrieben von",
       "browse"          => "Server durchsuchen...",
       "active"          => "Aktiv",
	   'page_article'	 => "Artikel",
	   'page_category'	 => "Artikelkategorie",
	   'allow_rate'		 => 'Erlaube Bewertung' ,
	   "read_more"		 => "Mehr erfahren"
     );
     foreach($ar_lang as $key => $value){
       $GLOBALS['lang']['ar_'.$key] = $value;
     }

	 