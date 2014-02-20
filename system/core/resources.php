<?php
/**
 * @package		Goma\System\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined("IN_GOMA") OR die();


ClassInfo::AddSaveVar("Resources", "names");
ClassInfo::AddSaveVar("Resources", "scanFolders");

/**
 * This class manages all Resources like CSS and JS-Files in a Goma-Page.
 *
 * @package		Goma\System\Core
 * @version		1.5.3
 */
class Resources extends Object {
    
	/**
	 * version of this class
	 *
	 *@var CONST
	*/
	const VERSION = "1.3.5";
	
	/**
	 * defines if gzip is enabled
	 *
	*/
	public static $gzip = false;
	
	/**
	 * this var defines if debug is enabled
	 *
	 *@var bool
	*/
	private static $debug = false;
	
	/**
	 * folders to scan to class-info
	 *
	 *@var array
	*/
	public static $scanFolders = array(
		SYSTEM_TPL_PATH,
		APPLICATION_TPL_PATH
	);
	
	/**
	 * default less-vars.
	*/
	static $lessVars = "default.less";
	
	/**
	 * enables debug
	 *
	*/
	public static function enableDebug() {
		self::$debug = true;
	}
	
	/**
	 * disables debug
	 *
	*/
	public static function disableDebug() {
		self::$debug = false;
	}
	
	/**
	 * this var contains all javascript-resources
	 *
	 *@var array
	*/
	private static $resources_js = array();
	
	/**
	 * this var contains all css-resources
	 *
	 *@var array
	*/
	private static $resources_css = array();
	
	/**
	 * this var contains names for special resources
	 *
	 *@var array
	*/
	public static $names = array();
	
	/**
	 * raw data
	 *
	 *@var array
	*/
	private static $resources_data = array();
	
	/**
	 * raw js code
	 *
	 *@var array
	*/
	private static $raw_js = array();
	
	/**
	 * if cache was updates this request
	 *
	*/
	public static $cacheUpdated = false;
	
	/**
	 * adds a special name
	 *@param string - name
	 *@param string - file
	*/
	public static function addName($name, $file)
	{
			self::$names[$name] = $file;
	}
	
	/**
	 * cache for css-default-diretory
	*/
	private static $default_directory_contents = false;
	
	/**
	 * registered resources
	 *
	 *@name registeredResources
	*/
	protected static $registeredResources = array("js" => array(), "css" => array());
	
	/**
	 * resets the resources
	 *
	 *@name reset
	*/
	public static function reset() {
		self::$registeredResources = array("js" => array(), "css" => array());
		self::$raw_js = array();
		self::$resources_data = array();
		self::$resources_css = array();
		self::$resources_js = array();
	}
	
