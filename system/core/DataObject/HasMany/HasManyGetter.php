<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Reading Has-Many-Relationships of Models.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0.2
 */
class HasManyGetter extends Extension {

    /**
     * id of class.
     */
    const ID = "HasManyGetter";

    /**
     * extra-methods.
     */
    public static $extra_methods = array(
        "getHasMany",
        "HasMany"
    );

    /**
     * @var array
     */
    protected static $relationShips = array(

    );

    /**
     * define statics extension.
     */
    public function extendDefineStatics() {
        if ($has_many = $this->hasMany()) {
            foreach ($has_many as $key => $val) {
                gObject::LinkMethod($this->getOwner()->classname, $key, array("HasManyGetter", function($instance) use($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    try {
                        return call_user_func_array(array($instance, "getHasMany"), $args);
                    } catch(InvalidArgumentException $e) {
                        throw new LogicException("Something got wrong wiring the HasMany-Relationship.", 0, $e);
                    }
                }), true);

                gObject::LinkMethod($this->getOwner()->classname, $key . "ids", array("this", "getRelationIDs"), true);
            }
        }
    }

    /**
     * @param string $name name of relationship
     * @param array|string $filter filter
     * @param array|string $sort sort
     * @param array|string $limit
     * @return HasMany_DataObjectSet
     */
    public function getHasMany($name, $filter = null, $sort = null, $limit = null) {
        $name = trim(strtolower($name));
        /** @var DataObject $owner */
        $owner = $this->getOwner();

        $has_many = $this->hasMany();
        if (!isset($has_many[$name]))
        {
            throw new InvalidArgumentException("No Has-many-relation '".$name."' on ".$this->classname);
        }

        /** @var HasMany_DataObjectSet $hasManyObject */
        $hasManyObject = $owner->fieldGet($name);
        if(!$hasManyObject || !is_a($hasManyObject, "DataObjectSet")) {
            $hasManyObject = $this->getNewHasManyObject($has_many, $name);
            $owner->setField($name, $hasManyObject);

            if ($owner->queryVersion == DataObject::VERSION_STATE) {
                $hasManyObject->setVersion(DataObject::VERSION_STATE);
            }
        }

        if(!$filter && !$sort && !$limit) {
            return $hasManyObject;
        }

        $objectToFilter = clone $hasManyObject;
        $objectToFilter->addFilter($filter);
        $objectToFilter->sort($sort);
        $objectToFilter->limit($limit);

        return $objectToFilter;
    }

    /**
     * generates new has-many-object.
     *
     * @param ModelHasManyRelationShipInfo[] $has_many
     * @param string $name
     * @return HasMany_DataObjectSet
     */
    protected function getNewHasManyObject($has_many, $name) {
        /** @var DataObject $owner */
        $owner = $this->getOwner();

        $filter = array();
        $ids = $owner->fieldGet($name . "ids");
        if($ids && is_array($ids)) {
            $filter["id"] = $ids;
        } else {
            $filter[$has_many[$name]->getInverse() . "id"] = $this->getOwner()->id;
        }

        $set = new HasMany_DataObjectSet($has_many[$name]->getTargetClass(), $filter);
        $set->setRelationENV($has_many[$name], $this->getOwner()->id);

        return $set;
    }

    /**
     * returns one or many hasMany-Relationsips.
     *
     * @name hasMany
     * @param string $component name of has-many-relation to give back.
     * @return ModelHasManyRelationShipInfo[]|ModelHasManyRelationShipInfo
     */
    public function hasMany($component = null) {
        $owner = $this->getOwner();

        if(!$owner) {
            return array();
        }

        if(!isset(self::$relationShips[$owner->classname])) {
            $has_many = isset(ClassInfo::$class_info[$owner->classname]["has_many"]) ? ClassInfo::$class_info[$owner->classname]["has_many"] : array();

            if ($classes = ClassInfo::dataclasses($owner->classname)) {
                foreach($classes as $class) {
                    if (isset(ClassInfo::$class_info[$class]["has_many"])) {
                        $has_many = array_merge(ClassInfo::$class_info[$class]["has_many"], $has_many);
                    }
                }
            }

            $hasManyClasses = array();
            foreach($has_many as $name => $value) {
                $hasManyClasses[$name] = new ModelHasManyRelationShipInfo($owner->classname, $name, $value);
            }

            self::$relationShips[$owner->classname] = $hasManyClasses;
        }

        if(!isset($component)) {
            return self::$relationShips[$owner->classname];
        } else {
            return isset(self::$relationShips[$owner->classname][$component]) ? self::$relationShips[$owner->classname][$component] : null;
        }
    }

    /**
     * duplicate extension.
     */
    public function duplicate() {
        /** @var DataObject $owner */
        $owner = $this->getOwner();
        foreach($this->hasMany() as $name => $class) {
            $owner->setField($name, $this->getHasMany($name));
        }
    }
}
gObject::extend("DataObject", "HasManyGetter");
