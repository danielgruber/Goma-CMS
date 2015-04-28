<?php defined("IN_GOMA") OR die();

/**
 * This class generates the class manifest.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		3.3.4
 */
class ClassManifest {
	/**
	 * Files, that are loaded at each request.
	 */
	public static $preload = array();

	/**
	 * Class cache.
	 */
	public static $classes = array();

	/**
	 * Array of all directories, that will be scanned recursively.
	 */
	static public $directories = array('system');

	/**
	 * List of class aliases.
	 */
	private static $class_alias = array("showsitecontroller" => "frontedcontroller", "_array" => "arraylib", "dataobjectholder" => "viewaccessabledata", "autoloader" => "ClassManifest", "testsuite" => "Object");

	/**
	 * Loads a class.
	 *
	 * @param	string $class Classname
	 *
	 * @return	void
	 */
	public static function load($class) {

		$class = self::resolveClassName($class);

		if(PROFILE)
			Profiler::mark("Manifest::load");
		
		self::loadInterface($class) || self::loadClass($class) || self::generateAlias($class);
		
		if(class_exists('Core', false)) {
			Core::callHook('loadedClass', $class);
			Core::callHook('loadedClass' . $class);
		}
		
		if(PROFILE)
			Profiler::unmark("Manifest::load");
	}

	/**
	 * tries to include a file.
	*/
	public static function tryToInclude($class, $file) {

		$class = self::resolveClassName($class);

		if(!class_exists($class, false)) {
			include($file);
		}
	}

	/**
	 * loads interface.
	*/
	protected static function loadInterface($class) {
		if(isset(ClassInfo::$interfaces[$class]) && !interface_exists($class, false)) {
			if(ClassInfo::$interfaces[$class]) {
				eval('interface '.$class.' extends '.ClassInfo::$interfaces[$class].' {}');
			} else {
				eval('interface '.$class.' {}');
			}
			
			return true;
		}
	}

	/**
	 * loads class.
	*/
	protected static function loadClass($class) {
		if(isset(ClassInfo::$files[$class])) {
			if(!include(ClassInfo::$files[$class])) {
				ClassInfo::Delete();
				throw new LogicException("Could not include " . ClassInfo::$files[$class] . ". ClassInfo seems to be old.");
			}
			self::setSaveVars(ClassInfo::$files[$class]);

			return true;
		}
	}

	/**
	 * generates alias.
	*/
	protected static function generateAlias($class) {
		if(isset(self::$class_alias[$class])) {
			if(DEV_MODE) {
				// we log this, because it's not good, aliases are just for deprecation
				logging("Making alias " . self::$class_alias[$class] . " of " . $class . "");
			}

			// make a alias
			class_alias(self::$class_alias[$class], $class);

			return true;
		}
	}

	/**
	 * sets safe-vars for all classes in the file.
	 *
	 * @param	string $file Filename
	 *
	 * @return	boolean
	 */
	public static function setSaveVars($file) {
		if(count($keys = array_keys(ClassInfo::$files, $file)) > 0) {
			foreach($keys as $class) {
				StaticsManager::setSaveVars($class);
			}
		}

		return true;
	}

	/**
	 * Generates class manifest for all in $directories defined folders.
	 *
	 * @param	string &$classes
	 * @param	string &class_info
	 * @param	array[] &$env
	 *
	 * @return	void
	 */
	public static function generate_all_class_manifest(&$classes, &$class_info, &$env) {
		foreach(self::$directories as $dir) {
			self::generate_class_manifest($dir, $classes, $class_info, $env);
		}
	}

    /**
     * returns true if two classes can be treated as the same.
     * @param string|object $class1
     * @param string|object $class2
     * @return bool
     */
    public static function isSameClass($class1, $class2) {
        return (self::resolveClassName($class1) == self::resolveClassName($class2));
    }

    /**
     * returns true if two classes can be treated as the same or are subclasses of each other.
     *
     * @param string|object $class1
     * @param string|object $class2
     * @return bool
     */
    public static function classesRelated($class1, $class2) {

        // force strings
        $class1 = self::resolveClassName($class1);
        $class2 = self::resolveClassName($class2);

        return self::isSameClass($class1, $class2) || is_subclass_of($class1, $class2) || is_subclass_of($class2, $class1);
    }

    /**
     * @param $class
     * @return string
     */
    public static function resolveClassName($class) {

		if(is_object($class)) {
			$class = get_class($class);
		}

		$class = trim(strtolower($class));

		if(substr($class, -1) == "\\") {
			$class = substr($class, 0, -1);
		}

		if(substr($class, 0, 1) == "\\") {
			$class = substr($class, 1);
		}

		return $class;
	}

