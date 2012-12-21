<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 12.12.2012
  * $Version: 2.1.6
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTTPresponse extends object
{
		public static $disabledparsing = false;
		/**
		 * my headers
		 *@access public
		 *@var array
		*/
		static public $headers = array();
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
				410		=> 'Gone',
				500		=> 'Internal Server Error',	
				501		=> 'Not Implemented',
				503		=> 'Service Unavailable',
				505		=> 'HTTP Version Not Supported'
		);
		/**
		 * if is cacheable
		 *@name cacheable
		 *@var bool
		*/
		public static $cacheable = false;
		/**
		 * response 
		 *@name response
		 *@access priavte
		*/
		private static $response;
		/**
		 * X-Powered-By
		 *@name X-Powered-By
		 *@access public
		 *@var string
		*/
		public static $XPoweredBy;
		/**
		 * the body of the response
		 *@access private
		 *@var string
		*/
		static private $body = "";
		/**
		  * add header
		  *@name addHeader
		  *@access public
		  *@param string - name
		  *@param string - content
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
		 * removes an header
		 *@name removeHeader
		 *@access public
		 *@param string - name
		*/
		public static function removeHeader($name)
		{
				unset(self::$headers[strtolower($name)]);
		}
		/**
		 * sets the body
		 *@name setBody
		 *@access public
		 *@return null
		*/
		public static function setBody($body)
		{
				self::$body = $body;
		}
		/**
		 * disables parsing
		 *
		 *@name disableParsing
		 *@access public
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
					Resources::addData("gloader.loaded['".$file."'] = true;");
				}
				
				
				if((!isset(self::$headers["content-type"]) || _eregi("html",self::$headers["content-type"])) && !self::$disabledparsing)
				{
						$body = str_replace('{$_queries}',sql::$queries,$body);
						
						$html = new htmlparser();
						$body = $html->parseHTML($body);
						
				} else if((isset(self::$headers["content-type"]) && _eregi("json",self::$headers["content-type"])) && is_array($body))
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
				
				if(PROFILE) Profiler::mark("sendDataToClient");
				echo $body;
				echo $data;
				if(PROFILE) Profiler::unmark("sendDataToClient");
				
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
				
				if(DEV_MODE) {
					global $start;
					$time =  microtime(true) - EXEC_START_TIME;
					self::addHeader("X-Time", $time);
				}
				
				$endWaitTime = microtime(true);
				define("END_WAIT_TIME", $endWaitTime);
				
				header('HTTP/1.1 ' . self::$response);
				foreach(self::$headers as $name => $content)
				{
						header($name . ': '. $content);
				}
				
				Core::callHook("sendheader");
		}
		
		/**
		 * sets current document cacheable
		 *@name setCacheable
		 *@access public
		 *@param timestamp - expires
		 *@param timestamp - last modfied
		*/
		public static function setCachable($expires, $last_modfied, $full = false)
		{
				self::$cacheable = array
				(
					"expires"		=> $expires,
					"last_modfied"	=> $last_modfied
				);
				if($full)
						HTTPResponse::addHeader("Pragma", "public");
				else
						HTTPResponse::addHeader("Pragma", "no-cache");
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
		 * redirects
		 *@name redirect
		 *@param string - to
		 *@param bool - 301?
		*/
		public static function redirect($url, $_301 = false)
		{	
				
				$hash = md5($url . $_SERVER["HTTP_HOST"]);
				if(defined('SPEEDCACHE_ACTIVE'))
				{
						
						
						$file = ROOT . CACHE_DIRECTORY . "/speedcache.".$hash.".php";
						if(file_exists($file))
						{
								unlink($file);
						}
				}
				
				if(!$_301)
				{
						self::setResHeader(302);
				} else
				{
						self::setResHeader(301);
				}
				if(Core::is_ajax())
				{
					if(Request::isJSResponse() || isset($_GET["dropdownDialog"])) {
						self::setResHeader(200);
						$response = new AjaxResponse();
						$response->exec("window.location.href = " . var_export($url, true)) . ";";
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
				echo '<script type="text/javascript">location.href = "'.$url.'";</script><br /> Redirecting to: <a href="'.$url.'">'.$url.'</a>';
				
				Core::callHook("onBeforeShutdown");
				exit;
		}	
}


class htmlparser extends object
{
		/**
		 * parses HTML-code
		 *@name parseHTML
		 *@return string
		*/
		public function parseHTML($html)
		{
				if(PROFILE) Profiler::mark("HTMLParser::parseHTML");
				
				if(PROFILE) Profiler::mark("HTMLParser scriptParse");
				if(!HTTPResponse::$disabledparsing)
				{
						preg_match_all('/\<script[^\>]*\>(.*)\<\/script\s*\>/Usi', $html, $no_tags);
						foreach($no_tags[1] as $key => $js)
						{
								if(!empty($js))
								{
										$html = str_replace($no_tags[0][$key], $this->js($js), $html );
								}
						}

						
						preg_match_all('/\<script[^\>]*src="(.+)"[^>]*\>(.*)\<\/script\s*\>/Usi', $html, $no_tags);
						foreach($no_tags[1] as $key => $js)
						{
								if(!empty($js) && file_exists(ROOT . $js))
								{
										$html = str_replace($no_tags[0][$key], $this->jsFile(ROOT . $js), $html );
								}
						}
				}
				if(PROFILE) Profiler::unmark("HTMLParser scriptParse");
				
				if(!Core::is_ajax())
					if(_eregi('</title>',$html)) {
						if(_eregi('\<base',$html)) {
							$html = str_replace('</title>', "</title>\n		<meta charset=\"utf-8\" />\n\n		<!--Resources-->\n" . resources::get() . "\n", $html);
						} else {
							$html = str_replace('</title>', "</title>\n		<meta charset=\"utf-8\" />\n<base href=\"".BASE_URI."\" />\n\n\n		<!--Resources-->\n" . resources::get() . "\n", $html);
						}
					} else {
						if(_eregi('\<base',$html)) {
							$html = '<meta charset="utf-8" />' . resources::get() . $html;
						} else {
							$html = '<!DOCTYPE html><html><head><meta charset="utf-8" /><title></title><base href="'.BASE_URI.'" />' . "\n".resources::get() . "\n</head><body>" . $html . "\n</body></html>";
						}
					}
				
				if(!HTTPResponse::$disabledparsing)
				{
						$html = self::process_links($html);
				}
				if(PROFILE) Profiler::unmark("HTMLParser::parseHTML");
				return $html;
		}
		
		/**
		  * processes links for non-mod-rewrite
		  *@name process_links
		  *@access public
		  *@param string - html
		*/
		public static function process_links($html)
		{
				if(PROFILE) Profiler::mark("HTMLParser::process_links");
				preg_match_all('/<a([^>]+)href="([^">]+)"([^>]*)>/Usi', $html, $links);
				foreach($links[2] as $key => $href)
				{
						$attrs = "";
						// check http
						if(preg_match('/^(http|https|ftp)/Usi', $href))
						{
								continue;
						}
						if(preg_match('/^#(.+)/', $href, $m))
						{
								$href = URL . URLEND . "#" . $m[1];
								$attrs = ' data-anchor="'.$m[1].'"';
						}
						if(preg_match('/^javascript:/i', $href))
						{
								continue;
						}
						if(preg_match('/^mailto:/', $href))
						{
								continue;
						}
						
						// check ROOT_PATH
						if(preg_match('/^' . preg_quote(ROOT_PATH, '/') . '/Usi', $href))
						{
								$href = substr($href, strlen(ROOT_PATH));
						}
						
						if(!_eregi('\.php/(.*)', $href))
						{
								if(file_exists(ROOT . $href))
								{
										continue;
								}
						}
						
						if(preg_match('/^' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href) || preg_match('/^.\/' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href))
						{
						
						} else
						{
								$href = BASE_SCRIPT . $href;
						}
						$newlink = '<a'.$links[1][$key].'href="'.$href.'"'.$links[3][$key].' '.$attrs.'>';
						$html = str_replace($links[0][$key], $newlink, $html);
				}
				
				preg_match_all('/<iframe([^>]+)src="([^">]+)"([^>]*)>/Usi', $html, $frames);
				foreach($frames[2] as $key => $href)
				{
						// check http
						if(preg_match('/^(http|https|ftp)/Usi', $href))
						{
								continue;
						}
						if(preg_match('/^#/', $href))
						{
								continue;
						}
						// check ROOT_PATH
						if(preg_match('/^' . preg_quote(ROOT_PATH, '/') . '/Usi', $href))
						{
								$href = substr($href, strlen(ROOT_PATH));
						}
						if(!_eregi('\.php/(.+)', $href))
						{
								if(file_exists(ROOT . $href))
								{
										continue;
								}
						}
						if(preg_match('/^' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href) || preg_match('/^.\/' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href))
						{
						
						} else
						{
								$href = BASE_SCRIPT . $href;
						}
						$newframes = '<iframe'.$frames[1][$key].'src="'.$href.'"'.$frames[3][$key].'>';
						$html = str_replace($frames[0][$key], $newframes, $html);
				}
				
				preg_match_all('/<img([^>]+)src="([^">]+)"([^>]*)>/Usi', $html, $images);
				foreach($images[2] as $key => $href)
				{
						if(!preg_match('/^images\/resampled/i', $href)) {
							continue;
						}
						$href = BASE_SCRIPT . $href;
						$newframes = '<img'.$images[1][$key].'src="'.$href.'"'.$images[3][$key].' />';
						$html = str_replace($images[0][$key], $newframes, $html);
				}
				
				if(PROFILE) Profiler::unmark("HTMLParser::process_links");
				return $html;
		}
		
		/**
		 * jshandler
		 *@name jsFile
		 *@return string
		*/
		public function jsFile($file)
		{
				Resources::add($file, "js", "tpl");
		}
		
		/**
		 * jshandler
		 *@name js
		 *@return string
		*/
		public function js($js)
		{
				Resources::addJS($js, "scripts");
		}
		
		/**
		 * csshandler
		 *@name css
		 *@return string
		*/
		public function css($css)
		{
				$name = "hash." . md5($css) . ".css";
				$file = ROOT_PATH . "js/cache/" . $name;
				if(file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
				{
						return '<link rel="stylesheet" href="'.$file.'" type="text/css" />';
				} else
				{
						if($h = fopen($_SERVER['DOCUMENT_ROOT'] . $file, 'w'))
						{
								fwrite($h, $css);
								fclose($h);
								return '<link rel="stylesheet" href="'.$file.'" type="text/css" />';
						} else
						{
								fclose($h);
								return "";
						}
				}
		}
		/**
		* XSS protection
		*@name: protect
		*@param: string - text
		*@use: protect html entitites
		*@return the protected string
		*/
		public function protect($str)
		{
				return htmlentities($str, ENT_COMPAT , "UTF-8");
		}
}