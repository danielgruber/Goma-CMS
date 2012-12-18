<?php
/**
  * loads up a cache of class-to-file
  * newer version of autoloader
  *
  *@package goma framework
  *@subpackage framework loader
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 16.11.2012
  * $Version 3.2.6
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ClassManifest {
	/**
	 * files, which should be loaded in each request
	 *
	 *@name reload
	*/
	public static $preload = array();
	
	/**
	 * class-cache
	 *
	 *@name classes
	 *@access private
	*/
	public static $classes = array();
	
	/**
	 * this var contains all directories, which will scanned recursivly
	 *
	 *@name directories
	 *@access public
	*/
	static public $directories = array('system');
	
	/**
	 * which classes are already loaded?
	 *
	 *@name loaded
	 *@access public
	*/
	public static $loaded = array(	"classinfo" 			=> true, 
									"core" 					=> true, 
									"object"				=> true,
									"sql"					=> true, 
									"requesthandler"		=> true,
									"dev"					=> true,
									"tplcaller"				=> true,
									"tplcacher"				=> true);
	/**
	 * class aliases
	 *
	 *@name class_alias
	 *@access private
	 *@var array
	*/
	private static $class_alias = array(
		"showsitecontroller"	=> "frontedcontroller",
		"_array"				=> "arraylib",
		"dataobjectholder"		=> "viewaccessabledata",
		"autoloader"			=> "ClassManifest"
	);
	
	/**
	 * loads a class
	 *
	 *@name load
	 *@access public
	*/
	public static function load($class) {
		
		if(PROFILE) Profiler::mark("Manifest::load");
		
		$class = strtolower(trim($class));
		
		if(!isset(self::$loaded[$class])) {
			if(isset(ClassInfo::$files[$class])) {
				if(!include_once(ClassInfo::$files[$class])) {
					ClassInfo::Delete();
					throwError(9, 'FileSystem-Error', "Could not include ".ClassInfo::$files[$class].". ClassInfo seems to be old. Please reload!");
				}
				self::registerLoaded(ClassInfo::$files[$class]);
			} else if(isset(self::$class_alias[$class])) {
				if(DEV_MODE) {
					// we log this, because it's not good, aliases are just for deprecation
					logging("Making alias ".self::$class_alias[$class]." of ".$class."");
				}
				
				// make a alias
				class_alias(self::$class_alias[$class], $class);
			}
		}
		
		if(PROFILE) Profiler::unmark("Manifest::load");
	}
	
	/**
	 * registers a file as loaded
	 *
	 *@name registerLoaded
	 *@access public
	*/
	public static function registerLoaded($file) {
		if(count($keys = array_keys(ClassInfo::$files, $file)) > 0) {
			foreach($keys as $class) {
				self::$loaded[$class] = true;
				ClassInfo::setSaveVars($class);
			}
		}
		
		return true;
	}
	
	/**
	 * generates the class manifest for all folders in this class
	 *
	 *@name generate_all_class_manifest
	 *@access public
	*/
	public static function generate_all_class_manifest(&$classes, &$class_info, &$env) {
		foreach(self::$directories as $dir) {
			self::generate_class_manifest($dir, $classes, $class_info, $env);
		}
	}
	
	/**
	 * generates the class-manifest for a given directory
	 *
	 *@name generate_class_manifest
	 *@access public
	*/
	public static function generate_class_manifest($dir, &$classes, &$class_info, &$env) {
		if(file_exists($dir . "/_exclude.php")) {
			include_once($dir . "/_exclude.php");
			return false;
		}
		
		if(file_exists($dir . "/autoloader_exclude"))
			return false;
		
		$dir = realpath($dir);
		
		// Extension-Layer
		if(file_exists($dir . "/contents/info.plist")) {
			require_once(ROOT . "system/libs/thirdparty/plist/CFPropertyList.php");
			$plist = new CFPropertyList($dir . "/contents/info.plist");
			$data = $plist->ToArray();
			
			// check if we have required data
			if(isset($data["name"], $data["type"], $data["loadCode"], $data["version"]) && ($data["type"] == "expansion" || $data["type"] == "extension")) {
				$data["folder"] = $dir . "/contents/";
				
				// test compatiblity
				if(!isset($data["requiredPHPVersion"]) || version_compare($data["requiredPHPVersion"], phpversion(), "<=")) {
					if(!isset($data["requireFrameworkVersion"]) || goma_version_compare($data["requireFrameworkVersion"], GOMA_VERSION . "-" . BUILD_VERSION, "<=")) {
						if(!isset($data["requireApp"]) || $data["requireApp"] == ClassInfo::$appENV["app"]["name"]) {
							if(!isset($data["requireAppVersion"]) || !isset($data["requireApp"]) || goma_version_compare($data["requireAppVersion"], ClassInfo::$appENV["app"]["version"] . "-" . ClassInfo::$appENV["app"]["build"], "<=")) {
								
								// compatible!!
								
								if(file_exists($dir . "/contents/.g_" . APPLICATION . ".disabled")) {
									return false;
								}
								
								// let's remove some data to avoid saving too much data
								unset($data["requireFrameworkVersion"], $data["requireApp"], $data["requireAppVersion"]);
								
								// register in environment
								$env["expansion"][strtolower($data["name"])] = $data;
								if(is_array($data["loadCode"])) {
									$_classes = array();
									foreach($data["loadCode"] as $ldir) {
										self::generate_class_manifest($dir . "/contents/" . $ldir, $_classes, $class_info, $env);
									}
									foreach($_classes as $_class => $file) {
										$class_info[$_class]["inExpansion"] = strtolower($data["name"]);
										$classes[$_class] = $file;
									}
									$env[strtolower($data["type"])][strtolower($data["name"])]["classes"] = array_keys($_classes);
									unset($_classes);
								} else {
									$_classes = array();
									self::generate_class_manifest($dir . "/contents/" . $data["loadCode"], $_classes, $class_info, $env);
									foreach($_classes as $_class => $file) {
										$class_info[$_class]["inExpansion"] = strtolower($data["name"]);
										$classes[$_class] = $file;
									}
									$env[strtolower($data["type"])][strtolower($data["name"])]["classes"] = array_keys($_classes);
									unset($_classes);
								}
								return true;
							} else {
								return false;
							}
						} else {
							return false;
						}
					} else {
						return false;
					}
				}
			}
		}
		
		foreach(scandir($dir) as $file) {
			if($file != "." && $file != ".."){
				if(is_dir($dir . "/" . $file)) {
					self::generate_class_manifest($dir . "/" . $file, $classes, $class_info, $env);
				} else if(_eregi('\.php$', $file)) {
					$contents = file_get_contents($dir . "/" . $file);
					
					$contents = preg_replace('/\/\*(.*)\*\//Usi', '', $contents);
					$contents = preg_replace('/\?\>(.*)\<?php/Usi', '', $contents);
					
					preg_match_all('/(abstract\s+)?class\s+([a-zA-Z0-9_]+)(\s+extends\s+([a-zA-Z0-9_]+))?(\s+implements\s+([a-zA-Z0-9_,\s]+))?\s+\{/Usi', $contents, $parts);
					foreach($parts[2] as $key => $class) {
						$class = trim(strtolower($class));
						
						if(isset($classes[$class]) && $classes[$class] != $dir . "/" . $file && file_exists($classes[$class])) {
							if(filemtime($classes[$class]) > filemtime($dir . "/" . $file)) {
								if(count($parts[2]) == 1 && isset(ClassInfo::$appENV["app"]["allowDeleteOld"])) {
									logging("Delete ".$dir . "/" . $file.", because old Class!");
									if(!DEV_MODE) {
										// unlink file
										// unlink file
										FileSystem::requireDir(ROOT . "__oldclasses/" . $dir . "/");
										rename($dir . "/" . $file, ROOT . "__oldclasses/" . $dir . "/" . $file);
									}
									continue;
								} else {
									unset($parts[2][$key]);
									continue;
								}
							} else if(filemtime($classes[$class]) < filemtime($dir . "/" . $file)) {
								if(count(array_keys($classes, $classes[$class])) == 1 && isset(ClassInfo::$appENV["app"]["allowDeleteOld"])) {
									logging("Delete ".$classes[$class].", because old Class!");
									if(!DEV_MODE) {
										// unlink file
										FileSystem::requireDir(ROOT . "__oldclasses/" . dirname($classes[$class]) . "/");
										rename($classes[$class], ROOT . "__oldclasses/" . dirname($classes[$class]) . "/" . basename($classes[$class]));
									}
								}
							}
						}
						$classes[$class] = $dir . "/" . $file;
						
						if(!isset($class_info[$class])) 
							$class_info[$class] = array();
						
						if($parts[4][$key]) {
							$class_info[$class]["parent"] = trim(strtolower($parts[4][$key]));
							if($class_info[$class]["parent"] == $class) {
								throwError(6, "Class-Definition-Error", "Class '".$class."' can not extend itself in ".$dir . "/" . $file .".");
							}
						}
						
						if($parts[6][$key]) {
							$interfaces = explode(",",$parts[6][$key]);
							$class_info[$class]["interfaces"] = array_map("strtolower",array_map("trim",$interfaces));
						}
						
						if($parts[1][$key]) {
							$class_info[$class]["abstract"] = true;
						}
					}
					
					// index interfaces too
					preg_match_all('/interface\s+([a-zA-Z0-9_]+)(\s+extends\s+([a-zA-Z0-9_]+))?\s+\{/Usi', $contents, $parts);
					foreach($parts[1] as $key => $class) {
						$class = trim(strtolower($class));
						
						if(isset($classes[$class]) && $classes[$class] != $dir . "/" . $file && file_exists($classes[$class])) {
							if(filemtime($classes[$class]) > filemtime($dir . "/" . $file)) {
								if(count($parts[2]) == 1 && isset(ClassInfo::$appENV["app"]["allowDeleteOld"])) {
									logging("Delete ".$dir . "/" . $file.", because old Class!");
									if(!DEV_MODE) {
										// unlink file
										// unlink file
										FileSystem::requireDir(ROOT . "__oldclasses/" . $dir . "/");
										rename($dir . "/" . $file, ROOT . "__oldclasses/" . $dir . "/" . $file);
									}
									continue;
								} else {
									unset($parts[2][$key]);
									continue;
								}
							} else if(filemtime($classes[$class]) < filemtime($dir . "/" . $file)) {
								if(count(array_keys($classes, $classes[$class])) == 1 && isset(ClassInfo::$appENV["app"]["allowDeleteOld"])) {
									logging("Delete ".$classes[$class].", because old Class!");
									if(!DEV_MODE) {
										// unlink file
										FileSystem::requireDir(ROOT . "__oldclasses/" . dirname($classes[$class]) . "/");
										rename($classes[$class], ROOT . "__oldclasses/" . dirname($classes[$class]) . "/" . basename($classes[$class]));
									}
								}
							}
						}
						$classes[$class] = $dir . "/" . $file;
						
						if(!isset($class_info[$class])) 
							$class_info[$class] = array();
						
						if($parts[3][$key]) {
							$class_info[$class]["parent"] = strtolower($parts[3][$key]);
							if($class_info[$class]["parent"] == $class) {
								throwError(6, "Interface-Definition-Error", "Interface '".$class."' can not extend itself in ".$dir . "/" . $file .".");
							}
						}

						$class_info[$class]["abstract"] = true;
						$class_info[$class]["interface"] = true;
						
					}
					
					unset($contents, $parts, $key, $class);
				}
				
				if($file == "_config.php") {
					self::addPreload($dir . "/" . $file);
				}
			}
		}
	}
	
	/**
	 * adds preloading
	 *@name addPreload
	 *@access public
	 *@param string - file
	*/
	public static function addPreload($file)
	{
		self::$preload[$file] = $file;
	}
	
	/**
	 * includes all files
	 *
	 *@name include_all
	 *@access public
	*/
	public static function include_all() {
		foreach(ClassInfo::$files as $class => $file) {
			self::load($class);
		}
	}
}


// fallback
if(!function_exists("spl_autoload_register")) {
	$GLOBALS["__autoload_stack"] = array();
	function spl_autoload_register($callback, $throw = false, $preprend = false) 
	{
		if($prepend)
			$GLOBALS["__autoload_stack"] = array_merge(array($callback), $GLOBALS["__autoload_stack"]);
		else
			$GLOBALS["__autoload_stack"] = array_merge($GLOBALS["__autoload_stack"], array($callback));
		
	}
	function __autoload($class_name)
	{
		foreach($GLOBALS["__autoload_stack"] as $callback) {
			call_user_func_array($callback, array($class_name));
		}
	}
}

spl_autoload_register("ClassManifest::load");

// This method does not exist in each PHP-Build
if(!function_exists("class_alias")) {
	function class_alias($org, $alias) {
		eval("class ".$org." extends ".$alias." {}");
	}
}