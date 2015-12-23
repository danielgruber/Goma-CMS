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
    static $extra_methods = array(
        "getHasMany",
        "HasMany"
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
     * @param array $has_many
     * @param string $name
     * @return HasMany_DataObjectSet
     */
    protected function getNewHasManyObject($has_many, $name) {
        /** @var DataObject $owner */
        $owner = $this->getOwner();

        $info = $this->findInverse($name, $has_many[$name]);

        $filter = array();
        $ids = $owner->fieldGet($name . "ids");
        if($ids && is_array($ids)) {
            $filter["id"] = $ids;
        } else {
            $filter[$info->getSecond() . "id"] = $this->getOwner()->id;
        }
        $set = new HasMany_DataObjectSet($info->getFirst(), $filter);
        $set->setRelationENV($name, $info->getSecond() . "id");

        return $set;
    }

    /**
     * finds inverse for has-many-relationship.
     *
     * @param string $name
     * @param string|array $hasMany has-many-relationship
     * @return Tuple<class,inverse>
     */
    protected function findInverse($name, $hasMany) {

        $inverse = null;
        if(is_array($hasMany) && isset($hasMany["class"])) {
            $class = $hasMany["class"];
            if(isset($hasMany["inverse"]) && isset(ClassInfo::$class_info[$class]["has_one"][$hasMany["inverse"]])) {
                $inverse = $hasMany["inverse"];
            }
        } else {
            $class = $hasMany;
        }

        if(!isset($inverse)) {
            $inverse = HasManyWriter::searchForBelongingHasOneRelationship($this->getOwner(), $name, $class);
        }

        return new Tuple($class, $inverse);
    }

    /**
     * returns one or many hasMany-Relations.
     *
     * @name hasMany
     * @param string $component name of has-many-relation to give back.
     * @param boolean $classOnly if to only give back the class or also give the relation to inverse back.
     * @return array|null
     */
    public function hasMany($component = null, $classOnly = true) {
        $owner = $this->getOwner();

        if(!$owner) {
            return array();
        }

        $has_many = (isset(ClassInfo::$class_info[$owner->classname]["has_many"]) ? ClassInfo::$class_info[$owner->classname]["has_many"] : array());

        if ($classes = ClassInfo::dataclasses($owner->classname)) {
            foreach($classes as $class) {
                if (isset(ClassInfo::$class_info[$class]["has_many"])) {
                    $has_many = array_merge(ClassInfo::$class_info[$class]["has_many"], $has_many);
                }
            }
        }

        if($component !== null) {
            if($has_many && isset($has_many[strtolower($component)])) {
                $has_many = $has_many[strtolower($component)];
            } else {
                return null;
            }
        }

        if($has_many && $classOnly) {
            return preg_replace('/(.+)?\..+/', '$1', $has_many);
        } else {
            return $has_many ? $has_many : array();
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
