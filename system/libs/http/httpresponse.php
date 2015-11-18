<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 05.04.2014
  * $Version: 2.1.8
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTTPResponse
{
	/**
	 * defines whether HTTPResponse should parse response with HTMLParser-Class
	 * before it is sent to client.
	 * default is that is parses.
	 *
	 * @name 	disabledparsing
	 * @var 	bool
	*/
	public static $disabledparsing = false;

	/**
	 * responsetypes
	 *@name restypes
	 *@var array
	*/
	public static $restypes = array(
			200 	=> 'OK',
			201		=> 'Created',
			202		=> 'Accepted',
			204 	=> 'No Content',
			206 	=> 'Partial Content',
			301		=> 'Moved Permanently',
			302  	=> 'Moved Temporarily',
			304		=> 'Not Modified',
			307 	=> 'Temporary Redirect',
			400		=> 'Bad Request',
			401		=> 'Unauthorized',
			403		=> 'Forbidden',	
			404 	=> 'Not Found',
			405		=> 'Method not Allowed',
			406 	=> "Not acceptable",
			410		=> 'Gone',
			500		=> 'Internal Server Error',	
			501		=> 'Not Implemented',
			503		=> 'Service Unavailable',
			505		=> 'HTTP Version Not Supported'
	);

	/**
	 * this array contains headers.
	 *
	 * @access 	public
	 * @var 	array
	*/
	public static $headers = array();

	/**
	 * if is cacheable.
	 *
	 * @name 	cacheable
	 * @var 	bool
	*/
	public static $cacheable = false;

	/**
	 * response.
	 *
	 * @name 	response
	 * @access 	priavte
	*/
	private static $response;

	/**
	 * X-Powered-By.
	 *
	 * @name 	X-Powered-By
	 * @access 	public
	 * @var 	string
	*/
	public static $XPoweredBy;

	/**
	 * the body of the response.
	 *
	 * @access 	private
	 * @var 	string
	*/
	static private $body = "";

	/**
	  * add header
	  * @name 	addHeader
	  * @access public
	  * @param 	string - name
	  * @param 	string - content
	*/		
	public static function addHeader($name, $content)
	{
		self::$headers[strtolower($name)] = $content;
	}

	/**
	  * synonym for @link addHeader
	*/		
	public static function setHeader($name, $content)
	{
		self::$headers[strtolower($name)] = $content;
	}

	/**
	 * removes an header.
	 *
	 * @name 	removeHeader
	 * @access 	public
	 * @param 	string - name
	*/
	public static function removeHeader($name)
	{
		unset(self::$headers[strtolower($name)]);
	}

	/**
	 * sets the body.
	 *
	 * @name 	setBody
	 * @access 	public
	 * @return 	null
	*/
	public static function setBody($body)
	{
			self::$body = $body;
	}

	/**
	 * disables parsing
	 *
	 * @name 	disableParsing
	 * @access 	public
	*/
	public function disableParsing() {
		self::$disabledparsing = true;
	}
	/**
	 * enables parsing
	 *
	 *@name enableParsing
	 *@access public
	*/
	public function enableParsing() {
		self::$disabledparsing = false;
	}
	/**
	 * get body
	 *@name getBody
	 *@access public
	 *@return string
	*/
	public static function getBody()
	{
			if(PROFILE) Profiler::mark("getBody");
			$body = self::$body;
			
			// gloader
			foreach(gloader::$preloaded as $file => $true) {
				Resources::addData("goma.ui.setLoaded('".$file."');");
			}
			
			
			if((!isset(self::$headers["content-type"]) || preg_match("/html/i",self::$headers["content-type"])) && !self::$disabledparsing)
			{
					$body = str_replace('{$_queries}',sql::$queries,$body);
					
					$html = new htmlparser();
					$body = $html->parseHTML($body);
					
			} else if((isset(self::$headers["content-type"]) && preg_match("/json/",self::$headers["content-type"])) && is_array($body))
				{
					$body = json_encode($body);
				}				
			if(Core::is_ajax()) {
				$data = Resources::get();
				self::addHeader("X-JavaScript-Load", implode(";", $data["js"]));
				self::addHeader("X-CSS-Load", implode(";", $data["css"]));
			}
			
			if(PROFILE) Profiler::unmark("getBody");

			return $body;
	}
	/**
	 * shows the body with headers
	 *@name output
	 *@access public
	 *@return null
	*/
	public static function output($body = null)
	{
			if(isset($body))
				self::setBody($body);
			
			$body = self::getBody();
			
			self::sendHeader();
			Core::callHook("onbeforeoutput");
			
			$data = ob_get_contents();
			ob_end_clean();
			
			ob_start("ob_gzhandler");
			
			if(PROFILE) Profiler::mark("sendDataToClient");
			echo $body;
			echo $data;
			if(PROFILE) Profiler::unmark("sendDataToClient");
			
			ob_end_flush();
			
			Core::callHook("onafteroutput");
			
	}
	/**
	 * sends the headers
	 *@name sendHeader
	 *@access public
	 *@return null
	*/
	public static function sendHeader()
	{
			if(!self::$XPoweredBy)
			{
					self::$XPoweredBy	= "Goma ".strtok(GOMA_VERSION, ".")." with PHP " . PHP_MAIOR_VERSION;
			}
			
			HTTPResponse::setHeader("vary", "Accept-Encoding");
			self::addHeader('X-Powered-By', self::$XPoweredBy);
			if(isset(ClassInfo::$appENV["app"]["name"]) && defined("APPLICATION_VERSION"))
				self::addHeader('X-GOMA-APP', ClassInfo::$appENV["app"]["name"] . " " . strtok(APPLICATION_VERSION, "."));
			
			if(!self::$response)
			{
					self::setResHeader(200);
			}

			if(self::$cacheable !== false)
			{
					HTTPResponse::addHeader("Last-Modified", gmdate('D, d M Y H:i:s', self::$cacheable["last_modfied"]).' GMT');
					HTTPResponse::addHeader("Expires", gmdate('D, d M Y H:i:s', self::$cacheable["expires"]).' GMT');		
					if(!isset(self::$headers["cache-control"])) {
						$age = self::$cacheable["expires"] - NOW;
						HTTPResponse::addHeader("cache-control", "public; max-age=".$age."");
						unset($age);
					}
			} else {
				HTTPResponse::addHeader("Last-Modified", gmdate('D, d M Y H:i:s', NOW).' GMT');
				HTTPResponse::addHeader("Expires", gmdate('D, d M Y H:i:s', NOW - 10).' GMT');
				HTTPResponse::addHeader("cache-control", "no-store; no-cache");
			}
			
			if(!isset(self::$headers["content-type"])) {
				self::$headers["content-type"] = "text/html;charset=utf-8";
			}
			
			if(DEV_MODE) {
				global $start;
				$time =  microtime(true) - EXEC_START_TIME;
				self::addHeader("X-Time", $time);
			}
			
			
			
			$endWaitTime = microtime(true);
			defined("END_WAIT_TIME") OR define("END_WAIT_TIME", $endWaitTime);
			
			header('HTTP/1.1 ' . self::$response);
			foreach(self::$headers as $name => $content)
			{
					header($name . ': '. $content);
			}
			
			Core::callHook("sendheader");
	}
	
	/**
	 * sets current document cacheable.
	 *
	 * @name 	setCacheable
	 * @access 	public
	 * @param 	timestamp - expires
	 * @param 	timestamp - last modfied
	*/
	public static function setCachable($expires, $last_modfied, $full = false)
	{
		self::$cacheable = array
		(
			"expires"		=> $expires,
			"last_modfied"	=> $last_modfied
		);
		if($full) {
			HTTPResponse::addHeader("Pragma", "public");
		} else {
			HTTPResponse::addHeader("Pragma", "no-cache");
		}
	}
	/**
	 * if cacheable this function moves last_modfied to the given timestamp if the current last modfied is past the given
	 *@name addLastModfied
	 *@access public
	 *@param timestamp
	*/
	public static function addLastModfied($m)
	{
			if(isset(self::$cacheable["last_modfied"]))
			{
					if(self::$cacheable["last_modfied"] < $m)
					{
							self::$cacheable["last_modfied"] = $m;
							
					}
			}
			return true;
	}
	/**
	 * turns browser-cache off
	 *@name unsetCacheable
	 *@access public
	*/
	public static function unsetCacheable()
	{
			self::$cacheable = false;
			HTTPResponse::addHeader("Pragma", "no-cache");
	}
	/**
	 * turns browser-cache off
	 *@name unsetCachable
	 *@access public
	*/
	public static function unsetCachable()
	{
			self::$cacheable = false;
			HTTPResponse::addHeader("Pragma", "no-cache");
	}
	/**
	 * sets the response-header, e.g 200
	 *@name setresHeader
	 *@access public
	 *@param numeric - errortype
	 *@return bool
	*/
	public static function setResHeader($type)
	{
			if(isset(self::$restypes[$type]))
			{
					self::$response = $type . " " . self::$restypes[$type];
			} else
			{
					return false;
			}
	}
	/**
	 * file upload
	 *@name sendFile
	 *@access public
	 *@param string - filename
	*/
	public static function sendFile($file)
	{
			self::addHeader('content-type', 'application/octed-stream');
			self::addHeader('Content-Disposition', 'attachment; filename="'.basename($file).'"');
			self::addHeader('Content-Transfer-Encoding','binary');
			self::addHeader('Cache-Control','post-check=0, pre-check=0');
			self::addHeader('Content-Length', filesize($file));
	}

	/**
	 * redirects back.
	 *
	 * @param $url
	 * @param bool|false $_301
	 */
	public static function redirect($url, $_301 = false) {
		Core::callHook("beforeRedirect", $url, $_301);

		self::setResHeader($_301 ? 301 : 302);
		if(Core::is_ajax())
		{
			if(Request::isJSResponse() || isset($_GET["dropdownDialog"])) {
				self::setResHeader(200);
				$response = new AjaxResponse();
				$response->exec("window.location.href = " . var_export($url, true) . ";");
				$output = $response->render();
				self::sendHeader();
				echo $output;
				exit;
			}

			if(preg_match('/\?/', $url))
			{
					$url .= "&ajax=1";
			} else
			{
					$url .= "?ajax=1";
			}
		}

		self::addHeader('Location', $url);
		self::sendheader();
		echo '<script type="text/javascript">location.href = "'.addSlashes($url).'";</script><br /> Redirecting to: <a href="'.addSlashes($url).'">'.convert::raw2text($url).'</a>';

		Core::callHook("onBeforeShutdown");
		exit;
	}
}