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
    public function getAggregate($version, $aggregate, $aggregateField = "*", $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array(), $groupby = array());
    public function getGroupedRecords($version, $groupField, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array());
    public function canFilterBy($field);
    public function canSortBy($field);
}

interface IDataObjectSetModelSource {
    public function createNew();
    public function getForm(&$form);
    public function getEditForm(&$form);
}
