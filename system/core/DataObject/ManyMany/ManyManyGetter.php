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
class ManyManyGetter extends AbstractGetterExtension implements ArgumentsQuery
{

    const ID = "ManyManyGetter";

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
                $this->linkMethodWithInstance(self::ID, $key, "getManyMany", "Something got wrong wiring the ManyMany-Relationship.");
                $this->linkMethodWithInstance(self::ID, "set" . $key . "ids", "setManyManyIDs", "Something got wrong wiring the ManyMany-Relationship.");
                $this->linkMethodWithInstance(self::ID, "set" . $key, "setManyMany", "Something got wrong wiring the ManyMany-Relationship.");
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
                unset($filterArray[$key]);
                $filterArray[] = " EXISTS ( ".
                    $this->buildRelationQuery($relationShips[strtolower($key)], $version, $value, $forceClasses)->build()
                    ." ) ";
            } else if(strtolower(substr($key, -6)) == ".count" && isset($relationShips[strtolower(substr($key, 0, -6))])) {
                unset($filterArray[$key]);
                $filterArray[] = " (".
                    $this->buildRelationQuery($relationShips[strtolower(substr($key, 0, -6))], $version, array(), $forceClasses)->build("count(*)")
                    .") = " . $value;
            } else {
                if (is_array($value)) {
                    $filterArray[$key] = $this->factorOutFilter($filterArray[$key], $version, $forceClasses, $relationShips);
                }
            }
        }

        return $filterArray;
    }

    /**
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param string $version
     * @param array $filter
     * @param bool $forceClasses
     * @return SelectQuery
     */
    protected function buildRelationQuery($relationShip, $version, $filter, $forceClasses) {
        $target = $relationShip->getTargetClass();
        /** @var DataObject $targetObject */
        $targetObject = new $target();
        $query = $targetObject->buildExtendedQuery($version, $filter, array(), array(), array(
            array(
                DataObject::JOIN_TYPE => "INNER",
                DataObject::JOIN_TABLE => $relationShip->getTableName(),
                DataObject::JOIN_STATEMENT => $relationShip->getTableName() . "." . $relationShip->getTargetField() . " = " . $relationShip->getTargetTableName() . ".id",
                DataObject::JOIN_INCLUDEDATA => false
            )
        ), $forceClasses);
        $query->addFilter($relationShip->getTableName()  . "." . $relationShip->getOwnerField() . " = " . $this->getOwner()->baseTable . ".id");

        return $query;
    }

    /**
     * gets many-many-objects
     *
     * @param string $name
     * @param array|string $filter
     * @param array|string $sort
     * @return ManyMany_DataObjectSet
     */
    public function getManyMany($name, $filter = null, $sort = null) {
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

        if(!$filter && !$sort) {
            return $instance;
        }

        $version = clone $instance;
        $version->filter($filter);
        $version->sort($sort);

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

        if (is_a($value, "DataObjectSet")) {
            if(!is_a($value, "ManyMany_DataObjectSet")) {
                $instance = new ManyMany_DataObjectSet($relationShipInfo->getTargetClass());
                $instance->setVersion($this->getOwner()->queryVersion);
                $instance->setRelationEnv($relationShipInfo, $this->getOwner());
                $instance->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
                $instance->addMany($value);
            } else {
                $instance = $value;
            }

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
        if(is_a($ids, "DataObjectSet")) {
            $this->setManyMany($name, $ids);
        } else {
            $this->getManyMany($name)->setSourceData($ids);
        }
    }
}
gObject::extend("DataObject", "ManyManyGetter");
