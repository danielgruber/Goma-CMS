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
        $content = new JSONResponseBody($content);
    }

    /**
     * @param Exception $e
     * @param string|null $content
     * @return string
     */
    public function handleException($e, $content) {
        $response = new GomaResponse();
        if(gObject::method_exists($e, "http_status")) {
            $response->setStatus($e->http_status());
        } else {
            $response->setStatus(500);
        }

        if(gObject::method_exists($e, "extra_info")) {
            $extra_info = $e->extra_info();
        } else {
            $extra_info = array();
        }

        $response->setBody(
            new JSONResponseBody(array_merge($extra_info, array("error" => $e->getCode(), "type" => get_class($e), "message" => $e->getMessage())))
        );

        $content = $response;
    }
}
