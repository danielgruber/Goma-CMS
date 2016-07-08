<?php
defined("IN_GOMA") OR die();

/**
 * Homepage-Controller.
 *
 * @package Goma CMS
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *
 * @version 1.0
 */
class HomePageController extends SiteController
{
    /**
     * shows the homepage of this page
     *
     * @return false|string
     */
    public function index() {
        defined("HOMEPAGE") OR define("HOMEPAGE", true);

        if (isset($this->getRequest()->get_params["r"])) {
            /** @var Page $redirect */
            $redirect = DataObject::get_one("pages", array("id" => $this->getRequest()->get_params["r"]));
            if ($redirect) {
                $query = preg_replace('/\&?r\=' . preg_quote($this->getRequest()->get_params["r"], "/") . '/', '', $_SERVER["QUERY_STRING"]);
                return GomaResponse::redirect($redirect->getURL() . "?" . $query);
            }
        }

        if ($data = DataObject::get_one("pages", array("parentid" => 0))) {
            return ControllerResolver::instanceForModel($data)->handleRequest($this->request);
        } else {
            return false;
        }
    }
}
