<?php
/**
  * every goma-class extends this class
  * this class allows you to add methods to a class with __call-overloading
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 29.11.2011
  * $Version 006
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


abstract class Object
{
		/**
		 * dynamical methods
		*/
		static private 
		$loaded_vars = array(),
		$cache_extensions = array();
		/**
		 * extensions
		*/
		public static
		$extra_methods = array(),
		$temp_extra_methods = array(),
		$extensions = array(),
		$extension_vars = array(),
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
		 * creates a method on the class
		 *
		 *@name creatMethod
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
				
				$class = trim(strtolower($class));
				$method = trim(strtolower($offset));
				
				if(empty($class))
					return false;
				
				// else we have some 500-errors on some servers
				if(!isset($object) && !defined("INSTALL") && !isset(Autoloader::$loaded[$class])) autoloader::load($class);
				
				
				
				if(method_exists($class, $method))
				{
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return true; 
				} else if(isset(self::$extra_methods[$class][$method]) || isset(self::$temp_extra_methods[$class][$method])) {
					if(PROFILE) Profiler::unmark("Object::method_exists");
					return true;
				} else if(isset($object) && method_exists($class, "__cancall") && $object->__canCall($offset)) {
					return true;
				} else
				{
						if(PROFILE) Profiler::mark("Object::method_exists after");
						
						$c = $class;
						while($c = ClassInfo::get_parent_class($c))
						{
								if(isset(self::$extra_methods[$c][$method]))
								{	
										// cache result for current class
										self::$extra_methods[$class][$method] = self::$extra_methods[$c][$method];
										if(PROFILE) {
											Profiler::unmark("Object::method_exists after");
											Profiler::unmark("Object::method_exists");
										}
										return true;
								}
						}
						unset($c);
						if(PROFILE) {
							Profiler::unmark("Object::method_exists after");
							Profiler::unmark("Object::method_exists");
						}
						
						return false;
				}
				
				
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
						if(_ereg('^([a-zA-Z0-9_-]+)\((.*)\)$', $ext, $exts))
						{
								$ext = $exts[0];
								$arguments = $exts[1];
						}
						/*if(class_exists($obj))
						{*/
								if(class_exists($ext))
								{
										if(is_subclass_of($ext, 'Extension') || is_subclass_of($ext, 'ControllerExtension'))
										{
												if(classinfo::getStatic($ext, 'extra_methods'))
												{
														foreach(classinfo::getStatic($ext, 'extra_methods') as $method)
														{
																self::$extra_methods[$obj][$method] = array("EXT:" . $ext,$method);
														}
												}
												self::$extensions[$obj][$ext] = $ext;
												self::$extension_vars[$obj][$ext] = $arguments;
										} else
										{
												throwError(6, 'PHP-Error', 'Extension '.text::protect($ext).' isn\'t a Extension.');
										}
								} else
								{
										throwError(6, 'PHP-Error', 'Extension '.text::protect($ext).' does not exist.');
								}
						/*} else
						{
								throwError(6, 'PHP-Error', 'Extendable class '.text::protect($obj).' wasn\'t found!');
						}*/
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
				
				if(isset(self::$cache_singleton_classes[$class]))
				{
						$class = clone self::$cache_singleton_classes[$class];
				} else
				{
						if($class == "")
						{
							
							$trace = debug_backtrace();
							throwError(6, 'PHP-Error', 'Cannot initiate empty Class in '.$trace[0]["file"].' on line '.$trace[0]["line"].'');
						}
						if(classinfo::isAbstract($class)) {
							$trace = debug_backtrace();
							throwError(6, 'PHP-Error', 'Cannot initiate abstract Class in '.$trace[0]["file"].' on line '.$trace[0]["line"].'');
						}
						
						if((defined("INSTALL") && class_exists($class)) || classinfo::exists($class)) {
								
								self::$cache_singleton_classes[$class] = new $class;
								$class = clone self::$cache_singleton_classes[$class];
						} else {
								throwError(6, 'PHP-Error', "Class ".$class." not found in Object.php on line ".__FILE__."");
						}
						
				}
				
				if(PROFILE) Profiler::unmark("Object::instance");
				
				return $class;
		}
		/**
		 * synonym for instance
		 *@name singleton
		 *@access public
		*/
		public static function singleton($name)
		{
				return self::instance($name);
		}
		
		
		/**
		 * construct
		*/
		public function __construct()
		{
				if(PROFILE) Profiler::mark("Object::__construct");

				$this->class = strtolower(get_class($this));
				
				if(!isset(ClassInfo::$set_save_vars[$this->class]))
					ClassInfo::setSaveVars($this->class);
				
				if(!isset(self::$loaded_vars[$this->class]) || defined("GENERATE_CLASS_INFO"))
				{
						self::$loaded_vars[$this->class] = true;
						
						foreach($this->getextensions() as $extension)
						{
								$arguments = $this->getExtArguments($extension);
								self::$extension_instances[$this->class][$extension] = array($extension, $arguments);
								unset($instance);
						}					
				}
				
				
				
				if(PROFILE) Profiler::unmark("Object::__construct");
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
			} else if(isset(self::$temp_extra_methods[$this->class][$name])) {
				return $this->callExtraMethod($name, self::$temp_extra_methods[$this->class][$name], $args);
			} else if(method_exists($this, $name))
				return call_user_func_array(array($this, $name), $args);
			
			
			$c = $this->class;
			if($c = ClassInfo::GetParentClass($c))
			{
					while($c = ClassInfo::GetParentClass($c))
					{
							if(isset(self::$extra_methods[$c][$name]))
							{
									$extra_method = self::$extra_methods[$c][$name];
									return $this->callExtraMethod($name, $extra_method, $args);
							}
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
	 			if(!defined("GENERATE_CLASS_INFO") && isset(self::$cache_extensions[$this->class])) {
	 				return self::$cache_extensions[$this->class];
	 			} else {
	 				$parent = $this->class;
	 				$extensions = array();
	 				while($parent !== false) {
	 					
	 					$extensions = array_merge($extensions, isset(self::$extensions[$parent]) ? self::$extensions[$parent] : array());
	 					$parent = classinfo::getParentClass($parent);
	 				}
	 				
	 				self::$cache_extensions[$this->class] = $extensions;
	 				return $extensions;
	 			}
	 		} else 
				return (isset(self::$extensions[$this->class])) ? self::$extensions[$this->class] : array();
		 }
		 /**
		  * gets an extension-instance
		  *
		  *@name getinstance
		  *@param string - name of extension
		 */
		 public function getinstance($name)
		 {
		 		if(isset($this->ext_instances[$name]))
		 			return $this->ext_instances[$name];
		 		
				if(isset(self::$extension_instances[$this->class][$name])) {
					if(is_array(self::$extension_instances[$this->class][$name])) {
						self::$extension_instances[$this->class][$name] = eval("return new ".self::$extension_instances[$this->class][$name][0]."(".self::$extension_instances[$this->class][$name][1].");");
						self::$extension_instances[$this->class][$name]->setOwner($this);
						$this->ext_instances[$name] = clone self::$extension_instances[$this->class][$name];
						return $this->ext_instances[$name];
					} else {
						$this->ext_instances[$name] = clone self::$extension_instances[$this->class][$name];
						return $this->ext_instances[$name];
					}
				} else {
					return false;
				}
		 }
		 /**
		  * gets arguments for given extension
		  *@name getExtArguments
		  *@access public
		  *@param string - extension
		 */
		 public function getExtArguments($extension)
		 {
				return isset(self::$extension_vars[$extension]) ? self::$extension_vars[$extension] : "";
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
		 public function callExtending($method, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null, $p6 = null, $p7 = null)
		 {
				$returns = array();
				foreach($this->getextensions(true) as $extension)
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
		 public function LocalCallExtending($method, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null, $p6 = null, $p7 = null)
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
		 
}

require_once(FRAMEWORK_ROOT . 'core/ClassInfo.php');
ClassInfo::addSaveVar("object", "extensions");
ClassInfo::addSaveVar("object", "extra_methods");