<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 17.08.2011
  * $Version 2.0.0 - 001
*/

Core::addRules(array(
	"rate/\$name/\$rate"			=> "ratingController",
	"search"						=> "searchController",
	"boxes_new"						=> "boxesController",
	"sitemap.xml"					=> "SitemapController",
	"favicon.ico"					=> "FaviconController"
), 11);


Core::addRules(array(
	'$path!//$Action/$id/$otherid' => "SiteController"
), 1);