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
 * @version		2.3.1
 */
class ViewAccessableData extends Object implements Iterator, ArrayAccess {
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
	 * extended casting.
	*/
	public $extendedCasting = array();

	/**
	 * data is stored in this var.
	 *
	 * @acccess public
	 * @var array
	 */
	protected $data = array();

	/**
	 * contains the original data at object-generation.
	 *
	 * @access public
	 */
	protected $original = array();

	/**
	 * customised data for template via ViewAccessableData::customise.
	 *
	 * @access public
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
	protected $changed = false;

	/**
	 * dataset in which this Object is.
	 *
	 * @access public
	 */
	public $dataset;

	/**
	 * dataClass contains the class for which this data is, if it's not the same as
	 * the class, which it contains.
	 *
	 * @access public
	 */
	public $dataClass;

	/**
	 * default values for specfic fields.
	 *
	 * @access public
	 */
	protected $defaults;

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
	public static $notViewableMethods = array("getdata", "getform", "geteditform", "getwholedata", "set_many_many", "get_has_one", "get_many", "get", "setfield", "setwholedata", "write", "writerecord", "__construct", "method_exists", "callmethodbyrecord", "getmanymany", "gethasmany", "search", "where", "fields", "getoffset", "getversion", "_get", "getobject", "versioned");

	/**
	 * a list of methods can't be called as getters. this is for internal usage.
	 *
	 * @access public
	 */
	public static $notCallableGetters = array("valid", "current", "rewind", "next", "key", "duplicate", "reset", "__construct");

