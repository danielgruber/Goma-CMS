<?php
/**
  * this class provides features of handling JavaScript and CSS
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 16.03.2013
  * Version: 1.3.5
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

ClassInfo::AddSaveVar("Resources", "names");
ClassInfo::AddSaveVar("Resources", "scanFolders");

/**
 * new resources class
*/

class Resources extends Object {
    
	/**
	 * version of this class
	 *
	 *@name VERSION
	 *@access public
	 *@var CONST
	*/
	const VERSION = "1.3.5";
	
	/**
	 * defines if gzip is enabled
	 *
	 *@name gzip
	 *@access public
	*/
	public static $gzip = false;
	
	/**
	 * this var defines if combining is enabled
	 *
	 *@name combine
	 *@access private
	 *@var bool
	*/
	private static $combine = true;
	
	/**
	 * folders to scan to class-info
	 *
	 *@name scanFolders
	 *@access public
	 *@var array
	*/
	public static $scanFolders = array(
		SYSTEM_TPL_PATH,
		APPLICATION_TPL_PATH
	);
	
	/**
	 * enables conbining
	 *
	 *@name enableCombine
	 *@access public
	*/
	public static function enableCombine() {
		self::$combine = true;
	}
	
	/**
	 * disables combining
	 *
	 *@name disableCombine
	 *@access public
	*/
	public static function disableCombine() {
		self::$combine = false;
	}
	
	/**
	 * this var contains all javascript-resources
	 *
	 *@name resources_js
	 *@access private
	 *@var array
	*/
	private static $resources_js = array();
	
	/**
	 * this var contains all css-resources
	 *
	 *@name resources_css
	 *@access private
	 *@var array
	*/
	private static $resources_css = array();
	
	/**
	 * this var contains names for special resources
	 *
	 *@name names
	 *@access public
	 *@var array
	*/
	public static $names = array();
	
	/**
	 * raw data
	 *
	 *@name resources_data
	 *@access private
	 *@var array
	*/
	private static $resources_data = array();
	
	/**
	 * raw js code
	 *
	 *@name rawjs
	 *@access private
	 *@var array
	*/
	private static $raw_js = array();
	
	/**
	 * if cache was updates this request
	 *
	 *@name cacheUpdated
	 *@access public
	*/
	public static $cacheUpdated = false;
	
