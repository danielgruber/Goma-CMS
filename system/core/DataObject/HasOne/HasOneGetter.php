<?php
defined("IN_GOMA") OR die();

/**
 * Describe your class
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 *
 * @method DataObject getOwner()
 */
class HasOneGetter extends Extension implements ArgumentsQuery {

    /**
     * id of class.
     */
    const ID = "HasOneGetter";

    /**
     * extra-methods.
     */
    public static $extra_methods = array(
        "HasOne",
        "GetHasOne"
    );

    /**
     * @var array
     */
    protected static $relationShips = array(

    );

    /**
     * @var array
     */
    protected $viewCache = array();

    /**
     * define statics extension.
     */
    public function extendDefineStatics() {
        if ($has_one = $this->HasOne()) {
            foreach($has_one as $key => $val) {
                gObject::LinkMethod($this->getOwner()->classname, $key, array("HasOneGetter", function($instance) use ($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    try {
                        return call_user_func_array(array($instance, "getHasOne"), $args);
                    } catch(InvalidArgumentException $e) {
                        throw new LogicException("Something got wrong wiring the HasOne-Relationship.", 0, $e);
                    }
                }), true);
                gObject::LinkMethod($this->getOwner()->classname, "set" . $key, array("HasOneGetter", function($instance) use ($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    try {
                        return call_user_func_array(array($instance, "setHasOne"), $args);
                    } catch(InvalidArgumentException $e) {
                        throw new LogicException("Something got wrong wiring the HasOne-Relationship.", 0, $e);
                    }
                }), true);
            }
        }
    }

    /**
     * returns one or many hasOne-Relationships.
     *
     * @name hasOne
     * @param string $component name of has-many-relation to give back.
     * @return ModelHasOneRelationShipInfo[]|ModelHasOneRelationShipInfo
     */
    public function hasOne($component = null) {
        $owner = $this->getOwner();

        if(!$owner) {
            return array();
        }

        if(!isset(self::$relationShips[$owner->classname])) {
            $has_one = isset(ClassInfo::$class_info[$owner->classname]["has_one"]) ? ClassInfo::$class_info[$owner->classname]["has_one"] : array();

            if ($classes = ClassInfo::dataclasses($owner->classname)) {
                foreach($classes as $class) {
                    if (isset(ClassInfo::$class_info[$class]["has_one"])) {
                        $has_one = array_merge(ClassInfo::$class_info[$class]["has_one"], $has_one);
                    }
                }
            }

            $hasOneClasses = array();
            foreach($has_one as $name => $value) {
                $hasOneClasses[$name] = new ModelHasOneRelationshipInfo($owner->classname, $name, $value);
            }

            self::$relationShips[$owner->classname] = $hasOneClasses;
        }

        if(!isset($component)) {
            return self::$relationShips[$owner->classname];
        } else {
            return isset(self::$relationShips[$owner->classname][$component]) ? self::$relationShips[$owner->classname][$component] : null;
        }
    }

    /**
     * gets a has-one-dataobject
     *
     * @param string $name name of relationship
     * @param array $filter
     * @return DataObject
     */
    public function getHasOne($name, $filter = null) {

        $name = strtolower(trim($name));
        $owner = $this->getOwner();

        if (PROFILE) Profiler::mark("getHasOne");

        $cache = "has_one_{$name}_".var_export($filter, true) . $this->getOwner()->fieldGet($name . "id");
        if (isset($this->viewCache[$cache])) {
            if (PROFILE) Profiler::unmark("getHasOne", "getHasOne viewcache");
            return $this->viewCache[$cache];
        }

        $has_one = $this->hasOne();
        if (isset($has_one[$name])) {
            if ($owner->isField($name) && is_object($owner->fieldGet($name)) && is_a($owner->fieldGet($name), $has_one[$name]) && !$filter) {
                if (PROFILE) Profiler::unmark("getHasOne");
                return $owner->fieldGet($name);
            }

            if ($this->getOwner()->fieldGet($name . "id") == 0) {

                if (PROFILE) Profiler::unmark("getHasOne");
                return null;
            }

            $filter["id"] = $this->getOwner()->fieldGet($name . "id");

            if (isset(DataObjectQuery::$datacache[$owner->baseClass][$cache])) {
                if (PROFILE) Profiler::unmark("getHasOne", "getHasOne datacache");
                $this->viewCache[$cache] = clone DataObjectQuery::$datacache[$owner->baseClass][$cache];
                return $this->viewCache[$cache];
            }

            $response = DataObject::get($has_one[$name]->getTargetClass(), $filter);

            if ($owner->queryVersion == DataObject::VERSION_STATE) {
                $response->setVersion(DataObject::VERSION_STATE);
            }

            if (($this->viewCache[$cache] = $response->first(false))) {
                DataObjectQuery::$datacache[$owner->baseClass][$cache] = clone $this->viewCache[$cache];
                if (PROFILE) Profiler::unmark("getHasOne");
                return $this->viewCache[$cache];
            } else {
                if (PROFILE) Profiler::unmark("getHasOne");
                return null;
            }
        } else {
            throw new InvalidArgumentException("No Has-one-relation '".$name."' on ".$this->classname);
        }
    }


