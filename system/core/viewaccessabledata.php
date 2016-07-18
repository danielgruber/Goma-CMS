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
 * @version		2.3.4
 */
class ViewAccessableData extends gObject implements Iterator, ArrayAccess {

	const ID = "ViewAccessableData";

	/**
	 * default datatype for casting.
	 *
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
	 * @var array
	 */
	protected $data = array();

	/**
	 * contains the original data at object-generation.
	 */
	protected $original = array();

	/**
	 * customised data for template via ViewAccessableData::customise.
	 *
	 * @var array
	 */
	public $customised = array();

	/**
	 * indicates whether the data was changes or not.
	 */
	protected $changed = false;

	/**
	 * default values for specific fields.
	 */
	protected $defaults;

	/**
	 * server-vars. This is for internal usage.
	 */
	private static $server;

	/**
	 * get-vars. This is for internal usage.
	 */
	private static $_get;

	/**
	 * post-vars. This is for internal usage.
	 */
	private static $_post;

	/**
	 * a list of not allowed methods. This is for internal usage.
	 *
	 * @var array
	 */
	public static $notViewableMethods = array("getdata", "get_versioned", "getform", "geteditform", "getwholedata", "set_many_many", "get_has_one", "get_many", "get", "setfield", "setwholedata", "write", "writerecord", "__construct", "method_exists", "callmethodbyrecord", "getmanymany", "gethasmany", "search", "where", "fields", "getoffset", "getversion", "_get", "getobject", "versioned");

	/**
	 * a list of methods can't be called as getters. this is for internal usage.
	 */
	public static $notCallableGetters = array("valid", "current", "rewind", "next", "key", "duplicate", "reset", "__construct");

	//!Init
	/**
	 * Constructor.
	 * @param array|null $data
	 */
	public function __construct($data = null) {
		parent::__construct();

		/* --- */

		if(isset($data)) {
			$this->data = ArrayLib::map_key("strtolower", (array)$data);
			$this->original = $this->data;
		}

        $this->defaults = $this->defaults();

		if(!isset(self::$server)) {
			self::$server = ArrayLib::map_key("strtolower", $_SERVER);
			self::$_get = ArrayLib::map_key("strtolower", $_GET);
			self::$_post = ArrayLib::map_key("strtolower", $_POST);
		}
	}

