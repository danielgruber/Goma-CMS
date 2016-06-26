<?php
defined("IN_GOMA") OR die();

/**
 * Describe your class
 *
 * @package Goma
 *
 * @author D
 * @copyright 2016 D
 *
 * @version 1.0
 */
interface SortableDataObjectSet {
    /**
     * @return bool
     */
    public function canSortSet();

    /**
     * moves item to given position.
     *
     * @param DataObject $item
     * @param int $position
     * @return mixed
     */
    public function move($item, $position);

    /**
     * sets sort by array of ids.
     *
     * @param int[]
     */
    public function setSortByIdArray($ids);

    /**
     * uasort.
     *
     * @param Callable
     */
    public function sortCallback($callback);
}