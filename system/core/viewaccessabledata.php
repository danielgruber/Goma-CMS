<?php
/**
 * @package		Goma\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined("IN_GOMA") OR die();


/**
 * Goma-Core to access data in a class from the template.
 *
 * This class allows the view (template) access to the class, which extends this
 * it provides methods to do e.g. $object["test"] = 1;
 * the following features are implemented
 * iterator - foreach
 * array-access 
 * overloading properties.
 *
 * @package		Goma\Core
 * @version		2.2.9
 */
class ViewAccessableData extends Object implements Iterator, ArrayAccess
{		
		/**
		 * default datatype for casting.
		 *
		 * @access public
		 * @var string
		*/
		static $default_casting = "HTMLText";
		
		/**
		 * set of fields with cast-type as value.
		 *
		 * @access public
		*/
		static $casting = array();
		
		/**
		 * data is stored in this var.
		 *
		 * @acccess public
		 * @var array
		*/
		public $data = array();
		
		/**
		 * contains the original data at object-generation.
		 *
		 * @access public
		*/
		public $original = array();
		
		/**
		 * customised data for template via ViewAccessableData::customise.
		 *
		 * @access protected
		 * @var array
		*/
		public $customised = array();
		
		/**
		 * dataset-position when this object is in a specific dataset.
		 *
		 * @access public
		*/
		public $dataSetPosition = 0;
		
		/**
		 * indicates whether the data was changes or not.
		 *
		 * @access public
		*/
		public $changed = false;
		
		/**
		 * dataset in which this Object is.
		 *
		 * @access public
		*/
		public $dataset;
		
		/**
		 * dataClass contains the class for which this data is, if it's not the same as the class, which it contains.
		 *
		 * @access public
		*/
		public $dataClass;
		
		/**
		 * default values for specfic fields.
		 *
		 * @access public
		*/
		public $defaults;
		
		/**
		 * server-vars. This is for internal usage.
		 *
		 * @access private
		*/
		private static $server;
		
		/**
		 * get-vars. This is for internal usage.
		 *
		 * @access public
		*/
		private static $_get;
		
		/**
		 * post-vars. This is for internal usage.
		 *
		 * @access public
		*/
		private static $_post;
		
		/**
		 * a list of not allowed methods. This is for internal usage.
		 *
		 * @access public
		 * @var array
		*/
		public static $notViewableMethods = array
		(
			"getdata",
			"getform",
			"geteditform",
			"getwholedata",
			"set_many_many",
			"get_has_one",
			"get_many",
			"get",
			"setfield",
			"setwholedata",
			"write",
			"writerecord",
			"__construct",
			"method_exists",
			"callmethodbyrecord",
			"getmanymany",
			"gethasmany",
			"search",
			"where",
			"fields",
			"getoffset",
			"getversion",
			"_get",
			"getobject",
			"versioned"
		);
		
		/**
		 * a list of methods can't be called as getters. this is for internal usage.
		 *
		 * @access public
		*/
		public static $notCallableGetters = array(
			"valid", "current", "rewind", "next", "key", "duplicate", "reset", "__construct"
		);
		
		//!Init
		/**
		 * Constructor.
		 *
		 * @access public
		 * @param array $data data to start with
		*/
		public function __construct($data = null)
		{
				parent::__construct();
				
				$this->dataClass = $this->class;
				
				/* --- */
				
				if(isset($data)) { 
					$this->data = ArrayLib::map_key("strtolower", (array)$data);
					$this->original = $this->data;
				}
				
				if(isset(ClassInfo::$class_info[$this->class]["defaults"])) {
					$this->defaults = array_merge((array) $this->defaults, (array) ClassInfo::$class_info[$this->class]["defaults"]);
				} else {
					$this->defaults = array();
				}
				
				if(!isset(self::$server)) {
					self::$server = ArrayLib::map_key($_SERVER, "strtolower");
					self::$_get = ArrayLib::map_key($_GET, "strtolower");
					self::$_post = ArrayLib::map_key($_POST, "strtolower");
				}
		}
		
