<?php defined("IN_GOMA") OR die();

interface argumentsQuery {
	public function argumentQuery($query, $version, $filter, $sort, $limit, $joins, $forceClasses);
}
interface argumentsSearchQuery {
	public function argumentSearchSQL($query, $searchQuery, $version, $filter, $sort, $limit, $join, $forceClasses);
}