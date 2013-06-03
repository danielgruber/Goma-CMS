<?php

defined("IN_GOMA") OR die();

interface ExtensionModel {

	public function setOwner($object);

	public function getOwner();
}

/**
 * Base class for _every_ Goma class.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version	3.2.2
 */
abstract class Object {

	/**
	 * caches
	 */
	static private $method_cache = array(), $cache_extensions = array();

	/**
	 * extensions
	 */
	public static $extra_methods = array(), $temp_extra_methods = array(), $cache_extra_methods = array(), $extensions = array(), $ci_funcs = array(), $cache_singleton_classes = array();
	// functions for generating classinfo

	/**
	 * protected vars
	 */
	protected static $extension_instances = array();

	/**
	 * local extension instances
	 */
	private $ext_instances = array();

	/**
	 * the current class-name in lowercase-letters
	 */
	public $class;

	/**
	 * this variable has a value if the class belongs to an extension, else it is null
	 */
	public $inExpansion;
	
	/**
	 * indicates whether a class ran the constructor.
	 *
	 * @access private
	*/
	private static $loaded;

	/**
	 * Gets the value of $class::$$var.
	 *
	 * @param	string $class Name of the class.
	 * @param	string $var Name of the variable.
	 *
	 * @return	mixed Value of $var.
	 */
	public static function getStatic($class, $var) {
		if (is_object($class))
			$class = $class -> class;

		if (!empty($class)) {
			if (!empty($var)) {
				return eval("return isset(" . $class . "::\$" . $var . ") ? " . $class . "::\$" . $var . " : null;");
			} else {
				throwError("6", "PHP-Error", "Invalid name of var in " . __METHOD__ . " in " . __FILE__ . "");
			}
		} else {
			throwError("6", "PHP-Error", "Invalid name of class in " . __METHOD__ . " in " . __FILE__ . "");
		}
	}

	/**
	 * Checks, if $class::$$var is set.
	 *
	 * @param	string $class Name of the class.
	 * @param	string $var Name of the variable.
	 *
	 * @return	boolean
	 */
	public static function hasStatic($class, $var) {
		if (is_object($class))
			$class = $class -> class;

		if (!empty($class)) {
			if (!empty($var)) {
				return eval("return isset(" . $class . "::\$" . $var . ");");
			} else {
				throwError("6", "PHP-Error", "Invalid name of var in " . __METHOD__ . " in " . __FILE__ . "");
			}
		} else {
			throwError("6", "PHP-Error", "Invalid name of class in " . __METHOD__ . " in " . __FILE__ . "");
		}
	}

	/**
	 * Sets $value for $class::$$var.
	 *
	 * @param	string $class Name of the class.
	 * @param	string $var Name of the variable.
	 * @param	mixed $value
	 *
	 * @return	void
	 */
	public static function setStatic($class, $var, $value) {
		if (is_object($class))
			$class = $class -> class;

		if (!empty($class)) {
			if (!empty($var)) {
				return eval($class . "::\$" . $var . " = " . var_export($value, true) . ";");
			} else {
				throwError("6", "PHP-Error", "Invalid name of var in " . __METHOD__ . " in " . __FILE__ . "");
			}
		} else {
			throwError("6", "PHP-Error", "Invalid name of class in " . __METHOD__ . " in " . __FILE__ . "");
		}
	}

	/**
	 * Calls $class::$$func.
	 *
	 * @param   string $class Name of the class.
	 * @param   string $func Name of the function.
	 *
	 * @return  void
	 */
	public static function callStatic($class, $func) {
		if (is_object($class))
			$class = $class -> class;

		if (!empty($class)) {
			if (!empty($func)) {
				return call_user_func_array(array($class, $func), array($class));
			} else {
				throwError("6", "PHP-Error", "Invalid name of function in " . __METHOD__ . " in " . __FILE__ . "");
			}
		} else {
			throwError("6", "PHP-Error", "Invalid name of class in " . __METHOD__ . " in " . __FILE__ . "");
		}
	}