		//!Setters and Getters
		/**
		 * bool - for IF in template
		 *
		 *@name toBool
		 *@access public
		*/
		public function bool() {
			return (count($this->data) > 0);
		}
		
		/**
		 * returns if this is the first entry or not
		 *@name first
		 *@access public
		*/
		public function first()
		{	
				return ($this->dataSetPosition == 0);
		}
		
		/**
		 * returns if this is the last entry or not
		 *@name last
		 *@access public
		*/
		public function last()
		{	
				return ($this->dataSetPosition + 1 == $this->dataset->count());
		}
		
		/**
		 * returns current position
		*/
		public function position() {
			return $this->dataSetPosition;
		}
		
		/**
		 * returns if this is a highlighted one
		 *
		 *@name highlight
		 *@access public
		*/
		public function highlight() {
			$r = ($this->dataSetPosition + 1) % 2;
			return ($r == 0);
		}
		
		/**
		 * returns if this is a white one
		 *
		 *@name white
		 *@access public
		*/
		public function white() {
			return (!$this->highlight());
		}
		/**
		 * make the functions on top to variables, for example $this.white
		*/
		public function getWhite() {
			return $this->white();
		}
		
		public function getHighlight() {
			return $this->highlight();
		}
		
		public function isFirst() {
			return $this->first();
		}
		
		public function isLast() {
			return $this->last();
		}
		
		public function getPosition() {
			return $this->dataSetPosition;
		}
		
		/**
		 * returns the object of the current logged in user
		 *
		 *@name loggedInUser
		 *@access public
		*/
		public function loggedInUser() {
			return member::$loggedIn;
		}
		
		/**
		 * returns if the record was changed
		 *
		 *@name wasChanged
		 *@access public
		*/
		public function wasChanged() {
			return ($this->changed || $this->data != $this->original);
		}
		
		/**
		 * returns if the record was changed
		 *
		 *@name hasChanged
		 *@access public
		*/
		public function hasChanged() {
			return $this->changed;
		}
		
		/**
		 * sets the value of changed
		 *
		 *@name setChanged
		 *@access public
		 *@param bool
		*/
		public function setChanged($val) {
			if(is_bool($val))
				$this->changed = $val;
		}
		
		//!APIs
		/**
		 * this function returns the current record as an array
		 *@name ToArray
		 *@access public
		 *@param array - extra fields, which are not in database
		*/
		public function ToArray($additional_fields = array())
		{
				if(empty($additional_fields))
						return $this->data;
				else
				{
						$data = $this->data;
						foreach($additional_fields as $field)
						{
								$data[$field] = $this[$field];
						}
						return $data;
				}
		}
				
		/**
		 * to cutomise this data with own data for loops
		 *@name customise
		 *@access public
		 *@param array - data for loops
		 *@param array - replacement-data
		*/
		public function customise($loops = array(), $loops_2 = array())
		{
				if(!empty($loops_2))
					$loops = array_merge($loops, $loops_2);
					
				$loops = Arraylib::map_key($loops, "strtolower");
				$this->customised = array_merge($this->customised, $loops);
				
				return $this;
		}
		
		/**
		 * sets the position of the array
		 *
		 *@name setPosition
		 *@access public
		*/
		public function setPosition($pos) {
			if($pos < count($this->data) && $pos > -1) {
				$this->position = $pos;
				if((count($this->data) / 2) < $pos) {
					end($this->data);
					$i = count($this->data);
					while($i > $pos) {
						prev($this->data);
						$i--;
					}
				} else {
					reset($this->data);
					$i = 0;
					while($i < $pos) {
						next($this->data);
						$i++;
					}
				}
			}
		}
		/**
		 * resets the data
		 *@name reset
		 *@access public
		*/
		public function reset()
		{
				$this->data = array();
				$this->position = 0;
				$this->customised = array();
		}
		/**
		 * some functions for the template
		*/
		
		/**
		 * returns this for <% CONTROL this() %>
		 *@name this
		 *@access public
		*/
		public function this()
		{
			return $this;
		}
		
