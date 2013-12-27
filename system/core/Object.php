<?php
defined("IN_GOMA") OR die();

interface ExtensionModel {

	public function setOwner($object);

	public function getOwner();
}

/**
 * Base class for _every_ Goma class.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Framework
 * @version 3.3
 */
abstract class Object {

	/**
	 * caches
	 */
	static private $method_cache = array(), $cache_extensions = array();

	/**
	 * Extension methods.
	 */
	public static $extra_methods = array();

	/**
	 * Temporary extension methods.
	 */
	public static $temp_extra_methods = array();

	public static $cache_extra_methods = array(), $extensions = array(), $ci_funcs = array(), $cache_singleton_classes = array();

	/**
	 * protected vars
	 */
	protected static $extension_instances = array();

	/**
	 * local extension instances
	 */
	private $ext_instances = array();

	/**
	 * The current lowercase class name.
	 */
	public $classname;

	/**
	 * The current lowercase class name.
	 *
	 * @deprecated Bad variable name.
	 */
	public $class;

	/**
	 * this variable has a value if the class belongs to an extension, else it is
	 * null
	 */
	public $inExpansion;

	/**
	 * Indicates if the constructor has already been axecuted.
	 */
	private static $loaded;

	/**
	 * Gets the value of $class::$$var.
	 *
	 * @param string $class Name of the class.
	 * @param string $var Name of the variable.
	 *
	 * @return mixed Value of $var.
	 */
	public static function getStatic($class, $var) {
		if(is_object($class))
			$class = $class->classname;

		if(!empty($class)) {
			if(!empty($var)) {
				return eval("return isset(" . $class . "::\$" . $var . ") ? " . $class . "::\$" . $var . " : null;");
			} else {
				throw new LogicException("Invalid name of variable $var for $class");
			}
		} else {
			throw new LogicException("Invalid name of class $class");
		}
	}

	/**
	 * Checks, if $class::$$var is set.
	 *
	 * @param string $class Name of the class.
	 * @param string $var Name of the variable.
	 *
	 * @return boolean
	 */
	public static function hasStatic($class, $var) {
		if(is_object($class))
			$class = $class->classname;

		if(!empty($class)) {
			if(!empty($var)) {
				return eval("return isset(" . $class . "::\$" . $var . ");");
			} else {
				throw new LogicException("Invalid name of variable $var for $class");
			}
		} else {
			throw new LogicException("Invalid name of class $class");
		}
	}

	/**
	 * Sets $value for $class::$$var.
	 *
	 * @param string $class Name of the class.
	 * @param string $var Name of the variable.
	 * @param mixed $value
	 *
	 * @return void
	 */
	public static function setStatic($class, $var, $value) {
		if(is_object($class))
			$class = $class->classname;

		if(!empty($class)) {
			if(!empty($var)) {
				return eval($class . "::\$" . $var . " = " . var_export($value, true) . ";");
			} else {
				throw new LogicException("Invalid name of variable $var for $class");
			}
		} else {
			throw new LogicException("Invalid name of class $class");
		}
	}

	/**
	 * Calls $class::$$func.
	 *
	 * @param string $class Name of the class.
	 * @param string $func Name of the function.
	 *
	 * @return void
	 */
	public static function callStatic($class, $func) {
		if(is_object($class))
			$class = $class->classname;

		if(!empty($class)) {
			if(!empty($func)) {
				return call_user_func_array(array($class, $func), array($class));
			} else {
				throw new LogicException("Invalid name of variable $var for $class");
			}
		} else {
			throw new LogicException("Invalid name of class $class");
		}
	}

	/**
	 * Extends a class with a method.
	 *
	 * @param string $class Name of the class.
	 * @param string $method Name of the method.
	 * @param string $code Code of the method.
	 * @param boolean $temp Is the method only temporarily?
	 *
	 * @return void
	 */
	public static function createMethod($class, $method, $code, $temp = false) {
		$method = strtolower($method);
		$class = strtolower($class);
		if($temp) {
			self::$temp_extra_methods[$class][$method] = create_function('$obj', $code);
		} else if(!Object::method_exists($class, $method)) {
			self::$extra_methods[$class][$method] = create_function('$obj', $code);
		}
	}

