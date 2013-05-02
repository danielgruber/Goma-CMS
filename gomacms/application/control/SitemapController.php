<?php
/**
  *@package goma-cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 23.02.2013
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class SitemapController extends Controller {
	public $model = "pages";
	
	public function index() {
		$data = DataObject::get("pages");
		
		$str = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		foreach($data as $record) {
			$str .= '<url>';
			$str .= '<loc>' . BASE_URI . BASE_SCRIPT . $record->path . URLEND . '</loc>';
			$str .= '<lastmod>' . date(DATE_W3C, $record->last_modified) . '</lastmod>';
			$str .= '</url>';
		}
		$str .= "</urlset>";
		
		HTTPResponse::setHeader("content-type", "text/xml");
		return $str;
	}
}