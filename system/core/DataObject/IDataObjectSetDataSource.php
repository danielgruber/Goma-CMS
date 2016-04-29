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

    /**
     * gets records.
     *
     * @param string $version
     * @param array $filter
     * @param array $sort
     * @param array $limit
     * @param array $joins
     * @param array $search
     * @return ViewAccessableData[]|array
     */
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

    /**
     * @param string $version
     * @param string $groupField
     * @param array $filter
     * @param array $sort
     * @param array $limit
     * @param array $joins
     * @param array $search
     * @return ViewAccessableData[]|array
     */
    public function getGroupedRecords($version, $groupField, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array());

    /**
     * @param string $field
     * @return bool
     */
    public function canFilterBy($field);

    /**
     * @param string $field
     * @return bool
     */
    public function canSortBy($field);

    /**
     * @return string
     */
    public function DataClass();

    /**
     * @return string
     */
    public function getInExpansion();

    /**
     * @return string
     */
    public function table();

    /**
     * @return string
     */
    public function baseTable();
}

interface IDataObjectSetModelSource {
    /**
     * @param array $data
     * @return ViewAccessableData
     */
    public function createNew($data = array());

    /**
     * @param Form $form
     */
    public function getForm(&$form);

    /**
     * @param Form $form
     */
    public function getEditForm(&$form);

    /**
     * @param Form $form
     */
    public function getActions(&$form);

    /**
     * @return string
     */
    public function DataClass();
    public function callExtending($method, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null);
}
