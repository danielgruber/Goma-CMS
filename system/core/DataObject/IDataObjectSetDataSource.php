<?php
defined("IN_GOMA") OR die();

/**
 * interface for data fetcher for DataObjectSet.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
interface IDataObjectSetDataSource {
    public function getRecords($version, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array());

    /**
     * gets specific aggregate like max, min, count, sum
     *
     * @param string $version
     * @param string|array $aggregate
     * @param string $aggregateField
     * @param bool $distinct
     * @param array $filter
     * @param array $sort
     * @param array $limit
     * @param array $joins
     * @param array $search
     * @param array $groupby
     * @return mixed
     */
    public function getAggregate($version, $aggregate, $aggregateField = "*", $distinct = false, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array(), $groupby = array());
    public function getGroupedRecords($version, $groupField, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array());
    public function canFilterBy($field);
    public function canSortBy($field);
    public function DataClass();
    public function getInExpansion();
}

interface IDataObjectSetModelSource {
    public function createNew($data = array());
    public function getForm(&$form);
    public function getEditForm(&$form);
    public function getActions(&$form);
    public function DataClass();
    public function callExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null);
}
