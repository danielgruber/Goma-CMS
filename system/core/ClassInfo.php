<?php
/**
  * this class let you know much about other classes or your class
  * you can get childs or other things
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 17.11.2011
  * $Version 003
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

define("CLASS_INFO_DATAFILE", "class_info.php.goma");

interface SaveVarSetter {
	public static function __setSaveVars($class);
}

class ClassInfo extends Object
{
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
		 *@name getChildren
		 *@access public
		 *@param string - class_name
		*/
		public static function getChildren($class)
		{
				
				$class = strtolower($class);
				if(isset(self::$class_info[$class]["child"]))
					return self::$class_info[$class]["child"];
				
				Profiler::mark("classinfo::getChildren");
				
				$f = ROOT . CACHE_DIRECTORY . "classparse." . md5($class) . ".php.goma";
				if(file_exists($f)) {
					include($f);
					self::$class_info[$class]["child"] = $children;
					Profiler::unmark("classinfo::getChildren");
					return $children;
				} else {
					self::$class_info[$class]["child"] = array();
					Profiler::unmark("classinfo::getChildren");
					return array();
				}
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
		 * classcache
		 *@name classcache
		 *@access private
		 *@var array
		*/
		private static $classcache = array();
		/**
		 * get classes by regexp
		 *@name getClasses
		 *@param string - regexp for _eregi
		 *@param array - static vars the 
		 *@access public static
		*/
		public static function getClasses($regexp, $must_static_vars = array())
		{
				if(isset(self::$classcache[$regexp]))
				{
						return self::$classcache[$regexp];
				}
				$cacher = new cacher("class_cache_" . md5($regexp));
				if($cacher->checkvalid())
				{
						self::$classcache[$regexp] = $cacher->checkvalid();
						return self::$classcache[$regexp];
				} else
				{
						autoloader::include_all();
						foreach(get_declared_classes() as $class)
						{
								if(_eregi($regexp, $class))
								{
										foreach($must_static_vars as $var)
										{
												if(!self::isStatic($class, $var))
												{
														continue;
												}
												if(self::getStatic($class, $var) == "")
												{
														continue;
												}
										}
										self::$classcache[$regexp][] = $class;
								}
						}
						$cacher->write(self::$classcache[$regexp], 120);
						return self::$classcache[$regexp];
				}
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
		 * gets the filename of an class
		 *@name file
		 *@access public
		 *@param string - class_name
		 *@return string
		*/
		public static function file($class_name)
		{
				return self::$class_info[$class_name]["file"];
		}
		/**
		 * includes all php-files
		 *@include_all
		 *@access public
		*/
		public static function include_all()
		{
				self::include_dir(ROOT . "includes/");
		}
		/**
		 * private function to include a directory recursivly
		 *@access private
		 *@param string - dir
		*/
		public static function include_dir($dir)
		{
				foreach(scandir($dir) as $file)
				{
						if(is_dir($dir . "/" . $file) && $file != "." && $file != ".." && !file_exists($dir . "/" . $file . "/autoloader_exclude" ))
						{
								self::include_dir($dir . "/" . $file);
						} else if(preg_match("/\.php$/Usi", $file))
						{
								require_once($dir . "/" . $file);
						}
				}
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
							}
					}
					
					if(ClassInfo::hasInterface($class, "saveVarSetter"))
						call_user_func_array(array($class, "__setSaveVars"), array($class));
								
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
		public function get_parent_class($class) {
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
		 * loads the classinfo from file
		 *@name loadFile
		 *@access public
		 *@return null
		*/
		public static function loadfile()
		{
				self::$class_info = array();
				self::$tables = array();
				self::$database = array();
				$file = ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE;
				if(file_exists($file)) {
					$mtime = filemtime($file);
					$exists = true;
				} else {
					$mtime = null;
					$exists = false;
				}
				
				
				
				if(!$exists || !isset($mtime) || $mtime < filemtime(FRAMEWORK_ROOT . "info.plist") || $mtime < filemtime(ROOT . APPLICATION . "/info.plist"))
				{
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
						if(@fopen(ROOT . "system/temp/write.test", "w")) {
							@unlink(ROOT . "system/temp/write.test");
						} else {
							$permissionsValid = false;
							$permissionsFalse .= '<li>./system/temp/</li>';
						}
						
						@chmod(APP_FOLDER . "temp/", 0777);
						if(!is_dir(APP_FOLDER . "temp/")) {
							mkdir(APP_FOLDER . "temp/", 0777, true);
						} else {
							if(fopen(APP_FOLDER . "temp/write.test", "w")) {
								@unlink(APP_FOLDER . "temp/write.test");
							} else {
								$permissionsValid = false;
								$permissionsFalse .= '<li>./'.APPLICATION.'/temp/</li>';
							}
						}
						
						if(!is_dir(APP_FOLDER . "log/")) {
							mkdir(APP_FOLDER . "log/", 0777, true);
						} else {
							@chmod(APP_FOLDER . "log/", 0777);
							if(@fopen(APP_FOLDER . "log/write.test", "w")) {
								@unlink(APP_FOLDER . "log/write.test");
							} else {
								$permissionsValid = false;
								$permissionsFalse .= '<li>./'.APPLICATION.'/log/</li>';
							}
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
						
						@chmod(ROOT . "system/installer/data/apps/", 0777);
						if(@fopen(ROOT . "system/installer/data/apps/write.test", "w")) {
							@unlink(ROOT . "system/installer/data/apps/write.test");
						} else {
							$permissionsValid = false;
							$permissionsFalse .= '<li>./system/installer/data/apps/*.*</li>';
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
						
						
						// end filesystem checks
						
						// get some data about app-env
						$frameworkplist = new CFPropertyList(FRAMEWORK_ROOT . "info.plist");
						$frameworkenv = $frameworkplist->toArray();
						
						$appplist = new CFPropertyList(ROOT . APPLICATION . "/info.plist");
						$appenv = $appplist->toArray();
						
						self::$appENV = array(
							"framework" => $frameworkenv,
							"app"		=> $appenv
						);
						
						// framework app
						unset($latestVersion);
						$appPackagePath = FRAMEWORK_ROOT . "installer/data/apps/framework/";
						if(file_exists($appPackagePath)) {
							// then check updates for the app-setup
							if(!isset(self::$appENV["app"]["prohibitUpdate"])) {
								if(!file_exists($appPackagePath . ".latest_version") || filemtime($appPackagePath . "/.latest_version") < NOW + 86400 || filemtime($appPackagePath . "/.latest_version") < filemtime($appPackagePath)) {
									$latestVersion = 0;
									foreach(scandir($appPackagePath) as $file) {
										if(preg_match('/^(.*)\.gfs$/i', $file, $matches)) {
											if(version_compare($matches[1], $latestVersion, ">")) {
												$latestVersion = $matches[1];
											}
										}
									}
									@chmod($appPackagePath, 0777);
									if(!file_put_contents($appPackagePath . "/.latest_version", $latestVersion)) {
										die('<h3>Please set Permissions of install/data/apps/framework/  recursivly to 0777.</h3>');
									}
								}
								
								if(isset($latestVersion)) {
									$version = $latestVersion;
								} else {
									$version = file_get_contents($appPackagePath . "/.latest_version");
								}
								if(version_compare($version, self::$appENV["framework"]["version"] . "-" . self::$appENV["framework"]["build"], ">")) {
									$gfs = new GFS_Package_installer($appPackagePath . $version . ".gfs");
									if($gfs->unpack(ROOT . APPLICATION . "/")) {
										$_SESSION["dev_without_perms"] = true;
										HTTPResponse::redirect(ROOT_PATH . "dev");
										exit;
									}
								}
							}
						}
						
						// current app
						unset($latestVersion);
						$appPackagePath = FRAMEWORK_ROOT . "installer/data/apps/" . self::$appENV["app"]["name"] ."/";
						if(file_exists($appPackagePath)) {
							// then check updates for the app-setup
							if(!isset(self::$appENV["app"]["prohibitUpdate"])) {
								if(!file_exists($appPackagePath . ".latest_version") || filemtime($appPackagePath . "/.latest_version") < NOW + 86400 || filemtime($appPackagePath . "/.latest_version") < filemtime($appPackagePath)) {
									$latestVersion = 0;
									foreach(scandir($appPackagePath) as $file) {
										if(preg_match('/^(.*)\.gfs$/i', $file, $matches)) {
											if(version_compare($matches[1], $latestVersion, ">")) {
												$latestVersion = $matches[1];
											}
										}
									}
									@chmod($appPackagePath, 0777);
									if(!file_put_contents($appPackagePath . "/.latest_version", $latestVersion)) {
										die('<h3>Please set Permissions of install/data/apps/' . self::$appENV["app"]["name"] .'/  recursivly to 0777.</h3>');
									}
								}
								
								if(isset($latestVersion)) {
									$version = $latestVersion;
								} else {
									$version = file_get_contents($appPackagePath . "/.latest_version");
								}
								if(version_compare($version, self::$appENV["app"]["version"] . "-" . self::$appENV["app"]["build"], ">")) {
									$gfs = new GFS_Package_installer($appPackagePath . $version . ".gfs");
									if($gfs->unpack(ROOT . APPLICATION . "/")) {
										$_SESSION["dev_without_perms"] = true;
										HTTPResponse::redirect(ROOT_PATH . "dev");
										exit;
									}
								}
							}
						}
						
						if(defined("SQL_LOADUP")) {
							self::$appENV["app"]["SQL"] = true;
						} else {
							self::$appENV["app"]["SQL"] = false;
						}
						
						Profiler::mark("generate_class_info");
						defined("GENERATE_CLASS_INFO") OR define('GENERATE_CLASS_INFO', true);
						logging('Regenerating Class-Info');
						
						$wasUnavailable = isProjectUnavailable();
						makeProjectUnavailable();
						
						
						core::deletecache(true);
						autoloader::include_all();
						
						
						
						foreach(get_declared_classes() as $class)
						{
							if(is_subclass_of($class, "Object") || $class == "Object") {
								/*
								 * with the reflecton-class you can find out information of the class
								 * e.g. you find out the file, the methods or the parents.
								*/
								
								$class = strtolower($class);
								
								$reflection = new ReflectionClass($class);
								
								if($reflection->isInternal())
										continue;
								
								
								$parent = strtolower(get_parent_class($class));
								self::$class_info[$class]["parent"] = $parent;
								
								$_c = strtolower($class);
								while($_c = get_parent_class($_c))
								{					
										$_c = strtolower($_c);
										self::$class_info[$_c]["child"][$class] = $class;
								}
								
								
								if(is_subclass_of($class , "object") || $class == "object")
								{
										// save vars
										foreach(self::getSaveVars($class) as $value)
										{
												self::$class_info[$class][$value] = self::getStatic($class, $value);
										}
										// ci-funcs
										// that are own function, for generating class-info
										foreach(classinfo::getStatic($class, "ci_funcs") as $name => $func)
										{
												self::$class_info[$class][$name] = classinfo::callStatic($class, $func);
										}
								}
								
								
								if($reflection->isAbstract())
										self::$class_info[$class]["abstract"] = true;
								

								$interfaces = array_map("strtolower", $reflection->getInterfaceNames( ));
								
								if(count($interfaces) > 0)
									self::$class_info[$class]["interfaces"] = $interfaces;
								
								// get the filename for the autoloader
								
								self::$class_info[$class]["file"] = $reflection->getFileName( );
							}		
						}
						
						
						Profiler::mark("classinfo_renderafter");
						
						foreach(self::$class_info as $class => $data)
						{
								Object::instance("ClassInfo")->callExtending("generate", $class);
								
								$reflection = new ReflectionClass($class);
								if(!$reflection->isAbstract()) {
									if(!$reflection->isAbstract() && in_array("PermissionProvider", $reflection->getInterfaceNames()))
									{
											$c = new $class();
											foreach($c->providePermissions() as $key => $value) {
												self::$advrights[$key] = array_merge($value, array("name" => $key));	
											}
											unset($c, $key, $value);
									}
									if(Object::method_exists($class, "generateClassInfo")) {
										if(!isset($c)) $c = new $class;
										$c->generateClassInfo();
									}
									unset($c);
								}
								
								
								if(isset(self::$class_info[$class]["child"])) {
									$f = ROOT . CACHE_DIRECTORY . "classparse.".md5($class).".php.goma";
									file_put_contents($f, '<?php $children = ' . var_export(self::$class_info[$class]["child"], true) . ";");
									chmod($f, 0773);
									self::$class_info[$class]["child"] = null;
									unset($_file, $f, $children);
								}
						}
												
						Profiler::unmark("classinfo_renderafter");
						
						if(!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE,'<?php $data = ' . var_export(array("env" => self::$appENV, 'classes' => self::$class_info, 'advrights' => self::$advrights, 'tables' => self::$tables, 'database' => self::$database), true) . ";"))
						{
								throwError("8", 'Could not write in cache-directory', 'Could not write '.$file.'');
						}
						
						chmod(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, 0773);
						
						if(!$wasUnavailable)
							makeProjectAvailable();
						
						/*if(defined("DEV_BUILD"))
							ClassInfo::delete();*/
						
						DataObject::$donothing = false; // dataobject reset
						Object::$cache_singleton_classes = array(); // object reset
						
						Profiler::unmark("generate_class_info");
				} else
				{
						
						include(ROOT . CACHE_DIRECTORY . "/class_info.php.goma");
						
						self::$class_info = $data["classes"];
						self::$tables = $data["tables"];
						self::$database = $data["database"];
						self::$advrights = $data["advrights"];
						self::$appENV = $data["env"];
						
						if(defined("SQL_LOADUP") && self::$appENV["app"]["SQL"] === false) {
							ClassInfo::delete();
							ClassInfo::loadfile();
							return ;
						}
						
						
				}
				
				defined("APPLICATION_VERSION") OR define("APPLICATION_VERSION", self::$appENV["app"]["version"]);
				defined("APPLICATION_BUILD") OR define("APPLICATION_BUILD", self::$appENV["app"]["build"]);
				
				if(isset(self::$appENV["app"]["requireFrameworkVersion"])) {
					if(version_compare(self::$appENV["app"]["requireFrameworkVersion"], GOMA_VERSION . "-".BUILD_VERSION, ">")) {
						throwError(7, 'Application-Error', "Application does not support this version of the goma-framework, please update the framework to <strong>".self::$appENV["app"]["requireFrameworkVersion"]."</strong>. <br />Framework-Version: <strong>".GOMA_VERSION."-".BUILD_VERSION."</strong>");
					}
				}
				
				define("CLASS_INFO_LOADED", true);

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
				$file = ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE;
				if(FileSystem::writeFileContents($file,"<?php \$data = " . var_export(array("env" => self::$appENV, 'classes' => self::$class_info, 'advrights' => self::$advrights, 'tables' => self::$tables, 'database' => self::$database), true) . ";")) {
					return true;
				} else {
					throwError("8", 'Could not write in cache-directory', 'Could not write '.$file.'');
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
		/**
		 * list extensions of a dataobject
		*/
		public static function listExtensions($class)
		{
				$reflection = new ReflectionClass($class);
				$parent = $reflection->getParentClass();
				if($parent != "dataobject" && $class != "dataobject" && is_subclass_of($parent, "dataobject"))
				{
						$arr = self::listExtensions($parent);
				} else
				{
						$arr = array();
				}
				
				$extensions = self::getStatic("object", "extensions");
				
				$exts = (isset($extensions[$class])) ? $extensions[$class] : array();
				
				if(!is_array($exts))
				{
						$exts = array();
				}				
				return $exts + $arr;			
		}
}