	/**
	 * add-functionality
	 *
	 *@param string - name: special name; name of gloader-resource @see gloader; filename
	 *@param resource-type
	 *@param combine-name
	*/
	public static function add($content, $type = false, $combine_name = "", $lessVars = null) {
		if (PROFILE) Profiler::mark("resources::Add");
		
		if (Core::is_ajax() || isset($_GET["debug"])) {
			self::enableDebug();
		}
		// special names
		if (isset(self::$names[$content])) {
			$content = self::$names[$content];
		}
		
		if (isset(gloader::$resources[$content])) {
			gloader::load($content);
			return true;
		}
		
		// find out type if not set
		if ($type === false) {
			if (checkFileExt($content, "css")) {
				$type = "css";
			} else {
				$type = "js";
			}
		}
		
		$content = str_replace("//", "/", $content);
		
		$type = strtolower($type);
		
		// check for common places
		if ($path = self::getFilePath($content)) {
			$content = $path;
			$path = true;
		}
		
		// check for ROOT in Path
		if (substr($content, 0, strlen(ROOT)) == ROOT)
			$content = substr($content, strlen(ROOT));
		
		switch($type) {
			case "css":
			case "style":
			case "stylesheet":
			
			
				if(checkFileExt($content, "php") || !self::file_exists(ROOT . $content)) {
					self::registerLoaded("css", $content);
 	 				
 	 				// register
 	 				self::$resources_css["default"]["files"][$content] = $content;
 	 				if (isset($lessVars)) {
						self::$resources_css["default"]["less"][$content] = $lessVars;
					}
					
					break;
				}
				
				$combineName = ($combine_name == "main") ? "main" : "combine";
				
				
				if (!isset(self::$resources_css["main"]["mtime"])) {
					self::$resources_css["main"]["mtime"] = filemtime(ROOT . $content);
				} else {
					$mtime = filemtime(ROOT . $content);
					if (self::$resources_css["main"]["mtime"] < $mtime) {
						self::$resources_css["main"]["mtime"] = $mtime;
					}
					unset($mtime);
				}
				self::$resources_css["main"]["files"][$content] = $content;
				
				if (isset($lessVars)) {
					if($lessP = self::getFilePath($lessVars)) {
						self::$resources_css["main"]["less"][$content] = $lessP;
					}
				}
				
			break;
			case "script":
			case "js":
			case "javascript":
				if ($combine_name != "" && !checkFileExt($content, "php") && $path === true /* file exists */) {
					// last modfied of the whole block
					if (!isset(self::$resources_js[$combine_name])) {
						self::$resources_js[$combine_name] = array(
							"files"	 	=> array(),
							"mtime"			=> filemtime(ROOT . $content),
							"raw"			=> array(),
							"name"			=> $combine_name
						);
					} else {
						$mtime = filemtime(ROOT . $content);
						if (self::$resources_js[$combine_name]["mtime"] < $mtime) {
							self::$resources_js[$combine_name]["mtime"] = $mtime;
						}
					}
					self::$resources_js[$combine_name]["files"][$content] = $content;
				} else {
					
					if ($combine_name == "main") {
						if (!isset(self::$resources_js["main"])) {
 	 					  self::$resources_js["main"] = array("files" => array());
						}
						self::$resources_js["main"]["files"][$content] = $content;
					} else {
						if (!isset(self::$resources_js["default"])) {
	 					   self::$resources_js["default"] = array();
 	 				 	 }
						self::$resources_js["default"]["files"][$content] = $content;
					}
					
  	 			
				}
			break;
		}
	
		if (PROFILE) Profiler::unmark("resources::Add");
	}
	
	/**
	 * registers a file as loaded
	 *
	 *@param string - type
	 *@param string - path
	*/
	static function registerLoaded($type, $path) {
		$type = (strtolower($type) == "css") ? "css" : "js";
		// register in autoloader
		if (file_exists($path))
			self::$registeredResources[$type][$path."?".filemtime($path)] = $path."?".filemtime($path);
		else
			self::$registeredResources[$type][$path] = $path;
	}
	
	/**
	 * checks the file-path
	 *
	*/
	public static function getFilePath($path) {
		if (self::file_exists($path))
			return $path;
		
		if (self::file_exists(tpl::$tplpath . Core::getTheme() . "/" . $path)) {
			return tpl::$tplpath . Core::getTheme() . "/" . $path;
		} else if (self::file_exists(APPLICATION_TPL_PATH . "/" . $path)) {
			return APPLICATION_TPL_PATH  . "/".  $path;
		} else if (self::file_exists(SYSTEM_TPL_PATH . "/" . $path)) {
			return SYSTEM_TPL_PATH . "/" . $path;
		} else if(SYSTEM_TPL_PATH . "/css/" . $path) {
			return SYSTEM_TPL_PATH . "/css/" . $path;
		} else {
			return false;
		}
	}
	
	/**
	 * adds some javascript code
	 *@param string - js
	*/
	public static function addJS($js, $combine_name = "scripts") {	
		if ($combine_name != "") {
			if (!isset(self::$resources_js[$combine_name])) {
				self::$resources_js[$combine_name] = array("files" => array(), "raw" => array(), "mtime"	=> 1, "name"	=> $combine_name);
			}
			self::$resources_js[$combine_name]["raw"][] = $js;
		} else {
			self::$raw_js[] = $js;
		}
	}
	
