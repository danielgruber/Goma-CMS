<?php defined("IN_GOMA") OR die();
/**
 * @package		Goma\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


define("CLASS_INFO_DATAFILE", ".class_info.goma.php");

/**
 * This class provides information about other classes.
 *
 * @package		Goma\System\Core
 * @version		3.8
 */
class ClassInfo extends Object {
	/**
	 * version of class-info
	 *
	 */
	const VERSION = "3.6";

	/**
	 * defines when class-info expires
	 *
	 */
	public static $expiringTime = 604800;
	// 7 days by default

	/**
	 * classinfo
	 */
	public static $class_info = array();

	/**
	 * table-object-relations
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
	 */
	static public $database = array();

	/**
	 * data about the current environment
	 *
	 */
	public static $appENV = array();

	/**
	 * child-data
	 *
	 */
	private static $childData = array();

	/**
	 * files
	 *
	 */
	public static $files = array();

	/**
	 * array of classes, which we have already set SaveVars
	 *
	 */
	public static $set_save_vars = array();

	/**
	 * this var saves for each class, which want to save vars in cache, the names
	 *@var array
	 */
	public static $save_vars;

	/**
	 * hooks for current execution which are executed when classinfo is generated
	 *
	 */
	public static $ClassInfoHooks = array();
	
	/**
	 * list of all known interfaces.
	*/
	public static $interfaces = array();

    /**
     * registers a hook on class info loaded
     *
     * @param mixed - callback
     * @return bool
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
     * gets the childs of a class
     *
     * @param string - class_name
     * @return array
     */
	public static function getChildren($class) {

		$class = ClassManifest::resolveClassName($class);
		if(isset(self::$class_info[$class]["child"]))
			return self::$class_info[$class]["child"];

		if(self::$childData == array() && file_exists(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE)) {
			include (ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE);
			self::$childData = $children;
		}

		return isset(self::$childData[$class]) ? self::$childData[$class] : array();
	}

	/**
	 * gets interfaces of a class
	 *
	 */
	public static function getInterfaces($class) {
		$class = ClassManifest::resolveClassName($class);
		
		return isset(self::$class_info[$class]["interfaces"]) ? self::$class_info[$class]["interfaces"] : array();
	}

    /**
     * checks if the class has the interface
     *
     * @param string - class
     * @param string - interface
     * @return bool
     */
	public static function hasInterface($class, $interface) {
		$class = ClassManifest::resolveClassName($class);

		$interface = ClassManifest::resolveClassName($interface);
		return isset(self::$class_info[$class]["interfaces"]) ? in_array($interface, self::$class_info[$class]["interfaces"]) : false;
	}

	/**
	 * gets the parent class of a class
	 *
	 */
	public static function getParentClass($class) {
		$class = ClassManifest::resolveClassName($class);

		return isset(self::$class_info[$class]["parent"]) ? self::$class_info[$class]["parent"] : false;
	}

	/**
	 * gets a table_name for a given class
	 */
	public static function classTable($class) {
		$class = ClassManifest::resolveClassName($class);

		return isset(classinfo::$class_info[$class]["table"]) ? classinfo::$class_info[$class]["table"] : false;
	}

    /**
     * gets db-fields of an table
     * @param string - table
     * @return array
     */
	public static function getTableFields($table) {
		return isset(self::$database[$table]) ? self::$database[$table] : array();
	}

	/**
	 * checks if an class exists
	 *@param string - class_name
	 *@return bool
	 */
	public static function exists($class) {
		$class = ClassManifest::resolveClassName($class);

		return (isset(self::$class_info[strtolower($class)]) || class_exists($class, false));
	}

    /**
     * validates that a class can be created and returns classname if it can.
     *
     * @param string class
     * @return string
     * @throws LogicException
     */
    public static function find_creatable_class($class) {
        $class = self::find_class_name($class);

        if (self::isAbstract($class)) {
            throw new LogicException("Cannot initiate abstract Class");
        }

        return $class;
    }