	/**
	 * Generates the class-manifest for a given directory.
	 *
	 * @param	string &$classes
	 * @param	string &class_info
	 * @param	array[] &$env
	 *
	 * @return	boolean
	 */
	public static function generate_class_manifest($dir, &$classes, &$class_info, &$env) {
		if(file_exists($dir . "/_exclude.php")) {
			include_once ($dir . "/_exclude.php");
			return false;
		}

		if(file_exists($dir . "/autoloader_exclude")) {
            return false;
        }

		if(!DEV_MODE && file_exists($dir . "/autoloader_non_dev_exclude")) {
            return false;
        }

		$dir = realpath($dir);

		// Extension-Layer
		if(file_exists($dir . '/contents/info.plist')) {
			$data = self::getPropertyList($dir . '/contents/info.plist');

            self::generateExtensionData($data, $dir, $class_info, $env);
		}

		foreach(scandir($dir) as $file) {
			if($file != "." && $file != "..") {
				if(is_dir($dir . "/" . $file)) {
					self::generate_class_manifest($dir . "/" . $file, $classes, $class_info, $env);
				} else if(_eregi('\.php$', $file) && $file != "ClassManifest.php") {
					$contents = file_get_contents($dir . "/" . $file);

					// remove everyting that is not php
					$contents = preg_replace('/\/\*(.*)\*\//Usi', '', $contents);
					$contents = preg_replace('/\?\>(.*)\<?php/Usi', '', $contents);

					$namespace = "";

					if(preg_match("/namespace\s+([a-zA-Z0-9\\\\\s_]+)\;/Usi", $contents, $matches)) {
						$namespace = strtolower($matches[1]);
						if(substr($namespace, -1) == "\\") {
							$namespace = substr($namespace, 0, -1);
						}

						if(substr($namespace, 0, 1) == "\\") {
							$namespace = substr($namespace, 1);
						}

						$namespace .= "\\";
					}

					preg_match_all('/(abstract\s+)?class\s+([a-zA-Z0-9\\\\_]+)(\s+extends\s+([a-zA-Z0-9\\\\_]+))?(\s+implements\s+([a-zA-Z0-9\\\\_,\s]+?))?\s+\{/Usi', $contents, $parts);
					foreach($parts[2] as $key => $class) {

						$class = self::resolveClassName($namespace . trim($class));

						if(isset($classes[$class]) && $classes[$class] != $dir . "/" . $file && file_exists($classes[$class])) {
							if(filemtime($classes[$class]) > filemtime($dir . "/" . $file)) {
								if(count($parts[2]) == 1 && isset(ClassInfo::$appENV["app"]["allowDeleteOld"])) {
									logging("Delete " . $dir . "/" . $file . ", because old Class!");
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
									logging("Delete " . $classes[$class] . ", because old Class!");
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
							$class_info[$class]["parent"] = self::resolveClassName($parts[4][$key]);
							if($class_info[$class]["parent"] == $class) {
								throwError(6, "Class-Definition-Error", "Class '" . $class . "' can not extend itself in " . $dir . "/" . $file . ".");
							}
						}

						if($parts[6][$key]) {
							$interfaces = explode(",", $parts[6][$key]);
							$class_info[$class]["interfaces"] = array_map(array("ClassManifest", "resolveClassName"), $interfaces);
						}

						if($parts[1][$key]) {
							$class_info[$class]["abstract"] = true;
						}
					}

					// index interfaces too
					preg_match_all('/interface\s+([a-zA-Z0-9\\\\_]+)(\s+extends\s+([a-zA-Z\\\\0-9_]+))?\s+\{/Usi', $contents, $parts);
					foreach($parts[1] as $key => $class) {
						$class = self::resolveClassName($namespace . trim($class));

						if(isset($classes[$class]) && $classes[$class] != $dir . "/" . $file && file_exists($classes[$class])) {
							if(filemtime($classes[$class]) > filemtime($dir . "/" . $file)) {
								if(count($parts[2]) == 1 && isset(ClassInfo::$appENV["app"]["allowDeleteOld"])) {
									logging("Delete " . $dir . "/" . $file . ", because old Class!");
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
									logging("Delete " . $classes[$class] . ", because old Class!");
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
							$class_info[$class]["parent"] = self::resolveClassName($parts[3][$key]);
							if($class_info[$class]["parent"] == $class) {
								throwError(6, "Interface-Definition-Error", "Interface '" . $class . "' can not extend itself in " . $dir . "/" . $file . ".");
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
     * returns array of data from PropertiyList.
     */
    public static function getPropertyList($file) {
        self::tryToInclude('CFPropertyList', 'system/libs/thirdparty/plist/CFPropertyList.php');
        $plist = new CFPropertyList($file);
        return $plist->ToArray();
    }

    /**
     * generates data for extension.
     *
     * @param array data
     * @param array environment
     */
    public static function generateExtensionData($data, $dir, &$class_info, &$env) {
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

	/**
	 * Add file for preload array.
	 *
	 * @param string $file Filename
	 *
	 * @return void
	 */
	public static function addPreload($file) {
		self::$preload[$file] = $file;
	}

	/**
	 * Include all files.
	 *
	 * @return void
	 */
	public static function include_all() {
		foreach(ClassInfo::$files as $class => $file) {
			if(!class_exists($class, false) && !interface_exists($class, false)) {
				self::load($class);
			}
		}
	}

	public static function addUnitTest () {
		self::$class_alias["unittestcase"] = "object";
	}

}

// fallback
if(!function_exists("spl_autoload_register")) {
	$GLOBALS["__autoload_stack"] = array();
	function spl_autoload_register($callback, $throw = false, $preprend = false) {
		if($prepend)
			$GLOBALS["__autoload_stack"] = array_merge(array($callback), $GLOBALS["__autoload_stack"]);
		else
			$GLOBALS["__autoload_stack"] = array_merge($GLOBALS["__autoload_stack"], array($callback));

	}

	function __autoload($class_name) {
		foreach($GLOBALS["__autoload_stack"] as $callback) {
			call_user_func_array($callback, array($class_name));
		}
	}

}

spl_autoload_register("ClassManifest::load");

// This method does not exist in each PHP-Build
if(!function_exists("class_alias")) {
	function class_alias($org, $alias) {
		eval("class " . $org . " extends " . $alias . " {}");
	}
}