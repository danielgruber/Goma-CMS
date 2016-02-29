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

    /**
     * @param Exception $e
     * @param string|null $content
     * @return string
     */
    public function handleException($e, $content) {
        HTTPResponse::setHeader("content-type", "text/json");

        if(Object::method_exists($e, "http_status")) {
            HTTPResponse::setResHeader($e->http_status());
        } else {
            HTTPResponse::setResHeader(500);
        }

        if(Object::method_exists($e, "extra_info")) {
            $extra_info = $e->extra_info();
        } else {
            $extra_info = array();
        }

        $content = json_encode(array_merge($extra_info, array("error" => $e->getCode(), "type" => get_class($e), "message" => $e->getMessage())));
    }
}
