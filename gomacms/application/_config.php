<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 17.08.2011
  * $Version 2.0.0 - 001
*/

Core::addRules(array(
	"rate/\$name/\$rate"			=> "ratingController",
	"search"						=> "searchController",
	"boxes_new"						=> "boxesController",
	"sitemap.xml"					=> "SitemapController"
), 11);


Core::addRules(array(
	'$path!//$Action/$id/$otherid' => "SiteController"
), 1);