	/**
	 * adds some css code
	 *@param string - js
	*/
	public static function addCSS($css) {
		self::$resources_css["raw"]["data"][] = $css;
	}
	
	/**
	 * if you want to use some data in your scripts, which is from the database you can add it here
	 *@param string - javascript-code
	*/
	public static function addData($js) {
	
		self::$resources_data[md5($js)] = $js;
	}
	
	/**
	 * gets the resources
	 *
	*/
	public static function get($css = true, $js = true) {
		if (PROFILE) Profiler::mark("Resources::get");
		
		if($path = self::getFilePath(self::$lessVars)) {
			self::$lessVars = $path;
		} else {
			throw new LogicException("static \$lessVars must be an existing less-file.");
		}
		
		// generate files
		$files = self::generateFiles($css, $js);
		$js = $files[1];
		$css = $files[0];
		
		if ($js && self::$registeredResources["js"])
			self::$resources_data[] = "goma.ui.registerResources('js', ".json_encode(array_values(self::$registeredResources["js"])).");";
		
		if ($css && self::$registeredResources["css"])
			self::$resources_data[] = "goma.ui.registerResources('css', ".json_encode(array_values(self::$registeredResources["css"])).");";

		if (Core::is_ajax()) {
			// write data to file
			if($js) {
				$datajs = implode("\n", self::$resources_data);
				FileSystem::Write(ROOT . CACHE_DIRECTORY . "/data.".md5($datajs).".js",$datajs);
				$js = array_merge(array(CACHE_DIRECTORY . "/data.".md5($datajs).".js"), $js);
			}
			
			return array("css"	=> $css, "js"	=> $js);
		} else {
			
			// now render
			$html = "";
			
			if($css) {
				if (isset($css["files"])) {
					foreach($css["files"] as $file) {
						$html .= "			<link rel=\"stylesheet\" type=\"text/css\" href=\"".ROOT_PATH . $file."\" />\n";
					}
					unset($css["files"]);
				}
				foreach($css as $key => $file) {
					$html .= "			<link rel=\"stylesheet\" type=\"text/css\" href=\"".ROOT_PATH . $file."\" />\n";
				}
			}
			
			if($js) {
				foreach($js as $file) {
					$html .= "			<script type=\"text/javascript\" src=\"".ROOT_PATH . $file."\"></script>\n";
				}
				
				if(isset(ClassInfo::$appENV["expansion"])) {
				    $file = self::getFileName(CACHE_DIRECTORY . "lang." . Core::$lang . count(i18n::$languagefiles) . count(ClassInfo::$appENV["expansion"]) . ".js");
	    			$cacher = new Cacher("lang_" . Core::$lang . count(i18n::$languagefiles) . count(ClassInfo::$appENV["expansion"]));
	    		} else {
	    		    $file = self::getFileName(CACHE_DIRECTORY . "lang." . Core::$lang . count(i18n::$languagefiles) . ".js");
	    			$cacher = new Cacher("lang_" . Core::$lang . count(i18n::$languagefiles));
	    		}
	    		
	    		if(!file_exists(ROOT . $file) || filemtime(ROOT . $file) < $cacher->created) {
	    		    FileSystem::write($file, self::getEncodedString('setLang('.json_encode($GLOBALS["lang"]).');'));
	    		}
	    		
	    		$html .= "			<script type=\"text/javascript\" src=\"".ROOT_PATH . $file."?".filemtime(ROOT . $file)."\"></script>\n";
				
				// generate data
				$datajs = implode("\n			", self::$resources_data);
				
				$html .= "\n\n
			<script type=\"text/javascript\">
			// <![CDATA[
				".$datajs."
			// ]]>
			</script>";
			}
			
			
			if (PROFILE) Profiler::unmark("Resources::get");
			return $html;
		}
		
		
	}
	