	/**
	 * adds a special name
	 *@name addName
	 *@access public
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
	 * add-functionality
	 *
	 *@name add
	 *@access public
	 *@param string - name: special name; name of gloader-resource @see gloader; filename
	 *@param resource-type
	 *@param combine-name
	*/
	public static function add($content, $type = false, $combine_name = "") {
		if(PROFILE) Profiler::mark("resources::Add");
		
		if(Core::is_ajax() || isset($_GET["debug"])) {
			self::disableCombine();
		}
		// special names
		if(isset(self::$names[$content])) {
			$content = self::$names[$content];
		}
		
		if(isset(gloader::$resources[$content])) {
			gloader::load($content);
			return true;
		}
		
		// find out type if not set
		if($type === false) {
			if(checkFileExt($content, "css")) {
				$type = "css";
			} else {
				$type = "js";
			}
		}
		
		$content = str_replace("//", "/", $content);
		
		$type = strtolower($type);
		
		if($path = self::getFilePath($content)) {
			$content = $path;
			$path = true;
		}
		
		if(substr($content, 0, strlen(ROOT)) == ROOT)
			$content = substr($content, strlen(ROOT));
		
		switch($type) {
			case "css":
			case "style":
			case "stylesheet":
			
				// first check if full path is given, best performance
				if(self::$combine && !checkFileExt($content, "php") && self::file_exists(ROOT . $content)) {
					// we have to classes: main and not main combines
					if($combine_name == "main") {
						if(!isset(self::$resources_css["main"]["mtime"])) {
							self::$resources_css["main"]["mtime"] = filemtime(ROOT . $content);
						} else {
							$mtime = filemtime(ROOT . $content);
							if(self::$resources_css["main"]["mtime"] < $mtime) {
								self::$resources_css["main"]["mtime"] = $mtime;
							}
							unset($mtime);
						}
						self::$resources_css["main"]["files"][$content] = $content;
					} else {
						if(!isset(self::$resources_css["combine"]["mtime"])) {
							self::$resources_css["combine"]["mtime"] = filemtime(ROOT . $content);
						} else {
							$mtime = filemtime(ROOT . $content);
							if(self::$resources_css["combine"]["mtime"] < $mtime) {
								self::$resources_css["combine"]["mtime"] = $mtime;
							}
							unset($mtime);
						}
						self::$resources_css["combine"]["files"][$content] = $content;
					}
					
					self::registerLoaded("css", $content);
					
				} else {
					if(!$path && self::file_exists(SYSTEM_TPL_PATH . "/css/" . $content)) {
						$content = SYSTEM_TPL_PATH . "/css/" . $content;
					} else if(!$path) {
						self::registerLoaded("css", $content);
	 	 				
	 	 				// register
					self::$resources_css["default"]["files"][$content] = $content;
						break;
					}
					
					if(self::$combine) {
						
						// we have to classes main and normal combines
						if($combine_name == "main") {
							if(!isset(self::$resources_css["main"]["mtime"])) {
								self::$resources_css["main"]["mtime"] = filemtime(ROOT . $content);
							} else {
								$mtime = filemtime(ROOT . $content);
								if(self::$resources_css["main"]["mtime"] < $mtime) {
									self::$resources_css["main"]["mtime"] = $mtime;
								}
								
							}
							self::$resources_css["main"]["files"][$content] = $content;
						} else {
							
							// if m-time for consolidated file is not set, we set it to this file
							if(!isset(self::$resources_css["combine"]["mtime"])) {
								self::$resources_css["combine"]["mtime"] = filemtime(ROOT . $content);
							} else {
								
								// check if consolidated file has a earlier mtime than this file
								$mtime = filemtime(ROOT . $content);
								if(self::$resources_css["combine"]["mtime"] < $mtime) {
									self::$resources_css["combine"]["mtime"] = $mtime;
								}
								
							}
							self::$resources_css["combine"]["files"][$content] = $content;
						}
						
						self::registerLoaded("css", $content);
	 	 				
						break;
					} else {
						self::registerLoaded("css", $content);
					}
				
				}
				
			break;
			case "script":
			case "js":
			case "javascript":
				if(self::$combine && $combine_name != "" && !checkFileExt($content, "php") && $path === true /* file exists */) {
					// last modfied of the whole block
					if(!isset(self::$resources_js[$combine_name])) {
						self::$resources_js[$combine_name] = array(
							"files"	 	=> array(),
							"mtime"			=> filemtime(ROOT . $content),
							"raw"			=> array(),
							"name"			=> $combine_name
						);
					} else {
						$mtime = filemtime(ROOT . $content);
						if(self::$resources_js[$combine_name]["mtime"] < $mtime) {
							self::$resources_js[$combine_name]["mtime"] = $mtime;
						}
					}
					self::$resources_js[$combine_name]["files"][$content] = $content;
				} else {
					
					if($combine_name == "main") {
						if(!isset(self::$resources_js["main"])) {
 	 					  self::$resources_js["main"] = array("files" => array());
						}
						self::$resources_js["main"]["files"][$content] = $content;
					} else {
						if(!isset(self::$resources_js["default"])) {
	 					   self::$resources_js["default"] = array();
 	 				 	 }
						self::$resources_js["default"]["files"][$content] = $content;
					}
					
  	 			
				}
			break;
		}
	
		if(PROFILE) Profiler::unmark("resources::Add");
	}
	
	/**
	 * registers a file as loaded
	 *
	 *@name registerLoaded
	 *@access public
	 *@param string - type
	 *@param string - path
	*/
	static function registerLoaded($type, $path) {
		$type = (strtolower($type) == "css") ? "css" : "js";
		// register in autoloader
		if(file_exists($path))
			self::$registeredResources[$type][] = $path."?".filemtime($path);
		else
			self::$registeredResources[$type][] = $path;
	}
	
	/**
	 * checks the file-path
	 *
	 *@name getFilePath
	 *@access public
	*/
	public static function getFilePath($path) {
		if(self::file_exists($path))
			return $path;
		
		if(self::file_exists(tpl::$tplpath . Core::getTheme() . "/" . $path)) {
			$content = tpl::$tplpath . Core::getTheme() . "/" . $path;
		} else if(self::file_exists(APPLICATION_TPL_PATH . "/" . $path)) {
			$content = APPLICATION_TPL_PATH  . "/".  $path;
		} else if(self::file_exists(SYSTEM_TPL_PATH . "/" . $path)) {
			$content = SYSTEM_TPL_PATH . "/" . $path;
		} else {
			$content = false;
		}
		return $content;
	}
	
