<?php
defined("IN_GOMA") OR die();

/**
 * Used to redirect-back.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version 1.0
 */
class ControllerRedirectBackResponse extends GomaResponse {

    /**
     * hinted.
     */
    protected $hintUrl;

    /**
     * from url.
     */
    protected $fromUrl;

    /**
     * used.
     */
    protected $parentControllerResolved;

    /**
     * values.
     */
    protected $params = array();

    /**
     * @var bool
     */
    protected $useJavascript;

    /**
     * ControllerRedirectBackResponse constructor.
     *
     * @param string $hintUrl
     * @param string $fromUrl
     * @param bool $useJavaScript
     * @return static
     */
    public static function create($hintUrl, $fromUrl = null, $useJavaScript = false) {
        return new static($hintUrl, $fromUrl, $useJavaScript);
    }

    /**
     * @return void
     */
    public function output() {
        $url = $this->hintUrl ? $this->hintUrl : BASE_URI;
        foreach($this->params as $key => $value) {
            $url = Controller::addParamToUrl($url, $key, $value);
        }
        if($this->useJavascript) {
            GomaResponse::create($this->header, $this->body)->redirectByJavaScript($url)->output();
        } else {
            GomaResponse::create($this->header, $this->body)->redirectRequest($url)->output();
        }
    }

    /**
     * @param string $hintUrl
     */
    public function __construct($hintUrl, $fromUrl = null, $useJavaScript = false)
    {
        parent::__construct();

        $this->fromUrl = $fromUrl;
        $this->useJavascript = $useJavaScript;
        $this->hintUrl = $hintUrl;
    }

    /**
     * @return mixed
     */
    public function getHintUrl()
    {
        return $this->hintUrl;
    }

    /**
     * @param mixed $hintUrl
     * @return $this
     */
    public function setHintUrl($hintUrl)
    {
        $this->hintUrl = $hintUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentControllerResolved()
    {
        return $this->parentControllerResolved;
    }

    /**
     * @param mixed $parentControllerResolved
     * @return $this
     */
    public function setParentControllerResolved($parentControllerResolved)
    {
        $this->parentControllerResolved = $parentControllerResolved;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromUrl()
    {
        return $this->fromUrl;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParam($name, $value) {
        if(isset($name)) {
            $this->params[$name] = $value;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return boolean
     */
    public function isUseJavascript()
    {
        return $this->useJavascript;
    }

    /**
     * @param boolean $useJavascript
     */
    public function setUseJavascript($useJavascript)
    {
        $this->useJavascript = $useJavascript;
    }
}
