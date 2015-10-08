<?php defined("IN_GOMA") OR die();

/**
 * Class which generates the Sitemap.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.7.2
 *
 * @property null|Pages parent
 * @property string path
 * @property bool active - can be set from outside
 * @property string title
 * @property Permission|null read_permission
 */
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

		$this->callExtending("extendSiteMap", $str);

		$str .= "</urlset>";

		HTTPResponse::setHeader("content-type", "text/xml");
		return $str;
	}
}