	/**
	 * adds some javascript code
	 *@name addJS
	 *@access public
	 *@param string - js
	*/
	public static function addJS($js, $combine_name = "scripts") {	
		if(self::$combine && $combine_name != "") {
			if(!isset(self::$resources_js[$combine_name])) {
				self::$resources_js[$combine_name] = array("files" => array(), "raw" => array(), "mtime"	=> 1, "name"	=> $combine_name);
			}
			self::$resources_js[$combine_name]["raw"][] = $js;
		} else {
			self::$raw_js[] = $js;
		}
	}
	
	/**
	 * adds some css code
	 *@name addCSS
	 *@access public
	 *@param string - js
	*/
	public static function addCSS($css) {
		self::$resources_css["raw"]["data"][] = $css;
	}
	
	/**
	 * if you want to use some data in your scripts, which is from the database you can add it here
	 *@name addData
	 *@access public
	 *@param string - javascript-code
	*/
	public static function addData($js) {
	
		self::$resources_data[md5($js)] = $js;
	}
	
	/**
	 * gets the resources
	 *
	 *@name get
	 *@access public
	*/
	public static function get() {
		if(PROFILE) Profiler::mark("Resources::get");
		
		// if ajax, no combine
		if(Core::is_ajax()) {
			self::disableCombine();
		}
		
		
		
		// generate files
		$files = self::generateFiles();
		$js = $files[1];
		$css = $files[0];
		
		if(self::$registeredResources["js"])
			self::$resources_data[] = "goma.ui.registerResources('js', ".json_encode(self::$registeredResources["js"]).");";
		
		if(self::$registeredResources["css"])
			self::$resources_data[] = "goma.ui.registerResources('css', ".json_encode(self::$registeredResources["css"]).");";
		
		if(Core::is_ajax()) {
			// write data to file
			$datajs = implode("\n", self::$resources_data);
			FileSystem::Write(ROOT . CACHE_DIRECTORY . "/data.".md5($datajs).".js",$datajs);
			$js = array_merge(array(ROOT_PATH . CACHE_DIRECTORY . "/data.".md5($datajs).".js"), $js);
			return array("css"	=> $css, "js"	=> $js);
		} else {
			// generate data
			$datajs = implode("\n			", self::$resources_data);
			// now render
			$html = "";
			if(isset($css["files"])) {
				foreach($css["files"] as $file) {
					$html .= "			<link rel=\"stylesheet\" type=\"text/css\" href=\"".ROOT_PATH . $file."\" />\n";
				}
				unset($css["files"]);
			}
			foreach($css as $key => $file) {
				$html .= "			<link rel=\"stylesheet\" type=\"text/css\" href=\"".ROOT_PATH . $file."\" />\n";
			}
			
			
			foreach($js as $file) {
				$html .= "			<script type=\"text/javascript\" src=\"".ROOT_PATH . $file."\"></script>\n";
			}
			
			$html .= "\n\n
		<script type=\"text/javascript\">
		// <![CDATA[
			".$datajs."
		// ]]>
		</script>\n
		<noscript><style type=\"text/css\">.hide-on-js { display: block !important; } .show-on-js { display: none !important; }</style></noscript>";
			
			
			
			if(PROFILE) Profiler::unmark("Resources::get");
			return $html;
		}
		
		
	}
	
