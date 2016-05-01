<?php
defined("IN_GOMA") OR die();

/**
 * Extends DataObject with Getters for ManyMany.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 *
 * @method DataObject getOwner()
 */
class ManyManyGetter extends Extension implements ArgumentsQuery
{
    /**
     * extra-methods.
     */
    public static $extra_methods = array(
        "getManyMany",
        "setManyMany",
        "setManyManyIDs"
    );

    /**
     * define statics extension.
     */
    public function extendDefineStatics() {
        if ($manyMany = $this->getOwner()->ManyManyRelationships()) {
            foreach ($manyMany as $key => $val) {
                gObject::LinkMethod($this->getOwner()->classname, $key, array("ManyManyGetter", function($instance) use($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    try {
                        return call_user_func_array(array($instance, "getManyMany"), $args);
                    } catch(InvalidArgumentException $e) {
                        throw new LogicException("Something got wrong wiring the ManyMany-Relationship.", 0, $e);
                    }
                }), true);

                gObject::LinkMethod($this->getOwner()->classname, $key . "ids", array("this", "getRelationIDs"), true);

                gObject::LinkMethod($this->getOwner()->classname, "set" . $key, array("ManyManyGetter", function($instance) use($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    try {
                        return call_user_func_array(array($instance, "setManyMany"), $args);
                    } catch(InvalidArgumentException $e) {
                        throw new LogicException("Something got wrong wiring the ManyMany-Relationship.", 0, $e);
                    }
                }), true);

                gObject::LinkMethod($this->getOwner()->classname, "set" . $key . "ids", array("ManyManyGetter", function($instance) use($key) {
                    $args = func_get_args();
                    $args[0] = $key;
                    try {
                        return call_user_func_array(array($instance, "setManyManyIDs"), $args);
                    } catch(InvalidArgumentException $e) {
                        throw new LogicException("Something got wrong wiring the ManyMany-Relationship.", 0, $e);
                    }
                }), true);
            }
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
     */
    public function argumentQuery($query, $version, $filter, $sort, $limit, $joins, $forceClasses)
    {
        $manyManyRelationships = $this->getOwner()->ManyManyRelationships();

        if(is_array($query->filter)) {
            $query->filter = $this->factorOutFilter($query->filter, $version, $forceClasses, $manyManyRelationships);
        }
    }

    /**
     * @param array $filterArray
     * @param string $version
     * @param bool $forceClasses
     * @param ModelManyManyRelationShipInfo[] $relationShips
     * @return array
     */
    protected function factorOutFilter($filterArray, $version, $forceClasses, $relationShips) {
        foreach($filterArray as $key => $value) {
            if(isset($relationShips[strtolower($key)])) {
                $relationShip = $relationShips[strtolower($key)];
                $target = $relationShip->getTargetClass();
                /** @var DataObject $targetObject */
                $targetObject = new $target();
                $query = $targetObject->buildExtendedQuery($version, $value, array(), array(), array(
                    "INNER JOIN " . DB_PREFIX . $relationShip->getTableName() . " AS " . $relationShip->getTableName() .
                    " ON " . $relationShip->getTableName() . "." . $relationShip->getTargetField() . " = " . $relationShip->getTargetTableName() . ".id"
                ), $forceClasses);
                $query->addFilter($relationShip->getTableName()  . "." . $relationShip->getOwnerField() . " = " . $this->getOwner()->baseTable . ".id");

                unset($filterArray[$key]);
                $filterArray[] = " EXISTS ( ".$query->build()." ) ";
            } else {
                if (is_array($value)) {
                    $filterArray[$key] = $this->factorOutFilter($filterArray[$key], $version, $forceClasses, $relationShips);
                }
            }
        }

        return $filterArray;
    }


    /**
     * gets many-many-objects
     *
     * @param string $name
     * @param array|string $filter
     * @param array|string $sort
     * @param array|int $limit
     * @return ManyMany_DataObjectSet
     */
    public function getManyMany($name, $filter = null, $sort = null, $limit = null) {
        $name = trim(strtolower($name));

        // get info
        $relationShip = $this->getOwner()->getManyManyInfo($name);

        // check field
        $instance = $this->getOwner()->fieldGet($name);
        if(!$instance || !is_a($instance, "ManyMany_DataObjectSet")) {
            $instance = new ManyMany_DataObjectSet($relationShip->getTargetClass());
            $instance->setRelationENV($relationShip, $this->getOwner());

            $this->getOwner()->setField($name, $instance);

            if ($this->getOwner()->queryVersion == DataObject::VERSION_STATE) {
                $instance->setVersion(DataObject::VERSION_STATE);
            } else {
                $instance->setVersion(DataObject::VERSION_PUBLISHED);
            }
        }

        if(!$filter && !$sort && !$limit) {
            return $instance;
        }

        $version = clone $instance;
        $version->filter($filter);
        $version->sort($sort);
        $version->limit($limit);

        return $version;
    }

    /**
     * sets many-many-data
     *
     * @param string $name
     * @param array|DataObjectSet|object $value
     */
    public function setManyMany($name, $value) {
        $relationShipInfo = $this->getOwner()->getManyManyInfo($name);

        if (is_a($value, "DataObjectSet") && !is_a($value, "ManyMany_DataObjectSet")) {
            $instance = new ManyMany_DataObjectSet($relationShipInfo->getTargetClass());
            $instance->setRelationEnv($relationShipInfo, $this->getOwner());
            $instance->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
            $instance->addMany($value);

            $this->getOwner()->setField($name, $instance);

            return;
        }

        $this->setManyManyIDs($name, $value);
    }

    /**
     * sets many-many-ids
     * @param string $name
     * @param array $ids
     */
    public function setManyManyIDs($name, $ids) {
        $this->getManyMany($name)->setSourceData($ids);
    }
}
gObject::extend("DataObject", "ManyManyGetter");