		/**
		 * renders a view with the data of this DataObject
		 *@name renderWith
		 *@access public
		 *@param string - template
		 *@param array - areas
		 *@param expansion-name of you want to use the expansion-path too
		*/
		public function renderWith($view, $expansion = null)
		{
				return tpl::render($view,array(), $this, $expansion);
		}
		
		
		/**
		 * deprecated method, please use if($object) instead of if($object->_count() > 0)
		 *
		 *@name _count
		 *@access public
		*/
		public function _count() {
			if(isset($this->data["count"]))
				return $this->data["count"];
			
			Core::deprecate(2.0);
			return 1;
		}
		
		/**
		 * gets a cloned object
		 * does the same as duplicate
		 *
		 *@name _clone
		 *@access public
		*/
		public function _clone() {
			return $this->duplicate();
		}
		
		/**
		 * gets a duplicated object
		 *
		 *@name duplicate
		 *@access public
		*/
		public function duplicate() {
			return clone $this;
		}
		
		//!Iterator
		
		/**
		 * iterator
		 * this extends this dataobject to use foreach on it
		 * @link http://php.net/manual/en/class.iterator.php
		*/
		/**
		 * this var is the current position
		 *@name position
		 *@access public
		*/
		private $position = 0;
		/**
		 * rewind $position to 0
		 *@name rewind
		*/
		public function rewind()
		{
			if(is_array($this->data)) {
				reset($this->data);
			}
			$this->position = 0;
		}
		/**
		 * check if data exists
		 *@name valid
		*/
		public function valid()
		{
				return ($this->position < count($this->data));
		}
		/**
		 * gets the key
		 *@name key
		*/
		public function key()
		{
				return key($this->data);
		}
		/**
		 * gets the next one
		 *@name next
		*/
		public function next()
		{
				
				$this->position++;
				next($this->data);
		}
		/**
		 * gets the current value
		 *@name current
		*/
		public function current()
		{
				$data = current($this->data);
				if(is_array($data))
					$data = new ViewAccessAbleData($data);
				
				return $data;
		}
		
		/**
		 * the following code is an attribute-abstraction-layer, which handles attributes dynamically
		 *
		 * it checks whether the attributes exists in the data-attribute, the customised attribute or a getter-method for it exists
		 * it also implements attribute-settings to data-attribute or with an setter-method
		 * it's optimized to work with the Goma-Template-System, so the view can access the data directly
		 *
		 * examples of the usage:
		 * echo $data->name;
		 * $data->name = "Walter";
		*/
		
		//!Attribute-Calling-API: isset
		
		/**
		 * new __cancall
		 *
		 *@access public
		 *@param string - name
		*/
		public function __cancall($name) {
			$name = trim($name);
			$lowername = strtolower($name);
			
			//  methods
			if($this->isOffsetMethod($lowername)) {
				return true;
			} else
			
			if(isset($this->customised[$lowername])) {
				return true;
			} else
			
			// server
			if($this->isServer($name, $lowername)) {
				return true;
			} else
			// data
			
			if(isset($this->data[$lowername])) {
				return true;
			}

			return false;
		}
		
		/**
		 * checks if the offset exists
		 *@name offsetExists
		*/
		public function offsetExists($offset)
		{
			// third call
			return Object::method_exists($this, $offset);
		}
		
		/**
		 * is field
		 *
		 *@name isField
		 *@access public
		*/
		public function isField($name) {
			$name = trim(strtolower($name));
			
			return (isset($this->data[$name]) || isset($this->defaults[$name]));
		}
		
		
		/**
		 * checks if object exists
		 *
		 *@name isOffset
		 *@access public
		*/
		final public function isOffset($offset) {
			return $this->__cancall($offset);
		}
		
		/**
		 * checks if there is a method get + $name or $name
		 *
		 *@name isOffsetMethod
		 *@access public
		 *@param string - name
		*/
		public function isOffsetMethod($name) {
			return (!in_array("get" . $name, self::$notViewableMethods) && Object::method_exists($this->class, "get" . $name));
		}
		