	/**
	 * @return string
	 */
	public function DataClass() {
		return $this->classname;
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
     * @param array - extra fields, which are not in database
     * @return array
     */
	public function &ToArray($additional_fields = array()) {
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
	public function ToRESTArray($additional_fields = array()) {
		return $this->ToArray($additional_fields);
	}

    /**
     * to customise this data with own special data which is not part of the model, but needed in view.
     *
     * @param array - data for loops
     * @return $this
     */
	public function customise($loops = array()) {
		$loops = Arraylib::map_key("strtolower", $loops);
		$this->customised = array_merge($this->customised, $loops);

		return $this;
	}

    /**
     * returns a customised object.
     *
     * @name    customisedObject
     * @param    array - data
     * @return ViewAccessableData
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
     * @param string - template
     * @param array - areas
     * @param expansion -name of you want to use the expansion-path too
     * @return string
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
	 * @return ViewAccessableData
	 */
	public function duplicate() {
		$duplicate = clone $this;
		$this->callExtending("duplicate");
		return $duplicate;
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
	 */
	public function rewind() {
		if(is_array($this->data)) {
			reset($this->data);
		}
		$this->position = 0;
	}

	/**
	 * check if data exists
	 */
	public function valid() {
		return ($this->position < count($this->data));
	}

	/**
	 * gets the key
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * gets the next one
	 */
	public function next() {

		$this->position++;
		next($this->data);
	}

    /**
     * gets the current value
     *
     * @return mixed|ViewAccessableData
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
     * returns if virtual property or method exists. called by Object::method_exists.
     *
     * @return bool
     */
	public function __canCall($name) {
		$name = trim($name);
		$lowername = strtolower($name);

		//  methods
		if($this->isOffsetMethod($lowername)) {
			return true;
		} else if(isset($this->customised[$lowername])) {
			return true;
		} else

		// server
		if($this->isServerMethod($lowername)) {
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
     */
	public function offsetExists($offset) {
		if(is_string($offset)) {
			return $this->__cancall($offset) || gObject::method_exists($this->classname, $offset);
		}

		return false;
	}

    /**
     * is field
     * @param String $name
     * @return bool
     */
	public function isField($name) {
		$name = trim(strtolower($name));

		return (isset($this->data[$name]) || isset($this->defaults[$name]));
	}

	/**
	 * checks if object exists
	 *
	 * @param String $offset
	 * @return bool
	 */
	final public function isOffset($offset) {
		return $this->__cancall($offset) || gObject::method_exists($this->classname, $offset);
	}

    /**
     * checks if there is a method get + $name or $name
     *
     * @param string - name
     * @return bool
     */
	protected function isOffsetMethod($name) {
		return (!in_array("get" . $name, self::$notViewableMethods) && gObject::method_exists($this->classname, "get" . $name));
	}


    /**
     * calls an offset method.
     *
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    protected function callOffsetMethod($methodName, $args) {
        $getterName = "get" . $methodName;
        if(method_exists($this, $getterName)) {
            return call_user_func_array(array($this, $getterName), $args);
        } else {
            return parent::__call($getterName, $args);
        }
    }

    /**
     * checks if Server-var exists
     *
     * @param string offset in lowercase
     * @return bool
     */
	protected function isServerMethod($lowerOffset) {

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

    public function __isset($offset) {
        return $this->__cancall($offset) || gObject::method_exists($this->classname, $offset);
    }

	/**
	 * new call method
	 *
	 */
	public function __call($methodName, $args) {

		if(gObject::method_exists($this->classname, $methodName)) {
			return parent::__call($methodName, $args);
		}

		return $this->doObject($methodName, $args);
	}

    /**
     * gets a given offset.
     *
     * @param string $offset offset
     * @return mixed
     */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

    /**
     * data layer
     * @param $name
     * @return string
     */
	public function fieldGet($name) {
		$name = trim(strtolower($name));
		if(isset($this->data[$name])) {
            return $this->data[$name];
        }

        if(isset($this->defaults[$name])) {
            return $this->defaults[$name];
        }

        return null;
	}

    /**
     * gets the offset
     *
     * @param    string $name
     * @param    array $args
     * @return   string
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

        if($this->shouldCast($name)) {
            $data = $this->executeCasting($name, $data);
        }

		return $data;
	}

    /**
     * if try-casting is set to true it will try casting data, else it returns it blank.
     *
     * @name    executeCasting
     * @param    boolean - shouldcast
     * @param    mixed data
     * @return  DBField|ViewAccessableData
     */
	protected function executeCasting($name, $data) {
		if(is_array($data) && isset($data["casting"], $data["value"])) {
			return DBField::getObjectByCasting($data["casting"], $name, $data["value"]);
		}

		if(is_array($data)) {
			return new ViewAccessableData($data);
		}

		return $data;
	}

    /**
     * give a boolean if we should just return the data without trying to cast it.
     *
     * @name    tryCasting
     * @param    string $name
     * @return bool
     */
	protected function shouldCast($name) {
		$lowername = strtolower($name);
		if($lowername == "baseclass") {
			return false;
		}

		if(isset($this->customised[$lowername])) {
			return true;
		}

		if(!in_array($lowername, self::$notCallableGetters) && gObject::method_exists($this->classname, $name)) {
			return false;
		}

		return true;
	}

    /**
     * gets data for offset.
     *
     * @param string $name
     * @param array $args
     * @return  mixed
     */
	protected function getOffsetData($name, $args) {
		$lowername = strtolower($name);

		if($lowername == "baseclass") {
			return $this->baseClass();
		}

		if(isset($this->customised[$lowername])) {
			return $this->customised[$lowername];
		}

		if(!in_array($lowername, self::$notCallableGetters) && gObject::method_exists($this->classname, $name)) {
			return parent::__call($name, $args);
		}

		// methods
		if($this->isOffsetMethod($lowername)) {
			return $this->callOffsetMethod($lowername, $args);
		}

		if(isset($this->data[$lowername])) {
			return $this->data[$lowername];
		}

		if($this->isServerMethod($lowername)) {
			return $this->serverGet($name, $lowername);
		}

		return null;
	}

    /**
     * gets server-var
     *
     * @param string - offset
     * @return bool|string
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
     * @name getTemplateVar
     * @return string
     */
	public function getTemplateVar($var) {

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

		if(is_object($data) && $remaining != "" && method_exists($data, "getTemplateVar")) {
			return $data->getTemplateVar($remaining);
		} else if(is_object($data) && gObject::method_exists($data, "forTemplate")) {
			return $data->forTemplate();
		} else if(isset($casting[$currentvar]) && !is_object($data)) {
			$object = $this->makeObject($currentvar, $data);
			return $remaining == "" ? $object->forTemplate() : $object->getTemplateVar($remaining);
		} else {
			return $data;
		}
	}

	//!Attribute-Object-Generation
    /**
     * generates an object from given data.
     *
     * @name    makeObject
     * @param    string key or name of object
     * @param    mixed data
     * @return   gObject
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
		} else if($this->isServerMethod(strtolower($name))) {
			$object = DBField::getObjectByCasting("varchar", $name, $data);
			return $object;
		} else {

			// check for casting or use default-casting.
            $cast = $this->getCast($name);

			$object = DBField::getObjectByCasting($cast, $name, $data);

			return $object;
		}
	}

    /**
     * returns casting for given field.
     *
     * @param string $field
     * @return string
     */
    protected function getCast($field) {
        $casting = $this->casting();

        $field = trim(strtolower($field));
        return isset($casting[$field]) ? $casting[$field] : StaticsManager::getStatic($this->classname, "default_casting");
    }

    /**
     * gets offset as object
     * @param string $name of offset
	 * @param array $args
     * @return gObject
     */
	public function doObject($name, $args = array()) {
		$name = trim($name);

		return $this->makeObject($name, $this->getOffset($name, $args));
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
	 * @param string $offset
	 * @return bool
	 */
	public function isSetMethod($offset) {
		return (self::method_exists($this, "set" . $offset) && !in_array(strtolower("set" . $offset), self::$notViewableMethods));
	}

	/**
	 * calls a method "set" . $offset
	 * @param string $offset
	 * @param mixed $value
	 * @return mixed
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
		$casting = isset(ClassInfo::$class_info[$this->classname]["casting"]) ? ClassInfo::$class_info[$this->classname]["casting"] : StaticsManager::getStatic($this->classname, "casting");

		return array_merge($casting, $this->extendedCasting);
	}

	/**
	 * returns casting-values
	 *
	 */
	public function defaults() {
		return isset(ClassInfo::$class_info[$this->classname]["defaults"]) ? ClassInfo::$class_info[$this->classname]["defaults"] : (array)StaticsManager::getStatic($this->classname, "default");
	}

	/**
	 * unsets a offset
	 * in this object it do nothing
	 * @param string $offset
	 */
	public function offsetUnset($offset) {
		if(isset($this->data[$offset])) {
			unset($this->data[$offset]);
		}
	}

	/**
	 * @param $name
	 */
	public function __unset($name)
	{
		return $this->offsetUnset($name);
	}

	/**
	 * returns a property of a given Item in the List.
	 *
	 * @param  array|gObject $item item
	 * @param  string $prop property
	 * @return null
	 */
	static function getItemProp($item, $prop) {
		if(is_array($item))
			return isset($item[$prop]) ? $item[$prop] : null;

		if(is_object($item)) {
			if(is_a($item, "ArrayAccess") && isset($item[$prop])) {
				return $item[$prop];
			}

			if(is_string($prop)) {
				return $item->{$prop};
			}
		}

		return property_exists($item, $prop) ? $item->$prop : null;
	}

	/**
	 * @param array $data
	 * @return ViewAccessableData
	 */
	public function createNew($data = array()) {
		if(isset($data["class_name"])) {
			return new $data["class_name"]($data);
		}

		return new $this($data);
	}

	/**
	 * get an array of pages by given pagecount
	 *
	 * @param int $pagecount
	 * @param int $currentpage
	 * @return array
	 */
	protected static function renderPages($pagecount, $currentpage = 1) {
		if($pagecount < 2) {
			return array(1 => array(
				"page" 	=> 1,
				"black"	=> true
			));
		} else {
			$data = array();
			if($pagecount < 8) {
				for($i = 1; $i <= $pagecount; $i++) {
					$data[$i] = array(
						"page" 	=> ($i),
						"black"	=> ($i == $currentpage)
					);
				}
			} else {

				$lastDots = false;
				for($i = 1; $i <= $pagecount; $i++) {
					if($i < 3 || ($i > $currentpage - 3 && $i < $currentpage + 3) || $i > $pagecount - 3) {
						$data[$i] = array(
							"page" 	=> ($i),
							"black"	=> ($i == $currentpage)
						);
						$lastDots = false;
					} else if(!$lastDots && (($i > 2 && $i < ($currentpage - 2)) || ($i < ($pagecount - 2) && $i > ($currentpage + 2)))) {
						$data[$i] = array(
							"page" 	=> "...",
							"black" => true
						);
						$lastDots = true;
					}
				}
			}
			return $data;
		}
	}

	public function raw() {
		return $this->data;
	}
}


/**
 * This class represents the Extension system.
 *
 * @package		Goma\System\Core
 * @version		1.0
 */
abstract class Extension extends gObject implements ExtensionModel {

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
     * @name setOwner
     * @return $this
     */
	public function setOwner($object) {
		if(!is_object($object) && !is_null($object)) {
            throw new InvalidArgumentException('Object is not an object');
		}

		$this->owner = $object;

		return $this;
	}

	/**
	 * gets the owner of class
	 *
	 * @return gObject
	 */
	public function getOwner() {
		return $this->owner;
	}
}

