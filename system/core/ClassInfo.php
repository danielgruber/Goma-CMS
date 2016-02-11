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
 * @version		3.8.1
 */
class ClassInfo extends gObject {
	const GENERATE_CLASS_INFO_KEY = "GENERATE_CLASS_INFO";
	/**
	 * version of class-info
	 *
	 */
	const VERSION = "3.6";

	/**
	 * defines when class-info expires
	 *
	 */
	public static $expiringTime = 604800; // 7 days by default

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
	 * hooks for current execution which are executed when classinfo is generated
	 *
	 */
	public static $ClassInfoHooks = array();
	
	/**
	 * list of all known interfaces.
	*/
	public static $interfaces = array();

	/**
	 * information if class-info has been regenerated in this request.
	 */
	public static function ClasssInfoHasBeenRegenerated() {
		return defined(self::GENERATE_CLASS_INFO_KEY);
	}

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
			/** @var array $children */
			if($children === null) {
				self::delete();
				self::loadfile();
				exit;
			}
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
     * @param string|gObject class
     * @return string
     * @throws LogicException
     */
    public static function find_class_name($class) {
        $class = ClassManifest::resolveClassName($class);

        if(!ClassInfo::exists($class) && !class_exists($class, false)) {
            throw new LogicException("Cannot find unknown class $class");
        }

        return $class;
    }


    /**
     * checks if class is abstract
     *
     * @param string|gObject $class
     * @return bool
     */
	public static function isAbstract($class) {
        return self::getSpecificInfo($class, "abstract", false);
	}

    /**
     * gets the parent class of a given class
     *
     * @param string|gObject $class
     * @return string
     */
	public static function get_parent_class($class) {
        return self::getSpecificInfo($class, "parent");
	}

    /**
     * gets specific info from class.
     *
     * @param string $class
     * @param string info
     * @param string default
     * @return mixed
     */
    public static function getSpecificInfo($class, $name, $default = null) {
        $info = self::getInfo($class);
        return isset($info[$name]) ? $info[$name] : $default;
    }

    /**
     * gets info to a specific class
     * @param string|gObject $class
     * @return array|null
     */
	public static function getInfo($class) {
		$class = ClassManifest::resolveClassName($class);

		if(isset(self::$class_info[$class])) {
			return self::$class_info[$class];
		} else {
			return null;
		}
	}

	/**
	 * gets the full version of the installed app
	 *
	 */
	public static function appVersion() {
		if(isset(self::$appENV["app"]["build"])) {
            return self::$appENV["app"]["version"] . "-" . self::$appENV["app"]["build"];
        }

		return self::$appENV["app"]["version"];
	}

