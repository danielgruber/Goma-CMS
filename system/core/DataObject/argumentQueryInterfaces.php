<?php defined("IN_GOMA") OR die();

interface argumentsQuery {
	/**
	 * @param SelectQuery $query
	 * @param string $version
	 * @param array|string $filter
	 * @param array|string $sort
	 * @param array|string|int $limit
	 * @param array|string|int $joins
	 * @param bool $forceClasses if to only get objects of this type of every object from the table
	 */
	public function argumentQuery($query, $version, $filter, $sort, $limit, $joins, $forceClasses);
}
interface argumentsSearchQuery {
	/**
	 * @param SelectQuery $query
	 * @param string|array $searchQuery
	 * @param string $version
	 * @param array|string $filter
	 * @param array|string $sort
	 * @param array|string|int $limit
	 * @param array|string|int $join
	 * @param bool $forceClasses if to only get objects of this type of every object from the table
	 */
	public function argumentSearchSQL($query, $searchQuery, $version, $filter, $sort, $limit, $join, $forceClasses);
}