	//!Init
	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $data data to start with
	 */
	public function __construct($data = null) {
		parent::__construct();

		$this->dataClass = $this->classname;

		/* --- */

		if(isset($data)) {
			$this->data = ArrayLib::map_key("strtolower", (array)$data);
			$this->original = $this->data;
		}

        $this->defaults = $this->defaults();

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
	 */
	public function bool() {
		return (count($this->data) > 0);
	}

	/**
	 * returns if this is the first entry or not
	 */
	public function first() {
		return ($this->dataSetPosition === 0);
	}

	/**
	 * returns if this is the last entry or not
	 */
	public function last() {

		if(!isset($this->dataset)) {
			return false;
		}

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
	 */
	public function highlight() {
		$r = ($this->dataSetPosition + 1) % 2;
		return ($r == 0);
	}

	/**
	 * returns if this is a white one
	 *
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
	 */
	public function loggedInUser() {
		return member::$loggedIn;
	}

	/**
	 * returns if the record was changed
	 *
	 */
	public function wasChanged() {
		return ($this->changed || $this->data != $this->original);
	}

	/**
	 * returns if the record was changed
	 *
	 */
	public function hasChanged() {
		return $this->changed;
	}

	/**
	 * sets the value of changed
	 *
	 *@param bool
	 */
	public function setChanged($val) {
		if(is_bool($val))
			$this->changed = $val;
	}

	//!APIs
	/**
	 * this function returns the current record as an array
	 *@param array - extra fields, which are not in database
	 */
	public function ToArray($additional_fields = array()) {
		if(empty($additional_fields))
			return $this->data;
		else {
			$data = $this->data;
			foreach($additional_fields as $field) {
				$data[$field] = $this[$field];
			}
			return $data;
		}
	}
	
	/**
	 * to array if we need data for REST-API.
	*/
	public function ToRESTArray($addtional_fields = array()) {
		return $this->ToArray($additional_fields);
	}

	/**
	 * to customise this data with own special data which is not part of the model, but needed in view.
	 *
	 *@param array - data for loops
	 *@param array - replacement-data
	 */
	public function customise($loops = array(), $loops_2 = array()) {
		if(!empty($loops_2))
			$loops = array_merge($loops, $loops_2);

		$loops = Arraylib::map_key($loops, "strtolower");
		$this->customised = array_merge($this->customised, $loops);

		return $this;
	}

	/**
	 * returns a customised object.
	 *
	 * @name 	customisedObject
	 * @param 	array - data
	*/
	public function customisedObject($data) {
		$new = clone $this;
		$new->customised = array_merge($new->customised, $data);
		return $new;
	}

	/**
	 * returns customisation.
	*/
	public function getCustomisation() {
		return $this->customised;
	}

	/**
	 * removes customisation on this object.
	*/
	public function removeCustomisation() {
		$this->customised = array();
		return $this;
	}

	/**
	 * removes customisation on an clone of this object and returns it.
	*/
	public function getObjectWithoutCustomisation() {
		$data = clone $this;
		$data->customised = array();
		return $data;
	}

	/**
	 * sets the position of the array
	 *
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
	 */
	public function reset() {
		$this->data = array();
		$this->position = 0;
		$this->customised = array();
	}

	/**
	 * some functions for the template
	 */

	/**
	 * returns this for <% CONTROL this() %>
	 */
	public function this() {
		return $this;
	}

	/**
	 * renders a view with the data of this DataObject
	 *@param string - template
	 *@param array - areas
	 *@param expansion-name of you want to use the expansion-path too
	 */
	public function renderWith($view, $expansion = null) {
		return tpl::render($view, array(), $this, $expansion);
	}

	/**
	 * deprecated method, please use if($object) instead of if($object->_count() > 0)
	 *
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
	 */
	public function _clone() {
		return $this->duplicate();
	}

	/**
	 * gets a duplicated object
	 *
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
	 */
	private $position = 0;
	/**
	 * rewind $position to 0
	 *@name rewind
	 */
	public function rewind() {
		if(is_array($this->data)) {
			reset($this->data);
		}
		$this->position = 0;
	}

	/**
	 * check if data exists
	 *@name valid
	 */
	public function valid() {
		return ($this->position < count($this->data));
	}

	/**
	 * gets the key
	 *@name key
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * gets the next one
	 *@name next
	 */
	public function next() {

		$this->position++;
		next($this->data);
	}

	/**
	 * gets the current value
	 *@name current
	 */
	public function current() {
		$data = current($this->data);
		if(is_array($data))
			$data = new ViewAccessAbleData($data);

		return $data;
	}

	/**
	 * the following code is an attribute-abstraction-layer, which handles attributes
	 * dynamically
	 *
	 * it checks whether the attributes exists in the data-attribute, the customised
	 * attribute or a getter-method for it exists
	 * it also implements attribute-settings to data-attribute or with an
	 * setter-method
	 * it's optimized to work with the Goma-Template-System, so the view can access
	 * the data directly
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
		} else if(isset($this->customised[$lowername])) {
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
	public function offsetExists($offset) {
		// third call
		return Object::method_exists($this, $offset);
	}

	/**
	 * is field
	 *
	 */
	public function isField($name) {
		$name = trim(strtolower($name));

		return (isset($this->data[$name]) || isset($this->defaults[$name]));
	}

	/**
	 * checks if object exists
	 *
	 */
	final public function isOffset($offset) {
		return $this->__cancall($offset);
	}

	/**
	 * checks if there is a method get + $name or $name
	 *
	 *@param string - name
	 */
	public function isOffsetMethod($name) {
		return (!in_array("get" . $name, self::$notViewableMethods) && Object::method_exists($this->classname, "get" . $name));
	}

	/**
	 * checks if Server-var exists
	 *
	 *@param string - offset
	 */
	public function isServer($offset, $lowerOffset) {

		if(substr($lowerOffset, 0, 8) == "_server_") {
			$key = substr($lowerOffset, 8);
			if($key == "redirect" || $key == "redirect_parent" || $key == "real_request_uri") {
				return true;
			}

			return isset(self::$server[$key]);
		} else if(substr($lowerOffset, 0, 6) == "_post_") {
			$key = substr($lowerOffset, 6);
			return isset(self::$_post[$key]);
		} else if(substr($lowerOffset, 0, 5) == "_get_") {
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
	 */
	public function __get($offset) {
		// third call
		return $this->getOffset($offset);
	}

	/**
	 * new call method
	 *
	 */
	public function __call($name, $args) {
		$name = trim($name);
		$lowername = strtolower($name);

		return $this->makeObject($lowername, $this->getOffset($name, $args));
	}

	/**
	 * gets a given offset.
	 *
	 *@param string $offset offset
	 */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	/**
	 * data layer
	 *
	 */
	public function fieldGet($name) {
		$name = trim(strtolower($name));
		if(isset($this->data[$name]))
			return $this->data[$name];
		else if(isset($this->defaults[$name]))
			return $this->defaults[$name];
		else
			return null;
	}

	/**
	 * gets the offset
	 *
	 * @param 	string - name
	 * @param 	array - args
	 */
	public function getOffset($name, $args = array()) {

		if(PROFILE)
			Profiler::mark("ViewAccessableData::getOffset");

		$name = trim($name);

		$data = $this->getOffsetData($name, $args);
		if($data === null) {
			if(PROFILE)
				Profiler::unmark("ViewAccessableData::getOffset");

			return null;
		}

		if(PROFILE)
			Profiler::unmark("ViewAccessableData::getOffset");

		$return = $this->executeCasting($this->tryCasting($name), $data);

		return $return;
	}

	/**
	 * if try-casting is set to true it will try casting data, else it returns it blank.
	 *
	 * @name 	executeCasting
	 * @param 	boolean - shouldcast
	 * @param 	mixed data
	*/
	protected function executeCasting($shouldCast, $data) {
		if(!$shouldCast) {
			return $data;
		}

		if(is_array($data) && isset($data["casting"], $data["value"])) {
			return DBField::getObjectByCasting($data["casting"], $lowername, $data["value"]);
		}

		if(is_array($data)) {
			return new ViewAccessableData($data);
		}

		return $data;
	}

	/**
	 * give a boolean if we should just return the data without trying to cast it.
	 *
	 * @name 	tryCasting
	 * @param 	string $name
	*/
	protected function tryCasting($name) {
		$lowername = strtolower($name);
		if($lowername == "baseclass") {
			return false;
		}

		if(isset($this->customised[$lowername])) {
			return true;
		} 

		if(!in_array($lowername, self::$notCallableGetters) && Object::method_exists($this->classname, $name)) {
			return false;
		}

		return true;
	}

	/**
	 * gets data for offset.
	 *
	 * @name 	getOffsetData
	 * @param 	name
	 * @param 	args
	*/
	protected function getOffsetData($name, $args) {
		$lowername = strtolower($name);

		if($lowername == "baseclass") {
			return $this->baseClass();
		}

		if(isset($this->customised[$lowername])) {
			return $this->customised[$lowername];
			// methods
		} 

		if(!in_array($lowername, self::$notCallableGetters) && Object::method_exists($this->classname, $name)) {
			return parent::__call($name, $args);
		}

		// methods
		if($this->isOffsetMethod($lowername)) {
			return call_user_func_array(array($this, "get" . $name), $args);
		}

		if(isset($this->data[$lowername])) {
			return $this->data[$lowername];
		} 

		if($this->isServer($name, $lowername)) {
			return $this->serverGet($name, $lowername);
		}

		return null;
	}

	/**
	 * gets server-var
	 *
	 *@param string - offset
	 */
	public function ServerGet($offset, $loweroffset) {

		if(substr($loweroffset, 0, 8) == "_server_") {

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
		} else if(substr($loweroffset, 0, 5) == "_get_") {
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
		if(PROFILE)
			Profiler::mark("ViewAccessableData::getTemplateVar");

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
				if(PROFILE)
					Profiler::unmark("ViewAccessableData::getTemplateVar");
				return $data->forTemplate();
			} else if(isset($casting[$currentvar])) {
				if(PROFILE)
					Profiler::unmark("ViewAccessableData::getTemplateVar");
				return $this->makeObject($currentvar, $data)->forTemplate();
			} else {
				if(PROFILE)
					Profiler::unmark("ViewAccessableData::getTemplateVar");
				return $data;
			}
		} else {
			if(is_object($data)) {
				if(PROFILE)
					Profiler::unmark("ViewAccessableData::getTemplateVar");
				return $data->getTemplateVar($remaining);
			} else if(isset($casting[$currentvar])) {
				if(PROFILE)
					Profiler::unmark("ViewAccessableData::getTemplateVar");
				return $this->makeObject($currentvar, $data)->getTemplateVar($remaining);
			} else {
				log_error("Not-Recursive-Error: Argument " . $var . " wasn't found because it's not recursive.");
				return null;
			}
		}
	}

	//!Attribute-Object-Generation
	/**
	 * generates an object from given data.
	 *
	 * @name 	makeObject
	 * @param 	string key or name of object
	 * @param 	mixed data
	 */
	public function makeObject($name, $data) {
		if(PROFILE)
			Profiler::mark("ViewAccessableData::makeObject");

		$object = $this->makeObjectHelper($name, $data);

		if(PROFILE)
			Profiler::unmark("ViewAccessableData::makeObject");

		return $object;
	}

	protected function makeObjectHelper($name, $data) {

		// if is already an object
		if(is_object($data)) {
			return $data;


		// if is array, get as array-object
		} else if(is_array($data)) {
			$object = new ViewAccessAbleData($data);

			return $object;

		// default object for server-vars
		} else if($this->isServer($name, strtolower($name))) {
			$object = DBField::getObjectByCasting("varchar", $name, $data);
			return $object;
		} else {

			// check for casting or use default-casting.
			$casting = $this->casting();
			$caste = isset($casting[$name]) ? $casting[$name] : ClassInfo::getStatic($this->classname, "default_casting");
			unset($casting);
			$object = DBField::getObjectByCasting($caste, $name, $data);

			return $object;
		}
	}

	/**
	 * gets offset as object
	 *@name doObject
	 *@param string - name of offset
	 */
	public function doObject($offset) {
		return $this->__call($offset, array());
	}

	//!Attribute-Settings-API
	/**
	 * sets the offset
	 *@name offsetSet
	 */
	public function offsetSet($offset, $value) {
	
		return $this->__set($offset, $value);
	}

	/**
	 * new set method
	 *
	 */
	public function __set($name, $value) {
		$this->changed = true;
		$name = strtolower(trim($name));

		if($this->isSetMethod($name)) {
			$this->callSetMethod($name, $value);
		} else {
			$this->setOffset($name, $value);
		}
	}

	/**
	 * sets a value of a given field.
	 *
	 * @param 	string $var offset
	 * @param 	mixed $value value
	 */
	public function setOffset($var, $value) {
		if($var === null) {
			if(is_array($this->data)) {
				array_push($this->data, $value);
			} else {
				$this->data = array($value);
			}
		} else {
			$var = trim(strtolower($var));
			
			if($value instanceof DBField) {
				$value = $value->raw();
				$this->extendedCasting[$var] = $value->classname;
			}
			
			if(is_array($this->data)) {
				// first unset, so the new value is last value of data stack
				unset($this->data[$var]);
				if(isset($this->data[$var]) && $this->data[$var] == $value) {
					return;
				}
				
				$this->data[$var] = $value;
			} else {
				$this->data = array($var => $value);
			}
		}
		
		$this->changed = true;
	}

    /**
     * gets default value for key.
     *
     * @param string field
     * @return string|null
     */
    protected function getDefaultValue($field) {
        $defaults = $this->defaults();


        if(isset($defaults[$field])) {
            return $defaults[$field];
        }

        if(isset($defaults[strtolower($field)])) {
            return $defaults[strtolower($field)];
        }
    }


    /**
	 * sets the value of a given field.
	 */
	public function setField($name, $value) {
		$this->setOffset($name, $value);
	}

	/**
	 * checks if a method "set" . $offset exists
	 *@param string - offset
	 */
	public function isSetMethod($offset) {
		return (self::method_exists($this, "set" . $offset) && !in_array(strtolower("set" . $offset), self::$notViewableMethods));
	}

	/**
	 * calls a method "set" . $offset
	 *@param string - offset
	 *@param mixed - value
	 */
	public function callSetMethod($offset, $value) {
		$func = "set" . $offset;
		return call_user_func_array(array($this, $func), array($value));
	}

	//!Dev

	/**
	 * returns casting-values
	 *
	 */
	public function casting() {
		$casting = isset(ClassInfo::$class_info[$this->classname]["casting"]) ? ClassInfo::$class_info[$this->classname]["casting"] : self::getStatic($this->classname, "casting");
		
		return array_merge($casting, $this->extendedCasting);
	}

	/**
	 * returns casting-values
	 *
	 */
	public function defaults() {
		return isset(ClassInfo::$class_info[$this->classname]["defaults"]) ? ClassInfo::$class_info[$this->classname]["defaults"] : (array) self::getStatic($this->classname, "default");
	}

	/**
	 * generates casting
	 *
	 */
	public function generateCasting() {
		$casting = self::getStatic($this->classname, "casting");
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
	 */
	public function generateDefaults() {
		if(self::hasStatic($this->classname, "default")) {
			$defaults = self::getStatic($this->classname, "default");
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
	public function offsetUnset($offset) {
		// do nothing
	}

}


/**
 * This class represents the Extension system.
 *
 * @package		Goma\System\Core
 * @version		1.0
 */
abstract class Extension extends ViewAccessAbleData implements ExtensionModel {

	/**
	 * extra_methods
	 */
	public static $extra_methods = array();
	/**
	 * the owner-class
	 *@name owner
	 */
	protected $owner;
	/**
	 * sets the owner-class
	 *@name setOwner
	 */
	public function setOwner($object) {
		if(!is_object($object)) {
			throwError(20, 'PHP-Error', '$object isn\'t a object in ' . __FILE__ . ' on line ' . __LINE__ . '');
		}
		if(class_exists($object->classname)) {
			$this->owner = $object;
		} else {
			throwError(20, 'PHP-Error', 'Class ' . $class . ' doesn\'t exist in context.');
		}

		return $this;
	}

	/**
	 * gets the owner of class
	 *@name getOwner
	 */
	public function getOwner() {
		return $this->owner;
	}

}