		/**
		 * checks if Server-var exists
		 *
		 *@name isServer
		 *@access public
		 *@param string - offset
		*/
		public function isServer($offset, $lowerOffset)
		{
				
				if(substr($lowerOffset, 0, 8) == "_server_")
				{
					$key = substr($lowerOffset, 8);
					if($key == "redirect" || $key == "redirect_parent" || $key == "real_request_uri") {
						return true;
					}
					
					
					
					return isset(self::$server[$key]);
				} else if(substr($lowerOffset, 0, 6) == "_post_") {
					$key = substr($lowerOffset, 6);
					return isset(self::$_post[$key]);
				} else if (substr($lowerOffset, 0, 5) == "_get_") {
					$key = substr($lowerOffset, 5);
					return isset(self::$_get[$key]);
				} else {
					return false;
				}
		}
		
		//!Attribute-Calling-API: getting
		/**
		 * new get method
		 *
		 *@name __get
		 *@access public
		*/
		public function __get($offset) {
			// third call
			return $this->getOffset($offset);
		}
		
		/**
		 * new call method
		 *
		 *@name __call
		 *@access public
		*/
		public function __call($name, $args) {
			$name = trim($name);
			$lowername = strtolower($name);
			
			return $this->makeObject($lowername, $this->getOffset($name, $args));
		}
		
		/**
		 * gets the offset
		 *@name offsetGet
		*/
		public function offsetGet($offset)
		{
				return $this->__get($offset);
		}
		
		/**
		 * data layer
		 *
		 *@name fieldGet
		 *@access public
		*/
		public function fieldGet($name) {
			$name = trim(strtolower($name));
			if(isset($this->data[$name])) 
				return $this->data[$name];
			else if (isset($this->defaults[$name]))
				return $this->defaults[$name];
			else
				return null;
		}
		
		/**
		 * gets the offset
		 *
		 *@name getOffset
		 *@access public
		 *@param string - name
		 *@param array - args
		*/
		public function getOffset($name, $args = array()) {
			
			if(PROFILE) Profiler::mark("ViewAccessableData::getOffset");
			
			$lowername = strtolower($name);
			
			if($lowername == "baseclass") {
				if(PROFILE) Profiler::unmark("ViewAccessableData::getOffset", "ViewAccessableData::getOffset baseclass ");
				return $this->baseClass();
			}
			
			if(isset($this->customised[$lowername])) {
				$data = $this->customised[$lowername];
			// methods
			} else if(!in_array($lowername, self::$notCallableGetters) && Object::method_exists($this->class, $name)) {
				if(PROFILE) Profiler::unmark("ViewAccessableData::getOffset", "ViewAccessableData::getOffset call " . $name);
				return parent::__call($name, $args);
			} else
			
			// methods
			if($this->isOffsetMethod($lowername)) {
				$data = call_user_func_array(array($this, "get" . $name), $args);
			} else
			
			// data
			
			if(isset($this->data[$lowername])) {
				$data = $this->data[$lowername];
			} else 
			
			if($this->isServer($name, $lowername)) {
				$data = $this->serverGet($name, $lowername);
			}
			
			if(isset($data)) {
				if(is_array($data) && isset($data["casting"], $data["value"])) {
					$data = DBField::getObjectByCasting($data["casting"], $lowername, $data["value"]);
				}
				
				if(is_array($data))
					$data = new ViewAccessableData($data);
				
				unset($lowername, $name);
				if(PROFILE) Profiler::unmark("ViewAccessableData::getOffset");
				
				return $data;
			} else {
				unset($lowername, $name);
				/*if(DEV_MODE) {
					$trace = debug_backtrace();
					if(isset($trace[1]['file']))
						logging('Warning: Call to undefined method ' . $this->class . '::' . $name . ' in '.$trace[1]['file'].' on line '.$trace[1]['line']);
					else {
						logging('Warning: Call to undefined method ' . $this->class . '::' . $name . '');
					}
					
				}*/
				if(PROFILE) Profiler::unmark("ViewAccessableData::getOffset");
				return null;
			}
		}