	/**
	 * generates a css file given by combined data
	 *
	*/
	public static function generateCSSFile($combine_css, $name = "",  &$css_files) {

		$lessFiles = array(self::$lessVars);
		
		if(isset($combine_css["less"])) {
			$lessFiles = array_merge($lessFiles, $combine_css["less"]);
		}
		
		$lessStr = "";
		foreach($lessFiles as $file) {
			$lessStr .= $file . "?" . filemtime($file);
		}
		
		$debugStr = (self::$debug) ? "debug" : "";
	
		$file = self::getFileName(CACHE_DIRECTORY . "css_".$name."_".md5($lessStr)."_".md5(implode("_", $combine_css["files"]))."_".$combine_css["mtime"]."_".preg_replace('/[^a-zA-Z0-9_]/', '_', self::VERSION).$debugStr.".css");
		
		
		if (is_file($file)) {
			$css_files[] = $file;
		} else {
			// generate css-file
			$css = "/**
 *@builder goma resources ".self::VERSION."
 *@license to see license of the files, go to the specified path for the file
*/\n\n";
			foreach($combine_css["files"] as $cssfile) {
				
				$less = isset($combine_css["less"][$cssfile]) ? $combine_css["less"][$cssfile] : self::$lessVars;
				$cachefile = ROOT . CACHE_DIRECTORY  . ".cache.".md5($less)."." . md5($cssfile) . $debugStr . ".css";
				if (self::file_exists($cachefile) && filemtime($cachefile) > filemtime(ROOT . $cssfile)) {
					$css .= file_get_contents($cachefile);
				} else {
					$data = "/* file ". $cssfile ." with $less */\n\n";
					
					$data .= trim(self::parseCSS(file_get_contents($less) . "\n\n" . file_get_contents(ROOT . $cssfile), $cssfile, ROOT_PATH)) . "\n\n";
					$css .= $data;
					FileSystem::Write($cachefile, $data);
				}
				unset($cfile, $data, $cachefile);
			}
			FileSystem::Write($file,self::getEncodedString($css));
			$css_files[] = $file;
			unset($filepointer, $css);
		}
		unset($combine_css, $file, $css_mtime);
	}
	