	/**
	 * Extends a class with a method.
	 *
	 * @param   string $class Name of the class.
	 * @param   string $method Name of the method.
	 * @param   string $code Code of the method.
	 * @param   boolean $temp Is the method only temporarily?
	 *
	 * @return  void
	 */
	public static function createMethod($class, $method, $code, $temp = false) {
		$method = strtolower($method);
		$class = strtolower($class);
		if ($temp) {
			self::$temp_extra_methods[$class][$method] = create_function('$obj', $code);
		} else if (!Object::method_exists($class, $method)) {
			self::$extra_methods[$class][$method] = create_function('$obj', $code);
		}
	}

	/**
	 * Links $class::$$method to $realfunc.
	 *
	 * @param   string $class Name of the class.
	 * @param   string $method Name of the method.
	 * @param   string $realfunc Name of the linked function.
	 * @param   boolean $temp Is the link only temporarily?
	 *
	 * @return  void
	 */
	public static function linkMethod($class, $method, $realfunc, $temp = false) {
		$method = strtolower($method);
		$class = strtolower($class);
		if ($temp) {
			self::$temp_extra_methods[$class][$method] = $realfunc;
		} else if (!Object::method_exists($class, $method)) {
			self::$extra_methods[$class][$method] = $realfunc;
		}

		self::$method_cache[$class . "::" . $method] = true;
	}

	/**
	 * Checks, if $class has $method.
	 *
	 * @param   string $class Name of the class.
	 * @param   string $method Name of the method.
	 *
	 * @return  boolean
	 */
	public static function method_exists($class, $method) {
		if (PROFILE)
			Profiler::mark("Object::method_exists");

		if (is_object($class)) {
			$object = $class;
			$class = strtolower(get_class($class));
		}

		$class = strtolower(trim($class));
		$method = strtolower(trim($method));

		if (empty($class) || empty($method)) {
			unset($class, $method);
			if (PROFILE)
				Profiler::unmark("Object::method_exists");
			return false;
		}

		if (isset(self::$method_cache[$class . "::" . $method])) {

			// object-case
			if (!self::$method_cache[$class . "::" . $method] && isset($object)) {
				if (method_exists($class, "__cancall") && $object -> __canCall($method)) {
					unset($class, $method);
					if (PROFILE)
						Profiler::unmark("Object::method_exists");
					return true;
				}
			}

			if (PROFILE)
				Profiler::unmark("Object::method_exists");
			return self::$method_cache[$class . "::" . $method];
		}

		if (version_compare(phpversion(), "5.3", "<") && !isset(ClassManifest::$loaded[$class]))
			ClassManifest::load($class);

		// check native
		if (method_exists($class, $method) && is_callable(array($class, $method))) {
			self::$method_cache[$class . "::" . $method] = true;
			unset($class, $method);
			if (PROFILE)
				Profiler::unmark("Object::method_exists");
			return true;
		}

		// check in DB
		if (isset(self::$extra_methods[$class][$method]) || isset(self::$temp_extra_methods[$class][$method])) {
			self::$method_cache[$class . "::" . $method] = true;
			unset($class, $method);
			if (PROFILE)
				Profiler::unmark("Object::method_exists");
			return true;
		}

		// check on object
		if (isset($object) && method_exists($class, "__cancall") && $object -> __canCall($method)) {
			unset($class, $method);
			if (PROFILE)
				Profiler::unmark("Object::method_exists");
			return true;
		}

		// check parents
		if (PROFILE)
			Profiler::mark("Object::method_exists after");

		$c = $class;
		while ($c = ClassInfo::get_parent_class($c)) {
			if (isset(self::$extra_methods[$c][$method])) {
				// cache result for current class
				self::$method_cache[$class . "::" . $method] = true;
				unset($c, $method, $class);
				if (PROFILE) {
					Profiler::unmark("Object::method_exists after");
					Profiler::unmark("Object::method_exists");
				}

				return true;
			}
		}

		self::$method_cache[$class . "::" . $method] = false;

		unset($c, $method, $class);
		if (PROFILE) {
			Profiler::unmark("Object::method_exists after");
			Profiler::unmark("Object::method_exists");
		}

		return false;
	}

