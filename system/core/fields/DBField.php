<?php defined("IN_GOMA") OR die();

/**
 * Every value of an field can used as object if you call doObject($offset)
 * This Object has some very cool methods to convert the field
 */
class DBField extends gObject implements IDataBaseField
{
    /**
     * this var contains the value
     *@name value
     *@var mixed
     */
    protected $value;

    /**
     * this field contains the field-name of this object
     *@name name
     *@access protected
     */
    protected $name;

    /**
     * args
     *
     *@name args
     *@access public
     */
    public $args = array();

    /**
     * cache for casting
     *
     *@name castingCache
     *@access private
     */
    private static $castingCache = array();

    /**
     *@name __construct
     *@access public
     *@param mixed - value
     */
    public function __construct($name, $value, $args = array())
    {
        $this->name = $name;
        $this->value = $value;
        $this->args = $args;
        parent::__construct();
    }

    /**
     * sets the value
     *@name setvalue
     *@access public
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    /**
     * gets the value
     *@name getValue
     *@access public
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * sets the name
     *@name setName
     *@access public
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * gets the anme
     *@name getName
     *@access public
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *@name raw
     *@access public
     */
    public function raw()
    {
        return $this->value;
    }

    /**
     * get it as text
     *@name text
     *@access public
     */
    public function text()
    {
        return convert::raw2xml($this->value);
    }

    /**
     * get it as text and correct  linebreaks
     *@name textLines
     *@access public
     */
    public function textLines()
    {
        return convert::raw2xmlLines($this->value);
    }

    /**
     * get this as url
     *@name url
     *@access public
     */
    public function url()
    {
        return convert::raw2text(urlencode($this->value));
    }
    /**
     * for js
     *@name JS
     *@access public
     */
    public function js()
    {
        return str_replace(array("\n","\t","\r","\"","'"), array('\n', '\t', '\r', '\\"', '\\\''), $this->value);
    }
    /**
     * converts string to uppercase
     *@name uppercase
     *@access public
     */
    public function UpperCase()
    {
        return strtoupper($this->value);
    }

    /**
     * converts string to lowerase
     * @name uppercase
     * @access public
     * @return string
     */
    public function LowerCase()
    {
        return strtolower($this->value);
    }


    /**
     * Layer for form-fields
     */

    /**
     * generates the default form-field for this field
     * @name formfield
     * @access public
     * @param string - title
     * @return FormField
     */
    public function formfield($title = null)
    {
        $field = new TextField($this->name, $title, $this->value);

        return $field;
    }

    /**
     * search-field for searching
     * @name searchfield
     * @access public
     * @param string - title
     * @return FormField
     */
    public function searchfield($title = null)
    {
        return $this->formfield($title);
    }

    /**
     * this function uses more than one convert-method
     *@name convertMulti
     *@access public
     *@param array - methods
     */
    public function convertMulti($arr)
    {
        $new = clone $this;
        foreach($arr as $method)
        {
            if(gObject::method_exists($new, $method))
            {
                $new->setValue($new->$method());
            }
        }
        return $new->getValue();
    }

    /**
     * gets the field-type
     *
     * @name getFieldType
     * @access public
     * @return string
     */
    static public function getFieldType($args = array()) {
        return "";
    }

    /**
     * toString-Method
     * @name __toString
     * @access public
     * @return string
     */
    public function __toString()
    {
        return $this->forTemplate();
    }

    /**
     * gets Data Converted for Template
     *
     * @name forTemplate
     * @return string
     */
    public function forTemplate() {
        return (string) $this->value;
    }

    /**
     * bool - for IF in template
     *
     * @name toBool
     * @access public
     * @return bool
     */
    public function toBool() {
        return (bool) $this->value;
    }

    /**
     * returns true when value is string and starts with given value.
     *
     * @param string $compare
     * @return bool
     */
    public function startsWith($compare) {
        $str = (string) $this->value;

        if(substr($str, 0, strlen($compare)) == $compare) {
            return true;
        }

        return false;
    }

    /**
     * returns true when value is string and ends with given value.
     *
     * @param string $compare
     * @return bool
     */
    public function endsWith($compare) {
        $str = (string) $this->value;

        if(substr($str, 0 - strlen($compare)) == $compare) {
            return true;
        }

        return false;
    }


    /**
     * calls
     *
     * @name __call
     * @access public
     * @return mixed|string
     */
    public function __call($methodName, $args) {

        if(defined("IN_UNIT_TEST")) {
            $trace = debug_backtrace();
            if(isset($trace[0]['file'])) {
                throw new LogicException('Call to undefined method ' . $this->classname . '::' . $methodName . ' in '.$trace[0]['file'].' on line '.$trace[0]['line']);
            } else {
                throw new LogicException('Call to undefined method ' . $this->classname . '::' . $methodName);
            }
        }

        if(DEV_MODE) {
            $trace = debug_backtrace();
            if(isset($trace[0]['file']))
                log_error('Warning: Call to undefined method ' . $this->classname . '::' . $methodName . ' in '.$trace[0]['file'].' on line '.$trace[0]['line']);
            else
                log_error('Warning: Call to undefined method ' . $this->classname . '::' . $methodName);

            if(DEV_MODE)
                AddContent::add('<div class="error"><b>Warning</b> Call to undefined method ' . $this->classname . '::' . $methodName . '</div>');
        }


        return $this->__toString();
    }

    /**
     * bool
     */
    public function bool() {
        return ($this->value);
    }

