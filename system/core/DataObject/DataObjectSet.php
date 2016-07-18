<?php defined("IN_GOMA") OR die();


/**
 * Basic class for getting Data as DataSet from DataBase. It implements all types of DataBase-Queriing and always needs a DataObject to query the DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5.2
 */
class DataObjectSet extends ViewAccessableData implements IDataSet {

	const ID = "DataObjectSet";

	const FETCH_MODE_CREATE_NEW = "fetch_create_new";
	const FETCH_MODE_EDIT = "fetch_mode_edit";

	/**
	 * how many items per page
	 *
	 * @var int
	 */
	protected $perPage = 10;

	/**
	 * the current page of this dataset
	 *
	 * @var int|null
	 */
	protected $page = null;

	/**
	 * sorting
	 */
	protected $sort;

	/**
	 * joins
	 */
	protected $join;

	/**
	 * filter
	 */
	protected $filter;

	/**
	 * for search
	 */
	protected $search = array();

	/**
	 * versioning
	 */
	protected $version;

	/**
	 * dataobject for this DataObjectSet
	 *
	 * @var IDataObjectSetDataSource
	 */
	protected $dbDataSource;

	/**
	 * model source.
	 *
	 * @var IDataObjectSetModelSource
	 */
	protected $modelSource;

	/**
	 * fetch-mode.
	 */
	protected $fetchMode;

	/**
	 * @var ArrayList
	 */
	protected $staging;

	/**
	 * @var array
	 */
	protected $protected_customised;

	/**
	 * cache for count.
	 *
	 * @var int|null
	 */
	protected $count;

	/**
	 * items.
	 *
	 * @var array|null
	 */
	protected $items;

	/**
	 * first-cache.
	 *
	 * @var ViewAccessableData
	 */
	private $firstCache;

	/**
	 * @var ViewAccessableData
	 */
	private $lastCache;

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * constructor
	 * @param string|IDataObjectSetDataSource|IDataObjectSetModelSource|array $class
	 * @param string|array $filter
	 * @param string|array $sort
	 * @param array $join
	 * @param string|array $search
	 * @param string|null $version
	 */
	public function __construct($class = null, $filter = null, $sort = null, $join = null, $search = null, $version = null) {
		parent::__construct();

		if(isset($class)) {
			$this->resolveSources($class);

			$this->filter($filter);
			$this->sort($sort);
			$this->join($join);
			$this->search($search);
			$this->setVersion($version);
		}

		$this->staging = new ArrayList();
		$this->protected_customised = $this->customised;
		$this->fetchMode = self::FETCH_MODE_EDIT;
	}

	/**
	 * clears cache.
	 */
	protected function clearCache() {
		$this->items = null;
		$this->count = null;
		$this->firstCache = null;
		$this->lastCache = null;
	}

	/**
	 * resolved sources.
	 *
	 * @param string|IDataObjectSetDataSource|IDataObjectSetModelSource|array $class
	 */
	protected function resolveSources($class) {
		if(is_a($class, "DataObjectSet")) {
			/** @var DataObjectSet $class */
			$this->setDbDataSource($class->getDbDataSource());
			$this->setModelSource($class->getModelSource());
		} else if(is_object($class)) {
			if(is_a($class, "IDataObjectSetDataSource")) {
				$this->setDbDataSource($class);
			}

			if(is_a($class, "IDataObjectSetModelSource")) {
				$this->setModelSource($class);
			}

			if(method_exists($class, "DataClass") && ClassInfo::exists($class->DataClass())) {
				$class = $class->DataClass();
			} else {
				return;
			}
		}

		if(is_array($class) && count($class) == 2) {
			if(is_a($class[0], "IDataObjectSetDataSource")) {
				$this->setDbDataSource($class[0]);
			}

			if(is_a($class[1], "IDataObjectSetModelSource")) {
				$this->setModelSource($class[1]);
			}
		} else

			if(is_string($class)) {
				if(ClassInfo::exists($class)) {
					if(method_exists($class, "getDbDataSource") && !isset($this->dbDataSource)) {
						$this->setDbDataSource(call_user_func_array(array($class, "getDbDataSource"), array($class)));
					}

					if(method_exists($class, "getModelDataSource") && !isset($this->modelSource)) {
						$this->setModelSource(call_user_func_array(array($class, "getModelDataSource"), array($class)));
					}

					if(!isset($this->dbDataSource) && !isset($this->modelSource)) {
						throw new InvalidArgumentException("Class " . $class . " does not integrate method getDbDataSource or getModelDataSource.");
					}
				} else {
					throw new InvalidArgumentException("Class " . $class . " does not exist.");
				}
			} else {
				throw new InvalidArgumentException("\$class must be either String or IDataObjectSetDataSource or IDataObjectSetModelSource or array of both.");
			}
	}