	/**
	 * Extends an object.
	 *
	 * @param   string The extended object.
	 * @param   string The extension.
	 *
	 * @return  void
	 */
	public static function extend($obj, $ext) {
		if (defined("GENERATE_CLASS_INFO")) {
			$obj = strtolower($obj);
			$ext = strtolower($ext);
			$arguments = "";
			if (preg_match('/^([a-zA-Z0-9_\-]+)\((.*)\)$/', $ext, $exts)) {
				$ext = $exts[0];
				$arguments = $exts[1];
			}
			if (class_exists($ext)) {
				if (ClassInfo::hasInterface($ext, "ExtensionModel")) {
					if (classinfo::getStatic($ext, 'extra_methods')) {
						foreach (classinfo::getStatic($ext, 'extra_methods') as $method) {
							self::$extra_methods[$obj][strtolower($method)] = array("EXT:" . $ext, $method);
						}
					}
					self::$extensions[$obj][$ext] = $arguments;
				} else {
					throwError(6, 'PHP-Error', 'Extension ' . convert::raw2text($ext) . ' isn\'t a Extension.');
				}
			} else {
				throwError(6, 'PHP-Error', 'Extension ' . convert::raw2text($ext) . ' does not exist.');
			}
		}
	}

	/**
	 * Gets the singleton of a class.
	 *
	 * @param	string $class Name of the class.
	 *
	 * @return	Object The singleton.
	 */
	public static function instance($class) {

		if (is_object($class)) {
			return clone $class;
		}

		$class = strtolower($class);

		if (PROFILE)
			Profiler::mark("Object::instance");

		/* --- */

		// caching
		if (isset(self::$cache_singleton_classes[$class])) {
			if (PROFILE)
				Profiler::unmark("Object::instance");
			$class = clone self::$cache_singleton_classes[$class];
		} else {

			// error catching
			if ($class == "") {
				$trace = debug_backtrace();
				throwError(6, 'Class-Initiate-Error', 'Cannot initiate empty Class in ' . $trace[0]["file"] . ' on line ' . $trace[0]["line"] . '');
			}

			if (ClassInfo::isAbstract($class)) {
				$trace = debug_backtrace();
				throwError(6, 'Class-Initiate-Error', 'Cannot initiate abstract Class in ' . $trace[0]["file"] . ' on line ' . $trace[0]["line"] . '');
			}

			// generate Class
			if ((defined("INSTALL") && class_exists($class)) || ClassInfo::exists($class)) {
				self::$cache_singleton_classes[$class] = new $class;
				$class = clone self::$cache_singleton_classes[$class];
			} else {
				throwError(6, 'PHP-Error', "Class " . $class . " not found in " . __FILE__ . " on line " . __LINE__ . "");
			}
		}

		if (PROFILE)
			Profiler::unmark("Object::instance");

		return $class;
	}

	/**
	 * Synonym for instance().
	 *
	 * @param	string $class Name of the class.
	 *
	 * @return	The singleton.
	 */
	public static function singleton($class) {
		return self::instance($class);
	}
	
	//! Non-Static Part
	/**
	 * Sets class name and save vars.
	 *
	 * @access public
	 */
	public function __construct() {
		$this -> class = strtolower(get_class($this));

		if (isset(ClassInfo::$class_info[$this -> class]["inExpansion"]))
			$this -> inExpansion = ClassInfo::$class_info[$this -> class]["inExpansion"];

		if (!isset(ClassInfo::$set_save_vars[$this -> class])) {
			ClassInfo::setSaveVars($this -> class);
			$this->defineStatics();
		} else
		
		if(!isset(self::$loaded[$this->class])) {
			$this->defineStatics();
			self::$loaded[$this->class] = true;
		}
	}
	