	/**
     * finds a file belonging to a class
     *
     * @param string - file
     * @param string - class
     * @return bool|string
     */
	public static function findFile($file, $class) {

		$class = ClassManifest::resolveClassName($class);

		if($folder = ExpansionManager::getExpansionFolder($class)) {
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
     * @param string - file
     * @param string - class
     * @return bool|string
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
     * @param string - file
     * @param string - class
     * @return bool|string
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
	public static function makePathRelative($path) {
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
		if(StaticsManager::hasStatic($class, "cname")) {
			return parse_lang(StaticsManager::getStatic($class, "cname"));
		}

		$c = new $class;

		return parse_lang($c->name);
	}
	
	/**
	 * gets the icon of a class
	 *
	 */
	public static function getClassIcon($class) {
		if(StaticsManager::hasStatic($class, "icon")) {
			return ClassInfo::findFileRelative(StaticsManager::getStatic($class, "icon"), $class);
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

		if(((!file_exists($file) || filemtime($file) < filemtime(FRAMEWORK_ROOT . "info.plist") || filemtime($file) < filemtime(ROOT . APPLICATION . "/info.plist") || filemtime($file) + self::$expiringTime < NOW))) {
			if(PROFILE)
				Profiler::mark("generate_class_info");
			defined(self::GENERATE_CLASS_INFO_KEY) OR define(self::GENERATE_CLASS_INFO_KEY, true);
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

					ClassManifest::tryToInclude("Dev", 'system/core/control/DevController.php');
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
                ClassManifest::tryToInclude("ExpansionManager", 'system/Core/CoreLibs/ExpansionManager.php');
				// expansions
				foreach(self::$appENV["expansion"] as $expansion => $data) {
					self::checkForUpgradeScripts(ExpansionManager::getExpansionFolder($expansion), ExpansionManager::expVersion($expansion));
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
			if(file_exists(APP_FOLDER . "/application/config.php")) {
				ClassManifest::addPreload(APP_FOLDER . "/application/config.php");
			}

			// normal code
			foreach(ClassManifest::$preload as $_file) {
				require_once ($_file);
			}

			if(PROFILE)
				Profiler::mark("classinfo_renderafter");

			foreach(self::$class_info as $class => $data) {
				if(gObject::method_exists($class, "buildClassInfo")) {
					call_user_func_array(array($class, "buildClassInfo"), array($class));
				}
			}

			foreach(self::$class_info as $class => $data) {
				if(gObject::method_exists($class, "buildClassInfo")) {
					call_user_func_array(array($class, "buildClassInfo"), array($class));
				}

				gObject::instance("ClassInfo")->callExtending("generate", $class);

				// generates save-vars
				if(class_exists($class) && is_subclass_of($class, "gObject") || $class == "gobject") {
					// save vars
					foreach(StaticsManager::getSaveVars($class) as $value) {
						self::$class_info[$class][$value] = StaticsManager::getStatic($class, $value, true);
						unset($value);
					}

					// ci-funcs
					// that are own function, for generating class-info
					foreach(StaticsManager::getStatic($class, "ci_funcs") as $name => $func) {
						self::$class_info[$class][$name] = StaticsManager::callStatic($class, $func);
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

					if(gObject::method_exists($class, "generateClassInfo")) {
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

			FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE, "<?php\n\$children = " . var_export(self::$childData, true) . ";", LOCK_EX);

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

			//chmod(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, 0773);

			if(!$wasUnavailable)
				makeProjectAvailable();

			define("CLASS_INFO_GENERATED", true);

			gObject::$cache_singleton_classes = array();
			// object reset
			StaticsManager::$set_save_vars = array();
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

			// just include file
			try {
				include (ROOT . CACHE_DIRECTORY . "/" . CLASS_INFO_DATAFILE);
			} catch (Exception $e) {
				ClassInfo::delete();
				clearstatcache();
				ClassInfo::loadfile();
				return;
			}

			if(!isset($root) || $root != ROOT || !isset($version) || version_compare($version, self::VERSION, "<")) {
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

			// run hooks
			if(self::$ClassInfoHooks) {
				foreach(self::$ClassInfoHooks as $hook) {
					call_user_func_array($hook, array());
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
	 * @name   checkPermissionsAndBuild
	 * @return bool|string
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
	 * @param string $interface
	 */
	static function addInterface($interface) {
		
		$interface = strtolower(trim($interface));
		
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
				if(class_exists($class) && is_subclass_of($class, "gObject") || $class == "gobject") {
					// save vars
					foreach(StaticsManager::getSaveVars($class) as $value) {
						self::$class_info[$class][$value] = StaticsManager::getStatic($class, $value);
					}
					// ci-funcs
					// that are own function, for generating class-info
					foreach(StaticsManager::getStatic($class, "ci_funcs") as $name => $func) {
						self::$class_info[$class][$name] = StaticsManager::callStatic($class, $func);
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
			foreach(self::$class_info[$class]["dataclasses"] as $dataClass) {
				if(isset(self::$class_info[$dataClass]["table"]) && self::$class_info[$dataClass]["table"] !== false) {
					$dataclasses[$dataClass] = self::$class_info[$dataClass]["table"];
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
			if($children === null) {
				self::delete();
				return;
			}
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

		// write files
		if(!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE, $php, LOCK_EX) ||
			!FileSystem::writeFileContents(ROOT . CACHE_DIRECTORY . ".children" . CLASS_INFO_DATAFILE, "<?php\n\$children = " . var_export(self::$childData, true) . ";", LOCK_EX)) {
			throw new LogicException('Could not write in cache-directory. Could not write ' . ROOT . CACHE_DIRECTORY . CLASS_INFO_DATAFILE);
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

StaticsManager::addSaveVar("gObject", "extensions");
StaticsManager::addSaveVar("gObject", "extra_methods");
