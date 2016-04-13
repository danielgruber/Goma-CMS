<?php defined("IN_GOMA") OR die();

/**
  * @package goma cms
  * @link http://goma-cms.org
  * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  * @author Goma-Team
*/

Director::addRules(array(
	'rate/$name/$rate'			=> 'ratingController',
	'search'						=> 'searchController',
	'boxes_new'						=> 'boxesController',
	'sitemap.xml'					=> 'SitemapController',
	'favicon.ico'					=> 'FaviconController'
), 11);



Director::addRules(array(
	'' => 'HomePageController',
	'$path!//$Action/$id/$otherid' => 'SiteController'
), 1);