	/**
	 * defines some basic stuff, but it has already an object loaded. You can hook in here for subclasses.
	 *
	 * @access protected
	*/
	protected function defineStatics() {
		
	}

	/**
	 * Method for overloading functions.
	 *
	 * @link	http://php.net/manual/de/language.oop5.overloading.php
	 *
	 * @param	string $name Name of the method.
	 * @param	string $args Arguments.
	 *
	 * @return	mixed The return of the function.
	 */
	public function __call($name, $args) {
		if ($name == "bool")
			return true;

		$name = trim(strtolower($name));

		if (isset(self::$extra_methods[$this -> class][$name])) {
			return $this -> callExtraMethod($name, self::$extra_methods[$this -> class][$name], $args);
		}

		if (isset(self::$cache_extra_methods[$this -> class][$name])) {
			return $this -> callExtraMethod($name, self::$cache_extra_methods[$this -> class][$name], $args);
		}

		if (method_exists($this, $name) && is_callable(array($this, $name)))
			return call_user_func_array(array($this, $name), $args);

		// check last
		if (isset(self::$temp_extra_methods[$this -> class][$name])) {
			return $this -> callExtraMethod($name, self::$temp_extra_methods[$this -> class][$name], $args);
		}

		// check parents
		$c = $this -> class;
		while ($c = ClassInfo::GetParentClass($c)) {
			if (isset(self::$extra_methods[$c][$name])) {
				$extra_method = self::$extra_methods[$c][$name];

				// cache result
				self::$cache_extra_methods[$this -> class][$name] = self::$extra_methods[$c][$name];

				unset($c);

				return $this -> callExtraMethod($name, $extra_method, $args);
			}
		}

		$trace = debug_backtrace();
		throwError(6, 'PHP-Error', '<b>Fatal Error</b> Call to undefined method ' . $this -> class . '::' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'] . '');
	}

	/**
	 * Calls an extra method.
	 *
	 * @param	string $method_name Name of the method
	 * @param	string $extra_method Name of the extra method.
	 * @param	mixed[] $args Array with all arguments.
	 *
	 * @return	mixed The return of the extra method.
	 */
	protected function callExtraMethod($method_name, $extra_method, $args = array()) {
		// first if it is a callback
		if (is_array($extra_method)) {
			if (is_string($extra_method[0]) && substr($extra_method[0], 0, 4) == "EXT:") {
				$extra_method[0] = $this -> getInstance(substr($extra_method[0], 4));
			} else if (is_string($extra_method[0]) && $extra_method[0] == "this") {
				array_unshift($args, $method_name);
				$extra_method[0] = $this;
			}

			return call_user_func_array($extra_method, $args);
		}

		array_unshift($args, $this);
		return call_user_func_array($extra_method, $args);
	}

	/**
	 * Gets extensions of a class.
	 *
	 * @param	boolean $recursive Working recursive?
	 *
	 * @return	array[] Array with all extensions.
	 */
	public function getExtensions($recursive = true) {
		if ($this -> class == "") {
			$this -> class = strtolower(get_class($this));
		}

		if ($recursive === true) {
			if (defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this -> class])) {
				$this -> buildExtCache();
			}
			return array_keys(self::$cache_extensions[$this -> class]);
		} else
			return (isset(self::$extensions[$this -> class])) ? array_keys(self::$extensions[$this -> class]) : array();
	}

	/**
	 * builds the extension-cache
	 *
	 * @name buildExtCache
	 * @access private
	 */
	private function buildExtCache() {
		$parent = $this -> class;
		$extensions = array();
		while ($parent !== false) {
			if (isset(self::$extensions[$parent])) {
				$extensions = array_merge(self::$extensions[$parent], $extensions);
			}
			$parent = ClassInfo::getParentClass($parent);
		}

		self::$cache_extensions[$this -> class] = $extensions;
		return $extensions;
	}

	/**
	 * gets an extension-instance
	 *
	 * @name getInstance
	 * @param string - name of extension
	 */
	public function getInstance($name) {
		$name = trim(strtolower($name));

		if (isset($this -> ext_instances[$name]))
			return $this -> ext_instances[$name];

		if (defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this -> class])) {
			$this -> buildExtCache();
		}

		if (!isset(self::$extension_instances[$this -> class][$name]) || !is_object(self::$extension_instances[$this -> class][$name])) {
			if (isset(self::$cache_extensions[$this -> class][$name])) {
				self::$extension_instances[$this -> class][$name] = clone eval("return new " . $name . "(" . self::$cache_extensions[$this -> class][$name] . ");");
			} else {
				return false;
			}
		}

		$this -> ext_instances[$name] = clone self::$extension_instances[$this -> class][$name];
		$this -> ext_instances[$name] -> setOwner($this);
		return $this -> ext_instances[$name];
	}

	/**
	 * gets arguments for given extension
	 *
	 * @name getExtArguments
	 * @access public
	 * @param string - extension
	 */
	public function getExtArguments($extension) {
		if (defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this -> class])) {
			$this -> buildExtCache();
		}
		return isset(self::$cache_extensions[$this -> class][$extension]) ? self::$cache_extensions[$this -> class][$extension] : "";
	}

	/**
	 * calls a named function on each extension
	 *
	 * @name callExtending
	 * @param string - method
	 * @param param1
	 * @param param2
	 * @param param3
	 * @param param4
	 * @param param5
	 * @param param6
	 * @param param7
	 * @access public
	 * @return array - return values
	 */
	public function callExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null) {
		$returns = array();
		foreach ($this->getextensions(true) as $extension) {
			if (Object::method_exists($extension, $method)) {
				if ($instance = $this -> getinstance($extension)) {

					// so let's call ;)
					$return = $instance -> $method($p1, $p2, $p3, $p4, $p5, $p6, $p7);
					if ($return)
						$returns[] = $return;

					unset($return);
				} else {
					log_error("Could not create instance of " . $extension . " for class " . $this -> class . "");
				}
			}
		}

		return $returns;
	}

	/**
	 * calls a named function on each extension, but just extensions, directly added to this class
	 *
	 * @name LocalcallExtending
	 * @param string - method
	 * @param param1
	 * @param param2
	 * @param param3
	 * @param param4
	 * @param param5
	 * @param param6
	 * @param param7
	 * @access public
	 * @return array - return values
	 */
	public function LocalCallExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null) {

		$returns = array();
		foreach ($this->getExtensions(false) as $extension) {
			if (Object::method_exists($extension, $method)) {
				if ($instance = $this -> getinstance($extension)) {
					$instance -> setOwner($this);
					$returns[] = $instance -> $method($p1, $p2, $p3, $p4, $p5, $p6, $p7);
				}
			}
		}

		return $returns;
	}

	/**
	 * some methods for extensions
	 */

	/**
	 * gets the resource-folder for an Expansion
	 *
	 * @name getResourceFolder
	 * @access public
	 */
	public function getResourceFolder($forceAbsolute = false, $exp = null) {
		if (!isset($exp)) {
			$exp = isset(ClassInfo::$class_info[$this -> class]["inExpansion"]) ? ClassInfo::$class_info[$this -> class]["inExpansion"] : null;
		}
		if (isset(ClassInfo::$appENV["expansion"][$exp])) {
			$extFolder = ClassInfo::getExpansionFolder($exp, false, $forceAbsolute);
			return isset(ClassInfo::$appENV["expansion"][$exp]["resourceFolder"]) ? $extFolder . ClassInfo::$appENV["expansion"][$exp]["resourceFolder"] : $extFolder . "resources";
		}

		return null;
	}

	/**
	 * generates class-info
	 *
	 * @name buildClassInfo
	 * @access public
	 */
	static function buildClassInfo($class) {
		foreach ((array) self::getStatic($class, "extend") as $ext) {
			Object::extend($class, $ext);
		}
	}

}
