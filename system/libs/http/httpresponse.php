<?php defined("IN_GOMA") OR die();

/**
 * Class HTTPResponse
 *
 * @deprecated
 */
class HTTPResponse
{
	/**
	 * defines whether HTTPResponse should parse response with HTMLParser-Class
	 * before it is sent to client.
	 * default is that is parses.
	 *
	 * @name 	disabledparsing
	 * @deprecated
	 * @var 	bool
	*/
	public static $disabledparsing = false;

	/**
	 * @var GomaResponse
	 */
	private static $gomaResponse;

	/**
	 * GomaResponse.
	 * @deprecated
	 */
	public static function gomaResponse() {
		if(!isset(self::$gomaResponse)) {
			self::$gomaResponse = new GomaResponse(null, new GomaResponseBody());
		}

		return self::$gomaResponse;
	}

	/**
	  * add header
	  * @name 	addHeader
	  * @access public
	  * @param 	string - name
	  * @param 	string - content
	 * @deprecated
	 */
	public static function addHeader($name, $content)
	{
		self::gomaResponse()->setHeader($name, $content);
	}

	/**
	  * synonym for @link addHeader
	 * @deprecated
	 */
	public static function setHeader($name, $content)
	{
		self::gomaResponse()->setHeader($name, $content);
	}

	/**
	 * removes an header.
	 *
	 * @name 	removeHeader
	 * @access 	public
	 * @param 	string - name
	 * @deprecated
	 */
	public static function removeHeader($name)
	{
		self::gomaResponse()->removeHeader($name);
	}

	/**
	 * sets the body.
	 *
	 * @name 	setBody
	 * @access 	public
	 * @return 	null
	 * @deprecated
	 */
	public static function setBody($body)
	{
		self::gomaResponse()->getBody()->setBody($body);
	}

	/**
	 * disables parsing
	 *
	 * @name 	disableParsing
	 * @access 	public
	 * @deprecated
	 */
	public function disableParsing() {
		self::$disabledparsing = true;
	}
	/**
	 * enables parsing
	 *
	 *@name enableParsing
	 *@access public
	 * @deprecated
	 */
	public function enableParsing() {
		self::$disabledparsing = false;
	}

	/**
	 * shows the body with headers
	 *
	 * @deprecated
	 */
	public static function output($body = null)
	{
		if(isset($body))
			self::setBody($body);

		self::gomaResponse()->getBody()->setParseHTML(!self::$disabledparsing);
		self::gomaResponse()->getBody()->setIncludeResourcesInBody(!core::is_ajax());

		self::gomaResponse()->output();
	}

	/**
	 * sets current document cacheable.
	 *
	 * @deprecated
	 * @name 	setCacheable
	 * @access 	public
	 * @param 	timestamp - expires
	 * @param 	timestamp - last modfied
	*/
	public static function setCachable($expires, $last_modfied, $full = false)
	{
		self::gomaResponse()->setCacheHeader($expires, $last_modfied, $full);
	}

	/**
	 * turns browser-cache off
	 *@name unsetCacheable
	 *@access public
	*/
	public static function unsetCacheable()
	{
		self::gomaResponse()->forceNoCache();
	}

	/**
	 * sets the response-header, e.g 200
	 *
	 * @deprecated
	 * @param int $type
	 */
	public static function setResHeader($type)
	{
		self::gomaResponse()->setStatus($type);
	}

	public static function sendHeader() {
		self::gomaResponse()->sendHeader();
	}

	/**
	 * redirects back.
	 *
	 * @deprecated
	 * @param string $url
	 * @param bool $permanent
	 */
	public static function redirect($url, $permanent = false) {
		$response = GomaResponse::redirect($url, $permanent);
		if(Core::is_ajax())
		{
			if(isset($_GET["ajaxfy"]) || isset($_GET["dropdownDialog"])) {
				$response->redirectByJavaScript($url);
			}
		}

		$response->output();
		Core::callHook("onBeforeShutdown");
		exit;
	}
}
