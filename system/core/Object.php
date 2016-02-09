<?php
defined("IN_GOMA") OR die();

interface ExtensionModel
{

    public function setOwner($object);

    public function getOwner();
}

/**
 * Base class for _every_ Goma class.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Framework
 * @version 3.6
 */
abstract class gObject
{
    const ID = "gObject";

    /**
     * caches
     */
    static private $method_cache = array(), $cache_extensions = array(), $wakeUpCache = array();

    /**
     * Extension methods.
     */
    public static $extra_methods = array();

    /**
     * Temporary extension methods.
     */
    public static $temp_extra_methods = array();

    public static   $cache_extra_methods = array(), // cache for extra-methods
        $extensions = array(),  // extensions of all classes
        $ci_funcs = array(),  // functions called when generating ClassInfo
        $cache_singleton_classes = array(); // cache for Singletons

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
     * const defines that method only exists on object.
     */
    const METHOD_ON_OBJECT_FOUND = 2;

    /**
     * returned when method wasn't found, but it has not been searched recusrively, yet.
     */
    const METHOD_NOT_FOUND_BUT_MAY_PARENT = 3;

    /**
     * method found.
     */
    const METHOD_FOUND = 1;


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
    public static function createMethod($class, $method, $code, $temp = false)
    {
        $method = strtolower($method);
        $class = strtolower($class);

        if ($temp) {
            self::$temp_extra_methods[$class][$method] = create_function('$obj', $code);
        } else if (!gObject::method_exists($class, $method)) {
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
    public static function linkMethod($class, $method, $realfunc, $temp = false)
    {
        $method = strtolower($method);
        $class = strtolower($class);

        if ($temp) {
            self::$temp_extra_methods[$class][$method] = $realfunc;
        } else if (!gObject::method_exists($class, $method)) {
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
    public static function method_exists($class, $method)
    {
        if(!$method || !$class) {
            throw new InvalidArgumentException("Method must be set and a string.");
        }

        if (PROFILE) {
            Profiler::mark("Object::method_exists");
        }

        // Gets class name if $class is an object.
        if (is_object($class)) {
            $object = $class;
            $class = strtolower(get_class($class));
        } else {
            $object = null;
        }

        // trim and bring to lowercase.
        $class = strtolower(trim($class));
        $method = strtolower(trim($method));

        // check for extra methods here
        $res = self::method_exists_on_object($class, $method, $object);

        if ($res === self::METHOD_NOT_FOUND_BUT_MAY_PARENT) {
            // check on parents
            $res = self::check_for_extra_methods_recursive($class, $method);
        }

        // we hold a method-cache to react to queries faster after first check.
        if (!isset(self::$method_cache[$class . "::" . $method]) && $res != self::METHOD_ON_OBJECT_FOUND) {
            self::$method_cache[$class . "::" . $method] = (bool)$res;
        }

        if (PROFILE) {
            Profiler::unmark("Object::method_exists");
        }

        return (bool)$res;
    }

    /**
     * searches for method and returns true or false when exists or not.
     * it won't search recursively upwards. arguments must be trimmed and lowercase.
     * @param string $class
     * @param string $method
     * @param gObject $object instance of class for __cancall null $object
     * @return int
     */
    protected static function method_exists_on_object($class, $method, $object = null)
    {

        if (isset(self::$method_cache[$class . "::" . $method]) && (self::$method_cache[$class . "::" . $method] || !isset($object))) {
            return self::$method_cache[$class . "::" . $method];
        }

        if(self::method_exists_native_db($class, $method)) {
            return self::METHOD_FOUND;
        }

        // check on object
        if (isset($object) && self::check_for_object_method($object, $method)) {
            return self::METHOD_ON_OBJECT_FOUND;
        }

        return self::METHOD_NOT_FOUND_BUT_MAY_PARENT;
    }

    /**
     * checks if method is existing in DB or native.
     *
     * @param string $class
     * @param string $method
     * @return bool
     */
    protected static function method_exists_native_db($class, $method) {
        // check native
        return (is_callable(array($class, $method)) ||
            isset(self::$extra_methods[$class][$method]) ||
            isset(self::$temp_extra_methods[$class][$method]));
    }

    /**
     * calls __cancall when object is given.
     */
    protected static function check_for_object_method($o, $m)
    {
        if (is_object($o) && method_exists($o, "__cancall")) {
            return $o->__canCall($m);
        } else {
            return false;
        }
    }

    /**
     * checks recursively upwards if extra-method exists.
     *
     * @param string $c current class
     * @param string $m method
     * @return bool
     */
    protected static function check_for_extra_methods_recursive($c, $m)
    {
        if (isset(self::$extra_methods[$c][$m])) {
            // cache result for class
            self::$method_cache[$c . "::" . $m] = true;
        } else if ($c = ClassInfo::get_parent_class($c)) {
            self::$method_cache[$c . "::" . $m] = self::check_for_extra_methods_recursive($c, $m);
        } else {
            self::$method_cache[$c . "::" . $m] = false;
        }

        return self::$method_cache[$c . "::" . $m];
    }

    /**
     * Extends an object.
     *
     * @param string The extended object.
     * @param string The extension.
     *
     * @return void
     */
    public static function extend($obj, $ext)
    {
        if (defined("GENERATE_CLASS_INFO")) {
            $obj = strtolower($obj);
            $info = self::getArgumentsFromExtend($ext);
            $name = $info[0];
            $arguments = $info[1];

            if (ClassInfo::hasInterface($name, "ExtensionModel")) {
                if ($methods = StaticsManager::getStatic($name, 'extra_methods')) {
                    foreach ($methods as $method) {
                        self::$extra_methods[$obj][strtolower($method)] = array("EXT:" . $name, $method);
                    }
                }
                self::$extensions[$obj][$name] = $arguments;
            } else {
                throw new LogicException("Extension $name isn't a Extension");
            }
        }
    }

    /**
     * get arguments from extend-call. it also checks if class exists.
     *
     * @param string extension
     * @return array first parameter is extension-name, second arguments.
     */
    public static function getArgumentsFromExtend($ext) {
        if (preg_match('/^([a-zA-Z0-9_\-]+)\((.*)\)$/', $ext, $exts)) {
            return array(ClassInfo::find_class_name($exts[1]), $exts[2]);
        }

        return array(ClassInfo::find_class_name($ext), array());

    }

    /**
     * Gets the singleton of a class.
     *
     * @param string|gObject $class Name of the class.
     *
     * @return gObject The singleton.
     */
    public static function instance($class)
    {

        if (is_object($class)) {
            return clone $class;
        }

        if (PROFILE) Profiler::mark('Object::instance');

        $class = ClassInfo::find_creatable_class($class);
        if (!isset(self::$cache_singleton_classes[$class])) {
            self::$cache_singleton_classes[$class] = new $class();
        }

        if (PROFILE) Profiler::unmark("Object::instance");

        return clone self::$cache_singleton_classes[$class];
    }

    /**
     * Sets class name and save vars.
     */
    public function __construct()
    {
        // Set class name
        $this->classname = ClassManifest::resolveClassName($this);
        $this->class = $this->classname;

        if (isset(ClassInfo::$class_info[$this->classname]["inExpansion"])) {
            $this->inExpansion = ClassInfo::$class_info[$this->classname]["inExpansion"];;
        }

        StaticsManager::setSaveVars($this);
    }

    /**
     * This method overloads functions.
     *
     * @link http://php.net/manual/de/language.oop5.overloading.php
     *
     * @param string $methodName Name of the method.
     * @param string $args Arguments.
     *
     * @return mixed The return of the function.
     */
    public function __call($methodName, $args)
    {
        $methodName = trim(strtolower($methodName));

        if (isset(self::$extra_methods[$this->classname][$methodName])) {
            return $this->callExtraMethod($methodName, self::$extra_methods[$this->classname][$methodName], $args);
        }

        if (isset(self::$cache_extra_methods[$this->classname][$methodName])) {
            return $this->callExtraMethod($methodName, self::$cache_extra_methods[$this->classname][$methodName], $args);
        }

        if (method_exists($this, $methodName) && is_callable(array($this, $methodName))) {
            return call_user_func_array(array($this, $methodName), $args);
        }

        // check last
        if (isset(self::$temp_extra_methods[$this->classname][$methodName])) {
            return $this->callExtraMethod($methodName, self::$temp_extra_methods[$this->classname][$methodName], $args);
        }

        // check parents
        $c = $this->classname;
        while ($c = ClassInfo::GetParentClass($c)) {
            if (isset(self::$extra_methods[$c][$methodName])) {

                // cache result
                self::$cache_extra_methods[$this->classname][$methodName] = self::$extra_methods[$c][$methodName];

                return $this->callExtraMethod($methodName, self::$extra_methods[$c][$methodName], $args);
            }
        }

        throw new BadMethodCallException("Call to undefined method '" . get_class($this) . "::" . $methodName . "'");
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
    protected function callExtraMethod($method_name, $extra_method, $args = array())
    {
        // first if it is a callback
        $method_callback = $this->getMethodCallBack($extra_method, $method_name, $args);

        if(!is_callable($method_callback)) {
            throw new BadMethodCallException('Tried to call Extra-Method ' . print_r($extra_method, true) .
                ' via '.print_r($method_callback, true).', but it is not callable.');
        }

        return call_user_func_array($method_callback, $args);
    }

    /**
     * gets method callback out of information and modifies args.
     *
     * @param array|Closure $extra_method
     * @param $method_name
     * @param array $args
     * @return Callback
     */
    protected function getMethodCallBack($extra_method, $method_name, &$args) {
        if(is_a($extra_method, "Closure")) {
            return $extra_method;
        } else if(is_a($extra_method[count($extra_method) - 1], "closure")) {
            /** @var Closure $closure */
            for($i = count($extra_method) - 2; $i >= 0; $i--) {
                $object = is_object($extra_method[$i]) ? $extra_method[$i] : $this->getInstance($extra_method[$i]);
                if(!isset($object)) {
                    $object = gObject::instance($extra_method[$i]);
                }
                array_unshift($args, $object);
            }
            $closure = $extra_method[1];

            return $closure;
        } else if(is_array($extra_method)) {
            $method_callback = $extra_method;
            if (is_string($extra_method[0]) && substr($extra_method[0], 0, 4) == "EXT:") {
                $method_callback[0] = $this->getInstance(substr($method_callback[0], 4));
            } else if (is_string($extra_method[0]) && $extra_method[0] == "this") {
                array_unshift($args, $method_name);
                $method_callback[0] = $this;
            }

            return $method_callback;
        } else {
            array_unshift($args, $this);
            return $extra_method;
        }
    }

    /**
     * Gets extensions of a class.
     *
     * @param boolean $recursive Working recursive?
     *
     * @return array[] Array with all extensions.
     */
    public function getExtensions($recursive = true)
    {
        return self::getExtensionsForClass($this->classname, $recursive);
    }

    /**
     * returns extensions for a given class as static context.
     *
     * @param $class
     * @param bool $recursive if to check for extensions from parents, too.
     * @return array
     */
    public static function getExtensionsForClass($class, $recursive = true) {
        $class = ClassManifest::resolveClassName($class);

        if ($recursive === true) {
            if (defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$class])) {
                self::buildExtCache($class);
            }
            return array_keys(self::$cache_extensions[$class]);
        } else {
            return (isset(self::$extensions[$class])) ? array_keys(self::$extensions[$class]) : array();
        }
    }

    /**
     * Builds the extension cache.
     *
     * @param $class
     * @return array[] Array with the extensions.
     */
    private static function buildExtCache($class)
    {
        $parent = $class;
        $extensions = array();
        while ($parent !== false) {
            if (isset(self::$extensions[$parent])) {
                $extensions = array_merge(self::$extensions[$parent], $extensions);
            }
            $parent = ClassInfo::getParentClass($parent);
        }

        self::$cache_extensions[$class] = $extensions;
        return $extensions;
    }

    /**
     * gets an extension-instance
     *
     * @name getInstance
     * @param string $extensionClassName of extension
     * @return Extension
     */
    public function getInstance($extensionClassName)
    {
        $extensionClassName = trim(strtolower($extensionClassName));

        // cache for instances. No clone here, cause an instance can used and customised.
        if (isset($this->ext_instances[$extensionClassName])) {
            $this->ext_instances[$extensionClassName]->setOwner($this);
            return $this->ext_instances[$extensionClassName];
        }

        if (defined("GENERATE_CLASS_INFO") || !isset(self::$cache_extensions[$this->classname])) {
            self::buildExtCache($this->classname);
        }

        // create new instance
        if (!isset(self::$extension_instances[$this->classname][$extensionClassName]) || !is_object(self::$extension_instances[$this->classname][$extensionClassName])) {

            if (!isset(self::$cache_extensions[$this->classname][$extensionClassName])) {
                return null;
            }

            $reflectionClass = new ReflectionClass($extensionClassName);
            $args =
                is_array(self::$cache_extensions[$this->classname][$extensionClassName]) ?
                    self::$cache_extensions[$this->classname][$extensionClassName] :
                    eval('return array('.self::$cache_extensions[$this->classname][$extensionClassName].');');
            self::$extension_instances[$this->classname][$extensionClassName] = $reflectionClass->newInstanceArgs($args);
        }

        // own instance
        $this->ext_instances[$extensionClassName] = clone self::$extension_instances[$this->classname][$extensionClassName];
        $this->ext_instances[$extensionClassName]->setOwner($this);
        return $this->ext_instances[$extensionClassName];
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
    public function callExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null)
    {
        $returns = array();
        foreach ($this->getextensions(true) as $extension) {
            if (gObject::method_exists($extension, $method)) {
                if ($instance = $this->getinstance($extension)) {

                    // so let's call ;)
                    $return = $instance->$method($p1, $p2, $p3, $p4, $p5, $p6, $p7);
                    if ($return)
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
    public function LocalCallExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null)
    {
        $returns = array();
        foreach ($this->getExtensions(false) as $extension) {
            if (gObject::method_exists($extension, $method)) {
                if ($instance = $this->getinstance($extension)) {
                    $instance->setOwner($this);
                    $returns[] = $instance->$method($p1, $p2, $p3, $p4, $p5, $p6, $p7);
                }
            }
        }

        return $returns;
    }

    /**
     * generates class-info
     *
     * @name buildClassInfo
     * @access public
     */
    static function buildClassInfo($class)
    {
        foreach ((array)StaticsManager::getStatic($class, "extend") as $ext) {
            gObject::extend($class, $ext);
        }
    }

    public function __wakeup()
    {
        /*if($this->ext_instances) {
            foreach ($this->ext_instances as $instance) {*/
                /** @var Extension $instance */
                /*$instance->setOwner($this);
                $instance->__wakeup();
            }
        }*/

        if(!isset(self::$wakeUpCache[$this->classname])) {
            StaticsManager::setSaveVars($this);
            self::$wakeUpCache[$this->classname] = true;
        }
    }

    public function __clone() {
        if($this->ext_instances) {
            foreach ($this->ext_instances as $key => $instance) {
                $this->ext_instances[$key] = clone $instance;
                $this->ext_instances[$key]->setOwner($this);
            }
        }
    }

    /**
     * bool.
     */
    public function bool()
    {
        return true;
    }
}