    /**
     * sets has-one.
     * @param string $name
     * @param DataObject $value
     */
    public function setHasOne($name, $value) {
        $name = strtolower(trim($name));

        $has_one = $this->hasOne();
        if (isset($has_one[$name])) {
            $idField = $name . "id";
            if(!isset($value)) {
                $this->getOwner()->$idField = 0;
            } else if(is_a($value, "DataObject")) {
                $this->getOwner()->setField($name, $value);
                $this->getOwner()->$idField = $value->id != 0 ? $value->id : null;
            } else {
                if(DEV_MODE) {
                    $trace = debug_backtrace();
                    $method = (isset($trace[1]["class"])) ? $trace[1]["class"] . "::" . $trace[1]["function"] : $trace[1]["function"];
                    $file = isset($trace[1]["file"]) ? $trace[1]["file"] : (isset($trace[2]["file"]) ? $trace[2]["file"] : "Undefined");
                    $line = isset($trace[1]["line"]) ? $trace[1]["line"] : (isset($trace[2]["line"]) ? $trace[2]["line"] : "Undefined");

                    log_error("SetHasOne called without giving a DataObject in $file on line $line (Method $method).");
                }
                $this->getOwner()->setField($name, $value);
            }
        } else if(substr($name, 0, 3) == "set") {
            $this->setHasOne(substr($name, 3), $value);
        } else {
            throw new InvalidArgumentException("No Has-one-relation '".$name."' on ".$this->classname);
        }
    }

    /**
     * @param SelectQuery $query
     * @param string $version
     * @param array|string $filter
     * @param array|string $sort
     * @param array|string|int $limit
     * @param array|string|int $joins
     * @param bool $forceClasses if to only get objects of this type of every object from the table
     * @return mixed
     */
    public function argumentQuery($query, $version, $filter, $sort, $limit, $joins, $forceClasses)
    {
        $hasOnes = array();
        $has_one = $this->hasOne();

        if (is_array($query->filter))
        {
            foreach($query->filter as $key => $value)
            {
                if (strpos($key, ".") !== false) {
                    // has one
                    $hasOnePrefix = strtolower(substr($key, 0, strpos($key, ".")));
                    if (isset($has_one[$hasOnePrefix])) {
                        $hasOnes[$hasOnePrefix] = $hasOnePrefix;
                    }
                }
                unset($key, $value, $table, $data, $__table, $_table);
            }
        }

        if (count($hasOnes) > 0) {
            foreach($hasOnes as $hasOneKey) {
                $relationShip = $has_one[$hasOneKey];
                $table = ClassInfo::$class_info[$relationShip->getTargetClass()]["table"];
                $hasOneBaseTable = (ClassInfo::$class_info[ClassInfo::$class_info[$relationShip->getTargetClass()]["baseclass"]]["table"]);

                $query->from[$table] = ' INNER JOIN
													'.DB_PREFIX . $table.'
												AS
													'.$hasOneKey.'
												ON
												 '.$hasOneKey.'.recordid = '.$this->getOwner()->Table().'.'.$hasOneKey.'id';
                $query->from[$hasOneBaseTable . "_state"] = ' INNER JOIN
													'.DB_PREFIX . $hasOneBaseTable.'_state
												AS
													'.$hasOneBaseTable.'_state
												ON
												 '.$hasOneBaseTable.'_state.publishedid = '.$hasOneKey.'.id';
            }
        }
    }
}
gObject::extend("DataObject", "HasOneGetter");
