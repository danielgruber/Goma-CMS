<?php
defined("IN_GOMA") OR die();

/**
 * Part of Goma-response.
 *
 * Handles additional parts like Resources.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class GomaResponseBody extends gObject {
    /**
     * @var string
     */
    protected $body;

    /**
     * include resources in body or not.
     */
    protected $includeResourcesInBody = true;

    /**
     * parse html or not.
     */
    protected $parseHTML = true;

    /**
     * @param null $body
     * @return static
     */
    public static function create($body = null) {
        return new static($body);
    }

    /**
     * GomaResponseBody constructor.
     * @param string $body
     */
    public function __construct($body = null)
    {
        parent::__construct();

        $this->setBody($body);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        if(is_array($body)) {
            throw new InvalidArgumentException("setBody requires String or object with __toString");
        }

        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->body;
    }

    /**
     * @return mixed
     */
    public function getIncludeResourcesInBody()
    {
        return $this->includeResourcesInBody;
    }

    /**
     * @param mixed $includeResourcesInBody
     * @return $this
     */
    public function setIncludeResourcesInBody($includeResourcesInBody)
    {
        $this->includeResourcesInBody = $includeResourcesInBody;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParseHTML()
    {
        return $this->parseHTML;
    }

    /**
     * @param mixed $parseHTML
     * @return $this
     */
    public function setParseHTML($parseHTML)
    {
        $this->parseHTML = $parseHTML;
        return $this;
    }

    /**
     * @param GomaResponse $response
     * @return string
     */
    public function toServableBody($response) {
        $this->callExtending("beforeServe", $response);

        $headers = $response->getHeader();
        if($this->parseHTML && strpos(strtolower($headers["content-type"]), "html") !== false) {
            $html = new htmlparser();
            $body = $html->parseHTML($this->body, true, $this->includeResourcesInBody);
        } else {
            $body = $this->body;
        }

        return $body;
    }

}
