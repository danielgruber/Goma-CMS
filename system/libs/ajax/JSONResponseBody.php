<?php
defined("IN_GOMA") OR die();

/**
 * JSON Response.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-team
 *
 * @version 1.0
 *
 * @property IRestResponse body
 */
class JSONResponseBody extends GomaResponseBody
{
    /**
     * @param GomaResponse $response
     * @return string
     */
    public function toServableBody($response)
    {
        $this->callExtending("beforeServe", $response);

        $response->setHeader("content-type", "text/json");

        if(is_a($this->body, "IRestResponse")) {
            return json_encode($this->body->ToRestArray());
        }

        return json_encode($this->body);
    }

    /**
     * this is required to allow arrays.
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
}
