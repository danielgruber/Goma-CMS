<?php defined("IN_GOMA") OR die();
/**
 * provides some methods to use to get, set and call statics on classes.
 *
 * @package		Goma\Core
 * @version		1.0
 */

class StaticsManager {
    /**
     * this var saves for each class, which want to save vars in cache, the names
     *@var array
     */
    public static $save_vars;
    /**
     * array of classes, which we have already set SaveVars
     *
     */
    public static $set_save_vars = array();

    /**
     * array of classes, which we already called static hook.
     */
    public static $hook_called = array();

    /**
     * validates if class and variable/method-names are valid.
     * it throws an exception if not and returns correct class-name.
     *
     * @param string $class
     * @param string $var
     * @return string classname
     */
    public static function validate_static_call($class, $var)
    {
        $class = ClassInfo::find_class_name($class);

        if (empty($var)) {
            throw new LogicException("Invalid name of variable $var for $class");
        }

        return $class;
    }

    /**
     * Gets the value of $class::$$var.
     *
     * @param string $class Name of the class.
     * @param string $var Name of the variable.
     *
     * @return mixed Value of $var.
     */
    public static function getStatic($class, $var)
    {
        $class = self::validate_static_call($class, $var);
        return eval('return isset(' . $class . '::$' . $var . ") ? " . $class . '::$' . $var . " : null;");
    }

    /**
     * Checks, if $class::$$var is set.
     *
     * @param string $class Name of the class.
     * @param string $var Name of the variable.
     *
     * @return boolean
     */
    public static function hasStatic($class, $var)
    {
        $class = self::validate_static_call($class, $var);
        return eval('return isset(' . $class . '::$' . $var . ');');
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
    public static function setStatic($class, $var, $value)
    {
        $class = self::validate_static_call($class, $var);
        return eval('
        if(isset(' . $class . '::$' . $var . '))
            ' . $class . '::$' . $var . ' = ' . var_export($value, true) . ';
        else
            throw new LogicException("Could not set Variable ' . $var . ' on Class ' . $class . '.");');
    }

    /**
     * Calls $class::$$func.
     *
     * @param string $class Name of the class.
     * @param string $func Name of the function.
     *
     * @return void
     */
    public static function callStatic($class, $func)
    {
        $class = self::validate_static_call($class, $func);

        if (is_callable(array($class, $func))) {
            return call_user_func_array(array($class, $func), array($class));
        } else {
            throw new BadMethodCallException('Call to unknown method ' . $class . '::' . $func);
        }
    }

    /**
     * adds a var to cache
     *@param class - class_name
     *@param name - var-name
     */
    public static function addSaveVar($class, $name)
    {
        if (class_exists("ClassManifest")) {
            $class = ClassManifest::resolveClassName($class);
        }

        self::$save_vars[$class][] = $name;
    }

    /**
     * gets for a specific class the save_vars
     *@name getSaveVars
     *@param string - class-name
     *@return array
     */
    public static function getSaveVars($class)
    {
        $class = ClassManifest::resolveClassName($class);

        if (isset(self::$save_vars[$class])) {
            return self::$save_vars[$class];
        }
        return array();
    }

    /**
     * sets the save_vars
     * @param $class
     */
    public static function setSaveVars($class)
    {
        if (PROFILE)  Profiler::mark("ClassInfo::setSaveVars");

        $classname = ClassManifest::resolveClassName($class);

        if(!defined('GENERATE_CLASS_INFO')) {
            if (!isset(self::$set_save_vars[$classname])) {
                self::setSaveVarsAndHook($classname);

                self::$set_save_vars[$classname] = true;
            }
        }

        if(defined('GENERATE_CLASS_INFO') || !isset(self::$hook_called[$classname])) {
            if(self::callStaticHook($class)) {
                self::$hook_called[$classname] = true;
            }
        }

        if (PROFILE) Profiler::unmark("ClassInfo::setSaveVars");
    }

    /**
     * sets save-vars and calls hook for them.
     *
     * @param string $class name of class
     */
    protected static function setSaveVarsAndHook($class) {
        foreach (self::getSaveVars($class) as $var) {
            if (isset(ClassInfo::$class_info[$class][$var])) {
                self::setStatic($class, $var, ClassInfo::$class_info[$class][$var]);
            }
        }

        if (ClassInfo::hasInterface($class, "saveVarSetter") && Object::method_exists($class, "__setSaveVars")) {
            call_user_func_array(array($class, "__setSaveVars"), array($class));
        }
    }

    /**
     * tries to call defineStatics-Hook.
     * returns true if it has tried and false if it was not object.
     *
     * @param Object $class
     * @return bool
     */
    public static function callStaticHook($class) {
        if(is_object($class)) {
            if(Object::method_exists(get_class($class), "defineStatics")) {
                $class->defineStatics();
            }

            return true;
        }

        return false;
    }
}