	/**
	 * @param IDataObjectSetDataSource $source
	 * @return $this
	 */
	public function setDbDataSource($source) {
		if(!is_a($source, "IDataObjectSetDataSource")) {
			throw new InvalidArgumentException("Argument must be type of IDataObjectSetDataSource.");
		}

		$this->dbDataSource = $source;
		$this->inExpansion = $source->getInExpansion();
		return $this;
	}

	/**
	 * @param IDataObjectSetModelSource $modelSource
	 * @return $this
	 */
	public function setModelSource($modelSource) {
		if(!is_a($modelSource, "IDataObjectSetModelSource")) {
			throw new InvalidArgumentException("Argument must be type of IDataObjectSetModelSource.");
		}

		$this->modelSource = $modelSource;
		return $this;
	}

	/**
	 * @param array $loops
	 * @return $this
	 */
	public function customise($loops = array())
	{
		$this->protected_customised = $loops;

		return parent::customise($loops);
	}

	/**
	 * @return IDataObjectSetDataSource
	 */
	public function getDbDataSource()
	{
		return $this->dbDataSource;
	}

	/**
	 * @return IDataObjectSetModelSource
	 */
	public function getModelSource()
	{
		return $this->modelSource;
	}

	/**
	 * @return string
	 */
	public function DataClass()
	{
		return isset($this->dbDataSource) ? $this->dbDataSource->DataClass() : (isset($this->modelSource) ? $this->modelSource->DataClass() : null);
	}

