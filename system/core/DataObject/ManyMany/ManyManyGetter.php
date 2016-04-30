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
}
gObject::extend("DataObject", "ManyManyGetter");
