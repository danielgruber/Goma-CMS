<?php
/**
  * as simple class to parse a URL and hold POST and GET-vars 
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 29.09.2012
  * $Version 2.0.1
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Request extends Object
{
		/**
		 * url of the request
		 *@name url
		 *@access public
		*/
		public $url;
		
		/**
		 * the method of this request
		 * POST, GET, PUT, DELETE or HEAD
		 *@name request_method
		 *@access public
		*/
		public $request_method;
		
		/**
		 * all params
		 *@name all_params
		 *@access public
		*/
		public $all_params = array();
		
		/**
		 * current params
		 *@name params
		 *@access public
		*/
		public $params = array();
		
		/**
		 * get params
		 *@name get_params
		 *@access public
		*/
		public $get_params = array();
		
		/**
		 * post params
		 *@name post_params
		 *@access public
		*/
		public $post_params = array();
		
		/**
		 * url-parts
		 *@name url_parts
		 *@access public
		*/
		public $url_parts = array();
		
		/**
		 * this var contains a sizeof params, which were parsed but not shifted
		 *@name unshiftedButParsedParams
		 *@access public
		*/
		public $unshiftedButParsedParams = 0;
		
		/**
		 * shifted path until now
		 *
		 *@name shiftedPart
		 *@access public
		*/
		public $shiftedPart = "";
		
		/**
		 *@name __construct
		 *@param string - Request-method
		*/
		public function __construct($method, $url, $get_params = array(), $post_params = array())
		{
				parent::__construct();
				
				
				$this->request_method = $method;
				$this->url = $url;
				$this->get_params = ArrayLib::map_key("strtolower", $get_params);
				$this->post_params = ArrayLib::map_key("strtolower", $post_params);
				$this->url_parts = explode('/', $url);
				
		}
		/**
		 * checks if POST
		 *@name isPOST
		 *@access public
		*/
		public function isPOST()
		{
				return ($this->request_method == "POST");
		}
		/**
		 * checks if GET
		 *@name isGET
		 *@access public
		*/
		public function isGET()
		{
				return ($this->request_method == "GET");
		}
		/**
		 * checks if PUT
		 *@name isPUT
		 *@access public
		*/
		public function isPUT()
		{
				return ($this->request_method == "PUT");
		}
		/**
		 * checks if DELETE
		 *@name isDELETE
		 *@access public
		*/
		public function isDELETE()
		{
				return ($this->request_method == "DELETE");
		}
		/**
		 * checks if DELETE
		 *@name isDELETE
		 *@access public
		*/
		public function isHEAD()
		{
				return ($this->request_method == "HEAD");
		}
		/**
		 * array-implementations
		*/
		
		/**
		 * gets a POST or GET-Param
		 *@name offsetGet
		 *@access public
		*/
		public function offsetGet($offset)
		{
				if(isset($this->get_params[$offset])) return $this->get_params[$offset];
				if(isset($this->post_params[$offset])) return $this->post_params[$offset];
				return false;
		}
		/**
		 * checks if POST or GET param exists
		 *@name offsetExists
		 *@access public
		*/
		public function offsetExists($offset)
		{
				if(isset($this->get_params[$offset])) return true;
				if(isset($this->post_params[$offset])) return true;
				return false;
		}
		
		public function offsetUnset($offset){}
		public function offsetSet($offset, $value){}
		
		/**
		 * matches the data with the url
		 *@name match
		 *@access public
		*/
		public function match($pattern, $shiftOnSuccess = false, $class = null)
		{
				if(PROFILE) Profiler::mark("request::match");
				// class check
				if(preg_match("/^([a-zA-Z0-9_]+)\:([a-zA-Z0-9\$_\-\/\!\s]+)$/si", $pattern, $matches))
				{
						$_class = $matches[1];
						$pattern = $matches[2];
						if(strtolower($_class) != $class)
							return false;
				}
				
				if(preg_match("/^(POST|PUT|DELETE|HEAD|GET)\s+([a-zA-Z0-9\$_\-\/\!]+)$/Usi", $pattern, $matches))
				{
						$method = $matches[1];
						$pattern = $matches[2];
						if(!call_user_func_array(array($this, "is" . $method), array()))
						{
								Profiler::unmark("request::match");
								return false;
						}
				}
				
				if(substr($pattern, -1) == "/") {
					$pattern = substr($pattern, 0, -1);
				}
				
				if(_ereg("^//",$pattern)) {
					$shiftOnSuccess = false;
					$pattern = substr($pattern, 2);
				}
				
				// // is the point to shift the url
				$shift = strpos($pattern, "//");
				if($shift)
				{
						$shiftCount = substr_count(substr($pattern, 0, $shift), '/') + 1;
						$pattern = str_replace('//', '/', $pattern);
				} else
				{
						$shiftCount = count(explode("/", $pattern));
				}
				
				$patternParts = explode("/", $pattern);
				
				
				$params = array();
				foreach($patternParts as $part)
				{
						if(isset($i))
						{
								$i++;
						} else
						{
								$i = 0;
						}
						
						// vars
						if(isset($part{0}) && $part{0} == '$')
						{
								if(substr($part, -1) == '!')
								{
										$required = true;
										if(!isset($this->url_parts[$i]) || $this->url_parts[$i] == "")
										{
												if(PROFILE) Profiler::unmark("request::match");
												return false;
										}
										$name = substr($part, 1, -1);
								} else
								{
										$required = false;
										if(!isset($this->url_parts[$i]))
										{
												continue;
										}
										$name = substr($part, 1);
								}	
								
								$data = $this->url_parts[$i];
								if($name == "controller" && !classinfo::exists($data))
								{
										if(PROFILE) Profiler::unmark("request::match");
										return false;
								}
								
								$params[strtolower($name)] = $data;							
						} else 
						{
								// literal parts are important!
								if(!isset($this->url_parts[$i]))
								{
										if(PROFILE) Profiler::unmark("request::match");
										return false;
								}
								
								if(strtolower($this->url_parts[$i]) != strtolower($part))
								{
										if(PROFILE) Profiler::unmark("request::match");
										return false;
								}
								
								$params[$i] = $this->url_parts[$i];
						}
				}
				
				if($shiftOnSuccess)
				{
						$this->shift($shiftCount);
						$this->unshiftedButParsedParams = count($this->url_parts) - $shiftCount;
				}
				
				
				$this->params = $params;
				$this->all_params = array_merge($this->all_params, $params);
				
				if($params === array()) $params['_matched'] = true;
				if(PROFILE) Profiler::unmark("request::match");
				return $params;
				
		}
		/**
		 * shifts remaining url-parts
		 *@name shift
		 *@access public
		 *@param numeric - show much to shift
		*/
		public function shift($count)
		{				
				for($i = 0; $i < $count; $i++)
				{
					if($this->shiftedPart == "") {
						$this->shiftedPart .= array_shift($this->url_parts);
					} else {
						$this->shiftedPart .= "/" . array_shift($this->url_parts);
					}
						
				}
		}
		/**
		 * gets a param
		 *@name getParam
		 *@access public
		*/
		public function getParam($param, $useall = true)
		{
				$param = strtolower($param);
				if(strtolower($useall) == "get") {
					return isset($this->get_params[$param]) ? $this->get_params[$param] : null;
				}
				
				if(strtolower($useall) == "post") {
					return isset($this->post_params[$param]) ? $this->post_params[$param] : null;
				}
				
				if(isset($this->params[$param]))
				{
						return utf8_encode($this->params[$param]);
				} else if(isset($this->get_params[$param]))
				{
						return $this->get_params[$param];
				}  else if(isset($this->post_params[$param]))
				{
						return $this->post_params[$param];
				}  else if(isset($this->all_params[$param]) && $useall)
				{
						return $this->all_params[$param];
				} else
				{
						return null;
				}
		}
		/**
		 * gets the remaining parts
		 *@name remaining
		 *@access public
		*/
		public function remaining()
		{
				return implode("/",$this->url_parts);
		}
		/**
		 * checks if ajax response is needed
		 *@name isJSResponse
		 *@access public
		*/
		public static function isJSResponse()
		{
				return (Core::is_ajax() && (isset($_GET["ajaxfy"]) || isset($_POST["ajaxfy"])));
		}
		/**
		 * checks if is ajax
		 *@name is_ajax
		 *@access public
		*/
		public static function is_ajax()
		{
				return Core::is_ajax();
		}
		/**
		 * Checks whether the browser supports GZIP (it should send in this case the header Accept-Encoding:gzip)
		 * @return bool If true the browser supports it, otherwise not 
		 */
		public static function CheckBrowserGZIPSupport()
		{
				if(file_exists(ROOT . ".htaccess"))
					if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
					{
							if ((stripos($_SERVER['HTTP_ACCEPT_ENCODING'],"gzip")!==false))
									return true;
							else
									return false;
					}
					else
					{
							return false;
					}
					return false;
		}
		/**
		 * Checks whether the browser supports deflate (it should send in this case the header Accept-Encoding:deflate)
		 * @return bool If true the browser supports it, otherwise not 
		 */
		public static function CheckBrowserDeflateSupport()
		{
			if(file_exists(ROOT . ".htaccess"))
				if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
				{
						if ((stripos($_SERVER['HTTP_ACCEPT_ENCODING'],"deflate")!==false))
								return true;
						else
								return false;
				}
				else
				{
						return false;
				}
				return false;
		}
}