<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Reading Has-Many-Relationships of Models.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0.1
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
                Object::LinkMethod($this->getOwner()->classname, $key, array("HasManyGetter", function($instance) use($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    return call_user_func_array(array($instance, "getHasMany"), $args);
                }), true);

                Object::LinkMethod($this->getOwner()->classname, $key . "ids", array("this", "getRelationIDs"), true);
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
            throw new LogicException("No Has-many-relation '".$name."' on ".$this->classname);
        }

        /** @var HasMany_DataObjectSet $hasManyObject */
        if($hasManyObject = $owner->fieldGet($name)) {
            if(!$filter && !$sort && !$limit) {
                return $hasManyObject;
            }

            $objectToFilter = clone $hasManyObject;
            $objectToFilter->filter($filter);
            $objectToFilter->sort($sort);
            $objectToFilter->limit($limit);

            return $objectToFilter;
        }

        $info = $this->findInverse($has_many[$name]);

        $filter[$info->getSecond() . "id"] = $this->getOwner()->id;
        $set = new HasMany_DataObjectSet($info->getFirst(), $filter, $sort, $limit);
        $set->setRelationENV($name, $info->getSecond() . "id");

        if ($owner->queryVersion == DataObject::VERSION_STATE) {
            $set->setVersion(DataObject::VERSION_STATE);
        }

        if(!$filter && !$sort && !$limit) {
            $owner->setField($name, $set);
        }

        return $set;
    }

    /**
     * finds inverse for has-many-relationship.
     *
     * @param string|array $hasMany has-many-relationship
     * @return Tuple<class,inverse>
     */
    protected function findInverse($hasMany) {

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
            $inverse = array_search($this->getOwner()->classname, ClassInfo::$class_info[$class]["has_one"]);
            if ($inverse === false)
            {
                $currentClass = $this->getOwner()->classname;
                while($currentClass = ClassInfo::getParentClass($currentClass))
                {
                    if ($inverse = array_search($currentClass, ClassInfo::$class_info[$class]["has_one"]))
                    {
                        break;
                    }
                }
            }

            if ($inverse === false)
            {
                throw new LogicException("No inverse has-one-relationship on '" . $class . "' found.");
            }
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
Object::extend("DataObject", "HasManyGetter");
