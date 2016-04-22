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
}