	/**
	 * Links $class::$$method to the function $realfunc.
	 *
	 * @param string $class Name of the class.
	 * @param string $method Name of the method.
	 * @param string $realfunc Name of the linked function.
	 * @param boolean $temp Is the link only temporarily?
	 *
	 * @return void
	 */
	public static function linkMethod($class, $method, $realfunc, $temp = false) {
		$method = strtolower($method);
		$class = strtolower($class);
		if($temp) {
			self::$temp_extra_methods[$class][$method] = $realfunc;
		} else if(!Object::method_exists($class, $method)) {
			self::$extra_methods[$class][$method] = $realfunc;
		}

		self::$method_cache[$class . "::" . $method] = true;
	}

	/**
	 * Checks if $class has $method.
	 *
	 * @param mixed $class Object or name of the class.
	 * @param string $method Name of the method.
	 *
	 * @return boolean
	 */
	public static function method_exists($class, $method) {
		if(PROFILE)
			Profiler::mark("Object::method_exists");

		// Gets class name if $class is an object.
		if(is_object($class)) {
			$object = $class;
			$class = strtolower(get_class($class));
		}

		$class = strtolower(trim($class));
		$method = strtolower(trim($method));

		// Class or method are null?
		if(empty($class) || empty($method)) {
			unset($class, $method);
			if(PROFILE)
				Profiler::unmark("Object::method_exists");
			return false;
		}

		if(isset(self::$method_cache[$class . "::" . $method])) {

			// object-case
			if(!self::$method_cache[$class . "::" . $method] && isset($object)) {
				if(method_exists($class, "__cancall") && $object->__canCall($method)) {
					unset($class, $method);
					if(PROFILE)
						Profiler::unmark("Object::method_exists");
					return true;
				}
			}

			if(PROFILE)
				Profiler::unmark("Object::method_exists");
			return self::$method_cache[$class . "::" . $method];
		}

		if(version_compare(phpversion(), "5.3", "<") && !isset(ClassManifest::$loaded[$class]))
			ClassManifest::load($class);

		// check native
		if(method_exists($class, $method) && is_callable(array($class, $method))) {
			self::$method_cache[$class . "::" . $method] = true;
			unset($class, $method);
			if(PROFILE)
				Profiler::unmark("Object::method_exists");
			return true;
		}

		// check in DB
		if(isset(self::$extra_methods[$class][$method]) || isset(self::$temp_extra_methods[$class][$method])) {
			self::$method_cache[$class . "::" . $method] = true;
			unset($class, $method);
			if(PROFILE)
				Profiler::unmark("Object::method_exists");
			return true;
		}

		// check on object
		if(isset($object) && method_exists($class, "__cancall") && $object->__canCall($method)) {
			unset($class, $method);
			if(PROFILE)
				Profiler::unmark("Object::method_exists");
			return true;
		}

		// check parents
		if(PROFILE)
			Profiler::mark("Object::method_exists after");

		$c = $class;
		while($c = ClassInfo::get_parent_class($c)) {
			if(isset(self::$extra_methods[$c][$method])) {
				// cache result for current class
				self::$method_cache[$class . "::" . $method] = true;
				unset($c, $method, $class);
				if(PROFILE) {
					Profiler::unmark("Object::method_exists after");
					Profiler::unmark("Object::method_exists");
				}

				return true;
			}
		}

		self::$method_cache[$class . "::" . $method] = false;

		unset($c, $method, $class);
		if(PROFILE) {
			Profiler::unmark("Object::method_exists after");
			Profiler::unmark("Object::method_exists");
		}

		return false;
	}

	/**
	 * Extends an object.
	 *
	 * @param string The extended object.
	 * @param string The extension.
	 *
	 * @return void
	 */
	public static function extend($obj, $ext) {
		if(defined("GENERATE_CLASS_INFO")) {
			$obj = strtolower($obj);
			$ext = strtolower($ext);
			$arguments = "";
			if(preg_match('/^([a-zA-Z0-9_\-]+)\((.*)\)$/', $ext, $exts)) {
				$ext = $exts[0];
				$arguments = $exts[1];
			}
			if(class_exists($ext)) {
				if(ClassInfo::hasInterface($ext, "ExtensionModel")) {
					if(classinfo::getStatic($ext, 'extra_methods')) {
						foreach(classinfo::getStatic($ext, 'extra_methods') as $method) {
							self::$extra_methods[$obj][strtolower($method)] = array("EXT:" . $ext, $method);
						}
					}
					self::$extensions[$obj][$ext] = $arguments;
				} else {
					throw new LogicException("Extension $ext isn't a Extension");
				}
			} else {
				throw new LogicException("Extension $ext does not exist.");
			}
		}
	}