    /**
     * returns if class exists and is not empty.
     * it returns correct class-name.
     *
     * @param string class
     * @return string
     * @throws LogicException
     */
    public static function find_class_name($class) {
        $class = ClassManifest::resolveClassName($class);

        if(!ClassInfo::exists($class) && !class_exists($class, false)) {
            throw new LogicException("Cannot find unknown class");
        }

        return $class;
    }

	/**
	 * adds a var to cache
	 *@param class - class_name
	 *@param name - var-name
	 */
	public static function addSaveVar($class, $name) {
		if(class_exists("ClassManifest"))
			$class = ClassManifest::resolveClassName($class);

		self::$save_vars[strtolower($class)][] = $name;
	}

	/**
	 * gets for a specific class the save_vars
	 *@name getSaveVars
	 *@param string - class-name
	 *@return array
	 */
	public static function getSaveVars($class) {
		$class = ClassManifest::resolveClassName($class);

		if(isset(self::$save_vars[strtolower($class)])) {
			return self::$save_vars[strtolower($class)];
		}
		return array();
	}

	/**
	 * sets the save_vars
	 */
	public static function setSaveVars($class) {
		if(PROFILE)
			Profiler::mark("ClassInfo::setSaveVars");

		if(count(self::$class_info) > 0) {
			$class = ClassManifest::resolveClassName($class);

			if(!defined('GENERATE_CLASS_INFO') && !isset(self::$set_save_vars[$class])) {
				foreach(self::getSaveVars($class) as $var) {
					if(isset(self::$class_info[$class][$var])) {
						self::setStatic($class, $var, self::$class_info[$class][$var]);
					}
				}

				unset($var);
			}

			if(ClassInfo::hasInterface($class, "saveVarSetter") && Object::method_exists($class, "__setSaveVars")) {
				call_user_func_array(array($class, "__setSaveVars"), array($class));
			}

			self::$set_save_vars[$class] = true;
		}

		if(PROFILE)
			Profiler::unmark("ClassInfo::setSaveVars");
	}

	/**
	 * checks if class is abstract
	 */
	public static function isAbstract($class) {
		$class = ClassManifest::resolveClassName($class);
		if(isset(self::$class_info[$class]["abstract"])) {
			return self::$class_info[$class]["abstract"];
		} else {
			return false;
		}
	}

	/**
	 * gets the parent class of a given class
	 *
	 */
	public static function get_parent_class($class) {
		$class = ClassManifest::resolveClassName($class);

		if(isset(self::$class_info[$class]["parent"])) {
			return self::$class_info[$class]["parent"];
		} else {
			return null;
		}
	}

	/**
	 * gets info to a specific class
	 *
	 */
	public static function getInfo($class) {
		$class = ClassManifest::resolveClassName($class);

		if(isset(self::$class_info[$class])) {
			return self::$class_info[$class];
		} else {
			return false;
		}
	}

	/**
	 * returns a list of database-tables that can be referred to the DataObject.
	 * TODO: Move to Model.
	 * 
	 * @name 	Tables
	 * @param 	string class
	 * @return 	array
	 */
	public static function Tables($class) {
		$class = ClassManifest::resolveClassName($class);

		if(!isset(self::$class_info[$class]["baseclass"]))
			return array();

		if(self::$class_info[$class]["baseclass"] == $class) {
			return self::TablesBaseClass($class);
		} else {
			return self::TablesBaseClass(self::$class_info[$class]["baseclass"]);
		}
	}

	/**
	 * gets all referred database-tables for a given baseclass.
	 * this method does not check for Base-Class.
	 *
	 * @param 	string baseclass
	 * @return 	array
	*/
	protected static function TablesBaseClass($class) {

		if(!isset(self::$class_info[$class]["table"]) || empty(self::$class_info[$class]["table"])) {
			return array();
		}

		$tables = array();

		$tables[$class . "_state"] = $class . "_state";

		$tables = self::fillTableArray($class, $tables);

		foreach(self::getChildren($class) as $subclass) {
			$tables = self::fillTableArray($subclass, $tables);
		}

		return $tables;
	}