	/**
	 * this method generates all filename and gives them back
	 *
	*/
	public static function generateFiles($css = true, $js = true) {
		
		if (PROFILE) Profiler::mark("Resources::generateFiles");
		$css_files = array();
		$js_files = array();
		
		if($css) {
			// css
			
			if (isset(self::$resources_css["default"])) {
				$css_files = array_merge($css_files, self::$resources_css["default"]);
			}
			// normal combines
			if (isset(self::$resources_css["combine"])) {
				self::generateCSSFile(self::$resources_css["combine"], "combine", $css_files);
			}
			// main combines
			if (isset(self::$resources_css["main"])) {
	 			self::generateCSSFile(self::$resources_css["main"], "main", $css_files);
			}
		}

		if($js) {
			if(Core::is_ajax()) {
				// javascript
				$resources_js = self::$resources_js;
	
	
				if(isset($resources_js["main"]["files"])) {
					$js_files = array_merge(array_values(self::$resources_js["main"]["files"]), $js_files);
				}
				if(isset($resources_js["default"]["files"])) {
					$js_files = array_merge(array_values(self::$resources_js["default"]["files"]), $js_files);
				}
				
				
				// raw
				if(isset($resources_js["default"]["raw"])) {
					self::$raw_js = array_merge(self::$raw_js, self::$resources_js["default"]["raw"]);
				}
				if(isset($resources_js["main"]["raw"])) {
					self::$raw_js = array_merge(self::$raw_js, self::$resources_js["main"]["raw"]);
				}
				
				
				unset($resources_js["main"]);
				unset($resources_js["default"]);
	
	
				foreach($resources_js as $data) {
					if(isset($data["files"])) {
						$js_files = array_merge($data["files"], $js_files);
					}
					
					if(isset($data["raw"])) {
						self::$raw_js = array_merge(self::$raw_js, $data["raw"]);
					}
				}
				
				foreach($js_files as $k => $f) {
					if(file_exists($f)) {
						$js_files[$k] = $f . "?" . filemtime($f);
					}
				}
	
				
	
				if(PROFILE) Profiler::mark("Resources::get");
				// we have to make raw-file
				if(count(self::$raw_js) > 0) {
					$file = self::getFilename(CACHE_DIRECTORY . "/raw_".md5(implode("", self::$raw_js)).".js");
					if(!is_file(ROOT . $file)) {
							$js = "";
							foreach(self::$raw_js as $code) {
								$js .= "/* RAW */\n\n";
								$js .= self::jsMin($code) . "\n\n";
							}
							FileSystem::Write($file,self::getEncodedString($js));
							$js_files[] = $file;
					} else {
							$js_files[] = $file;
					}
				}
			} else {
				// javascript
				$resources_js = self::$resources_js;
				// main
				if (isset($resources_js["main"])) {
						$js_files[] = self::makeCombiedJS($resources_js["main"]);
						unset($resources_js["main"]);
				}
				
				// default
				if (isset($resources_js["default"])) {
						foreach($resources_js["default"]["files"] as $jsfile) {
							if (file_exists($jsfile)) {
								$js_files[] = $jsfile . "?" . filemtime($jsfile);
							} else {
								$js_files[] = $jsfile;
							}
							self::registerLoaded("js", $jsfile);
						}
						unset($resources_js["default"], $jsfile);
				}
				
				// all others
				foreach($resources_js as $combine_name) {
					$js_files[] = self::makeCombiedJS($combine_name);
				}
				
				$debugStr = (self::$debug) ? "debug" : "";
				
				// we have to make raw-file
				if (count(self::$raw_js) > 0) {
					$file = self::getFileName(ROOT . CACHE_DIRECTORY . "raw_".md5(implode("", self::$raw_js))."_".preg_replace('/[^a-zA-Z0-9_]/', '_', self::VERSION).$debugStr.".js");
					if (!is_file($file)) {
							$js = "";
							foreach(self::$raw_js as $code) {
								$js .= "/* RAW */\n\n";
								$js .= self::jsMIN($code) . "\n\n";
							}
							FileSystem::Write($file,self::getEncodedString($js));
							$js_files[] = $file;
					}
				}
				
				// raw JS
				if (isset(self::$resources_css["raw"]["data"]) && count(self::$resources_css["raw"]["data"]) > 0) {
					$css = implode("\n\n", self::$resources_css["raw"]["data"]);
					$filename = self::getFileName(CACHE_DIRECTORY . "/raw_" . md5($css) . ".css");
					if (!is_file(ROOT . $filename)) {
						FileSystem::Write($filename,self::getEncodedString($css));
						$css_files[] = $filename;
					} else {
						$css_files[] = $filename;
					}
				}
				usort($js_files, array("Resources", "sortjs"));
			}
		}

		if (PROFILE) Profiler::unmark("Resources::generateFiles");
		return array($css_files, $js_files);
		
	}
	
	/**
	 * sorts js files, main at first and scripts at last
	*/
	public static function sortJS($a, $b) {
		if (preg_match("/main/", $a)) 
			return -1;
			
		if (preg_match("/main/", $b)) 
			return 1;
		
		if (preg_match("/data/", $a)) 
			return -1;
			
		if (preg_match("/data/", $b)) 
			return 1;
		
		if (preg_match("/scripts/", $a)) 
			return 1;
			
		if (preg_match("/scripts/", $b)) 
			return -1;
			
		if (preg_match("/raw/", $a)) 
			return 1;
			
		if (preg_match("/raw/", $b)) 
			return -1;
			
		return 0;
	}
	
