<?php
defined("IN_GOMA") OR die();

/**
 * Includes information how to treat this request.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class GomaResponse extends gObject {
    /**
     * responsetypes
     *
     * @var array
     */
    public static $http_status_types = array(
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method not Allowed',
        406 => "Not acceptable",
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        511 => 'Network Authentication Required'
    );
    /**
     * header.
     *
     * @var array
     */
    protected $header = array();

    /**
     * body.
     *
     * @var GomaResponseBody
     */
    protected $body;

    /**
     * response-status.
     */
    protected $status = 200;

    /**
     * should serve?.
     * @var bool
     */
    protected $shouldServe = true;

    /**
     * GomaResponse constructor.
     *
     * @param array|null $header
     * @param string|null $body
     */
    public function __construct($header = null, $body = null)
    {
        parent::__construct();

        $this->setDefaultHeader();
        foreach((array) $header as $name => $value) {
            $this->setHeader($name, $value);
        }
        $this->setBody($body);
    }

    protected function setDefaultHeader() {
        $this->setHeader("vary", "Accept-Encoding");
        $this->setHeader("X-Powered-By", "Goma ".strtok(GOMA_VERSION, ".")." with PHP " . PHP_MAIOR_VERSION);
        $this->setHeader("content-type", "text/html;charset=utf-8");
        $this->setHeader("x-base-uri", BASE_URI);
        $this->setHeader("x-root-path", ROOT_PATH);

        if(isset(ClassInfo::$appENV["app"]["name"]) && defined("APPLICATION_VERSION"))
            $this->setHeader('X-GOMA-APP', ClassInfo::$appENV["app"]["name"] . " " . strtok(APPLICATION_VERSION, "."));
    }

    /**
     * sets a header.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value) {
        $this->header[strtolower($name)] = $value;
    }

    public function removeHeader($name) {
        unset($this->header[strtolower($name)]);
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return GomaResponseBody
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getResponseBodyString() {
        return (string) $this->body;
    }

    /**
     * @param string|GomaResponseBody $body
     */
    public function setBody($body)
    {
        if(is_a($body, "GomaResponseBody")) {
            $this->body = $body;
        } else {
            if(!isset($this->body)) {
                $this->body = new GomaResponseBody($body);
            } else {
                $this->body->setBody($body);
            }
        }
    }

    /**
     * @param string $body
     */
    public function setBodyString($body) {
        $this->body->setBody($body);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        if(!isset(self::$http_status_types)) {
            throw new InvalidArgumentException("HTTP Status $status not known.");
        }

        $this->status = $status;
    }

    /**
     * @param string $url
     * @param bool $permanent
     * @return GomaResponse
     */
    public function redirectRequest($url, $permanent = false)
    {
        $request = clone $this;

        $request->setStatus($permanent ? 301 : 302);
        $request->setHeader("location", $url);
        $request->setBody('<script type="text/javascript">location.href = "'.addSlashes($url).'";</script><br /> Redirecting to: <a href="'.addSlashes($url).'">'.convert::raw2text($url).'</a>');
        $request->setShouldServe(false);

        return $request;
    }

    /**
     * redirect by javascript
     *
     * @param string $url
     * @return GomaResponse
     */
    public function redirectByJavaScript($url) {
        $response = new AjaxResponse($this->header, $this->body);

        $response->exec("window.location.href = " . var_export($url, true) . ";");

        return $response;
    }

    /**
     * @param string $url
     * @param bool $permanent
     * @return GomaResponse
     */
    public static function redirect($url, $permanent = false)
    {
        $response = new GomaResponse();
        return $response->redirectRequest($url, $permanent);
    }

    /**
     * Sends file with redirect to FileSender.
     *
     * @param string $file
     * @return GomaResponse
     */
    public function sendFile($file, $filename = null) {
        if(!file_exists($file))
            throw new InvalidArgumentException("File must exist.");

        $hash = randomString(20);
        FileSystem::write(FRAMEWORK_ROOT . "temp/download." . $hash . ".goma", serialize(array("file" => realpath($file), "filename" => $filename)));

        return $this->redirectRequest(ROOT_PATH . "system/libs/file/Sender/FileSender.php?downloadID=" . $hash);
    }

    /**
     * sets Pragma, Last-Modified, Expires and Cache-Control.
     *
     * @param int $expires
     * @param int $lastModfied
     * @param bool $includeProxy
     */
    public function setCacheHeader($expires, $lastModfied, $includeProxy = false)
    {
        if($includeProxy) {
            $this->setHeader("Pragma", "public");
        } else {
            $this->setHeader("Pragma", "No-Cache");
        }

        $this->setHeader("Last-Modified", gmdate('D, d M Y H:i:s', $lastModfied).' GMT');
        $this->setHeader("Expires", gmdate('D, d M Y H:i:s', $expires).' GMT');
        $age = $expires - time();
        $this->setHeader("cache-control", "public; max-age=".$age."");
    }

    /**
     * sets Pragma, Last-Modified, Expires and Cache-Control.
     * Browser won't cache also when back is pressed.
     */
    public function forceNoCache()
    {
        $this->setHeader("Pragma", "No-Cache");
        $this->setHeader("Last-Modified", '');
        $this->setHeader("Expires", '0');
        $this->setHeader("cache-control", " no-cache, max-age=0, must-revalidate, no-store");
    }

    /**
     * renders body.
     */
    public function render() {
        if(!is_a($this->body, "GomaResponseBody")) {
            $body = new GomaResponseBody($this->body);
            return $body->toServableBody($this);
        }

        return $this->body->toServableBody($this);
    }

    /**
     * sends header and response body.
     */
    public function output()
    {
        if($this->status == 301 || $this->status == 302) {
            $isPermanent = $this->status == 301;
            Core::callHook("beforeRedirect", $this->header["location"], $isPermanent, $this);

            // TODO: Fix this hack.
            addcontent::add(addcontent::get());
        }

        $content = $this->render();
        $this->addResourcesToHeaders();

        $this->callExtending("beforeOutput");

        $this->sendHeader();

        Core::callHook("onbeforeoutput");

        $data = ob_get_contents();
        ob_end_clean();

        if($data != null && !DEV_MODE) {
            throw new LogicException("There should not be any output than body.");
        }

        ob_start("ob_gzhandler");

        echo $content;
        echo $data;

        ob_end_flush();
    }

    protected function addResourcesToHeaders() {
        $data = Resources::get(true, true, true);
        $this->setHeader("X-JavaScript-Load", implode(";", $data["js"]));
        $this->setHeader("X-CSS-Load", implode(";", $data["css"]));
    }

    /**
     * sends header.
     *
     * @internal
     */
    public function sendHeader()
    {
        if(DEV_MODE) {
            $time =  microtime(true) - EXEC_START_TIME;
            $this->setHeader("X-Time", $time);
        }

        header('HTTP/1.1 ' . $this->status . " " . self::$http_status_types[$this->status]);
        foreach($this->header as $key => $value) {
            header($key . ": " . $value);
        }
    }

    /**
     *
     */
    public function shouldServe()
    {
        return $this->shouldServe;
    }

    /**
     * @return bool
     */
    public function getShouldServe()
    {
        return $this->shouldServe;
    }

    /**
     * @param bool $shouldServe
     */
    public function setShouldServe($shouldServe)
    {
        $this->shouldServe = $shouldServe;
    }

    /**
     * @param GomaResponse $response
     * @internal
     */
    public function merge($response) {
        $this->header = array_merge($response->header, $this->header);
        if($this->status == 200 && $response->status != 200) {
            $this->status = $response->status;
        }
    }
}
