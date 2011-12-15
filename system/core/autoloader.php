<?php
/**
  * the class autoloads data by __autoload-method in php
  * it creats a cache for classes
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 22.06.2011
  * $Version 2.0.0 - 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

classinfo::addSaveVar("autoloader","preload");



class autoloader extends object
{
		/**
		 * preloader
		*/
		public static $preload = array();
		/**
		 * classes
		 *@name classes
		 *@access private
		*/
		public static $classes = array();
		/**
		 * cache instance
		 *@name cache
		 *@access private
		*/
		public static $cacher = false;
		/**
		 * this var contains all directories, which will scanned recursivly
		*/
		static public $directories = array(	'system');
		/**
		 * this var defines if the cache-file has already been loaded
		 *
		 *@name hasCacheLoaded
		 *@access public
		*/
		private static $hasCacheLoaded = false;
		/**
		 * which classes are already loaded?
		*/
		public static $loaded = array(	"classinfo" 			=> true, 
										"core" 					=> true, 
										"object"				=> true,
										"registry"				=> true, 
										"sql"					=> true, 
										"requesthandler"		=> true, 
										"gloader"				=> true, 
										"domains"				=> true, 
										"dataobject"			=> true, 
										"viewaccessabledata"	=> true,
										"cacher"				=> true,
										"dev"					=> true,
										"tplcaller"				=> true,
										"tplcacher"				=> true,
										"dbfield"				=> true,
										"resources"				=> true,
										"request"				=> true,
										"dataobjectholder"		=> true,
										"dataobjectextension"	=> true,
										"extension"				=> true);
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
			"dataobjectholder"		=> "viewaccessabledata"
		);
		/**
		 * construct
		*/
		public function __construct()
		{
				parent::__construct();
				
				if(PROFILE) Profiler::mark("autoloader");
				
				
				foreach(ClassInfo::$class_info['autoloader']['preload'] as $file)
				{
						include_once($file);
				}
				
				if(PROFILE) Profiler::unmark("autoloader");

		}
		/**
		 * checks if cache-file exists and load class
		 * if not exist, generates new cache
		 *@name load
		 *@param string - class_name
		 *@return null
		*/
		public static function load($class_name)
		{
				if(PROFILE) Profiler::mark("autoloader::load");
				
				
				
				$class_name = trim(strtolower($class_name));
				if((!defined("INCLUDE_ALL") && isset(self::$loaded[$class_name])) || empty($class_name)) {
					if(PROFILE) Profiler::unmark("autoloader::load");
					return true;
				}
				
				self::$loaded[$class_name] = true;
				if(isset(classinfo::$class_info[$class_name]['file']))
				{			

						if(PROFILE) Profiler::unmark("autoloader::load");
						if(isset(ClassInfo::$class_info[$class_name]["parent"]) && !isset(self::$loaded[ClassInfo::$class_info[$class_name]["parent"]]))
							self::load(ClassInfo::$class_info[$class_name]["parent"]);
							
						if(PROFILE) Profiler::mark("autoloader_require");
						if(!include_once(classinfo::$class_info[$class_name]['file']))
						{
								ClassInfo::delete();
								throwError(6, 'PHP-Error', 'Classinfo-File is old<br />
															Plese do the folling things:<br /> 
															<ul>
																<li>try again</li>
																<li>If the error persists, please run dev</li>
										 					</ul>
															Developer Information:
															'.__CLASS__.'::'.__METHOD__.' on line '.__LINE__.' in '.__FILE__.'');
						}
						
						if(PROFILE) Profiler::unmark("autoloader_require");
						
						ClassInfo::setSaveVars($class_name);
						
						unset($class_name);
						
				} else if(isset(self::$class_alias[$class_name])) {
					logging("making alias ".self::$class_alias[$class_name]." of ".$class_name."");
					class_alias(self::$class_alias[$class_name], $class_name);
					if(PROFILE) Profiler::unmark("autoloader::load");
				
				// code not clean!
				} else {
					if(!self::$hasCacheLoaded) {
						self::updatecache();
						self::$hasCacheLoaded = true;
					}
					
					// backup	
					if(isset(self::$classes[$class_name]) && file_exists(self::$classes[$class_name]))
					{
							if(PROFILE) Profiler::unmark("autoloader::load");
							if(PROFILE) Profiler::mark("autoloader_require");
							require_once(self::$classes[$class_name]);
							if(PROFILE) Profiler::unmark("autoloader_require");
							
							ClassInfo::setSaveVars($class_name);
	
					}
					
					if(PROFILE) Profiler::unmark("autoloader::load");
				}

		}
		/**
		 * gets all classes in the directories
		 *@name updatecache
		 *@access public
		*/
		public static function updatecache()
		{
				
				
				if(PROFILE) Profiler::mark("autoloader::updatecache");
				
				require_once(FRAMEWORK_ROOT . "libs/cache/cacher.php");
				
				foreach(self::$directories as $dir)
				{
						self::read(ROOT . $dir);
				}
				
				if(PROFILE) Profiler::unmark("autoloader::updatecache");
				
		}
		/**
		 * read
		 *@name read
		 *@param string - folder
		 *@access public
		*/
		public static function read($dir)
		{
				foreach(scandir($dir) as $file)
				{
						if($file == "." || $file == "..")
						{
								continue;
						}
						if(is_file($dir . "/" . $file))
						{
								if(preg_match('/^(.*)\.class\.php$/Ui', $file, $a))
								{

										self::$classes[strtolower($a[1])] = $dir . "/" . $file;
										
								} else if(preg_match('/^(.*)\.php$/Ui', $file, $a))
								{

										self::$classes[strtolower($a[1])] = $dir . "/" . $file;
										
								}
						} else if(!file_exists($dir . "/" . $file . '/autoloader_exclude'))
						{
								self::read($dir . "/" . $file);
						}
				}
		}
		/**
		 * loads files of an folder recursivly
		 *@name preload
		 *@access public
		 *@param string - directory
		*/
		public static function preload($dir)
		{
				foreach(scandir($dir) as  $file)
				{
						if(!is_dir($dir . "/" . $file))
						{
								if(preg_match('/\.php$/Ui', $file))
								{
										require_once($dir . "/" . $file);
								}
						}
				}
		}
		/**
		 * include all classes
		 *@name include_all
		 *@access public
		*/
		public static function include_all()
		{
				defined("INCLUDE_ALL") OR define("INCLUDE_ALL", true);
				foreach(self::$directories as $dir)
				{
						self::include_all_helper(ROOT . $dir);
				}
		}
		/**
		 * include all classes (helper)
		 *@name include_all_helper
		 *@access protected
		*/
		public static function include_all_helper($dir)
		{
				foreach(scandir($dir) as $file)
				{
						if($file == "." || $file == "..")
						{
								continue;
						}
						if(!is_dir($dir . "/" . $file))
						{
								if(preg_match('/\.php$/Ui', $file, $a))
								{
										include_once($dir . "/" . $file);									
								}
						} else if(!file_exists($dir . "/" . $file . '/autoloader_exclude'))
						{
								self::include_all_helper($dir . "/" . $file);
						}
				}
		}
		/**
		 * adds preloading
		 *@name addPreload
		 *@access public
		 *@param string - file
		*/
		public function addPreload($file)
		{
				self::$preload[] = $file;
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

spl_autoload_register("autoloader::load");

if(!function_exists("class_alias")) {
	function class_alias($org, $alias) {
		eval("class ".$org." extends ".$alias." {}");
	}
}