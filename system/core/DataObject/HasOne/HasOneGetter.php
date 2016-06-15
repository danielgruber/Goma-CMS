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
     * create objects for all has-one-data.
     */
    public function initValues() {
        foreach($this->hasOne() as $name => $data) {
            if(is_array($this->getOwner()->fieldGet($name))) {
                $this->getOwner()->setField($name, $this->getOwner()->createNew($this->getOwner()->fieldGet($name)));
            }
        }
    }

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

        if(!isset(self::$relationShips[$owner->classname]) ||
            (!self::$relationShips[$owner->classname] && ClassInfo::ClassInfoHasBeenRegenerated())) {
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
     * @return DataObject
     */
    public function getHasOne($name) {
        $name = trim(strtolower($name));

        // get info
        if($relationShip = $this->hasOne($name)) {
            if($this->getOwner()->fieldGet($name . "id")) {
                // check field
                $instance = $this->getOwner()->fieldGet($name);
                if (!$instance || !is_a($instance, "DataObject")) {
                    $response = DataObject::get($relationShip->getTargetClass(), array(
                        "id" => $this->getOwner()->fieldGet($name . "id")
                    ));

                    if ($this->getOwner()->queryVersion == DataObject::VERSION_STATE) {
                        $response->setVersion(DataObject::VERSION_STATE);
                    }

                    $this->getOwner()->setField($name, $instance = $response->first());
                }

                return $instance;
            } else {
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
            if(!isset($value)) {
                $this->getOwner()->setField($name, $value);
                $this->getOwner()->setField($name  ."id", 0);
            } else if(is_a($value, "DataObject")) {
                $this->getOwner()->setField($name, $value);
                $this->getOwner()->setField($name  ."id", $value->id != 0 ? $value->id : null);
            } else {
                throw new InvalidArgumentException("setting HasOne-Relationship must be either DataObject or null.");
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
        if (is_array($query->filter))
        {
            $hasOnes = array();
            $has_one = $this->hasOne();
            $query->filter = $this->parseHasOnes($query->filter, $has_one, $hasOnes);

            if (count($hasOnes) > 0) {
                foreach($hasOnes as $hasOneKey) {
                    $relationShip = $has_one[$hasOneKey];
                    $this->addJoinForRelationship($query, $hasOneKey, $relationShip);
                }
            }
        }
    }

    /**
     * @param SelectQuery $query
     * @param string $hasOneKey
     * @param ModelHasOneRelationshipInfo $relationShip
     */
    protected function addJoinForRelationship($query, $hasOneKey, $relationShip) {
        $table = ClassInfo::$class_info[$relationShip->getTargetClass()]["table"];
        $hasOneBaseTable = (ClassInfo::$class_info[ClassInfo::$class_info[$relationShip->getTargetClass()]["baseclass"]]["table"]);

        if(!$query->aliasExists($hasOneKey)) {
            $query->innerJoin(
                $table,
                $hasOneKey . '.recordid = ' . $this->getOwner()->Table() . '.' . $hasOneKey . 'id',
                $hasOneKey,
                false
            );
            $query->innerJoin(
                $hasOneBaseTable . '_state',
                $hasOneBaseTable . '_state.publishedid = ' . $hasOneKey . '.id',
                $hasOneBaseTable . '_state',
                false
            );
        }
    }

    /**
     * @param array $filter
     * @param ModelHasOneRelationshipInfo[] $has_one
     * @param array $hasOnes
     * @return array
     */
    protected function parseHasOnes($filter, $has_one, &$hasOnes) {
        if (is_array($filter))
        {
            foreach($filter as $key => $value)
            {
                if (strpos($key, ".") !== false) {
                    // has one
                    $hasOnePrefix = strtolower(substr($key, 0, strpos($key, ".")));
                    if (isset($has_one[$hasOnePrefix])) {
                        $hasOnes[$hasOnePrefix] = $hasOnePrefix;
                    }
                } else {
                    if(is_array($value)) {
                        $filter[$key] = $this->parseHasOnes($value, $has_one, $hasOnes);
                    }
                }
            }
        }

        return $filter;
    }

    /**
     * @param SelectQuery $query
     * @param string $aggregateField
     * @param array $aggregates
     */
    public function extendAggregate(&$query, &$aggregateField, &$aggregates) {
        if (strpos($aggregateField, ".") !== false) {
            $has_one = $this->hasOne();

            $hasOnePrefix = strtolower(substr($aggregateField, 0, strpos($aggregateField, ".")));
            if (isset($has_one[$hasOnePrefix])) {
                $this->addJoinForRelationship($query, $hasOnePrefix, $has_one[$hasOnePrefix]);
            }
        }
    }

    /**
     * @param array $result
     * @param SelectQuery $query
     * @param string $version
     */
    public function argumentQueryResult(&$result, $query, $version) {
        foreach ($this->getHasOnesToFetch($result) as $name => $relationShip) {
            // build ids
            $ids = array();
            foreach($result as $key => $record) {
                if(isset($record[$name . "id"]) && $record[$name . "id"] != 0) {
                    $id = $record[$name . "id"];
                    if(!isset($ids[$id])) {
                        $ids[$id] = array();
                    }
                    $ids[$id][] = $key;
                }
            }

            if(count($ids) > 0) {
                $relationShipData = DataObject::get_versioned($relationShip->getTargetClass(), $version, array(
                    "id" => array_keys($ids)
                ));
                /** @var DataObject $record */
                foreach($relationShipData as $record) {
                    foreach($ids[$record->id] as $resultKey) {
                        $result[$resultKey][$name] = $record->ToArray();
                    }
                }
            }
        }
    }

    /**
     * @param array $result
     * @return array
     */
    protected function getHasOnesToFetch($result) {
        $hasOnes = array();
        if(count($result) > 0) {
            foreach ($this->hasOne() as $name => $relationShip) {
                if ($relationShip->getFetchType() == DataObject::FETCH_TYPE_EAGER) {
                    if(isset($result[0][$name . "id"])) {
                        $hasOnes[$name] = $relationShip;
                    }
                }
            }
        }
        return $hasOnes;
    }
}
gObject::extend("DataObject", "HasOneGetter");