	/**
	 * sets the data and datacache of this set
	 * @deprecated
	 */
	public function setData($data = array()) {
		Core::Deprecate("2.0", "setFetchMode");
		if($data === array()) {
			$this->setFetchMode(self::FETCH_MODE_CREATE_NEW);
		} else {
			foreach($data as $record) {
				if(is_array($record)) {
					$this->staging->add($record);
				} else {
					throw new InvalidArgumentException("setData requires array of arrays. And It's marked as Deprecated.");
				}
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function getFetchMode()
	{
		return $this->fetchMode;
	}

	/**
	 * @param string $fetchMode
	 * @return $this
	 */
	public function setFetchMode($fetchMode)
	{
		if($fetchMode == self::FETCH_MODE_EDIT || $fetchMode == self::FETCH_MODE_CREATE_NEW) {
			$this->fetchMode = $fetchMode;

			$this->items = &$this->staging->ToArray();
			if($fetchMode == self::FETCH_MODE_CREATE_NEW) {
				$this->count = $this->staging->count();
			}
		} else {
			throw new InvalidArgumentException("Invalid fetchmode for DataObjectSet.");
		}

		return $this;
	}

	/**
	 * this function returns the data as an array
	 *
	 * @return array
	 */
	public function ToArray()
	{
		return array_merge((array) $this->items, $this->staging->ToArray());
	}

	/**
	 * gets query-version
	 */
	public function queryVersion() {
		return $this->version;
	}

	/**
	 * returns the first item
	 *
	 * @return DataObject|null
	 */
	public function first() {
		if(!isset($this->firstCache)) {
			$this->checkForPageUpdate();

			$start = $this->page === null ? 0 : $this->page * $this->perPage - $this->perPage;
			$range = $this->getRange($start, 1);
			$this->firstCache = $this->getConverted($range->first());
		}

		return $this->firstCache;
	}

	/**
	 * @deprecated
	 * @return DataObject|null
	 */
	public function getFirst() {
		Core::Deprecate(2.0, "first");
		return $this->first();
	}

	/**
	 * returns last item.
	 *
	 * @return DataObject|null
	 */
	public function last() {
		if(!isset($this->lastCache)) {
			$this->checkForPageUpdate();

			if($this->page === null || $this->page == $this->getPageCount()) {
				$this->lastCache = $this->getConverted($this->getRange($this->countWholeSet() - 1, 1)->first());
			} else {
				$index = $this->page * $this->perPage - 1;
				$this->lastCache = $this->getConverted($this->getRange($index, 1)->first());
			}
		}

		return $this->lastCache;
	}

	/**
	 * checks for page-update.
	 */
	protected function checkForPageUpdate() {
		if($this->page !== null && $this->getPageCount() < $this->page) {
			$this->page = $this->getPageCount();
		}
	}

	/**
	 * @return DataObject
	 */
	public function firstOrNew() {
		return $this->first() ? $this->first() : $this->modelSource->createNew();
	}

	/**
	 * gets a Range of items in a DataSet of this DataSet
	 * pagination is always ignored
	 *
	 * @param int $start
	 * @param int $length
	 * @return DataSet
	 */
	public function getRange($start, $length) {
		$set = new DataSet($this->getRecordsByRange($start, $length));
		$set->inExpansion = $this->inExpansion;
		return $set;
	}

	/**
	 * gets range within current page.
	 *
	 * @param int $start
	 * @param int $length
	 * @return DataSet
	 */
	public function getPaginatedRange($start, $length) {
		$start = $this->page === null ? $start : $this->page * $this->perPage - $this->perPage + $start;
		$length = min($length, $this->perPage);
		$set = new DataSet($this->getRecordsByRange($start, $length));
		$set->inExpansion = $this->inExpansion;
		return $set;
	}

	/**
	 * gets a Range of items as array of this DataSet
	 * pagination is always ignored
	 * @param int $start
	 * @param int $length
	 * @return array
	 */
	public function getArrayRange($start, $length) {
		return $this->getRecordsByRange($start, $length);
	}

	/**
	 * returns page-count.
	 *
	 * @return int
	 */
	public function getPageCount() {
		return $this->page === null ? 1 : ceil($this->countWholeSet() / $this->perPage);
	}

	/**
	 * returns count in set.
	 */
	public function count() {
		if($this->page === null) {
			return (int) $this->countWholeSet();
		}

		if($this->page < $this->getPageCount()) {
			return (int) $this->perPage;
		}

		if($this->getPageCount() == 0) {
			return 0;
		}

		return $this->countWholeSet() - ($this->getPageCount() - 1)  * $this->perPage;
	}

	/**
	 * count
	 *
	 * @name Count
	 * @access public
	 * @return int
	 */
	public function countWholeSet() {
		if(!isset($this->count)) {
			$this->count = (int) $this->dbDataSource()->getAggregate(
					$this->version, "count", "*", false,
					$this->getFilterForQuery(), array(), null,
					$this->getJoinForQuery(), $this->search) + $this->getStagingWithFilterAndSort()->count();
		}

		return $this->count;
	}

	/**
	 * @param string $field
	 * @return int
	 */
	public function CountDistinct($field) {
		if(!preg_match('/^[a-zA-Z\.0-9_\-]+$/', $field)) {
			throw new InvalidArgumentException("Field must have only letters, numbers and underscore.");
		}

		return (int) $this->dbDataSource()->getAggregate(
			$this->version, "count", $field, true,
			$this->getFilterForQuery(), array(), null,
			$this->getJoinForQuery(), $this->search) + $this->staging->count();
	}

	/**
	 * gets the maximum value of given field in this set.
	 *
	 * Attention: Does not support staging.
	 *
	 * @param string $field
	 * @return null|int
	 */
	public function Max($field) {
		return (double) $this->dbDataSource()->getAggregate(
			$this->version, "max", $field, false,
			$this->getFilterForQuery(), array(), null,
			$this->getJoinForQuery(), $this->search);
	}

	/**
	 * gets the maximum value of given field in this set + returns a count of all fields in this set as a
	 * comma-seperated-string. this is for use in caching.
	 *
	 * Attention: Does not support staging.
	 *
	 * @param string $field
	 * @return null|string
	 */
	public function MaxCount($field) {
		$data = $this->dbDataSource()->getAggregate(
			$this->version, array("max", "count"), $field, false,
			$this->getFilterForQuery(), array(), null,
			$this->getJoinForQuery(), $this->search);

		return $data["max"] . "," . $data["count"];
	}

	/**
	 * gets the minimum value of given field in this set.
	 *
	 * Attention: Does not support staging.
	 *
	 * @param string $field
	 * @return null
	 */
	public function Min($field) {
		return (double) $this->dbDataSource()->getAggregate(
			$this->version, "min", $field, false,
			$this->getFilterForQuery(), array(), null,
			$this->getJoinForQuery(), $this->search);
	}

	/**
	 * gets the sum value of given field in this set.
	 *
	 * Attention: Does not support staging.
	 *
	 * @name sum
	 * @access public
	 * @param string $field
	 * @return null
	 */
	public function Sum($field) {
		return (double) $this->dbDataSource()->getAggregate(
			$this->version, "Sum", $field, false,
			$this->getFilterForQuery(), array(), null,
			$this->getJoinForQuery(), $this->search);
	}

	/**
	 * rewind
	 */
	public function rewind() {
		$this->forceData();
		$this->position = 0;
	}

	/**
	 * gets the current value
	 *
	 * @name current
	 * @return mixed|ViewAccessableData
	 */
	public function current($position = null)
	{
		if(!isset($position))
			$position = $this->position;

		$this->forceData();

		if($position == 0 && $this->firstCache !== null) {
			$this->items[$position] = $this->firstCache;
		}

		if($position == count($this->items) - 1 && $this->lastCache !== null) {
			$this->items[$position] = $this->lastCache;
		}

		$this->items[$position] = $this->getConverted($this->items[$position]);

		return $this->items[$position];
	}

	/**
	 * check if data exists
	 */
	public function valid()
	{
		return isset($this->items[$this->position]);
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		$this->position++;
	}

	public function reset()
	{
		$this->position = 0;
		$this->forceData();
	}

	/**
	 * forces to have the data from the database
	 * @return $this
	 */
	public function forceData() {
		if(!isset($this->items)) {
			if($this->fetchMode == self::FETCH_MODE_CREATE_NEW) {
				$this->items = &$this->getStagingWithFilterAndSort()->ToArray();
			} else {
				if($this->page !== null && $this->getPageCount() < $this->page) {
					$this->page = $this->getPageCount();
				}

				$limit = array();
				if ($this->page !== null) {
					$startIndex = $this->page * $this->perPage - $this->perPage;
					$limit[0] = $startIndex;
					$limit[1] = $this->perPage;
				} else {
					$limit[0] = 0;
				}

				$offsetInsertLast = 1;
				if(!isset($limit[1])) {
					if(isset($this->count)) {
						if($this->lastCache) {
							$offsetInsertLast = 0;
							$limit[1] = $this->count - 1;
						} else {
							$limit[1] = $this->count;
						}
					} else {
						$limit[1] = PHP_INT_MAX;
					}
				}

				if(isset($this->firstCache)) {
					$limit[0]++;
					$limit[1]--;
				}
				$this->items = $this->getRecordsByRange($limit[0], $limit[1]);
				if(isset($this->firstCache)) array_unshift($this->items, $this->firstCache);
				if(isset($this->lastCache)) $this->items[count($this->items) - $offsetInsertLast] = $this->lastCache;

				if ($this->page === null) {
					$this->count = count($this->items);
				}
			}
		}

		return $this;
	}

	/**
	 * @return ArrayList
	 */
	public function getStagingWithFilterAndSort() {
		try {
			return $this->staging->filter((array)$this->filter)->sort($this->sort);
		} catch(Exception $e) {
			log_exception($e);
			return new ArrayList();
		}
	}

	/**
	 * filters the data
	 * @return $this
	 */
	public function filter() {
		$filter = call_user_func_array(array("DataSet", "getFilterFromArgs"), func_get_args());

		if(isset($filter) && $this->filter != $filter) {
			$this->filter = $filter;
			$this->clearCache();
		}
		return $this;
	}

	/**
	 * adds a filter
	 *
	 * @param
	 * @param
	 * @return $this
	 */
	public function addFilter() {
		$filter = call_user_func_array(array("DataSet", "getFilterFromArgs"), func_get_args());

		if(isset($filter)) {
			$this->filter = array_merge((array) $this->filter, (array) $filter);
			$this->clearCache();
		}
		return $this;
	}

	/**
	 * group by a specific field
	 * @param  string $field
	 * @return array
	 */
	public function groupBy($field) {
		return $this->dbDataSource()->getGroupedRecords($this->version, $field, $this->getFilterForQuery(), $this->getSortForQuery(), array(), $this->getJoinForQuery(), $this->search);
	}

	/**
	 * @param string $field
	 * @return DataSet
	 */
	public function getGroupedSet($field) {
		return new DataSet($this->groupBy($field));
	}

	/**
	 * adds a join
	 * @param string $join
	 * @return $this
	 */
	public function addJoin($join) {
		$this->join = array_merge((array)$this->join, (array)$join);
		$this->clearCache();
		return $this;
	}

	/**
	 * removes a join by given key
	 * @param string|int $key key in array
	 * @return $this
	 */
	public function removeJoin($key) {
		unset($this->join[$key]);
		$this->clearCache();
		return $this;
	}

	/**
	 * sets the variable join
	 * @param array $join
	 * @return $this
	 */
	public function join($join) {
		if(isset($join)) {
			$this->join = (array) $join;
			$this->clearCache();
		}
		return $this;
	}

	/**
	 * activates pagination
	 *
	 * @param int|null $page
	 * @param int|null $perPage
	 * @return $this
	 */
	public function activatePagination($page = null, $perPage = null) {
		$this->clearCache();
		if(isset($perPage) && $perPage > 0)
			$this->perPage = $perPage;

		if(isset($page) && RegexpUtil::isNumber($page) && $page > 0) {
			// first validate the data
			$pages = max(ceil($this->countWholeSet() / $this->perPage), 1);
			if($pages < $page) {
				$page = $pages;
			}

			$this->page = $page;
		}

		if(!isset($this->page)) {
			$this->page = 1;
		}

		return $this;
	}

	/**
	 * disables pagination.
	 */
	public function disablePagination() {
		$this->clearCache();
		$this->page = null;
		return $this;
	}

	/**
	 * resorts the data
	 *
	 * @return $this
	 * @example $list->sort('Name'); // default ASC sorting
	 * @example $list->sort('Name DESC'); // DESC sorting
	 * @example $list->sort('Name', 'ASC');
	 * @example $list->sort(array('Name', 'ASC'));
	 */
	public function sort() {
		$args = func_get_args();
		if(count($args) == 0 || $args[0] === null || $args[0] === array()){
			$this->sort = null;
			$this->clearCache();
			return $this;
		}
		if(count($args) == 1 && is_array($args[0]) && isset($args[0][0])) {
			$args = $args[0];
		}
		if(count($args)>2){
			throw new InvalidArgumentException('Sort takes zero, one or two arguments');
		}

		$columns = $types = array();
		if(is_string($args[0])) {
			if(substr(strtolower($args[0]), -4) == "desc") {
				$args[0] = substr($args[0], 0, -4);
				$types = array("desc");
			} else if(substr(strtolower($args[0]), -3) == "asc") {
				$args[0] = substr($args[0], 0, -3);
				$types = array("asc");
			} else {
				$types = array(isset($args[1]) && strtolower($args[1]) == "desc" ? "desc" : "asc");
			}
			$columns = array($args[0]);
		} else if(is_array($args[0])) {
			$columns = array_keys($args[0]);
			$types = array_values($args[0]);
		}

		foreach($columns as $column) {
			if (!$this->canSortBy($column)) {
				throw new InvalidArgumentException("can not sort by $column");
			}
		}

		$sort = array_combine($columns, $types);

		if($this->sort == $sort) {
			return $this;
		}

		$this->sort = $sort;
		$this->clearCache();

		return $this;
	}

	/**
	 * checks if we can sort by a specified field
	 *
	 * @param string $field
	 * @return bool
	 */
	public function canSortBy($field) {
		return $this->dbDataSource()->canSortBy($field);
	}

	/**
	 * checks if we can sort by a specified field
	 *
	 * @param $field
	 * @return bool
	 */
	public function canFilterBy($field) {
		return $this->dbDataSource()->canFilterBy($field);
	}

	/**
	 * sets version-type.
	 *
	 * @param    mixed $version type: "published"/"state"/"grouped"/false (get all records not grouped by recordid)/integer
	 * @return $this
	 */
	public function setVersion($version) {
		$this->version = $version;
		$this->clearCache();
		return $this;
	}

	/**
	 * returns the current version
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @param int $start
	 * @param int $length
	 * @return array
	 */
	protected function getRecordsByRange($start, $length)
	{
		if($start < 0) {
			if ($start + $length <= 0) {
				return array();
			} else {
				$length -= abs($start);
				$start = 0;
			}
		}

		if($this->fetchMode == self::FETCH_MODE_CREATE_NEW) {
			return $this->getStagingWithFilterAndSort()->getRange($start, $length)->ToArray();
		}

		$result = $this->getResultFromCache($start, $length);
		if ($result === null) {
			$result = $this->dbDataSource()->getRecords($this->version, $this->getFilterForQuery(), $this->getSortForQuery(), array($start, $length), $this->getJoinForQuery(), $this->search);
		}

		if (count($result) < $length) {
			$missing = $length - count($result);

			return array_merge($result, $this->getStagingWithFilterAndSort()->getRange(0, $missing)->ToArray());
		}

		return $result;
	}

	/**
	 * @param int $start
	 * @param int $length
	 * @return array|null
	 */
	protected function getResultFromCache($start, $length) {
		if($this->items !== null) {
			if ($this->page === null) {
				return array_slice($this->items, $start, $length);
			} else {
				$starting = $this->page * $this->perPage - $this->perPage;
				$pre = $start - $starting;

				if (count($this->items) < $this->perPage || count($this->items) >= $pre + $length) {
					return array_slice($this->items, $pre, $length);
				}
			}
		}

		if($this->count === 0) {
			return array();
		}

		return null;
	}

	/**
	 * search
	 *
	 * @return $this
	 */
	public function search($search) {
		if(isset($search)) {
			$this->search = $search;
			$this->clearCache();
		}
		return $this;
	}

	/**
	 * adds a new record to this set
	 * @param DataObject $record
	 * @param bool $write
	 * @return $this
	 */
	public function push($record, $write = false) {
		if(!gObject::method_exists($record, "writeToDBInRepo")) {
			throw new InvalidArgumentException("DataObjectSet::push requires DataObject as first argument.");
		}

		if(($record->id != 0 && $this->staging->find("id", $record->id)) || $this->staging->itemExists($record)) {
			throw new LogicException("You can't add a record to staging twice.");
		}

		foreach((array) $this->defaults as $key => $value) {
			if(empty($record->{$key}))
				$record->{$key} = $value;
		}

		$matchesFilter = $this->filter ? ArrayList::itemMatchesFilter($record, (array) $this->filter) : true;

		if($this->count !== null && $matchesFilter) {
			$this->count++;
		}

		$this->staging->add($record);

		if(($this->page === null || count($this->items) == $this->perPage)) {
			if(count($this->items) < $this->perPage || $matchesFilter) {
				if ($this->items !== null) {
					$this->items[] = $record;
				}

				$this->lastCache = $record;
			}
		} else {
			$this->clearCache();
		}

		if($write)
			$this->commitStaging();

		return $this;
	}

	/**
	 * alias for push
	 * @param mixed $item
	 * @param bool $write
	 * @return DataObjectSet
	 */
	public function add($item, $write = false) {
		return $this->push($item, $write);
	}

	/**
	 * adds a new record to this set
	 *
	 * @name addMany
	 * @access public
	 * @return array
	 */
	public function addMany($data) {
		$addedIDs = array();
		foreach($data as $record) {
			if(is_integer($record)) {
				$_data = DataObject::get_one($this->DataClass(), array("id" => $record));
				if($_data) {
					$this->add($_data);
					$addedIDs = $record;
				}
			} else {
				$this->add($record);
				$addedIDs = $record->ID;
			}
		}

		return $addedIDs;
	}

	/**
	 * converts the item to the right format
	 *
	 * @param Object|array|mixed $item
	 * @return object
	 */
	public function getConverted($item) {
		if(is_array($item)) {
			$object = $this->modelSource()->createNew($item);
		} else if(is_object($item)) {
			$object = $item;
		} else if(is_null($item)) {
			return null;
		} else {
			throw new InvalidArgumentException("\$item for getConverted must be either array or object.");
		}

		if(is_a($object, "DataObject")) {
			$object->queryVersion = $this->queryVersion();
		}

		if(is_object($object) && method_exists($object, "customise")) {
			$object->customise($this->protected_customised);
			return $object;
		} else {
			return $object;
		}
	}

	/**
	 * toString
	 * @return string
	 */
	public function __toString() {
		return "DataObjectSet {$this->classname}{".$this->count()."}";
	}

	/**
	 * bool - for IF in template
	 */
	public function bool() {
		return ($this->Count() > 0);
	}

	/**
	 * returns an array of the values of a specific field
	 *
	 * @param string $field
	 * @return array
	 */
	public function fieldToArray($field) {
		$this->forceData();
		$arr = array();
		foreach((array)$this->items as $record) {
			$arr[] = self::getItemProp($record, $field);
		}
		return $arr;
	}

	/**
	 * write to DB
	 * @param bool $forceInsert
	 * @param bool $forceWrite
	 * @param int $snap_priority
	 * @param null|IModelRepository $repository
	 * @throws DataObjectSetCommitException
	 */
	public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2, $repository = null) {
		$exceptions = array();
		$errorRecords = array();

		$repository = isset($repository) ? $repository : Core::repository();

		/** @var DataObject $record */
		foreach($this->staging as $record) {
			if(is_array($record)) {
				$record = $this->getConverted($record);
			}

			try {
				$record->writeToDBInRepo($repository, $forceInsert, $forceWrite, $snap_priority);

				$this->staging->remove($record);
			} catch(Exception $e) {
				$exceptions[] = $e;
				$errorRecords[] = $record;
			}
		}

		$this->clearCache();
		$this->fetchMode = DataObjectSet::FETCH_MODE_EDIT;

		if(count($exceptions) > 0) {
			throw new DataObjectSetCommitException($exceptions, $errorRecords, count($errorRecords) . " could not be written.");
		}

		$this->dbDataSource()->clearCache();
	}

	/**
	 * @deprecated
	 * @param bool $forceInsert
	 * @param bool $forceWrite
	 * @param int $snap_priority
	 * @throws DataObjectSetCommitException
	 */
	public function write($forceInsert = false, $forceWrite = false, $snap_priority = 2) {
		Core::Deprecate(2.0, "commitStaging");
		$this->commitStaging($forceInsert, $forceWrite, $snap_priority);
	}

	/**
	 * remove from stage.
	 * @param DataObject $record
	 */
	public function removeFromStage($record) {
		$this->staging->remove($record);

		if(isset($this->items)) {
			foreach ($this->items as $key => $item) {
				if ($item === $record) {
					unset($this->items[$key]);
				}
			}

			$this->items = array_values($this->items);
		}

		if($this->firstCache === $record) {
			$this->firstCache = null;
		}

		if($this->lastCache === $record) {
			$this->lastCache = null;
		}

		if(isset($this->count)) {
			$this->count--;
		}
	}

	/**
	 * @return ArrayList
	 */
	public function getStaging()
	{
		return $this->staging;
	}

	/**
	 * @return int|null
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * @param int $page
	 * @return DataObjectSet
	 */
	public function setPage($page) {
		return $this->activatePagination($page);
	}

	/**
	 * @return int
	 */
	public function getPerPage()
	{
		return $this->perPage;
	}

	/**
	 * sets pointer to last page
	 */
	public function goToLastPage() {
		$this->setPage($this->getPageCount());
		return $this;
	}

	/**
	 * returns if it has a page before
	 *
	 * @return bool
	 */
	public function isPageBefore() {
		return ($this->page > 1);
	}

	/**
	 * checks if there is a next page
	 *
	 * @return bool
	 */
	public function isNextPage() {
		return ($this->page < $this->getPageCount());
	}

	/**
	 * returns the page-number of the next page
	 *
	 * @return int
	 */
	public function nextPage() {
		if($this->page < $this->getPageCount()) {
			return $this->page + 1;
		} else {
			return $this->getPageCount();
		}
	}

	/**
	 * returns the page before
	 * @return int|null
	 */
	public function pageBefore() {
		if($this->page > 1) {
			return $this->page - 1;
		} else {
			return 1;
		}
	}

	/**
	 * @return boolean
	 */
	public function isPagination()
	{
		return $this->page !== null;
	}

	/**
	 * @param string|int $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		if(RegexpUtil::isNumber($offset)) {
			$this->forceData();

			if(isset($this->items[$offset])) {
				$this->items[$offset] = $this->getConverted($this->items[$offset]);
				return $this->items[$offset];
			}
			return null;
		}

		return parent::offsetGet($offset);
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		if(RegexpUtil::isNumber($offset)) {
			return ($offset < $this->count());
		}

		return parent::offsetExists($offset);
	}

	/**
	 * generates a form
	 *
	 * @param string $name
	 * @param bool $edit
	 * @param bool $disabled
	 * @param Request $request
	 * @param Controller $controller
	 * @param string|null|array|Closure $submission
	 * @return Form
	 */
	public function generateForm($name = null, $edit = false, $disabled = false, $request = null, $controller = null, $submission = null) {
		// if name is not set, we generate a name from this model
		if(!isset($name)) {
			$name = $this->getModelSource()->DataClass() . "_dataobjectset_new";
		}

		$form = new Form($controller, $name, array(), array(), array(), $request, $model = $this->createNewModel());
		if($disabled)
			$form->disable();

		// default submission
		$form->setSubmission(isset($submission) ? $submission : "submit_form");

		$form->addValidator(new DataValidator($model), "datavalidator");

		$form->add(new HiddenField("class_name", $model->DataClass()));

		foreach($this->defaults as $key => $value) {
			$form->add(new HiddenField($key, $value));
		}

		// render form
		if($edit) {
			$this->modelSource()->getEditForm($form);
		} else {
			$this->modelSource()->getForm($form);
		}

		$this->modelSource()->callExtending('getForm', $form, $edit);
		$this->modelSource()->getActions($form, $edit);
		$this->modelSource()->callExtending('getActions', $form, $edit);

		return $form;
	}


	/**
	 * gets available pages as array to render it in good pagination-style.
	 *
	 * @return array
	 */
	public function getPages() {
		return self::renderPages($this->getPageCount(), $this->page);
	}

	/**
	 * @return DataSet
	 */
	public function toDataSet() {
		return new DataSet($this->forceData()->ToArray());
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function __cancall($offset) {
		$loweroffset = trim(strtolower($offset));
		if($loweroffset == "current")
			return true;

		return parent::__cancall($offset);
	}

	/**
	 * @return IDataObjectSetDataSource
	 */
	protected function dbDataSource()
	{
		if(!isset($this->dbDataSource)) {
			throw new InvalidArgumentException("This DataObjectSet has no bound DataSource. It can't be used for queries.");
		}

		return $this->dbDataSource;
	}

	/**
	 * @return IDataObjectSetModelSource
	 */
	protected function modelSource() {
		if(!isset($this->modelSource)) {
			throw new InvalidArgumentException("This DataObjectSet has no bound ModelSource. It can't be used for creating new Models or converting arrays.");
		}

		return $this->modelSource;
	}

	/**
	 * creates new model and adds it with data.
	 * @param array $data
	 * @return DataObjectSet
	 */
	public function createNewModelAndAdd($data = array()) {
		return $this->add($this->createNewModel($data));
	}

	/**
	 * @param array $data
	 * @return ViewAccessableData
	 */
	protected function createNewModel($data = array())
	{
		return $this->modelSource()->createNew($data);
	}

	/**
	 * @param array $data
	 * @return ViewAccessableData
	 */
	public function createNew($data = array())
	{
		return $this->modelSource()->createNew($data);
	}

	/**
	 * @return DataObjectSet
	 */
	public function getObjectWithoutCustomisation()
	{
		/** @var DataObjectSet $object */
		$object = parent::getObjectWithoutCustomisation();
		$object->protected_customised = array();

		$data = array_merge(array("firstCache" => $this->firstCache, "lastCache" => $this->lastCache), (array) $this->items);
		/** @var ViewAccessableData $record */
		foreach($this->protected_customised as $key => $val) {
			foreach ($data as $id => $record) {
				if ($record !== null && isset($record->customised) && isset($record->customised[$key]) && $record->customised[$key] == $val) {
					if(is_string($id)) {
						$object->{$id} = clone $record;
						unset($object->{$id}->customised[$key]);
					} else {
						$object->items[$id] = clone $record;
						unset($object->items[$id]->customised[$key]);
					}
				}
			}
		}

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getFilterForQuery()
	{
		return $this->filter;
	}

	/**
	 * @return array
	 */
	protected function getSortForQuery() {
		return $this->sort;
	}

	/**
	 * @return array
	 */
	protected function getJoinForQuery() {
		return $this->join;
	}

	/**
	 * returns starting item-count, ending item-count and page
	 */
	public function getPageInfo() {
		if($this->page !== null) {
			$end = $this->page * $this->perPage;
			if($this->count() < $end) {
				$end = $this->count();
			}
			return array("start" => $this->page * $this->perPage - $this->perPage, "end" => $end, "whole" => $this->countWholeSet());
		}
		return false;
	}

	/**
	 * finds first matching record for key and value in this DataObjectSet.
	 *
	 * @param string $name
	 * @param string $value
	 * @param bool $caseInsensitive
	 * @return DataObject|null
	 */
	public function find($name, $value, $caseInsensitive = false) {
		if($this->fetchMode == self::FETCH_MODE_CREATE_NEW) {
			return $this->getStagingWithFilterAndSort()->find($name, $value, $caseInsensitive);
		} else if(($this->items && $this->page === null)) {
			foreach((array) $this->items as $item) {
				if($caseInsensitive && strtolower(self::getItemProp($item, $name)) == strtolower($value)) {
					return $item;
				} else if(self::getItemProp($item, $name) == $value) {
					return $item;
				}
			}
			return null;
		} else {
			$set = clone $this;
			$set->addFilter(array(
				$name => !$caseInsensitive ? $value : array("LIKE", $value)
			));
			return $set->first() ? $set->first() : $this->getStagingWithFilterAndSort()->find($name, $value, $caseInsensitive);
		}
	}

	/**
	 * @return $this
	 */
	public function setModifyAllMode() {
		if($this->page !== null) {
			throw new LogicException("Modification-Mode requires to have no pagination.");
		}

		if($this->fetchMode == self::FETCH_MODE_EDIT) {
			$this->forceData();
			$this->items = array_map(array($this, "getConverted"), $this->items);

			$this->staging->merge($this->items);
			$this->setFetchMode(self::FETCH_MODE_CREATE_NEW);
		}

		return $this;
	}
}

class DataObjectSetCommitException extends GomaException {
	/**
	 * exceptions.
	 *
	 * @var Exception[]
	 */
	public $exceptions;

	/**
	 * @var DataObject[]
	 */
	public $records;

	protected $standardCode = ExceptionManager::DATAOBJECTSET_COMMIT;

	/**
	 * DataObjectSetCommitException constructor.
	 * @param Exception[] $exceptions
	 * @param DataObject[] $records
	 * @param string $message
	 * @param null|int $code
	 * @param null|Exception $previous
	 */
	public function __construct($exceptions, $records, $message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->exceptions = $exceptions;
		$this->records = $records;
	}
}