	/**
	 * makes a combined javascript-file
	 *
	 *@param data-array
	*/
	public static function makeCombiedJS($data) {
		if (PROFILE) Profiler::mark("Resources::makeCombinedJS");
		
		if (isset($data["raw"])) {
			$hash = md5(implode("", $data["files"])) . md5(implode("", $data["raw"]));
		} else {
			$hash = md5(implode("", $data["files"]));
		}
		
		$debugStr = (self::$debug) ? "debug" : "";
		
		$file = self::getFileName(CACHE_DIRECTORY . "js_combined_".$data["name"]."_".$hash."_".$data["mtime"]."_".preg_replace('/[^0-9a-zA-Z_]/', '_', self::VERSION).$debugStr.".js");
		if (self::file_exists($file)) {
			return $file;
		} else {
			// remake file
			$js = "/**
 *@builder goma resources ".self::VERSION."
 *@license to see license of the files, go to the specified path for the file 
*/\n\n";
			foreach($data["files"] as $jsfile) {
				$cachefile = ROOT . CACHE_DIRECTORY . ".cache.".md5($jsfile).".".self::VERSION.$debugStr.".js";
				if (self::file_exists($cachefile) && filemtime($cachefile) > filemtime(ROOT . $jsfile)) {
					$js .= file_get_contents($cachefile);
				} else {
					$jsdata = "/* File ".$jsfile." */\n";
					
					$jsdata .= self::jsMIN(file_get_contents(ROOT . $jsfile)) . ";\n\n";
					$js .= $jsdata;
  	 				FileSystem::Write($cachefile,$jsdata);
				}
				unset($cfile, $jsdata, $cachefile);
			}
			
			if (isset($data["raw"])) {
				foreach($data["raw"] as $code) {
					if (strlen($code) > 4000) {
						$cachefile = ROOT . CACHE_DIRECTORY . ".cache.".md5($code).$debugStr.".js";
						if (self::file_exists($cachefile)) {
							$js .= file_get_contents($cachefile);
						} else {
							$jsdata = "/* RAW */\n\n";
							$jsdata .= self::jsMIN($code) . ";\n\n";
							$js .= $jsdata;
							FileSystem::Write($cachefile,$jsdata);
						}
						unset($cfile, $jsdata, $cachefile);
					} else {
						$js .= "/* RAW */\n\n";
						$js .= self::jsMIN($code) . ";\n\n";
					}
				}
			}
			
			$files = array();
			if (isset($data["files"]))
				foreach((array) $data["files"] as $jsfile) {
					if (file_exists($jsfile))
						$files[] = $jsfile . "?" . filemtime($jsfile);
					else
						$files[] = $jsfile;
				}
			
			if (count($files) > 0) {
				$js .= "goma.ui.registerResources('js', ".json_encode($files).");";
			}
			
			FileSystem::Write($file,self::getEncodedString($js));
			unset($filepointer, $js);
			if (PROFILE) Profiler::unmark("Resources::generateFiles");
			return $file;
		}
	}
	
	/**
	 * cache for following functions
	 *
	*/
	private static $extCache = false;
	
	/**
	 * gets the filename
	 *
	*/
	public static function getFileExt() {
		if (self::$extCache === false) {
			$gzip = self::$gzip;
			// first check if defalte is available
			// defalte is 21% faster than normal gzip
			if ($gzip != 0 && request::CheckBrowserDeflateSupport() && function_exists("gzdeflate")) {
				self::$extCache = ".gdf";			
			// if not, check if gzip
			} else if ($gzip != 0 && request::CheckBrowserGZIPSupport() && function_exists("gzencode")) {
				self::$extCache = ".ggz";
			// else send normal file
			} else {
				self::$extCache = "";
			}
		}
		return self::$extCache;
	}
	
	/**
	 * gets full file
	 *
	 *@param string - file
	*/
	public static function getFileName($file) {
		$ext = self::getFileExt();
		if (checkFileExt($file, "js")) {
			return substr($file, 0, -3) . $ext . ".js";
		}
		
		if (checkFileExt($file, "css")) {
			return substr($file, 0, -4) . $ext . ".css";
		}
		
		return $file . $ext;
	}
	
