<?php
defined("IN_GOMA") OR die();

/**
 * REST-Controller Extension to Parse IRestResponse.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version 1.0
 */
class RestControllerExtension extends Extension {
    /**
     * @param mixed $content
     */
    public function handleOutput(&$content) {
        if(is_a($content, "IRestResponse")) {
            HTTPResponse::setHeader("content-type", "text/json");

            /** @var IRestResponse $content */
            $content = json_encode($content->ToRestArray());
        }
    }
}