    /**
     * parses casting-args and gives back the result
     *
     * @name parseCasting
     * @access public
     * @param string - casting
     * @return array|null
     */
    public static function parseCasting($casting) {

        if(isset(self::$castingCache[$casting]))
            return self::$castingCache[$casting];

        if(PROFILE) Profiler::mark("DBField::parseCasting");

        if(is_array($casting)) {
            return $casting;
        }

        $method = self::parseCastingString($casting, $name, $args);

        if(ClassInfo::exists($name) && ClassInfo::hasInterface($name, "IDataBaseField")) {
            $valid = true;
        } else if (ClassInfo::exists($name . "SQLField") && ClassInfo::hasInterface($name . "SQLField", "IDataBaseField")) {
            $name = $name . "SQLField";
            $valid = true;
        }

        if(!isset($valid)) {
            self::$castingCache[$casting] = null;
            if(PROFILE) Profiler::unmark("DBField::parseCasting");
            return null;
        }

        $data = array(
            "class" => $name
        );

        if(!empty($args)) {
            $data["args"] = $args;
        }

        if(ClassInfo::hasInterface($name, "DefaultConvert")) {
            $data["convert"] = true;
        }

        if(isset($method)) {
            $data["method"] = $method;
        }

        self::$castingCache[$casting] = $data;
        if(PROFILE) Profiler::unmark("DBField::parseCasting");

        return $data;
    }

    /**
     * parses casting and returns name of method or null if not set.
     *
     * @param string $casting
     * @param string $name variable to fill name in
     * @param array $args variable for args
     * @return string
     */
    protected static function parseCastingString($casting, &$name, &$args) {
        $method = null;
        $args = array();

        if(preg_match('/\-\>([a-zA-Z0-9_]+)\s*$/Usi', $casting, $matches)) {
            $method = $matches[1];
            $casting = substr($casting, 0, 0 - strlen($method) - 2);
            unset($matches);
        }

        if(strpos($casting, "(")) {
            $name = trim(substr($casting, 0, strpos($casting, "(")));
            if(preg_match('/\(([^\(\)]+)\)?/', $casting, $matches)) {
                $args = $matches[1];
                $args = eval('return array('.$args.');');
            }
            unset($matches);
        } else {
            $name = trim($casting);
        }

        $name = ClassManifest::resolveClassName($name);

        return $method;
    }

    /**
     * gets a var for template
     *
     * @name getTemplateVar
     * @return string|null
     */
    public function getTemplateVar($var) {
        if(strpos($var, ".")) {
            throw new InvalidArgumentException('Arguments with dots like "' . $var . '" are not allowed in a DB-Field, because it\'s recursive.');
        }

        // check for args
        if(strpos($var, "(") && substr($var, -1) == ")") {
            $args = eval("return array(" . substr($var, strpos($var, "(") + 1, -1) . ");");
            $var = substr($var, 0, strpos($var, "("));
        } else {
            $args = array();
        }

        if(gObject::method_exists($this, $var)) {
            return call_user_func_array(array($this, $var), $args);
        }

        return null;
    }

    /**
     * converts by casting
     *
     * @name convertByCasting
     * @access public
     * @param string|array casting
     * @param string - name
     * @param mixed - value
     * @return string
     */
    public static function convertByCasting($casting, $name, $value) {
        if(!is_string($name)) {
            throw new InvalidArgumentException("Second argument (\$name) of DBField::convertByCastingIfDefault must be an string.");
        }
        $casting = self::parseCasting($casting);
        if(isset($casting)) {
            $object = new $casting["class"]($name, $value, isset($casting["args"]) ? $casting["args"] : array());
            if(isset($casting["method"])) {
                return call_user_func_array(array($object, $casting["method"]), array());
            } else {
                return $object->__toString();
            }
        } else {
            throw new InvalidArgumentException("Invalid casting-Array given to DBField::convertByCasting");
        }
    }

    /**
     * converts by casting if convertDefault
     *
     * @name convertByCastingIfDefault
     * @access public
     * @param string|array casting
     * @param string - name
     * @param mixed - value
     * @return mixed
     */
    public static function convertByCastingIfDefault($casting, $name, $value) {
        if(!is_string($name)) {
            throw new InvalidArgumentException("Second argument (\$name) of DBField::convertByCastingIfDefault must be an string.");
        }
        $casting = self::parseCasting($casting);
        if(isset($casting["convert"]) && $casting["convert"]) {
            return self::convertByCasting($casting, $name, $value);
        }

        return $value;
    }

    /**
     * gets an object by casting
     *
     * @name getObjectByCasting
     * @access public
     * @param string|array casting
     * @param string - name
     * @param mixed - value
     * @return DBField
     */
    public static function getObjectByCasting($casting, $name, $value, $throwErrorOnFail = false) {
        if(!is_string($name)) {
            throw new InvalidArgumentException("Second argument (\$name) of DBField::convertByCastingIfDefault must be an string.");
        }
        $casting = self::parseCasting($casting);
        if(isset($casting)) {
            return new $casting["class"]($name, $value, isset($casting["args"]) ? $casting["args"] : array());
        } else if($throwErrorOnFail) {
            throw new LogicException("Invalid casting given to DBField::getObjectByCasting '".$casting."'");
        } else {
            return new DBField($name, $value, array());
        }
    }

    /**
     * returns false because no object can be done
     *
     * @name canDoObject
     * @return bool
     */
    public function canDoObject() {
        return false;
    }

    /**
     * returns field-value for database.
     */
    public function forDBQuery()
    {
        return "'".convert::raw2sql($this->value)."'";
    }
}