	/**
	 * Gets the singleton of a class.
	 *
	 * @param string $class Name of the class.
	 *
	 * @return Object The singleton.
	 */
	public static function instance($class) {

		if(is_object($class)) {
			return clone $class;
		}

		$class = strtolower($class);

		if(PROFILE)
			Profiler::mark("Object::instance");

		/* --- */

		// caching
		if(isset(self::$cache_singleton_classes[$class])) {
			if(PROFILE)
				Profiler::unmark("Object::instance");
			$class = clone self::$cache_singleton_classes[$class];
		} else {

			// error catching
			if($class == "") {
				throw new LogicException("Cannot initiate empty Class");
			}

			if(ClassInfo::isAbstract($class)) {
				throw new LogicException("Cannot initiate empty Class");
			}

			// generate Class
			if((defined("INSTALL") && class_exists($class)) || ClassInfo::exists($class)) {
				self::$cache_singleton_classes[$class] = new $class;
				$class = clone self::$cache_singleton_classes[$class];
			} else {
				throw new LogicException("Cannot initiate empty Class");
			}
		}

		if(PROFILE)
			Profiler::unmark("Object::instance");

		return $class;
	}

	/**
	 * Synonym for instance().
	 *
	 * @param string $class Name of the class.
	 *
	 * @return The singleton.
	 */
	public static function singleton($class) {
		return self::instance($class);
	}

	/**
	 * Sets class name and save vars.
	 */
	public function __construct() {
		// Set class name
		$this->classname = strtolower(get_class($this));

		// temporary until release
		//@TODO: remove this
		$this->class = strtolower(get_class($this));

		if(isset(ClassInfo::$class_info[$this->classname]["inExpansion"]))
			$this->inExpansion = ClassInfo::$class_info[$this->classname]["inExpansion"];

		if(!isset(ClassInfo::$set_save_vars[$this->classname])) {
			ClassInfo::setSaveVars($this->classname);
			$this->defineStatics();
		} else if(!isset(self::$loaded[$this->classname])) {
			$this->defineStatics();
			self::$loaded[$this->classname] = true;
		}
	}

	/**
	 * Defines some basic stuff, but it has already an object loaded. You can hook in
	 * here for subclasses.
	 */
	protected function defineStatics() {

	}

	/**
	 * This method overloads functions.
	 *
	 * @link http://php.net/manual/de/language.oop5.overloading.php
	 *
	 * @param string $name Name of the method.
	 * @param string $args Arguments.
	 *
	 * @return mixed The return of the function.
	 */
	public function __call($name, $args) {
		if($name == "bool")
			return true;

		$name = trim(strtolower($name));

		if(isset(self::$extra_methods[$this->classname][$name])) {
			return $this->callExtraMethod($name, self::$extra_methods[$this->classname][$name], $args);
		}

		if(isset(self::$cache_extra_methods[$this->classname][$name])) {
			return $this->callExtraMethod($name, self::$cache_extra_methods[$this->classname][$name], $args);
		}

		if(method_exists($this, $name) && is_callable(array($this, $name)))
			return call_user_func_array(array($this, $name), $args);

		// check last
		if(isset(self::$temp_extra_methods[$this->classname][$name])) {
			return $this->callExtraMethod($name, self::$temp_extra_methods[$this->classname][$name], $args);
		}

		// check parents
		$c = $this->classname;
		while($c = ClassInfo::GetParentClass($c)) {
			if(isset(self::$extra_methods[$c][$name])) {
				$extra_method = self::$extra_methods[$c][$name];

				// cache result
				self::$cache_extra_methods[$this->classname][$name] = self::$extra_methods[$c][$name];

				unset($c);

				return $this->callExtraMethod($name, $extra_method, $args);
			}
		}

		throw new BadMethodCallException("Call to undefined method ' . $this->classname . '::' . $name . '");
	}

