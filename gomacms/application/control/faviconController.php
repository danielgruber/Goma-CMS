<?php defined("IN_GOMA") OR die();

/**
 * Delivers the favicon.
 *
 * @package     Goma-CMS\Controller
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */
class FaviconController extends Controller {
	/**
	 * index
	*/
	public function index() {
		if($file = settingsController::get("favicon")) {
			if(preg_match("/\.ico$/", $file->filename)) {
				readfile($file->realfile);
				exit;
			} else {
				if(!file_exists(ROOT . CACHE_DIRECTORY . "/favicon.".$file->id.".v2.ico")) {
					$image = new Image($file->realfile);
					$fav = $image->resize(240, 240, false);
					$fav->toFile(ROOT . CACHE_DIRECTORY . "/favicon.".$file->id.".v2.ico", 70, "ico");
				}
				
				HTTPResponse::setHeader("content-type", "image/x-icon");
				HTTPResponse::sendHeader();
				readfile(ROOT . CACHE_DIRECTORY . "/favicon.".$file->id.".v2.ico");
				exit;
			}
		} else {
			return false;
		}
	}
}

