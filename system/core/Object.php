<?php
/**
  * every goma-class extends this class
  * this class allows you to add methods to a class with __call-overloading
  *
  * this class implements since 3.1 the very basic of the new expansion-system of Goma 2, so the ability to create full extensions, which contains classes, views and other code
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 22.01.2013
  * $Version 3.2.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

interface ExtensionModel {
	public function setOwner($object);
	public function getOwner();
}

abstract class Object
{
		/**
		 * caches
		*/
		static private 
		$method_cache = array(),
		$cache_extensions = array();
		
		/**
		 * extensions
		*/
		public static
		$extra_methods = array(),
		$temp_extra_methods = array(),
		$cache_extra_methods = array(),
		$extensions = array(),
		$ci_funcs = array(),
		$cache_singleton_classes = array(); // functions for generating classinfo
		
		/**
		 * protected vars
		*/
		protected static
		$extension_instances = array();
		
		/**
		 * local extension instances
		*/
		private $ext_instances = array();
		
		/**
		 * the current class-name in lowercase-letters
		 *
		 *@name class
		 *@access public
		*/
		public $class;
		
		/**
		 * this variable has a value if the class belongs to an extension, else it is null
		 *
		 *@name inExtension
		 *@access public
		*/
		public $inExpansion;
		
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
								return eval("return isset(".$class_name."::\$".$var.") ? ".$class_name."::\$".$var." : null;");
						} else
						{
								throwError("6","PHP-Error", "Invalid name of var in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("6","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
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
								throwError("6","PHP-Error", "Invalid name of var in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("6","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
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
								throwError("6","PHP-Error", "Invalid name of var in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("6","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
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
								throwError("6","PHP-Error", "Invalid name of function in ".__METHOD__." in ".__FILE__."");
						}
				} else
				{
						throwError("6","PHP-Error", "Invalid name of class in ".__METHOD__." in ".__FILE__."");
				}
		}
		
		/**
		 * creates a method on the class
		 *
		 *@name createMethod
		 *@param string - class for the method
		 *@param string - method-name
		 *@param string - code
		*/
		public static function createMethod($class, $method, $code, $temp = false)
		{
				$method = strtolower($method);
				$class = strtolower($class);
				if($temp) {
					self::$temp_extra_methods[$class][$method] = create_function('$obj',$code);
				} else if(!Object::method_exists($class, $method)) {
					self::$extra_methods[$class][$method] = create_function('$obj',$code);
				}
				
		}
		
		/**
		 * links a real method to this class
		 *
		 *@param string - class for the method
		 *@param string - method-name
		 *@param string - real function
		*/
		public static function linkMethod($class, $method, $realfunc, $temp = false)
		{
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
		 * checks if an method exists
		 *@name method_exists
		 *@access public static
		 *@param string - class
		 *@param string - methodname
		*/
		public static function method_exists($class, $offset)
		{
				if(PROFILE) Profiler::mark("Object::method_exists");
				
				if(is_object($class))
				{
						$object = $class;
						$class = strtolower(get_class($class));
				}
				
				$class = strtolower(trim($class));
				$method = strtolower(trim($offset));
				
				if(empty($class) || empty($method)) {
					unset($class, $method);
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return false;
				}
										
				if(isset(self::$method_cache[$class . "::" . $method])) {
						
					// object-case
					if(!self::$method_cache[$class . "::" . $method] && isset($object)) {
						if(method_exists($class, "__cancall") && $object->__canCall($offset)) {
							unset($class, $method);
							if(PROFILE) Profiler::unmark("Object::method_exists");
							return true;
						}
					}
					
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return self::$method_cache[$class . "::" . $method];
				}
				
				if(version_compare(phpversion(), "5.3", "<") && !isset(ClassManifest::$loaded[$class]))
					ClassManifest::load($class);
				
				// check native
				if(method_exists($class, $method) && is_callable(array($class, $method)))
				{
					self::$method_cache[$class . "::" . $method] = true;
					unset($class, $method);
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return true;
				}
				
				// check in DB
				if(isset(self::$extra_methods[$class][$method]) || isset(self::$temp_extra_methods[$class][$method])) {
					self::$method_cache[$class . "::" . $method] = true;
					unset($class, $method);
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return true;
				}
				
				// check on object
				if(isset($object) && method_exists($class, "__cancall") && $object->__canCall($offset)) {
					unset($class, $method);
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return true;
				}
				
				// check parents
				if(PROFILE) Profiler::mark("Object::method_exists after");
				
				$c = $class;
				while($c = ClassInfo::get_parent_class($c))
				{
						if(isset(self::$extra_methods[$c][$method]))
						{	
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
	  	 * extends the object
		 *@name extend
		 *@access public
		 *@param string - object to extend
		 *@param string - extension
		*/
		public static function extend($obj, $ext)
		{
				if(defined("GENERATE_CLASS_INFO"))
				{
						$obj = strtolower($obj);
						$ext = strtolower($ext);
						$arguments = "";
						if(preg_match('/^([a-zA-Z0-9_\-]+)\((.*)\)$/', $ext, $exts)) {
								$ext = $exts[0];
								$arguments = $exts[1];
						}
						if(class_exists($ext))
						{
								if(ClassInfo::hasInterface($ext, "ExtensionModel")) {
										if(classinfo::getStatic($ext, 'extra_methods'))
										{
												foreach(classinfo::getStatic($ext, 'extra_methods') as $method)
												{
														self::$extra_methods[$obj][strtolower($method)] = array("EXT:" . $ext,$method);
												}
										}
										self::$extensions[$obj][$ext] = $arguments;
								} else
								{
										throwError(6, 'PHP-Error', 'Extension '.convert::raw2text($ext).' isn\'t a Extension.');
								}
						} else
						{
								throwError(6, 'PHP-Error', 'Extension '.convert::raw2text($ext).' does not exist.');
						}
				}
		}
		
			/**
		  * gets singletom of the given class
		  *@name instance
		  *@access public
		  *@param string - class
		*/
		public static function instance($class)
		{
				if(PROFILE) Profiler::mark("Object::instance");
				
				if(is_object($class))
				{
						return clone $class;
				}
				
				$class = strtolower($class);
				
				/* --- */
				
               			// caching
				if(isset(self::$cache_singleton_classes[$class]))
				{
						$class = clone self::$cache_singleton_classes[$class];
				} else
				{
                        
                        			// error catching
						if($class == "")
						{
							$trace = debug_backtrace();
							throwError(6, 'Class-Initiate-Error', 'Cannot initiate empty Class in '.$trace[0]["file"].' on line '.$trace[0]["line"].'');
						}
                        
						if(ClassInfo::isAbstract($class)) {
							$trace = debug_backtrace();
							throwError(6, 'Class-Initiate-Error', 'Cannot initiate abstract Class in '.$trace[0]["file"].' on line '.$trace[0]["line"].'');
						}
						
                        			// generate Class
						if((defined("INSTALL") && class_exists($class)) || ClassInfo::exists($class)) {
								self::$cache_singleton_classes[$class] = new $class;
								$class = clone self::$cache_singleton_classes[$class];
						} else {
								throwError(6, 'PHP-Error', "Class ".$class." not found in ".__FILE__." on line ".__LINE__."");
						}
						
				}
				
				if(PROFILE) Profiler::unmark("Object::instance");
				
				return $class;
		}
		
		/**
		 * synonym for instance
		 *
		 *@name singleton
		 *@access public
		*/
		public static function singleton($name)
		{
				return self::instance($name);
		}
		
		
		/**
		 * sets class-name and, if hasn't done, yet, the Save-Vars
		 *
		 *@name __construct
		 *@access public
		*/
		public function __construct()
		{
				$this->class = strtolower(get_class($this));
				
				if(isset(ClassInfo::$class_info[$this->class]["inExpansion"]))
					$this->inExpansion = ClassInfo::$class_info[$this->class]["inExpansion"];
				
				if(!isset(ClassInfo::$set_save_vars[$this->class]))
					ClassInfo::setSaveVars($this->class);
		}
		
		/**
		 * magic method for overloading functions
		 * we implement temporary methods and long-time-overloading
		 *
		 *@link http://php.net/manual/de/language.oop5.overloading.php
		 *@name __call
		 *@access public
		*/
		 public function __call($name, $args)
		 {
	 		if($name == "bool")
	 			return true;
	 		
			$name = trim(strtolower($name));
			
			if(isset(self::$extra_methods[$this->class][$name]))
			{
				return $this->callExtraMethod($name, self::$extra_methods[$this->class][$name], $args);
			}
			
			if(isset(self::$cache_extra_methods[$this->class][$name])) {
				return $this->callExtraMethod($name, self::$cache_extra_methods[$this->class][$name], $args);
			}
			
			if(method_exists($this, $name) && is_callable(array($this, $name)))
				return call_user_func_array(array($this, $name), $args);
			
			// check last
			if(isset(self::$temp_extra_methods[$this->class][$name])) {
				return $this->callExtraMethod($name, self::$temp_extra_methods[$this->class][$name], $args);
			}
			
			// check parents
			$c = $this->class;
			while($c = ClassInfo::GetParentClass($c))
			{
					if(isset(self::$extra_methods[$c][$name]))
					{
							$extra_method = self::$extra_methods[$c][$name];
							
							// cache result
							self::$cache_extra_methods[$this->class][$name] = self::$extra_methods[$c][$name];
							
							unset($c);
							
							return $this->callExtraMethod($name, $extra_method, $args);
					}
			}
			
			
			$trace = debug_backtrace();
			throwError(6, 'PHP-Error', '<b>Fatal Error</b> Call to undefined method ' . $this->class . '::' . $name . ' in '.$trace[0]['file'].' on line '.$trace[0]['line'].'');
				
		 }
		 
		 /**
		  * calls the extra-method
		  * we implement calling for Extensions and having dynamic callbacks with the current object
		  * you can set the first item of the callback-array to this and we replace it with $this
		  *
		  *@name callExtraMethod
		  *@access protected
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
		  * gets the extensions
		  *
		  *@name getExtensions
		  *@access public
		 */
		 public function getExtensions($recursive = true)
		 {
		 	if($this->class == "") {
		 		$this->class = strtolower(get_class($this));
		 	}
		 	
	 		if($recursive === true) {
	 			if(defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->class])) {
	 				$this->buildExtCache();
	 			}
	 			return array_keys(self::$cache_extensions[$this->class]);
	 		} else 
				return (isset(self::$extensions[$this->class])) ? array_keys(self::$extensions[$this->class]) : array();
		 }
		 
		 /**
		  * builds the extension-cache
		  *
		  *@name buildExtCache
		  *@access private
		 */
		 private function buildExtCache() {
		 	$parent = $this->class;
			$extensions = array();
			while($parent !== false) {
				if(isset(self::$extensions[$parent])) {
					$extensions = array_merge(self::$extensions[$parent], $extensions);
				}
				$parent = ClassInfo::getParentClass($parent);
			}
			
			self::$cache_extensions[$this->class] = $extensions;
			return $extensions;
		 }
		
		 
		 /**
		  * gets an extension-instance
		  *
		  *@name getInstance
		  *@param string - name of extension
		 */
		 public function getInstance($name)
		 {
		 		$name = trim(strtolower($name));
		 		
		 		if(isset($this->ext_instances[$name]))
		 			return $this->ext_instances[$name];
		 		
		 		if(defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->class])) {
	 				$this->buildExtCache();
	 			}

	 			if(!isset(self::$extension_instances[$this->class][$name]) || !is_object(self::$extension_instances[$this->class][$name])) {
	 				if(isset(self::$cache_extensions[$this->class][$name])) {
	 					self::$extension_instances[$this->class][$name] = clone eval("return new ".$name."(".self::$cache_extensions[$this->class][$name].");");
	 				} else {
	 					return false;
	 				}
	 			}
	 			
	 			$this->ext_instances[$name] = clone self::$extension_instances[$this->class][$name];
	 			$this->ext_instances[$name]->setOwner($this);
	 			return $this->ext_instances[$name];
		 }
		 
		 /**
		  * gets arguments for given extension
		  *
		  *@name getExtArguments
		  *@access public
		  *@param string - extension
		 */
		 public function getExtArguments($extension)
		 {
		 		if(defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->class])) {
	 				$this->buildExtCache();
	 			}
				return isset(self::$cache_extensions[$this->class][$extension]) ? self::$cache_extensions[$this->class][$extension] : "";
		 }
		 
		 /**
		  * calls a named function on each extension
		  *
		  *@name callExtending
		  *@param string - method
		  *@param param1
		  *@param param2
		  *@param param3
		  *@param param4
		  *@param param5
		  *@param param6
		  *@param param7
		  *@access public
		  *@return array - return values
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
						log_error("Could not create instance of ".$extension." for class ".$this->class."");
					}
				}
			}
			
			return $returns;
		 }
		 
		 /**
		  * calls a named function on each extension, but just extensions, directly added to this class
		  *
		  *@name LocalcallExtending
		  *@param string - method
		  *@param param1
		  *@param param2
		  *@param param3
		  *@param param4
		  *@param param5
		  *@param param6
		  *@param param7
		  *@access public
		  *@return array - return values
		 */
		 public function LocalCallExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null)
		 {
		 		
				$returns = array();
				foreach($this->getExtensions(false) as $extension)
				{
						if(Object::method_exists($extension, $method))
						{
								if($instance = $this->getinstance($extension))
								{
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
		  *@name getResourceFolder
		  *@access public
		 */
		 public function getResourceFolder($forceAbsolute = false, $exp = null) {
		 	if(!isset($exp)) {
		 		$exp = isset(ClassInfo::$class_info[$this->class]["inExpansion"]) ? ClassInfo::$class_info[$this->class]["inExpansion"] : null;
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
		  *@name buildClassInfo
		  *@access public
		 */
		 static function buildClassInfo($class) {
			 foreach((array) self::getStatic($class, "extend") as $ext) {
				 Object::extend($class, $ext);
			 }
		 }
		 
}