		/**
		 * gets server-var
		 *
		 *@name ServerGet
		 *@access public
		 *@param string - offset
		*/
		public function ServerGet($offset, $loweroffset)
		{
				
				if(substr($loweroffset, 0, 8) == "_server_")
				{
					
					$key = substr($loweroffset, 8);
					if($key == "redirect") {
						return getredirect();
					} else if($key == "redirect_parent") {
						return getredirect(true);
					}
					
					if($key == "request_uri") {
						if(Core::is_ajax() && isset($_SERVER["HTTP_X_REFERER"]) && $_SERVER["HTTP_X_REFERER"]) {
							return $_SERVER["HTTP_X_REFERER"];
						}
					}
					
					if($key == "real_request_uri") {
						return $_SERVER["REQUEST_URI"];
					}
					
					return self::$server[$key];
				} else if(substr($loweroffset, 0, 6) == "_post_") {
					$key = substr($loweroffset, 6);
					return self::$_post[$key];
				} else if (substr($loweroffset, 0, 5) == "_get_") {
					$key = substr($loweroffset, 5);
					return self::$_get[$key];
				} else {
					return false;
				}
		}
		
		/**
		 * gets a var for template
		 *
		 *@name getTemplateVar
		*/
		public function getTemplateVar($var) {
			if(PROFILE) Profiler::mark("ViewAccessableData::getTemplateVar");
			
			if(strpos($var, ".")) {
				$currentvar = substr($var, 0, strpos($var, "."));
				$remaining = substr($var, strpos($var, ".") + 1);
			} else {
				$currentvar = $var;
				$remaining = "";
			}
			
			$currentvar = trim(strtolower($currentvar));
			$data = $this->getOffset($currentvar, array());
			
			$casting = $this->casting();
			
			if($remaining == "") {
				if(is_object($data)) {
					if(PROFILE) Profiler::unmark("ViewAccessableData::getTemplateVar");
					return $data->forTemplate();
				} else if(isset($casting[$currentvar])) {
					if(PROFILE) Profiler::unmark("ViewAccessableData::getTemplateVar");
					return $this->makeObject($currentvar, $data)->forTemplate();
				} else {
					if(PROFILE) Profiler::unmark("ViewAccessableData::getTemplateVar");
					return $data;
				}
			} else {
				if(is_object($data)) {
					if(PROFILE) Profiler::unmark("ViewAccessableData::getTemplateVar");
					return $data->getTemplateVar($remaining);
				} else if(isset($casting[$currentvar])) {
					if(PROFILE) Profiler::unmark("ViewAccessableData::getTemplateVar");
					return $this->makeObject($currentvar, $data)->getTemplateVar($remaining);
				} else {
					log_error("Not-Recursive-Error: Argument ".$var." wasn't found because it's not recursive.");
					return null;
				}
			}
		}
		
		//!Attribute-Object-Generation
		/**
		 * forces making an object of the given data
		 *
		 *@name makeObject
		 *@access public
		*/
		public function makeObject($name, $data) {
			if(PROFILE) Profiler::mark("ViewAccessableData::makeObject");
			
			// if is already an object
			if(is_object($data)) {
			
				if(PROFILE) Profiler::unmark("ViewAccessableData::makeObject");
				return $data;
			
			// if is array, get as array-object
			} else if(is_array($data)) {
				$object = new ViewAccessAbleData($data);
				if(PROFILE) Profiler::unmark("ViewAccessableData::makeObject");
				return $object;
			
			// default object
			} else if($this->isServer($name, strtolower($name))) {
				$object = DBField::getObjectByCasting("varchar", $name, $data);
				
				if(PROFILE) Profiler::unmark("ViewAccessableData::makeObject");
				return $object;
			} else {
				$casting = $this->casting();
				$caste = isset($casting[$name]) ? $casting[$name] : ClassInfo::getStatic($this->class, "default_casting");
				unset($casting);
				$object = DBField::getObjectByCasting($caste, $name, $data);
				
				if(PROFILE) Profiler::unmark("ViewAccessableData::makeObject");
				return $object;
			}
		}
		
		/**
		 * gets offset as object
		 *@name doObject
		 *@param string - name of offset
		*/
		public function doObject($offset)
		{		
				return $this->__call($offset, array());
		}
		