	/**
	 * fills an array with key and value the same for tables for given class.
	 *
	 * @param 	string 	class
	 * @param 	array 	tables
	 * @return 	array
	*/
	protected static function fillTableArray($class, $tables) {
		if(isset(self::$class_info[$class]["table"])) {
			$table = self::$class_info[$class]["table"];

			if($table) {
				$tables[$table] = $table;
			}
		}
				
		if(isset(self::$class_info[$class]["many_many_tables"]) && self::$class_info[$class]["many_many_tables"]) {
			foreach(self::$class_info[$class]["many_many_tables"] as $data) {
				$tables[$data["table"]] = $data["table"];
			}
		}

		return $tables;

	}

    /**
     * returns the base-folder of a expansion or class.
     *
     * @param    string    extension or class-name
     * @param    bool    if force to use as class-name
     * @param    bool    if force to be absolute path.
     * @return null|string
     */
	public static function getExpansionFolder($name, $forceAbsolute = false) {
		$name = self::getExpansionName($name);
		if(!isset($name)) {
			return null;
		}

		$data = self::getExpansionData($name);
		$folder = $data["folder"];

		if(isset($folder)) {
			if($forceAbsolute) {
				return realpath($folder) . "/";
			} else {
				return self::makePathRelative($folder);
			}
		} else {
			return null;
		}
	}

	/**
	 * determines expansion name by classname or expansion-name.
	 *
	 * @param 	string name
	 * @return 	string|null
	*/
	public static function getExpansionName($name) {
		if(is_object($name)) {
			if(isset($name->inExpansion) && self::getExpansionData($name->inExpansion)) {
				return $name->inExpansion;
			} else {
				$name = ClassManifest::resolveClassName($name);
			}
		}

		if(isset(ClassInfo::$class_info[$name]["inExpansion"]) && self::getExpansionData(ClassInfo::$class_info[$name]["inExpansion"])) {
			return ClassInfo::$class_info[$name]["inExpansion"];
		}

		return self::getExpansionData($name) ? $name : null;
	}

	/**
	 * returns data if expansion with given name exists, else null.
	 *
	 * @param 	string name
	 * @return 	array|null
	*/
	public static function getExpansionData($name) {
		return isset(self::$appENV["expansion"][strtolower($name)]) ? self::$appENV["expansion"][strtolower($name)] : null;
	}

	/**
	 * gets the full version of the installed app
	 *
	 */
	public static function appVersion() {
		if(isset(self::$appENV["app"]["build"]))
			return self::$appENV["app"]["version"] . "-" . self::$appENV["app"]["build"];

		return self::$appENV["app"]["version"];
	}

	/**
	 * gets the full version of a installed expansion
	 *
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
	 *@param string - file
	 *@param string - class
	 */
	public static function findFile($file, $class) {

		$class = ClassManifest::resolveClassName($class);

		if($folder = self::getExpansionFolder($class)) {
			if(file_exists($folder . "/" . $file)) {
				return $folder . "/" . $file;
			}
		}

		if(isset(self::$files[$class])) {
			if(file_exists(dirname(self::$files[$class]) . "/" . $file) && !is_dir(dirname(self::$files[$class]) . "/" . $file)) {
				return dirname(self::$files[$class]) . "/" . $file;
			}
		}

		if(file_exists(APPLICATION . "/" . $file) && !is_dir(APPLICATION . "/" . $file)) {
			return APPLICATION . "/" . $file;
		}

		if(file_exists($file) && !is_dir($file)) {
			return $file;
		} else {
			return false;
		}
	}

	/**
	 * finds a file belonging to a class with absolute path
	 *
	 *@param string - file
	 *@param string - class
	 */
	public static function findFileAbsolute($file, $class) {
		if($path = self::findFile($file, $class)) {
			return realpath($path);
		} else {
			return false;
		}
	}

	/**
	 * finds a file belonging to a class with relative path
	 *
	 *@param string - file
	 *@param string - class
	 */
	public static function findFileRelative($file, $class) {
		if($path = self::findFile($file, $class)) {
			return self::makePathRelative($path);
		} else {
			return false;
		}
	}

	/**
 	 * makes a path relative.
	*/
	protected function makePathRelative($path) {
		if(substr($path, 0, strlen(ROOT)) == ROOT) {
			return substr($path, strlen(ROOT));
		} else {
			return $path;
		}
	}