	/**
	 * gets the string encoded
	 *
	*/
	public static function getEncodedString($data) {
		$ext = self::getFileExt();
		if ($ext == ".gdf") {
			return gzdeflate($data);
		} else if ($ext == ".ggz") {
			return gzencode($data);
		} else {
			return $data;
		}
	}
	
	/**
	 * bacause the background-image-locations arent't right anymore, we have to correct them
	 *
	*/
	public static function parseCSS($css, $file, $base) {
		$path = substr($file, 0, strrpos($file, '/'));
		if (preg_match('/^' . preg_quote($base, "/") . "/", $path)) {
				$path = substr($path, strlen($base));
		}
		
		preg_match_all('/url\(((\'|"|\s?)(.*)(\"|\'|\s?))\)/Usi', $css, $matches);
		foreach($matches[3] as $key => $url) {
			$css = str_replace($matches[0][$key], 'url("' . $base . $path . "/" .$url . '")', $css);
		}
		
		try {
			$less = new lessc;
			$css = $less->compile($css);
		} catch(Exception $e) {
			log_exception($e);
		}
		
		if(self::$debug)
			return $css;
		
		return CSSMin::minify($css);
	}
	
	/**
	 * checks if a file exists
	 * for optional further caching
	 *
	*/ 
	public static function file_exists($file) {
  	 
		if (isset($_GET["flush"]) && self::$cacheUpdated === false) {
			Object::instance("Resources")->generateClassInfo();
			ClassInfo::write();
		}
		
		if (defined("CLASS_INFO_LOADED")) {
			if (!strpos($file, "../") && preg_match('/\.(js|css|html)$/i', $file) && substr($file, 0, strlen(SYSTEM_TPL_PATH)) == SYSTEM_TPL_PATH || substr($file, 0, strlen(APPLICATION_TPL_PATH)) == APPLICATION_TPL_PATH) {
	 		   return isset(ClassInfo::$class_info["resources"]["files"][$file]);
  	 		 }
  	 	} else {
  	 		logging("CLASS_INFO not loaded for file ".$file.". using filesystem. -> poor performance");
  	 	}
  	 	
		return file_exists($file);
	}
	
	/**
	 * generates the Class-Info
	 *
	*/
	public function generateClassInfo() {
		if (PROFILE) Profiler::mark("Resources::GenerateClassInfo");
		
		// scan directories
		ClassInfo::$class_info[$this->classname]["files"] = array();
		foreach(self::$scanFolders as $folder) {
			$this->scanToClassInfo($folder);
		}
		
		// expansion
		if (isset(ClassInfo::$appENV["expansion"]) && is_array(ClassInfo::$appENV["expansion"]))
		foreach(ClassInfo::$appENV["expansion"] as $name => $data) {
			if (isset($data["viewFolder"])) {
				$this->scanToClassInfo($data["folder"] . $data["viewFolder"]);
			} else if (file_exists($data["folder"] . "views")) {
				$this->scanToClassInfo($data["folder"] . "views");
			}
		}
		
		
		$this->callExtending("generateClassInfo");
		
		self::$cacheUpdated = true;
		if (PROFILE) Profiler::unmark("Resources::GenerateClassInfo");
	}
	
	/**
	 * scan's directories to class-info
	 *
	 *@param string - dir
	*/
	public function scanToClassInfo($dir) {
		if (file_exists($dir))
			foreach(scandir($dir) as $file) {
				if (is_dir($dir . "/" . $file) && $file != "." && $file != "..") {
					$this->scanToClassInfo($dir . "/" . $file);
				} else { 
					if (preg_match('/\.(js|css|html|less)$/i', $file)) {
						ClassInfo::$class_info[$this->classname]["files"][$dir . "/" . $file] = true;
					}
				}
				
			}
	}
	
	/**
	 * minifies JS.
	*/
	static function jsMIN($js) {
		return (self::$debug) ? $js : jsmin::minify($js);
	}
}