		//!Attribute-Settings-API
		/**
		 * sets the offset
		 *@name offsetSet
		*/
		public function offsetSet($offset, $value)
		{
				
				return $this->__set($offset, $value);
		}
		
		/**
		 * new set method
		 *
		 *@name __set
		 *@access public
		*/
		public function __set($name, $value) {
			$this->changed = true;
			$name = strtolower(trim($name));
			
			if($this->isSetMethod($name))
			{
					$this->callSetMethod($name, $value);
			} else
			{
					$this->setOffset($name, $value);
			}
		}
		
		/**
		 * new set method
		 *@name setOffset
		 *@access public
		 *@param string - offset
		 *@param mixed - value
		*/
		public function setOffset($var, $value)
		{
				$var = trim(strtolower($var));
				if(is_array($this->data))
				{
						// first unset, so the new value is last value of data stack 
						unset($this->data[$var]);
						$this->data[$var] = $value;
				} else
				{
						$this->data = array($var => $value);
				}
				$this->changed = true;
		}
		
		/**
		 * sets the field
		 *
		 *@name setField
		 *@access public
		*/
		public function setField($name, $value) {
			$this->changed = true;
			$this->setOffset($name, $value);
		}
		
		/**
		 * checks if a method "set" . $offset exists
		 *@name isSetMethod
		 *@access public
		 *@param string - offset
		*/
		public function isSetMethod($offset)
		{
				return (self::method_exists($this, "set" . $offset) && !in_array(strtolower("set" . $offset), self::$notViewableMethods));
		}
		/**
		 * calls a method "set" . $offset
		 *@name callSetMethod
		 *@access public
		 *@param string - offset
		 *@param mixed - value
		*/
		public function callSetMethod($offset, $value)
		{
				$func = "set" . $offset;
				return call_user_func_array(array($this, $func), array($value));
		}
		
		//!Dev
		
		/**
		 * returns casting-values
		 *
		 *@name casting
		 *@access public
		*/
		public function casting() {
			return isset(ClassInfo::$class_info[$this->class]["casting"]) ? ClassInfo::$class_info[$this->class]["casting"] : self::getStatic($this->class, "casting");
		}
		
		/**
		 * returns casting-values
		 *
		 *@name casting
		 *@access public
		*/
		public function defaults() {
			return isset(ClassInfo::$class_info[$this->class]["defaults"]) ? ClassInfo::$class_info[$this->class]["defaults"] : self::getStatic($this->class, "casting");
		}
		
		/**
		 * generates casting
		 *
		 *@name generateCasting
		 *@access public
		*/
		public function generateCasting() {
			$casting = self::getStatic($this->class, "casting");
			foreach($this->LocalcallExtending("casting") as $_casting) {
				$casting = array_merge($casting, $_casting);
				unset($_casting);
			}
			
			$parent = get_parent_class($this);
			if(strtolower($parent) != "viewaccessabledata" && !ClassInfo::isAbstract($parent)) {
				$casting = array_merge(Object::instance($parent)->generateCasting(), $casting);
			}
			
			$casting = ArrayLib::map_key("strtolower", $casting);
			return $casting;
		}
		
		/**
		 * defaults
		 *
		 *@name defaults
		 *@access public
		*/
		public function generateDefaults() {		
			if(self::hasStatic($this->class, "default")) {
				$defaults = self::getStatic($this->class, "default");
			} else {
				$defaults = array();
			}
			
			// get parents
			$parent = get_parent_class($this);
			if(strtolower($parent) != "viewaccessabledata" && !ClassInfo::isAbstract($parent)) {
				$defaults = array_merge(Object::instance($parent)->generateDefaults(), $defaults);
			}
			
			foreach($this->LocalcallExtending("defaults") as $defaultsext) {
				$defaults = array_merge($defaults, $defaultsext);
				unset($defaultsext);
			}
			
			// free memory
			unset($parent);
			$defaults = ArrayLib::map_key($defaults, "strtolower");
			return $defaults;
		}
		
		/**
		 * unsets a offset
		 * in this object it do nothing
		 *@name offsetUnset
		*/
		public function offsetUnset($offset)
		{
				// do nothing
		}
		
		
}