	/**
	 * Calls an extra method.
	 *
	 * @param string $method_name Name of the method
	 * @param string $extra_method Name of the extra method.
	 * @param mixed[] $args Array with all arguments.
	 *
	 * @return mixed The return of the extra method.
	 */
	protected function callExtraMethod($method_name, $extra_method, $args = array()) {
		// first if it is a callback
		if(is_array($extra_method)) {
			if(is_string($extra_method[0]) && substr($extra_method[0], 0, 4) == "EXT:") {
				$extra_method[0] = $this->getInstance(substr($extra_method[0], 4));
			} else if(is_string($extra_method[0]) && $extra_method[0] == "this") {
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
	 * @param boolean $recursive Working recursive?
	 *
	 * @return array[] Array with all extensions.
	 */
	public function getExtensions($recursive = true) {
		if($this->classname == "") {
			$this->classname = strtolower(get_class($this));
		}

		if($recursive === true) {
			if(defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->classname])) {
				$this->buildExtCache();
			}
			return array_keys(self::$cache_extensions[$this->classname]);
		} else
			return (isset(self::$extensions[$this->classname])) ? array_keys(self::$extensions[$this->classname]) : array();
	}

	/**
	 * Builds the extension cache.
	 *
	 * @return array[] Array with the extensions.
	 */
	private function buildExtCache() {
		$parent = $this->classname;
		$extensions = array();
		while($parent !== false) {
			if(isset(self::$extensions[$parent])) {
				$extensions = array_merge(self::$extensions[$parent], $extensions);
			}
			$parent = ClassInfo::getParentClass($parent);
		}

		self::$cache_extensions[$this->classname] = $extensions;
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

		if(isset($this->ext_instances[$name]))
			return $this->ext_instances[$name];

		if(defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->classname])) {
			$this->buildExtCache();
		}

		if(!isset(self::$extension_instances[$this->classname][$name]) || !is_object(self::$extension_instances[$this->classname][$name])) {
			if(isset(self::$cache_extensions[$this->classname][$name])) {
				self::$extension_instances[$this->classname][$name] = clone eval("return new " . $name . "(" . self::$cache_extensions[$this->classname][$name] . ");");
			} else {
				return false;
			}
		}

		$this->ext_instances[$name] = clone self::$extension_instances[$this->classname][$name];
		$this->ext_instances[$name]->setOwner($this);
		return $this->ext_instances[$name];
	}

	/**
	 * gets arguments for given extension
	 *
	 * @name getExtArguments
	 * @access public
	 * @param string - extension
	 */
	public function getExtArguments($extension) {
		if(defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->classname])) {
			$this->buildExtCache();
		}
		return isset(self::$cache_extensions[$this->classname][$extension]) ? self::$cache_extensions[$this->classname][$extension] : "";
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
		foreach($this->getextensions(true) as $extension) {
			if(Object::method_exists($extension, $method)) {
				if($instance = $this->getinstance($extension)) {

					// so let's call ;)
					$return = $instance->$method($p1, $p2, $p3, $p4, $p5, $p6, $p7);
					if($return)
						$returns[] = $return;

					unset($return);
				} else {
					log_error("Could not create instance of " . $extension . " for class " . $this->classname . "");
				}
			}
		}

		return $returns;
	}

	/**
	 * calls a named function on each extension, but just extensions, directly added
	 * to this class
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
		foreach($this->getExtensions(false) as $extension) {
			if(Object::method_exists($extension, $method)) {
				if($instance = $this->getinstance($extension)) {
					$instance->setOwner($this);
					$returns[] = $instance->$method($p1, $p2, $p3, $p4, $p5, $p6, $p7);
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
		if(!isset($exp)) {
			$exp = isset(ClassInfo::$class_info[$this->classname]["inExpansion"]) ? ClassInfo::$class_info[$this->classname]["inExpansion"] : null;
		}
		if(isset(ClassInfo::$appENV["expansion"][$exp])) {
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
		foreach((array) self::getStatic($class, "extend") as $ext) {
			Object::extend($class, $ext);
		}
	}
	
	
	/**
	 * deep cloning.
	*/
	public function deepClone() {
		$c = unserialize(serialize($this));
		return $c;
	}
	
	/**
	 * returns class-icon.
	*/
	public function ClassIcon() {
		return ClassInfo::getClassIcon($this->classname);
	} 

}
