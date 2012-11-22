<?php
/**
  * this class let you know much about other classes or your class
  * you can get childs or other things
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 22.11.2012
  * $Version 3.6.3
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

define("CLASS_INFO_DATAFILE", ".class_info.goma.php");

interface SaveVarSetter {
	public static function __setSaveVars($class);
}

class ClassInfo extends Object
{
		/**
		 * version of class-info
		 *
		 *@name version
		 *@access public
		*/
		const VERSION = "3.6";
		
		/**
		 * defines when class-info expires
		 *
		 *@name expiringTime
		 *@access public
		*/
		public static $expiringTime = 604800; // 7 days by default
		
		/**
		 * classinfo
		 *@name classinfo
		 *@access public
		*/
		public static $class_info = array();
		
		/**
		 * table-object-relations
		 *@name tables
		 *@access public
		*/
		static public $tables = array();
		
		/**
		 * advrights
		 *@name advrights
		 *@var array
		*/
		public static $advrights = array();
		
		/**
		 * database
		 *@name database
		 *@access public
		*/
		static public $database = array();
		
		/**
		 * data about the current environment
		 *
		 *@name appENV
		 *@access public
		*/ 
		public static $appENV = array();
		
		/**
		 * child-data
		 *
		 *@name childData
		 *@access private
		*/
		private static $childData = array();
		
		/**
		 * files
		 *
		 *@name files
		 *@access public
		*/
		public static $files = array();
		
		/**
		 * array of classes, which we have already set SaveVars
		 *
		 *@name setSaveVars
		 *@access public
		*/
		public static $set_save_vars = array();
		
		/**
		 * this var saves for each class, which want to save vars in cache, the names 
		 *@name save_vars
		 *@access public
		 *@var array
		*/
		public static $save_vars;
		
		/**
		 * hooks for current execution which are executed when classinfo is generated
		 *
		 *@name ClassInfoHooks
		 *@access public
		*/
		public static $ClassInfoHooks = array();
		
		/**
		 * registers a hook on class info loaded
		 *
		 *@name onClassInfoLoaded
		 *@access public
		 *@param mixed - callback
		*/
		public static function onClassInfoLoaded($call) {
			if(!is_callable($call)) {
				return false;
			}
			if(defined("CLASS_INFO_LOADED")) {
				call_user_func_array($call, array());
			} else {
				self::$ClassInfoHooks[] = $call;
			}
			return true;
		}
		
		/**
		 * get a static param for a class
		 *@name getStatic
		 *@access public
		 *@param string - class_name
		 *@param string - var_name
		*/
		public static function getStatic($class_name, $var)
		{
				if(is_object($class_name))
					$class_name = $class_name->class;
				
				if(!empty($class_name))
				{
						if(!empty($var))
						{
								return eval("return ".$class_name."::\$".$var.";");
						} else
						{
								throwError("20","PHP-Error", "Invalid name of var in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("20","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
				}
		}
		
		/**
		 * checks if a static var isset
		 *@name hasStatic
		 *@access public 
		 *@param string - class_name
		 *@param string - var_name
		*/
		public static function hasStatic($class_name, $var)
		{
				if(is_object($class_name))
					$class_name = $class_name->class;
				
				if(!empty($class_name))
				{
						if(!empty($var))
						{
								return eval("return isset(".$class_name."::\$".$var.");");
						} else
						{
								throwError("20","PHP-Error", "Invalid name of var in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("20","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
				}
		}
		
		/**
		 * checks if a static var isset
		 *@name setStatic
		 *@access public 
		 *@param string - class_name
		 *@param string - var_name
		 *@param mixed - value
		*/
		public static function setStatic($class_name, $var, $value)
		{
				if(is_object($class_name))
					$class_name = $class_name->class;
				
				if(!empty($class_name))
				{
						if(!empty($var))
						{
								return eval($class_name."::\$".$var." = ".var_export($value, true).";");
						} else
						{
								throwError("20","PHP-Error", "Invalid name of var in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("20","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
				}
		}
		
		/**
		 * calls a static function
		 *@name callStatic
		 *@access public
		 *@param string - class_name
		 *@param string - func-name
		 *@return mixed
		*/
		public static function callStatic($class, $func)
		{
				if(is_object($class_name))
					$class_name = $class_name->class;
				
				if(!empty($class))
				{
						if(!empty($func))
						{
								return call_user_func_array(array($class, $func), array($class));
						} else
						{
								throwError("20","PHP-Error", "Invalid name of function in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("20","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
				}
		}
		
		/**
		 * class
		 *@name name
		 *@param object
		*/
		public static function name($class)
		{
				return get_class($class);
		}
		
		/**
		 * gets the childs of a class
		 *
		 *@name getChildren
		 *@access public
		 *@param string - class_name
		*/
		public static function getChildren($class)
		{
				
				$class = strtolower($class);
				if(isset(self::$class_info[$class]["child"]))
					return self::$class_info[$class]["child"];
				
				if(self::$childData == array() && file_exists(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE)) {
					include(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE);
					self::$childData = $children;
				}
				
				return isset(self::$childData[$class]) ? self::$childData[$class] : array();
		}
		
		/**
		 * gets interfaces of a class
		 *
		 *@name getInterfaces
		 *@access public
		*/
		public static function getInterfaces($class) {
			$class = strtolower($class);
			return isset(self::$class_info[$class]["interfaces"]) ? self::$class_info[$class]["interfaces"] : array();
		}
		
		/**
		 * checks if the class has the interface
		 *
		 *@name hasInterface
		 *@access public
		 *@param string - class
		 *@param string - interface
		*/
		public static function hasInterface($class, $interface) {
			if(is_object($class)) {
				$class = $class->class;
			} else {
				$class = strtolower($class);
			}
			$interface = strtolower($interface);
			return isset(self::$class_info[$class]["interfaces"]) ? in_array($interface, self::$class_info[$class]["interfaces"]) : false;
		}

		/**
		 * gets the parent class of a class
		 *
		 *@name getParentClass
		 *@access public
		*/
		public static function getParentClass($class) {
			$class = strtolower($class);
			return isset(self::$class_info[$class]["parent"]) ? self::$class_info[$class]["parent"] : false;
		}
			
		/**
		 * gets a table_name for a given class
		 *@name classTable
		 *@access public
		*/
		public static function classTable($class)
		{
				return isset(classinfo::$class_info[$class]["table_name"]) ? classinfo::$class_info[$class]["table_name"] : false;
		}
		
		/**
		 * gets db-fields of an table
		 *@name getTableFields
		 *@access public
		 *@param string - table
		*/
		public static function getTableFields($table)
		{
				return isset(self::$database[$table]) ? self::$database[$table] : array();
		}
		
		/**
		 * checks if an class exists
		 *@name exists
		 *@access public
		 *@param string - class_name
		 *@return bool
		*/
		public static function exists($class)
		{
				return isset(self::$class_info[strtolower($class)]);
		}
		
		/**
		 * adds a var to cache
		 *@name addSaveVar
		 *@access public
		 *@param class - class_name
		 *@param name - var-name
		*/
		public static function addSaveVar($class, $name)
		{
				self::$save_vars[strtolower($class)][] = $name;
		}
		
		/**
		 * gets for a specific class the save_vars
		 *@name getSaveVars
		 *@param string - class-name
		 *@return array
		*/
		public static function getSaveVars($class)
		{
				if(isset(self::$save_vars[strtolower($class)]))
				{
						return self::$save_vars[strtolower($class)];
				}
				return array();
		}

		/**
		 * sets the save_vars
		 *@name setSaveVars
		 *@access public
		*/
		public static function setSaveVars($class)
		{	
				if(PROFILE) Profiler::mark("ClassInfo::setSaveVars");
				
				if(count(self::$class_info) > 0) {
					$class = strtolower($class);
					
					if(!defined('GENERATE_CLASS_INFO') && !isset(self::$set_save_vars[$class]))
					{
							foreach(self::getSaveVars($class) as $var)
							{
									if(isset(self::$class_info[$class][$var]))
									{
											self::setStatic($class, $var, self::$class_info[$class][$var]);
									}
									unset($var);
							}
					}
					
					if(ClassInfo::hasInterface($class, "saveVarSetter")) {
						call_user_func_array(array($class, "__setSaveVars"), array($class));
					}
								
					self::$set_save_vars[$class] = true;
				}
				
				if(PROFILE) Profiler::unmark("ClassInfo::setSaveVars");
		}
		
		/**
		 * checks if class is abstract
		 *@name isAbstract
		 *@access public
		*/
		public static function isAbstract($class)
		{
				$class = strtolower($class);
				if(isset(self::$class_info[$class]["abstract"]))
						return self::$class_info[$class]["abstract"];
				else
						return false;
		}
		
		/**
		 * gets the parent class of a given class
		 *
		 *@name get_parent_class
		 *@access public
		*/
		public static function get_parent_class($class) {
			if(isset(self::$class_info[$class]["parent"])) {
				return self::$class_info[$class]["parent"];
			} else {
				return null;
			}
		}
		
		/**
		 * gets info to a specific class
		 *
		 *@name getInfo
		 *@access public
		*/
		public static function getInfo($class) {
			$class = trim(strtolower($class));
			if(isset(self::$class_info[$class])) {
				return self::$class_info[$class];
			} else {
				return false;
			}
		}
		
		/**
		 * returns a list of database-tables of the dataobject without prefixes
		 *
		 *@name Tables
		 *@access public
		*/
		public static function Tables($class) {
			$class = trim(strtolower($class));
			if(!isset(self::$class_info[$class]["baseclass"]))
				return array();
			
			if(self::$class_info[$class]["baseclass"] == $class) {
				$tables = array();
				if(!isset(self::$class_info[$class]["table_name"]) || empty(self::$class_info[$class]["table_name"]))
					return array();
				
				$tables[self::$class_info[$class]["table_name"]] = self::$class_info[$class]["table_name"];
				$tables[$class . "_state"] = $class . "_state";
				
				if(isset(self::$class_info[$class]["many_many_tables"]) && self::$class_info[$class]["many_many_tables"]) {
					foreach(self::$class_info[$class]["many_many_tables"] as $data) {
						$tables[$data["table"]] = $data["table"];
					}
				}
				
				foreach(self::getChildren($class) as $_class) {
					if(isset(self::$class_info[$_class]["table_name"]) && self::$class_info[$_class]["table_name"])
						$tables[self::$class_info[$_class]["table_name"]] = self::$class_info[$_class]["table_name"];
					
					if(isset(self::$class_info[$_class]["many_many_tables"]) && self::$class_info[$_class]["many_many_tables"]) {
						foreach(self::$class_info[$_class]["many_many_tables"] as $data) {
							$tables[$data["table"]] = $data["table"];
						}
					}
				}
				
				return $tables;
			} else {
				return self::Tables(self::$class_info[$class]["baseclass"]);
			}
		}
		
		/**
		 * returns the base-folder of a expansion or class
		 *
		 *@name getExpansionFolder
		 *@access public
		 *@param string - extension or class-name
		 *@param bool - if force to use as class-name
		*/
		public static function getExpansionFolder($name, $forceClass = false, $forceAbsolute = false) {
			if(is_object($name)) {
				if(!$forceClass && isset($name->inExpansion) && isset(self::$appENV["expansion"][strtolower($name->inExpansion)])) {
					$name = $name->inExpansion;
				} else {
					$name = $name->class;
				}
			}
			
			if(!$forceClass && isset(self::$appENV["expansion"][strtolower($name)])) {
				$folder = self::$appENV["expansion"][strtolower($name)]["folder"];
			} else
			
			if(isset(self::$class_info[strtolower($name)]["inExpansion"])) {
				$folder = self::$appENV["expansion"][self::$class_info[strtolower($name)]["inExpansion"]]["folder"];
			}
			
			
			if(isset($folder) && !$forceAbsolute) {
				if(substr($folder, 0, strlen(ROOT)) == ROOT) {
					return substr($folder, strlen(ROOT));
				} else {
					return $folder;
				}
			} else if(isset($folder)) {
				return realpath($folder) . "/";
			} else {
				return false;
			}
		}
		
		/**
		 * gets the full version of the installed app
		 *
		 *@name appVersion
		 *@access public
		*/
		public static function appVersion() {
			if(isset(self::$appENV["app"]["build"]))
				return self::$appENV["app"]["version"] . "-" . self::$appENV["app"]["build"];
				
			return self::$appENV["app"]["version"];
		}
		
		/**
		 * gets the full version of a installed expansion
		 *
		 *@name expVersion
		 *@access public
		 *@param string - name of expansion
		*/
		public static function expVersion($name) {
			if(!isset(self::$appENV["expansion"][$name]))
				return false;
			
			if(isset(self::$appENV["expansion"][$name]["build"]))
				return self::$appENV["expansion"][$name]["version"] . "-" . self::$appENV["expansion"][$name]["build"];
				
			return self::$appENV["expansion"][$name]["version"];
		}
		
		/**
		 * finds a file belonging to a class
		 *
		 *@name findFile
		 *@access public
		 *@param string - file
		 *@param string - class
		*/
		public function findFile($file, $class) {
			if($folder = self::getExpansionFolder($class)) {
				if(file_exists($folder . "/" . $file)) {
					return $folder . "/" . $file;
				}
			}
			
			if(is_object($class)) {
				$class = $class->class;
			}
			
			if(isset(self::$files[$class])) {
				if(file_exists(dirname(self::$files[$class]) . "/" . $file)) {
					return dirname(self::$files[$class]) . "/" . $file;
				}
			}
			
			if(file_exists($file))
				return $file;
			else
				return false;
		}
		
		/**
		 * finds a file belonging to a class with absolute path
		 *
		 *@name findFileAbsolute
		 *@access public
		 *@param string - file
		 *@param string - class
		*/
		public function findFileAbsolute($file, $class) {
			if($path = self::findFile($file, $class))
				return realpath($path);
			else
				return false;
		}
		
		/**
		 * finds a file belonging to a class with relative path
		 *
		 *@name findFileRelative
		 *@access public
		 *@param string - file
		 *@param string - class
		*/
		public function findFileRelative($file, $class) {
			if($path = self::findFile($file, $class)) {
				if(substr($path, 0, strlen(ROOT)) == ROOT) {
					return substr($path, strlen(ROOT));
				} else {
					return $path;
				}
			} else
				return false;
		}
		
		/**
		 * loads the classinfo from file
		 *@name loadFile
		 *@access public
		 *@return null
		*/
		public static function loadfile()
		{
				self::$tables = array();
				self::$database = array();
				$file = ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE;
				
				if(((!file_exists($file) || filemtime($file) < filemtime(FRAMEWORK_ROOT . "info.plist") || filemtime($file) < filemtime(ROOT . APPLICATION . "/info.plist") || filemtime($file) + self::$expiringTime < NOW) && (!function_exists("apc_exists") || !apc_exists(CLASS_INFO_DATAFILE))) || isset($_GET["flush"]))
				{
						if(PROFILE) Profiler::mark("generate_class_info");
						defined("GENERATE_CLASS_INFO") OR define('GENERATE_CLASS_INFO', true);
						logging('Regenerating Class-Info');
						
						
						// FIRST WE'VE GOT SOME ESSENTIELL CHECKS
						
						$permissionsValid = true;
						$permissionsFalse = "";
						@chmod(ROOT, 0777);
						// make some filesystem checks
						if(@fopen(ROOT . "write.test", "w")) {
							@unlink(ROOT . "write.test");
						} else {
							$permissionsValid = false;
							$permissionsFalse .= '<li>./</li>';
						}
						
						@chmod(ROOT . "system/temp/", 0777);
						if(!is_dir(ROOT . "system/temp/")) {
							mkdir(ROOT . "system/temp/", 0777, true);
							@chmod(ROOT . "system/temp/", 0777);
						} else {
							if(@fopen(ROOT . "system/temp/write.test", "w")) {
								@unlink(ROOT . "system/temp/write.test");
							} else {
								$permissionsValid = false;
								$permissionsFalse .= '<li>./system/temp/</li>';
							}
						}
						
						@chmod(APP_FOLDER . "temp/", 0777);
						if(!is_dir(APP_FOLDER . "temp/")) {
							mkdir(APP_FOLDER . "temp/", 0777, true);
							@chmod(APP_FOLDER . "temp/", 0777);
						} else {
							if(fopen(APP_FOLDER . "temp/write.test", "w")) {
								@unlink(APP_FOLDER . "temp/write.test");
							} else {
								$permissionsValid = false;
								$permissionsFalse .= '<li>./'.APPLICATION.'/temp/</li>';
							}
						}
						
						if(!is_dir(APP_FOLDER . LOG_FOLDER)) {
							mkdir(APP_FOLDER . LOG_FOLDER, 0777, true);
							@chmod(APP_FOLDER . LOG_FOLDER, 0777);
						} else {
							@chmod(APP_FOLDER . LOG_FOLDER, 0777);
							if(@fopen(APP_FOLDER . LOG_FOLDER . "/write.test", "w")) {
								@unlink(APP_FOLDER . LOG_FOLDER . "/write.test");
							} else {
								$permissionsValid = false;
								$permissionsFalse .= '<li>./'.APPLICATION.'/'.LOG_FOLDER.'/</li>';
							}
						}
						
						if(!is_dir(APP_FOLDER . "code/")) {
							mkdir(APP_FOLDER . "code/", 0777, true);
							@chmod(APP_FOLDER . "code/", 0777);
						}
						
						if(file_exists(APP_FOLDER . "uploaded/")) {
							@chmod(APP_FOLDER . "uploaded/", 0777);
							if(@fopen(APP_FOLDER . "uploaded/write.test", "w")) {
								@unlink(APP_FOLDER . "uploaded/write.test");
							} else {
								$permissionsValid = false;
								$permissionsFalse .= '<li>./'.APPLICATION.'/uploaded/</li>';
							}
						
						}
						
						@chmod(ROOT . "tpl/", 0777);
						if(@fopen(ROOT . "tpl/write.test", "w")) {
							@unlink(ROOT . "tpl/write.test");
						} else {
							$permissionsValid = false;
							$permissionsFalse .= '<li>./tpl/</li>';
						}
						
						
						$appFolder = ROOT . "system/installer/data/apps";
						@chmod($appFolder, 0777);
						if(@fopen($appFolder . "/write.test", "w")) {
							@unlink($appFolder . "/write.test");
							
							$files = scandir($appFolder);
							if(file_exists($appFolder . "/.index.db")) {
								$data = unserialize(file_get_contents($appFolder . "/.index.db"));
							} else {
								$data = array("fileindex" => array(), "packages" => array());
							}
							if($data["fileindex"] != $files) {
								$data["fileindex"] = $files;
								foreach($files as $file) {
									if(preg_match('/\.gfs$/i', $file)) {
										require_once(FRAMEWORK_ROOT . "/libs/GFS/gfs.php");
										require_once(FRAMEWORK_ROOT . "/libs/thirdparty/plist/CFPropertyList.php");
										if(file_exists($appFolder . "/" . $file . ".plist") && filemtime($appFolder . "/" . $file . ".plist") >= filemtime($appFolder . "/" . $file)) {
											$plist = new CFPropertyList();
											$plist->parse(file_get_contents($appFolder . "/" . $file . ".plist"));
											$info = $plist->toArray();
											
											$info["file"] = $file;
											if(isset($info["type"], $info["version"])) {
												if(isset($info["name"])) {
													$data["packages"][$info["type"]][$info["name"]][$info["version"]] = $info;
												} else {
													$data["packages"][$info["type"]][$info["version"]] = $info;
												}
											}
										} else {
											$gfs = new GFS($appFolder . "/" . $file);
											if($gfs->valid) {
												$info = $gfs->parsePlist("info.plist");
												$info["file"] = $file;
												if(isset($info["type"], $info["version"])) {
													if(isset($info["name"])) {
														$data["packages"][$info["type"]][$info["name"]][$info["version"]] = $info;
													} else {
														$data["packages"][$info["type"]][$info["version"]] = $info;
													}
												}
												
												$gfs->writeToFileSystem("info.plist", $appFolder . "/" . $file . ".plist");
											} else {
												if($gfs->error == 1) {
													$permissionsValid = false;
													$permissionsFalse .= '<li>./system/installer/data/apps/'.$file.'</li>';
												}
											}
										}
									}
								}

								if($permissionsValid) {
									FileSystem::write(ROOT . "system/installer/data/apps/.index.db", serialize($data));
								}
							}
							
							
							unset($files, $data);
						} else {
							$permissionsValid = false;
							$permissionsFalse .= '<li>./system/installer/data/apps/</li>';
						}
						
						if($permissionsValid === false) {
							$data = file_get_contents(FRAMEWORK_ROOT . "templates/framework/permission_fail.html");
							$data = str_replace('{BASE_URI}', BASE_URI, $data);
							$data = str_replace('{$permission_errors}',$permissionsFalse, $data);
							header("content-type: text/html;charset=UTF-8");
							header("X-Powered-By: Goma Framework " . GOMA_VERSION . "-" . BUILD_VERSION);
							echo $data;
							exit;
						}
						
						unset($permissionValid, $permissionFalse);
						
						// some global tests for the framework to run
						if(function_exists("gd_info"))
						{
							$data = gd_info();
							if(preg_match('/2/',$data["GD Version"])) {
								// okay
								unset($data);
							} else {
								$error = file_get_contents(ROOT . "system/templates/framework/software_run_fail.html");
								$error = str_replace('{$error}', 'You need to have a installed GD-Library 2.', $error);
								$error = str_replace('{BASE_URI}', BASE_URI, $error);
								header("HTTP/1.1 500 Server Error");
								echo $error;
								exit;
							}
						} else
						{
							$error = file_get_contents(ROOT . "system/templates/framework/software_run_fail.html");
							$error = str_replace('{$error}', 'You need to have a installed GD-Library 2.', $error);
							$error = str_replace('{BASE_URI}', BASE_URI, $error);
							header("HTTP/1.1 500 Server Error");
							echo $error;
							exit;
						}
						
						if(!class_exists("reflectionClass")) {
							$error = file_get_contents(ROOT . "system/templates/framework/software_run_fail.html");
							$error = str_replace('{$error}', 'You need to have the Reflection-API installed.', $error);
							$error = str_replace('{BASE_URI}', BASE_URI, $error);
							header("HTTP/1.1 500 Server Error");
							echo $error;
							exit;
						}
						
						// END TESTS
						
						if(file_exists($file) && (filemtime($file) < filemtime(FRAMEWORK_ROOT . "info.plist") || filemtime($file) < filemtime(ROOT . APPLICATION . "/info.plist"))) {
							if(!preg_match("/^dev/i", URL)) {
								$_SESSION["dev_without_perms"] = true;
								header("Location:" . BASE_URI . BASE_SCRIPT . "dev?redirect=" . urlencode($_SERVER["REQUEST_URI"]));
								exit;	
							}
						}
						
						writeProjectConfig();
						writeSystemConfig();
						
						// end filesystem checks
						
						
						require_once(ROOT . "system/libs/thirdparty/plist/CFPropertyList.php");
						// get some data about app-env
						$frameworkplist = new CFPropertyList(FRAMEWORK_ROOT . "info.plist");
						$frameworkenv = $frameworkplist->toArray();
						
						$appplist = new CFPropertyList(ROOT . APPLICATION . "/info.plist");
						$appenv = $appplist->toArray();
						
						self::$appENV["framework"] = $frameworkenv;
						self::$appENV["app"] = $appenv;
						
						unset($frameworkplist, $frameworkenv, $appplist, $appenv);
						
						self::$files = array();
						self::$class_info = array();
						self::$childData = array();
						
						if(PROFILE) Profiler::mark("ClassManifest::indexFiles");
						
						ClassManifest::generate_all_class_manifest(self::$files, self::$class_info, self::$appENV);
						
						if(PROFILE) Profiler::unmark("ClassManifest::indexFiles");
								
						if(defined("SQL_LOADUP")) {
							self::$appENV["app"]["SQL"] = true;
						} else {
							self::$appENV["app"]["SQL"] = false;
						}
						
						$wasUnavailable = isProjectUnavailable();
						makeProjectUnavailable();
						
						Core::deletecache(true);
						
						// register shutdown hook
						register_shutdown_function(array("ClassInfo", "finalizeClassInfo"));
						
						foreach(ClassInfo::$class_info as $class => $data) {
							$_c = $class;
							while(isset(ClassInfo::$class_info[$_c]["parent"]))
							{
								$_c = ClassInfo::$class_info[$_c]["parent"];
								self::$class_info[$_c]["child"][$class] = $class;
								if(isset(ClassInfo::$class_info[$class]["interfaces"])) {
									ClassInfo::$class_info[$class]["interfaces"] = array_values(ArrayLib::key_value(array_merge(ClassInfo::$class_info[$class]["interfaces"], ClassInfo::getInterfaces($_c))));
								} else {
									ClassInfo::$class_info[$class]["interfaces"] = ClassInfo::getInterfaces($_c);
								}
							}
							
							if(isset(ClassInfo::$class_info[$class]["interfaces"]) && count(ClassInfo::$class_info[$class]["interfaces"]) == 0)
								unset(ClassInfo::$class_info[$class]["interfaces"]);
							
							unset($_c, $class, $data);
						}
						
						if(PROFILE) Profiler::mark("include_all");
						
						ClassManifest::include_all();
						
						if(PROFILE) Profiler::unmark("include_all");
						
						defined("CLASS_INFO_LOADED") OR define("CLASS_INFO_LOADED", true);
						
						// patch until 2.1 daisy, then we drop this
						if(file_exists(APP_FOLDER . "/application/config.php"))
							ClassManifest::addPreload(APP_FOLDER . "/application/config.php");
						
						// normal code
						foreach(ClassManifest::$preload as $_file) {
							require_once($_file);
						}
						
						if(PROFILE) Profiler::mark("classinfo_renderafter");
						
						foreach(self::$class_info as $class => $data)
						{
							Object::instance("ClassInfo")->callExtending("generate", $class);
								
							// generates save-vars
							if(class_exists($class) && is_subclass_of($class , "object") || $class == "object") {
								// save vars
								foreach(self::getSaveVars($class) as $value) {
									self::$class_info[$class][$value] = self::getStatic($class, $value);
									unset($value);
								}
								// ci-funcs
								// that are own function, for generating class-info
								foreach(classinfo::getStatic($class, "ci_funcs") as $name => $func) {
									self::$class_info[$class][$name] = classinfo::callStatic($class, $func);
									unset($name, $func);
								}
							}
							
							unset($class, $data);
						}
						
						foreach(self::$class_info as $class => $data)
						{
							if(!ClassInfo::isAbstract($class)) {
								if(ClassInfo::hasInterface($class, "PermProvider")) {
									$c = new $class();
									Permission::addPermissions($c->providePerms());
								}
								
								if(Object::method_exists($class, "generateClassInfo")) {
									if(!isset($c)) $c = new $class;
									$c->generateClassInfo();
								}
								unset($c);
							}
							
							if(isset(self::$class_info[$class]["child"]) && !empty(self::$class_info[$class]["child"])) {
								self::$childData[$class] = self::$class_info[$class]["child"];
								//chmod($f, 0777);
								self::$class_info[$class]["child"] = null;
								unset($_file, $f, $children, self::$class_info[$class]["child"]);
							}
							
							unset($class, $data);
						}
						
						FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . ".children" .  CLASS_INFO_DATAFILE, "<?php\n\$children = ".var_export(self::$childData, true).";");
						
						// reappend
						self::$class_info["permission"]["providedPermissions"] = Permission::$providedPermissions;
											
						if(PROFILE) Profiler::unmark("classinfo_renderafter");
						
						$php = "<?php\n";
						$php .= "ClassInfo::\$appENV = ".var_export(self::$appENV, true).";\n";
						$php .= "ClassInfo::\$class_info = ".var_export(self::$class_info, true).";\n";
						$php .= "ClassInfo::\$files = ".var_export(self::$files, true).";\n";
						$php .= "ClassInfo::\$tables = ".var_export(self::$tables, true).";\n";
						$php .= "ClassInfo::\$database = ".var_export(self::$database, true).";\n";
						$php .= "ClassManifest::\$preload = ".var_export(array_values(ClassManifest::$preload), true).";\n";
						$php .= "\$root = ".var_export(ROOT, true).";";
						$php .= "\$version = ".var_export(self::VERSION, true).";";
						
						foreach(ClassManifest::$preload as $_file) {
							$php .= "\ninclude_once(".var_export($_file, true).");";
						}
						
						if(!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, $php))
						{
								throwError("8", 'Could not write in cache-directory', 'Could not write '.$file.'');
						}
						
						if(function_exists("apc_store")) {
							$data = array(
								"appENV" 		=> self::$appENV,
								"class_info"	=> self::$class_info,
								"files"			=> self::$files,
								"tables"		=> self::$tables,
								"database"		=> self::$database,
								"preload"		=> ClassManifest::$preload,
								"childData"		=> self::$childData,
								"root"			=> ROOT,
								"version"		=> self::VERSION
							);
							apc_store(CLASS_INFO_DATAFILE, $data);
						}
						
						//chmod(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, 0773);
						
						if(!$wasUnavailable)
							makeProjectAvailable();
						
						define("CLASS_INFO_GENERATED", true);
						
						DataObject::$donothing = false; // dataobject reset
						Object::$cache_singleton_classes = array(); // object reset
						self::$set_save_vars = array(); // class_info reset
						
						// run hooks
						if(self::$ClassInfoHooks) {
							foreach(self::$ClassInfoHooks as $hook) {
								call_user_func_array($hook, array());
							}
						}
					
						if(PROFILE) Profiler::unmark("generate_class_info");
						
						// now check for upgrade-scripts
						if(PROFILE) Profiler::mark("checkVersion");
	
						// first framework!
						self::checkForUpgradeScripts(ROOT . "system", GOMA_VERSION . "-" . BUILD_VERSION);
						
						// second application!
						self::checkForUpgradeScripts(ROOT . APPLICATION, self::appversion());
						
						// expansions
						foreach(self::$appENV["expansion"] as $expansion => $data) {
							self::checkForUpgradeScripts(self::getExpansionFolder($expansion), self::expVersion($expansion));
						}
						
						if(PROFILE) Profiler::unmark("checkVersion");
				} else
				{
						defined("CLASS_INFO_LOADED") OR define("CLASS_INFO_LOADED", true);
						
						// apc-cache
						if(function_exists("apc_fetch") && apc_exists(CLASS_INFO_DATAFILE)) {
							$data = apc_fetch(CLASS_INFO_DATAFILE);
							if($data["root"] != ROOT || !isset($data["root"]) || !isset($data["version"]) || version_compare($data["version"], self::VERSION, "<")) {
								ClassInfo::delete();
								clearstatcache();
								ClassInfo::loadfile();
								return ;
							}
							
							// init vars
							self::$appENV = $data["appENV"];
							self::$class_info = $data["class_info"];
							self::$files = $data["files"];
							self::$tables = $data["tables"];
							self::$database = $data["database"];
							self::$childData = $data["childData"];
							ClassManifest::$preload = $data["preload"];
							
							// load preloads
							foreach($data["preload"] as $file) {
								if(file_exists($file)) {
									include_once($file);
								} else {
									ClassInfo::delete();
									clearstatcache();
									ClassInfo::loadfile();
									return ;
								}
							}
							
							// run hooks
							if(self::$ClassInfoHooks) {
								foreach(self::$ClassInfoHooks as $hook) {
									call_user_func_array($hook, array());
								}
							}
						} else {
							
							// just include file
							try {
								include(ROOT . CACHE_DIRECTORY . "/" . CLASS_INFO_DATAFILE);
							} catch (Exception $e) {
								ClassInfo::delete();
								clearstatcache();
								ClassInfo::loadfile();
								return ;
							}
	
							if($root != ROOT || !isset($root) || !isset($version) || version_compare($version, self::VERSION, "<")) {
								ClassInfo::delete();
								clearstatcache();
								ClassInfo::loadfile();
								return ;
							}
							
							if(defined("SQL_LOADUP") && self::$appENV["app"]["SQL"] === false) {
								ClassInfo::delete();
								ClassInfo::loadfile();
								return ;
							}
							
							if(function_exists("apc_store")) {
								ClassInfo::Write();
							}
							
							// run hooks
							if(self::$ClassInfoHooks) {
								foreach(self::$ClassInfoHooks as $hook) {
									call_user_func_array($hook, array());
								}
							}
						}
				}
				
				defined("APPLICATION_VERSION") OR define("APPLICATION_VERSION", self::$appENV["app"]["version"]);
				defined("APPLICATION_BUILD") OR define("APPLICATION_BUILD", self::$appENV["app"]["build"]);
				
				if(isset(self::$appENV["app"]["requireFrameworkVersion"])) {
					if(version_compare(self::$appENV["app"]["requireFrameworkVersion"], GOMA_VERSION . "-".BUILD_VERSION, ">")) {
						throwError(7, 'Application-Error', "Application does not support this version of the goma-framework, please update the framework to <strong>".self::$appENV["app"]["requireFrameworkVersion"]."</strong>. <br />Framework-Version: <strong>".GOMA_VERSION."-".BUILD_VERSION."</strong>");
					}
				}
		}
		
		/**
		 * finalizes the classinfo
		 *
		 *@name finalizeClassInfo
		 *@access public
		*/
		static function finalizeClassInfo() {
			if(defined("CLASS_INFO_GENERATED") && !defined("ERROR_CODE")) {
				logging("finalize");
				foreach(ClassInfo::$class_info as $class => $data) {
					// generates save-vars
					if(class_exists($class) && is_subclass_of($class , "object") || $class == "object") {
						// save vars
						foreach(self::getSaveVars($class) as $value) {
							self::$class_info[$class][$value] = self::getStatic($class, $value);
						}
						// ci-funcs
						// that are own function, for generating class-info
						foreach(ClassInfo::getStatic($class, "ci_funcs") as $name => $func) {
							self::$class_info[$class][$name] = classinfo::callStatic($class, $func);
						}
					}
				}
				
				ClassInfo::write();
			}
		}
		
		/**
		 * this function checks for upgrade-scripts in a given folder with given current version
		 *
		 *@name checkForUpgradeScripts
		 *@access public
		 *@param folder
		 *@param version
		*/
		public static function checkForUpgradeScripts($folder, $current_version) {
			if(file_exists($folder . "/version.php")) {
				include($folder . "/version.php");
			} else {
				$version = 0;
			}
			
			if(goma_version_compare($current_version, $version, ">")) {
				// run upgrade-scripts
				if(is_dir($folder. "/upgrade")) {
					$files = scandir($folder . "/upgrade");
					$versions = array();
					foreach($files as $file) {
						if(is_file($folder . "/upgrade/" . $file) && preg_match('/\.php$/i', $file)) {
							if(goma_version_compare(substr($file, 0, -4), $version, ">")) {
								$versions[] = substr($file, 0, -4);
							}
						}
					}
					usort($versions, "goma_version_compare");
					foreach($versions as $v) {
						FileSystem::write($folder . "/version.php", '<?php $version = '.var_export($v, true).';');
						include($folder . "/upgrade/" . $v . ".php");
					}
					FileSystem::write($folder . "/version.php", '<?php $version = '.var_export($current_version, true).';');
					ClassInfo::delete();
					
					$http = (isset($_SERVER["HTTPS"])) ? "https" : "http";
					$port = $_SERVER["SERVER_PORT"];
					if($http == "http" && $port == 80){
						$port = "";
					} else if($http == "https" && $port == 443){
						$port = "";
					} else {
						$port = ":" . $port;
					}
					header("Location: " . $http . "://" . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"]);
					exit;
				}
				FileSystem::write($folder . "/version.php", '<?php $version = '.var_export($current_version, true).';');
			}
		}
		
		/**
		 * deletes the file
		 *@name delete
		 *@access public
		*/
		public static function delete()
		{
			if(file_exists(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE))
				if(!@unlink(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE))
					throwError("8", 'Could not write in cache-directory', 'Could not delete '.CACHE_DIRECTORY . CLASS_INFO_DATAFILE.'');
			
			if(function_exists("apc_delete"))
				apc_delete(CLASS_INFO_DATAFILE);
			
			return true;
		}
		/**
		 * gets dataclasses for a given dataobject
		 *@name dataclasses
		 *@access public
		 *@param string
		*/
		public static function dataclasses($class)
		{
				if(isset(self::$class_info[$class]["dataclasses"]))
				{
						$dataclasses = array();
						foreach(self::$class_info[$class]["dataclasses"] as $c)
						{
								if(isset(self::$class_info[$c]["table_name"]) && self::$class_info[$c]["table_name"] !== false)
								{
										$dataclasses[$c] = self::$class_info[$c]["table_name"];
								}
						}
						
						return $dataclasses;
				} else
				{
						return false;
				}
		}
		
		/**
		 * writes the file
		 *@name write
		 *@access public
		*/
		public static function write()
		{
				// first consolidate data
				if(self::$childData == array() && file_exists(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE)) {
					include(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE);
					self::$childData = $children;
				}
				
				// generate file
				$php = "<?php\n";
				$php .= "ClassInfo::\$appENV = ".var_export(self::$appENV, true).";\n";
				$php .= "ClassInfo::\$class_info = ".var_export(self::$class_info, true).";\n";
				$php .= "ClassInfo::\$files = ".var_export(self::$files, true).";\n";
				$php .= "ClassInfo::\$tables = ".var_export(self::$tables, true).";\n";
				$php .= "ClassInfo::\$database = ".var_export(self::$database, true).";\n";
				$php .= "ClassManifest::\$preload = ".var_export(array_values(ClassManifest::$preload), true).";\n";
				$php .= "\$root = ".var_export(ROOT, true).";";
				$php .= "\$version = ".var_export(self::VERSION, true).";";
				
				foreach(ClassManifest::$preload as $_file) {
					$php .= "\ninclude_once(".var_export($_file, true).");";
				}
				
				// generate apc-part
				if(function_exists("apc_store")) {
					$data = array(
						"appENV" 		=> self::$appENV,
						"class_info"	=> self::$class_info,
						"files"			=> self::$files,
						"tables"		=> self::$tables,
						"database"		=> self::$database,
						"preload"		=> ClassManifest::$preload,
						"childData"		=> self::$childData,
						"root"			=> ROOT,
						"version"		=> self::VERSION
					);
					apc_store(CLASS_INFO_DATAFILE, $data);
				}
				
				// write files
				if(!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, $php) || !FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . ".children" .  CLASS_INFO_DATAFILE, "<?php\n\$children = ".var_export(self::$childData, true).";"))
				{
					throwError("8", 'Could not write in cache-directory', 'Could not write '.ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE.'');
				} else {
					return true;
				}
				
		}

		/**
		 * adds a table-object-relation
		 *@name addTable
		 *@access public
		 *@param string - table
		 *@param string - object
		*/
		public static function addTable($table, $object)
		{
				self::$tables[$table] = $object;
		}
}

ClassInfo::addSaveVar("object", "extensions");
ClassInfo::addSaveVar("object", "extra_methods");