	/**
	 * generates a css file given by combined data
	 *
	 *@name generateCSSFile
	 *@access public
	*/
	public static function generateCSSFile($combine_css, $name = "",  &$css_files) {
		$file = self::getFileName(CACHE_DIRECTORY . "css_".$name."_".md5(implode("_", $combine_css["files"]))."_".$combine_css["mtime"]."_".preg_replace('/[^a-zA-Z0-9_]/', '_', self::VERSION).".css");
		if(is_file($file)) {
			$css_files[] = $file;
		} else {
			// generate css-file
			$css = "/**
 *@builder goma resources ".self::VERSION."
 *@license to see license of the files, go to the specified path for the file
*/\n\n";
			foreach($combine_css["files"] as $cssfile) {
				
				$cachefile = ROOT . CACHE_DIRECTORY  . ".cache." . md5($cssfile) . ".css";
				if(self::file_exists($cachefile) && filemtime($cachefile) > filemtime(ROOT . $cssfile)) {
					$css .= file_get_contents($cachefile);
				} else {
					$data = "/* file ". $cssfile ." */\n\n";
					$data .= trim(self::parseCSSURLs(cssmin::minify(file_get_contents(ROOT . $cssfile)), $cssfile, ROOT_PATH)) . "\n\n";
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
	 *@name generateFiles
	 *@access public
	*/
	public static function generateFiles() {
		
		if(PROFILE) Profiler::mark("Resources::generateFiles");
		$css_files = array();
		$js_files = array();
		if(self::$combine) {
			// css
			
			if(PROFILE) Profiler::mark("Resources::generateFiles CSS");
			if(isset(self::$resources_css["default"])) {
				$css_files = array_merge($css_files, self::$resources_css["default"]);
			}
			// normal combines
			if(isset(self::$resources_css["combine"])) {
				self::generateCSSFile(self::$resources_css["combine"], "combine", $css_files);
			}
			// main combines
			if(isset(self::$resources_css["main"])) {
	 			self::generateCSSFile(self::$resources_css["main"], "main", $css_files);
			}
			
			if(PROFILE) Profiler::unmark("Resources::generateFiles CSS");
			
			if(PROFILE) Profiler::mark("Resources::generateFiles JS");
			
			// javascript
			$resources_js = self::$resources_js;
			// main
			if(isset($resources_js["main"])) {
					$js_files[] = self::makeCombiedJS($resources_js["main"]);
					unset($resources_js["main"]);
			}
			
			// default
			if(isset($resources_js["default"])) {
					foreach($resources_js["default"]["files"] as $jsfile) {
						if(file_exists($jsfile)) {
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
			// we have to make raw-file
			if(count(self::$raw_js) > 0) {
				$file = self::getFileName(ROOT . CACHE_DIRECTORY . "raw_".md5(implode("", self::$raw_js))."_".preg_replace('/[^a-zA-Z0-9_]/', '_', self::VERSION).".js");
				if(!is_file($file)) {
						$js = "";
						foreach(self::$raw_js as $code) {
							$js .= "/* RAW */\n\n";
							$js .= jsmin::minify($code) . "\n\n";
						}
						FileSystem::Write($file,self::getEncodedString($js));
						$js_files[] = $file;
				}
			}
			
			if(isset(self::$resources_css["raw"]["data"]) && count(self::$resources_css["raw"]["data"]) > 0) {
				$css = implode("\n\n", self::$resources_css["raw"]["data"]);
				$filename = self::getFileName(CACHE_DIRECTORY . "/raw_" . md5($css) . ".css");
				if(!is_file(ROOT . $filename)) {
					FileSystem::Write($filename,self::getEncodedString($css));
					$css_files[] = $filename;
				} else {
					$css_files[] = $filename;
				}
			}
			usort($js_files, array("Resources", "sortjs"));
			
			if(PROFILE) Profiler::unmark("Resources::generateFiles JS");
		} else {
			
			$css_files = isset(self::$resources_css["default"]["files"]) ? array_values(self::$resources_css["default"]["files"]) : array();
			$js_files = isset(self::$resources_js["default"]["files"]) ? array_values(self::$resources_js["default"]["files"]) : array();
			
			foreach($css_files as $k => $f) {
				if(file_exists($f)) {
					$css_files[$k] = $f . "?" . filemtime($f);
				}
			}
			
			if(isset(self::$resources_js["main"]["files"])) {
				$js_files = array_merge(array_values(self::$resources_js["main"]["files"]), $js_files);
			}
						
			foreach($js_files as $k => $f) {
				if(file_exists($f)) {
					$js_files[$k] = $f . "?" . filemtime($f);
				}
			}
			
			// raw
			if(isset(self::$resources_js["default"]["raw"])) {
				self::$raw_js = array_merge(self::$raw_js, self::$resources_js["default"]["raw"]);
			}
			if(isset(self::$resources_js["main"]["raw"])) {
				self::$raw_js = array_merge(self::$raw_js, self::$resources_js["main"]["raw"]);
			}
			
			
			if(PROFILE) Profiler::mark("Resources::get");
			// we have to make raw-file
			if(count(self::$raw_js) > 0) {
				$file = self::getFilename(CACHE_DIRECTORY . "/raw_".md5(implode("", self::$raw_js)).".js");
				if(!is_file(ROOT . $file)) {
						$js = "";
						foreach(self::$raw_js as $code) {
							$js .= "/* RAW */\n\n";
							$js .= jsmin::minify($code) . "\n\n";
						}
						FileSystem::Write($file,self::getEncodedString($js));
						$js_files[] = $file;
				} else {
						$js_files[] = $file;
				}
			}
			
			if(!Core::is_ajax()) {
				foreach($js_files as $file) {
					self::registerLoaded("js", $file);
				}
			}
			
			if(PROFILE) Profiler::unmark("Resources::get");
			
			if(isset(self::$resources_css["raw"]["data"]) && count(self::$resources_css["raw"]["data"]) > 0) {
				$css = implode("\n\n", self::$resources_css["raw"]["data"]);
				$filename = self::getFileName(CACHE_DIRECTORY . "/raw." . md5($css) . ".css");
				if(!is_file(ROOT . $filename)) {
					FileSystem::Write($filename,self::getEncodedString($css));
					$css_files[] = $filename;
				} else {
					$css_files[] = $filename;
				}
			}
		}
		// reorder js-files
		
		if(PROFILE) Profiler::unmark("Resources::generateFiles");
		return array($css_files, $js_files);
		
	}
	
	/**
	 * sorts js files, main at first and scripts at last
	*/
	public static function sortJS($a, $b) {
		if(preg_match("/main/", $a)) 
			return -1;
			
		if(preg_match("/main/", $b)) 
			return 1;
		
		if(preg_match("/data/", $a)) 
			return -1;
			
		if(preg_match("/data/", $b)) 
			return 1;
		
		if(preg_match("/scripts/", $a)) 
			return 1;
			
		if(preg_match("/scripts/", $b)) 
			return -1;
			
		if(preg_match("/raw/", $a)) 
			return 1;
			
		if(preg_match("/raw/", $b)) 
			return -1;
			
		return 0;
	}
	
	/**
	 * makes a combined javascript-file
	 *
	 *@name makeCombinedJS
	 *@access public
	 *@param data-array
	*/
	public static function makeCombiedJS($data) {
		if(PROFILE) Profiler::mark("Resources::makeCombinedJS");
		
		if(isset($data["raw"])) {
			$hash = md5(implode("", $data["files"])) . md5(implode("", $data["raw"]));
		} else {
			$hash = md5(implode("", $data["files"]));
		}
		
		
		$file = self::getFileName(CACHE_DIRECTORY . "js_combined_".$data["name"]."_".$hash."_".$data["mtime"]."_".preg_replace('/[^0-9a-zA-Z_]/', '_', self::VERSION).".js");
		if(self::file_exists($file)) {
			return $file;
		} else {
			// remake file
			$js = "/**
 *@builder goma resources ".self::VERSION."
 *@license to see license of the files, go to the specified path for the file 
*/\n\n";
			foreach($data["files"] as $jsfile) {
				$cachefile = ROOT . CACHE_DIRECTORY . ".cache.".md5($jsfile).".".self::VERSION.".js";
				if(self::file_exists($cachefile) && filemtime($cachefile) > filemtime(ROOT . $jsfile)) {
					$js .= file_get_contents($cachefile);
				} else {
					$jsdata = "/* File ".$jsfile." */\n";
					
					$jsdata .= jsmin::minify(file_get_contents(ROOT . $jsfile)) . ";\n\n";
					$js .= $jsdata;
  	 				FileSystem::Write($cachefile,$jsdata);
				}
				unset($cfile, $jsdata, $cachefile);
			}
			
			if(isset($data["raw"])) {
				foreach($data["raw"] as $code) {
					if(strlen($code) > 4000) {
						$cachefile = ROOT . CACHE_DIRECTORY . ".cache.".md5($code).".js";
						if(self::file_exists($cachefile)) {
							$js .= file_get_contents($cachefile);
						} else {
							$jsdata = "/* RAW */\n\n";
							$jsdata .= jsmin::minify($code) . ";\n\n";
							$js .= $jsdata;
							FileSystem::Write($cachefile,$jsdata);
						}
						unset($cfile, $data, $cachefile);
					} else {
						$js .= "/* RAW */\n\n";
						$js .= jsmin::minify($code) . ";\n\n";
					}
				}
			}
			
			$files = array();
			foreach((array) $data["files"] as $jsfile) {
				if(file_exists($jsfile))
					$files[] = $jsfile . "?" . filemtime($jsfile);
				else
					$files[] = $jsfile;
			}
			if(count($files) > 0) {
				$js .= "goma.ui.registerResources('js', ".json_encode($files).");";
			}
			
			FileSystem::Write($file,self::getEncodedString($js));
			unset($filepointer, $js);
			if(PROFILE) Profiler::unmark("Resources::generateFiles");
			return $file;
		}
	}
	
	/**
	 * cache for following functions
	 *
	 *@name extCache
	 *@access private
	*/
	private static $extCache = false;
	
	/**
	 * gets the filename
	 *
	 *@name getFileExt
	 *@access public
	*/
	public static function getFileExt() {
		if(self::$extCache === false) {
			$gzip = self::$gzip;
			// first check if defalte is available
			// defalte is 21% faster than normal gzip
			if($gzip != 0 && request::CheckBrowserDeflateSupport() && function_exists("gzdeflate")) {
				self::$extCache = ".gdf";			
			// if not, check if gzip
			} else if($gzip != 0 && request::CheckBrowserGZIPSupport() && function_exists("gzencode")) {
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
	 *@name getFileName
	 *@access public
	 *@param string - file
	*/
	public static function getFileName($file) {
		$ext = self::getFileExt();
		if(checkFileExt($file, "js")) {
			return substr($file, 0, -3) . $ext . ".js";
		}
		
		if(checkFileExt($file, "css")) {
			return substr($file, 0, -4) . $ext . ".css";
		}
		
		return $file . $ext;
	}
	
	/**
	 * gets the string encoded
	 *
	 *@name getEncodedString
	 *@access public
	*/
	public static function getEncodedString($data) {
		$ext = self::getFileExt();
		if($ext == ".gdf") {
			return gzdeflate($data);
		} else if($ext == ".ggz") {
			return gzencode($data);
		} else {
			return $data;
		}
	}
	
	/**
	 * bacause the background-image-locations arent't right anymore, we have to correct them
	 *
	 *@name parseCSSURLs
	 *@access public
	*/
	public static function parseCSSURLs($css, $file, $base) {
		$path = substr($file, 0, strrpos($file, '/'));
		if(preg_match('/^' . preg_quote($base, "/") . "/", $path)) {
				$path = substr($path, strlen($base));
		}
		
		preg_match_all('/url\(((\'|"|\s?)(.*)(\"|\'|\s?))\)/Usi', $css, $matches);
		foreach($matches[3] as $key => $url) {
			$css = str_replace($matches[0][$key], 'url("' . $base . $path . "/" .$url . '")', $css);
		}
		return $css;
	}
	
	/**
	 * checks if a file exists
	 * for optional further caching
	 *
	 *@name file_exists
	 *@access public
	*/ 
	public static function file_exists($file) {
  	 
		if(isset($_GET["flush"]) && self::$cacheUpdated === false) {
			Object::instance("Resources")->generateClassInfo();
			ClassInfo::write();
		}
		
		if(defined("CLASS_INFO_LOADED")) {
			if(!strpos($file, "../") && preg_match('/\.(js|css|html)$/i', $file) && substr($file, 0, strlen(SYSTEM_TPL_PATH)) == SYSTEM_TPL_PATH || substr($file, 0, strlen(APPLICATION_TPL_PATH)) == APPLICATION_TPL_PATH) {
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
	 *@name generateClassInfo
	 *@access public
	*/
	public function generateClassInfo() {
		if(PROFILE) Profiler::mark("Resources::GenerateClassInfo");
		
		// scan directories
		ClassInfo::$class_info[$this->class]["files"] = array();
		foreach(self::$scanFolders as $folder) {
			$this->scanToClassInfo($folder);
		}
		
		// expansion
		if(isset(ClassInfo::$appENV["expansion"]) && is_array(ClassInfo::$appENV["expansion"]))
		foreach(ClassInfo::$appENV["expansion"] as $name => $data) {
			if(isset($data["viewFolder"])) {
				$this->scanToClassInfo($data["folder"] . $data["viewFolder"]);
			} else if(file_exists($data["folder"] . "views")) {
				$this->scanToClassInfo($data["folder"] . "views");
			}
		}
		
		
		$this->callExtending("generateClassInfo");
		
		self::$cacheUpdated = true;
		if(PROFILE) Profiler::unmark("Resources::GenerateClassInfo");
	}
	
	/**
	 * scan's directories to class-info
	 *
	 *@name scanToClassInfo
	 *@access public
	 *@param string - dir
	*/
	public function scanToClassInfo($dir) {
		if(file_exists($dir))
			foreach(scandir($dir) as $file) {
				if(is_dir($dir . "/" . $file) && $file != "." && $file != "..") {
					$this->scanToClassInfo($dir . "/" . $file);
				} else { 
					if(preg_match('/\.(js|css|html)$/i', $file)) {
						ClassInfo::$class_info[$this->class]["files"][$dir . "/" . $file] = true;
					}
				}
				
			}
	}
}