	/**
	 * gets the title of a class
	 *
	 */
	public static function getClassTitle($class) {
		if(self::hasStatic($class, "cname")) {
			return parse_lang(self::getStatic($class, "cname"));
		}

		$c = new $class;

		return parse_lang($c->name);
	}
	
	/**
	 * gets the icon of a class
	 *
	 */
	public static function getClassIcon($class) {
		if(self::hasStatic($class, "icon")) {
			return ClassInfo::findFileRelative(self::getStatic($class, "icon"), $class);
		}

		return null;
	}

	/**
	 * loads the classinfo from file
	 *@return null
	 */
	public static function loadfile() {
		self::$tables = array();
		self::$database = array();
		$file = ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE;

		if(((!file_exists($file) || filemtime($file) < filemtime(FRAMEWORK_ROOT . "info.plist") || filemtime($file) < filemtime(ROOT . APPLICATION . "/info.plist") || filemtime($file) + self::$expiringTime < NOW) && (!function_exists("apc_exists") || !apc_exists(CLASS_INFO_DATAFILE)))) {
			if(PROFILE)
				Profiler::mark("generate_class_info");
			defined("GENERATE_CLASS_INFO") OR define('GENERATE_CLASS_INFO', true);
			logging('Regenerating Class-Info');

			// check for permissions
			$permissionInfo = self::checkPermissionsAndBuild();
			if($permissionInfo !== true) {
				$data = file_get_contents(FRAMEWORK_ROOT . "templates/framework/permission_fail.html");
				$data = str_replace('{BASE_URI}', BASE_URI, $data);
				$data = str_replace('{$permission_errors}', $permissionInfo, $data);
				header("content-type: text/html;charset=UTF-8");
				header("X-Powered-By: Goma Framework " . GOMA_VERSION . "-" . BUILD_VERSION);
				echo $data;
				exit;
			}

			// some global tests for the framework to run
			$dependencies = self::checkForSoftwareDependencies();
			if($dependencies !== true) {
				self::raiseSoftwareError($dependencies);
			}

			// END TESTS

			if(file_exists($file) && (filemtime($file) < filemtime(FRAMEWORK_ROOT . "info.plist") || filemtime($file) < filemtime(ROOT . APPLICATION . "/info.plist"))) {
				if(!preg_match("/^dev/i", URL)) {

					ClassManifest::tryToInclude("Dev", 'system/Core/control/DevController.php');
					Dev::redirectToDev();
				}
			}

			writeProjectConfig();
			writeSystemConfig();

			// end filesystem checks

			// check for clean-up
			if(file_exists(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log")) {
				$count = count(scandir(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log"));
				if($count > 60) {
					register_shutdown_function(array("ClassInfo", "autoCleanUpLog"));
				}
			}

			require_once (ROOT . "system/libs/thirdparty/plist/CFPropertyList.php");
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

			// now check for upgrade-scripts
			if(PROFILE)
				Profiler::mark("checkVersion");

			// first framework!
			self::checkForUpgradeScripts(ROOT . "system", GOMA_VERSION . "-" . BUILD_VERSION);

			// second, application!
			self::checkForUpgradeScripts(ROOT . APPLICATION, self::appversion());

			if(isset(self::$appENV["expansion"])) {
				// expansions
				foreach(self::$appENV["expansion"] as $expansion => $data) {
					self::checkForUpgradeScripts(self::getExpansionFolder($expansion), self::expVersion($expansion));
				}
			}

			if(PROFILE)
				Profiler::unmark("checkVersion");

			if(PROFILE)
				Profiler::mark("ClassManifest::indexFiles");

			ClassManifest::generate_all_class_manifest(self::$files, self::$class_info, self::$appENV);

			if(PROFILE)
				Profiler::unmark("ClassManifest::indexFiles");

			if(defined("SQL_LOADUP")) {
				self::$appENV["app"]["SQL"] = true;
			} else {
				self::$appENV["app"]["SQL"] = false;
			}

			$wasUnavailable = isProjectUnavailable();
			makeProjectUnavailable();

			Core::deletecache(true);

			// check for disk-quote
			if(GOMA_FREE_SPACE / 1024 / 1024 < 20) {
				header("HTTP/1.1 500 Server Error");
				die(file_get_contents(ROOT . "system/templates/framework/disc_quota_exceeded.html"));
			}

			// register shutdown hook
			register_shutdown_function(array("ClassInfo", "finalizeClassInfo"));

			foreach(ClassInfo::$class_info as $class => $data) {
				$_c = $class;
				while(isset(ClassInfo::$class_info[$_c]["parent"])) {
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

			if(PROFILE)
				Profiler::mark("include_all");

			ClassManifest::include_all();

			if(PROFILE)
				Profiler::unmark("include_all");

			defined("CLASS_INFO_LOADED") OR define("CLASS_INFO_LOADED", true);

			// patch until 2.1 daisy, then we drop this
			if(file_exists(APP_FOLDER . "/application/config.php"))
				ClassManifest::addPreload(APP_FOLDER . "/application/config.php");

			// normal code
			foreach(ClassManifest::$preload as $_file) {
				require_once ($_file);
			}

			if(PROFILE)
				Profiler::mark("classinfo_renderafter");

			foreach(self::$class_info as $class => $data) {
				if(Object::method_exists($class, "buildClassInfo")) {
					call_user_func_array(array($class, "buildClassInfo"), array($class));
				}
			}

			foreach(self::$class_info as $class => $data) {
				if(Object::method_exists($class, "buildClassInfo")) {
					call_user_func_array(array($class, "buildClassInfo"), array($class));
				}

				Object::instance("ClassInfo")->callExtending("generate", $class);

				// generates save-vars
				if(class_exists($class) && is_subclass_of($class, "object") || $class == "object") {
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
			
			
			
			foreach(self::$class_info as $class => $data) {
				if(!ClassInfo::isAbstract($class)) {
					if(ClassInfo::hasInterface($class, "PermProvider")) {
						$c = new $class();
						Permission::addPermissions($c->providePerms());
					}

					if(Object::method_exists($class, "generateClassInfo")) {
						if(!isset($c))
							$c = new $class;
						$c->generateClassInfo();
					}

					unset($c);
				}
				
				if(isset(self::$class_info[$class]["interfaces"])) {
					foreach(self::$class_info[$class]["interfaces"] as $interface) {
						self::addInterface($interface);
					}
				}
				
				if(isset(self::$class_info[$class]["child"]) && !empty(self::$class_info[$class]["child"])) {
					self::$childData[$class] = self::$class_info[$class]["child"];
					//chmod($f, 0777);
					self::$class_info[$class]["child"] = null;
					unset($_file, $f, $children, self::$class_info[$class]["child"]);
				}

				unset($class, $data);
			}

			FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE, "<?php\n\$children = " . var_export(self::$childData, true) . ";");

			// reappend
			self::$class_info["permission"]["providedPermissions"] = Permission::$providedPermissions;

			
			
			if(PROFILE)
				Profiler::unmark("classinfo_renderafter");

			$php = "<?php\n";
			$php .= "ClassInfo::\$interfaces = " . var_export(self::$interfaces, true) . ";\n";
			$php .= "ClassInfo::\$appENV = " . var_export(self::$appENV, true) . ";\n";
			$php .= "ClassInfo::\$class_info = " . var_export(self::$class_info, true) . ";\n";
			$php .= "ClassInfo::\$files = " . var_export(self::$files, true) . ";\n";
			$php .= "ClassInfo::\$tables = " . var_export(self::$tables, true) . ";\n";
			$php .= "ClassInfo::\$database = " . var_export(self::$database, true) . ";\n";
			$php .= "ClassManifest::\$preload = " . var_export(array_values(ClassManifest::$preload), true) . ";\n";
			$php .= "\$root = " . var_export(ROOT, true) . ";";
			$php .= "\$version = " . var_export(self::VERSION, true) . ";";

			foreach(ClassManifest::$preload as $_file) {
				$php .= "\ninclude_once(" . var_export($_file, true) . ");";
			}

			if(!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, $php)) {
				throw new ClassInfoWriteError('Could not write ClassInfo. Could not write ' . $file, ExceptionManager::CLASSINFO_WRITE_ERROR);
			}

			if(function_exists("apc_store")) {
				$data = array("appENV" => self::$appENV, "class_info" => self::$class_info, "files" => self::$files, "tables" => self::$tables, "database" => self::$database, "preload" => ClassManifest::$preload, "interfaces" => self::$interfaces, "childData" => self::$childData, "root" => ROOT, "version" => self::VERSION);
				apc_store(CLASS_INFO_DATAFILE, $data);
			}

			//chmod(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, 0773);

			if(!$wasUnavailable)
				makeProjectAvailable();

			define("CLASS_INFO_GENERATED", true);

			Object::$cache_singleton_classes = array();
			// object reset
			self::$set_save_vars = array();
			// class_info reset

			// run hooks
			if(self::$ClassInfoHooks) {
				foreach(self::$ClassInfoHooks as $hook) {
					call_user_func_array($hook, array());
				}
			}
			
			foreach(self::$class_info as $class => $data) {
				Core::callHook("loadedClass" . $class);
			}
			
			if(PROFILE)
				Profiler::unmark("generate_class_info");
		} else {
			defined("CLASS_INFO_LOADED") OR define("CLASS_INFO_LOADED", true);

			// apc-cache
			if(function_exists("apc_fetch") && apc_exists(CLASS_INFO_DATAFILE)) {
				$data = apc_fetch(CLASS_INFO_DATAFILE);
				if($data["root"] != ROOT || !isset($data["root"]) || !isset($data["version"]) || version_compare($data["version"], self::VERSION, "<")) {
					ClassInfo::delete();
					clearstatcache();
					ClassInfo::loadfile();
					return;
				}

				// init vars
				self::$appENV = $data["appENV"];
				self::$class_info = $data["class_info"];
				self::$files = $data["files"];
				self::$tables = $data["tables"];
				self::$database = $data["database"];
				self::$childData = $data["childData"];
				self::$interfaces = $data["interfaces"];
				ClassManifest::$preload = $data["preload"];

				// load preloads
				foreach($data["preload"] as $file) {
					if(file_exists($file)) {
						include_once ($file);
					} else {
						ClassInfo::delete();
						clearstatcache();
						ClassInfo::loadfile();
						return;
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
					include (ROOT . CACHE_DIRECTORY . "/" . CLASS_INFO_DATAFILE);
				} catch (Exception $e) {
					ClassInfo::delete();
					clearstatcache();
					ClassInfo::loadfile();
					return;
				}

				if($root != ROOT || !isset($root) || !isset($version) || version_compare($version, self::VERSION, "<")) {
					ClassInfo::delete();
					clearstatcache();
					ClassInfo::loadfile();
					return;
				}

				if(defined("SQL_LOADUP") && self::$appENV["app"]["SQL"] === false) {
					ClassInfo::delete();
					ClassInfo::loadfile();
					return;
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
			if(goma_version_compare(self::$appENV["app"]["requireFrameworkVersion"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
				throw new LogicException("Application does not support this version of the goma-framework, please update the framework to <strong>" . self::$appENV["app"]["requireFrameworkVersion"] . "</strong>. <br />Framework-Version: <strong>" . GOMA_VERSION . "-" . BUILD_VERSION . "</strong>",ExceptionManager::APPLICATION_FRAMEWORK_VERSION_MISMATCH);
			}
		}
	}

	/**
	 * checks for permissions and rebuilds package-index.
	 *
	 * @name 	checkPermissionsAndBuild
	*/
	public static function checkPermissionsAndBuild() {


		include_once("./system/libs/file/PermissionChecker.php");
		$permissionChecker = new PermissionChecker();
		$permissionChecker->addFolders(array(
			"system/temp/",
			APP_FOLDER . "temp/",
			APP_FOLDER . LOG_FOLDER,
			APP_FOLDER . "code/",
			APP_FOLDER . "uploaded/",
			"./system/installer/data/apps"
		));

		// check for basic file permissions
		$info = $permissionChecker->tryWrite();
		$permissionsFalse = array();
		if($info) {
			$permissionsFalse = array_merge($permissionsFalse, $info);
		}
		

		// add autoloder-exclude files in temp-folders.
		if(!file_exists(ROOT . "system/temp/autoloader_exclude")) {
			@file_put_contents(ROOT . "system/temp/autoloader_exclude", 1);
		}

		if(!file_exists(APP_FOLDER . "temp/autoloader_exclude")) {
			@file_put_contents(APP_FOLDER . "temp/autoloader_exclude", 1);
		}

		// rebuild package-index and get Permission-Errors from there.
		require_once(FRAMEWORK_ROOT . "/libs/GFS/SoftwareType.php");
		$errors = g_SoftwareType::buildPackageIndex();
		if($errors) {
			$permissionsFalse = array_merge($permissionsFalse, $errors);
		}

		$permissionsFalseString = "";
		foreach($permissionsFalse as $f) {
			$permissionsFalseString = '<li>' . $f . '</li>';
		}

		// okay, we have some permission-errors.
		if($permissionsFalseString != "") {
			return $permissionsFalseString;
		}

		return true;
	}
	
	/**
	 * checks for basic dependencies and returns string if error has happended.
	*/
	public static function checkForSoftwareDependencies() {
		// some global tests for the framework to run
		if(function_exists("gd_info")) {
			$data = gd_info();
			if(preg_match('/2/', $data["GD Version"])) {
				// okay
				unset($data);
			} else {
				return 'You need to have GD-Library 2 installed.';
			}
		} else {
			return 'You need to have GD-Library 2 installed.';
		}

		if(!class_exists("reflectionClass")) {
			return 'You need to have the Reflection-API installed.';
		}

		return true;
	}

	/**
	 * raises an software error with given error-string.
	 *
	 * @name 	raiseSoftwareError
	 * @param 	string error
	*/
	public static function raiseSoftwareError($err) {
		$error = file_get_contents(ROOT . "system/templates/framework/software_run_fail.html");
		$error = str_replace('{$error}', $err, $error);
		$error = str_replace('{BASE_URI}', BASE_URI, $error);
		header("HTTP/1.1 500 Server Error");
		echo $error;
		exit;
	} 

	/**
	 * adds interface to list.
	*/
	static function addInterface($interface) {
		
		$interface = strtolower($interface);
		
		if($interface == "")
			return;
		
		if(!isset(self::$interfaces[$interface])) {
			$parentsi = array_values(class_implements($interface));
			self::$interfaces[$interface] = isset($parentsi[0]) ? $parentsi[0] : false;
			
			if(isset($parentsi[0])) {
				self::addInterface($parentsi[0]);
			}
		}
	}

	/**
	 * finalizes the classinfo
	 *
	 */
	static function finalizeClassInfo() {
		if(defined("CLASS_INFO_GENERATED") && !defined("ERROR_CODE")) {
			logging("finalize");
			foreach(ClassInfo::$class_info as $class => $data) {
				// generates save-vars
				if(class_exists($class) && is_subclass_of($class, "object") || $class == "object") {
					// save vars
					foreach(self::getSaveVars($class) as $value) {
						self::$class_info[$class][$value] = self::getStatic($class, $value);
					}
					// ci-funcs
					// that are own function, for generating class-info
					foreach(ClassInfo::getStatic($class, "ci_funcs") as $name => $func) {
						self::$class_info[$class][$name] = classinfo::callStatic($class, $func);
					}
					
					if(isset(self::$class_info[$class]["interfaces"])) {
						foreach(self::$class_info[$class]["interfaces"] as $interface) {
							self::addInterface($interface);
						}
					}
				}
			}

			ClassInfo::write();
		}
	}

	/**
	 * callback for auto-clean-up for log-files
	 *
	 */
	public static function autoCleanUpLog() {
		Core::cleanUpLog(60);
	}

	/**
	 * this function checks for upgrade-scripts in a given folder with given current
	 * version
	 *
	 *@param folder
	 *@param version
	 */
	public static function checkForUpgradeScripts($folder, $current_version) {

		ClassManifest::tryToInclude("softwareupgrademanager", 'system/core/CoreLibs/SoftwareUpgradeManager.php');

		if(SoftwareUpgradeManager::checkForUpgradeScripts($folder, $current_version)) {

			// after upgrade reload.
			ClassInfo::delete();

			$http = (isset($_SERVER["HTTPS"])) ? "https" : "http";
			$port = ":" . $_SERVER["SERVER_PORT"];
			if(($http == "http" && $port == 80) || ($http == "https" && $port == 443)) {
				$port = "";
			}
			header("Location: " . $http . "://" . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"]);
			exit;
		}
	}

	/**
	 * deletes the file
	 */
	public static function delete() {
		if(file_exists(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE)) {
			if(!@unlink(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE)) {
				throw new ClassInfoWriteError( 'Could not delete ' . CACHE_DIRECTORY . CLASS_INFO_DATAFILE . '.', ExceptionManager::CLASSINFO_WRITE_ERROR);
			}
		}

		if(function_exists("apc_delete")) {
			apc_delete(CLASS_INFO_DATAFILE);
		}

		return true;
	}

	/**
	 * gets dataclasses for a given dataobject
	 *
	 * @param 	string
	 * @return 	array
	 */
	public static function DataClasses($class) {
		if(isset(self::$class_info[$class]["dataclasses"])) {

			$dataclasses = array();
			foreach(self::$class_info[$class]["dataclasses"] as $c) {
				if(isset(self::$class_info[$c]["table"]) && self::$class_info[$c]["table"] !== false) {
					$dataclasses[$c] = self::$class_info[$c]["table"];
				}
			}

			return $dataclasses;
		} else {
			return array();
		}
	}

	/**
	 * writes the file
	 */
	public static function write() {
		// first consolidate data
		if(self::$childData == array() && file_exists(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE)) {
			include (ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE);
			self::$childData = $children;
		}

		// generate file
		$php = "<?php\n";
		$php .= "ClassInfo::\$interfaces = " . var_export(self::$interfaces, true) . ";\n";
		$php .= "ClassInfo::\$appENV = " . var_export(self::$appENV, true) . ";\n";
		$php .= "ClassInfo::\$class_info = " . var_export(self::$class_info, true) . ";\n";
		$php .= "ClassInfo::\$files = " . var_export(self::$files, true) . ";\n";
		$php .= "ClassInfo::\$tables = " . var_export(self::$tables, true) . ";\n";
		$php .= "ClassInfo::\$database = " . var_export(self::$database, true) . ";\n";
		$php .= "ClassManifest::\$preload = " . var_export(array_values(ClassManifest::$preload), true) . ";\n";
		$php .= "\$root = " . var_export(ROOT, true) . ";";
		$php .= "\$version = " . var_export(self::VERSION, true) . ";";

		foreach(ClassManifest::$preload as $_file) {
			$php .= "\ninclude_once(" . var_export($_file, true) . ");";
		}

		// generate apc-part
		if(function_exists("apc_store")) {
			$data = array("appENV" => self::$appENV, "class_info" => self::$class_info, "files" => self::$files, "tables" => self::$tables, "database" => self::$database, "preload" => ClassManifest::$preload, "interfaces" => self::$interfaces, "childData" => self::$childData, "root" => ROOT, "version" => self::VERSION);
			apc_store(CLASS_INFO_DATAFILE, $data);
		}

		// write files
		if(!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, $php) || !FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE, "<?php\n\$children = " . var_export(self::$childData, true) . ";")) {
			throwError("8", 'Could not write in cache-directory', 'Could not write ' . ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE . '');
		} else {
			return true;
		}

	}

	/**
	 * adds a table-object-relation
	 *@param string - table
	 *@param string - object
	 */
	public static function addTable($table, $object) {
		self::$tables[$table] = $object;
	}

}

class ClassInfoWriteError extends LogicException {
	/**
	 * constructor.
	*/
	public function __construct($msg, $code = ExceptionManager::CLASSINFO_WRITE_ERROR, $e = null) {
		parent::__construct($msg, $code, $e);
	}
}

ClassInfo::addSaveVar("Object", "extensions");
ClassInfo::addSaveVar("Object", "extra_methods");
