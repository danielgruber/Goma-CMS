<?php
/**
  * DataSet: for multiple viewaccessable-data records or DataObject records with all in one set without lazy loading, so it's poor performance for much data
  * DataObjectSet: lazy-loading DataSet, so loads Data from DB on demand, so better Performance
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  *********
  * last modified: 15.12.2011
  * $Version: 005
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class DataSet extends ViewAccessAbleData implements CountAble {
	/**
	 * pagination-attributes
	*/
	
	/**
	 * if to use pages in this dataset
	 *@name pages
	 *@param bool
	*/
	protected $pagination = false;
	/**
	 * how many items per page
	 *
	 *@name perPage
	 *@access public
	*/
	protected $perPage = 10;
	/**
	 * the current page of this dataset
	 *
	 *@name page
	 *@access public
	*/
	public $page = null;
	/**
	 * data cache, we will store all information here, too
	 *
	 *@name dataCache
	 *@access protected
	*/
	protected $dataCache = array();
	
	/**
	 * defaults
	 *
	 *@name defaults
	 *@access public
	*/
	public $defaults = array();
	
	/**
	 * construction
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($set = array()) {
		parent::__construct();
		
		/* --- */
		
		if(isset($set)) {
			$this->data = array_values((array)$set);
			$this->reRenderSet();
		}
	}
	
	/**
	 * groups dataset
	 *
	 *@name groupby
	 *@access public
	*/
	public function groupBy($field) {
		$set = array();
		foreach($this->data as $dataobject) {
			$key = $dataobject[$field];
			if($key !== null) {
				if(!isset($set[$key]))
					$set[$key] = new DataSet();
				
				$set[$key]->push($dataobject);
			}
		}
		return $set;
	}
	/**
	 * getGroupedSet
	 *
	 *@name getGroupedSet
	 *@access public 
	*/
	public function getGroupedSet($field) {
		return new DataSet($this->groupBy($field));
	}
	
	/**
	 * returns the number of records in this set
	 *
	 *@name Count
	 *@access public
	*/
	public function Count() {
		return count($this->data);
	}
	
	/**
	 * deprecated method
	 *
	 *@name _count
	 *@access public
	*/
	public function _count() {
		Core::Deprecate(2.0, "".$this->class."::Count");
		return $this->Count();
	}
	
	
	/**
	 * current sort field
	 *
	 *@access protected
	*/
	protected $sortField;
	
	/**
	 * resorts the data
	 *
	 *@name sort
	 *@access public
	 *@param string - column
	 *@param string - optional - type
	*/
	public function sort($column, $type = "ASC") {
		switch($type) {
			case "DESC":
				$type = "DESC";
			break;
			default:
				$type = "ASC";
			break;
		}
		$this->sortField = $column;
		if($type == "DESC")
			uasort($this->data, array($this, "sortDESCHelper"));
		else
			uasort($this->data, array($this, "sortASCHelper"));
		
		$this->reRenderSet();
		return $this;
	}
	
	
	
	/**
	 * helper for Desc-sort
	 *
	 *@name sortDESCHelper
	 *@access public - I think it need to be public
	*/
	public function sortDESCHelper($a, $b) {
		if(isset($b[$this->sortField], $a[$this->sortField]))
   	 		return strcmp($b[$this->sortField], $a[$this->sortField]);
   	 	
   	 	return 0;
	}
	/**
	 * helper for ASC-sort
	 *
	 *@name sortASCHelper
	 *@access public - I think it need to be public
	*/
	public function sortASCHelper($a, $b) {
		if(isset($b[$this->sortField], $a[$this->sortField]))
			return strcmp($a[$this->sortField], $b[$this->sortField]);
		
		return 0;
	}
	
	
	
	/**
	 * adds a item to this set
	 *
	 *@name push
	 *@access public
	*/
	public function push($item) {
		if(is_array($this->data))
			array_push($this->data, $item);
		else
			$this->data = array($item);
			
		$this->reRenderSet();
		return true;
	}
	
	/**
	 * removes the last item of the set and returns it
	 *
	 *@name pop
	 *@access public
	*/
	public function pop() {
		$data = array_pop($this->data);
		$this->reRenderSet();
		return $data;
	}
	
	/**
	 * removes the first item of the set and returns it
	 *
	 *@name shift
	 *@access public
	*/
	public function shift() {
		$data = array_shift($this->data);
		$this->reRenderSet();
		return $data;
	}
	
	/**
	 * this returns whether this rentry is the last or not
	 *@name last
	 *@access public
	*/
	public function last()
	{	
		return ($this->position + 1 == count($this->data));
	}
	
	
	
	/**
	 * returns the first item
	 *@name first
	 *@access public
	*/
	public function first()
	{	
		if(isset($this->data[key($this->data)])) {
			$data = $this->getConverted($this->data[key($this->data)]);
			$this->data[key($this->data)] = $data;
			return $data;
		} else
			return null;
	}
	/**
	 * returns current position
	*/
	public function position() {
		return $this->position;
	}
	/**
	 * returns if this is a highlighted one
	 *
	 *@name highlight
	 *@access public
	*/
	public function highlight() {
		$r = ($this->position + 1) % 2;
		return ($r == 0);
	}
	/**
	 * returns if this is a white one
	 *
	 *@name white
	 *@access public
	*/
	public function white() {
		return (!$this->highlight());
	}
	/**
	 * make the functions on top to variables, for example $this.white
	*/
	public function getWhite() {
		return $this->white();
	}
	public function getHighlight() {
		return $this->highlight();
	}
	public function getFirst() {
		return $this->first();
	}
	public function getPosition() {
		return $this->position;
	}
	
	/**
	 * iterator
	 * this extends this dataobject to use foreach on it
	 * @link http://php.net/manual/en/class.iterator.php
	*/
	/**
	 * this var is the current position
	 *@name position
	 *@access protected
	*/
	protected $position = 0;
	/**
	 * rewind $position to 0
	 *@name rewind
	*/
	public function rewind()
	{
			reset($this->data);
			$this->position = key($this->data);
	}
	/**
	 * check if data exists
	 *@name valid
	*/
	public function valid()
	{	
		
		return ($this->position >= key($this->data) && $this->position < count($this->data));
	}
	/**
	 * gets the key
	 *@name key
	*/
	public function key()
	{
		return $this->position;
	}
	/**
	 * gets the next one
	 *@name next
	*/
	public function next()
	{
		$this->position++;
	}
	/**
	 * gets the current value
	 *@name current
	*/
	public function current()
	{
		$data = $this->getConverted($this->data[$this->position]);#
		$this->data[$this->position] = $data;
		return $data;
	}
	
	/**
	 * sets the position of the array
	 *
	 *@name setPosition
	 *@access public
	*/
	public function setPosition($pos) {
		if($pos < count($this->data) && $pos > -1) {
			$this->position = $pos;
		}
		return $this->current();
	}
	
	/**
	 * gets a Range of items in a DataSet of this DataSet
	 * pagination is always ignored
	 *
	 *@name getRange
	 *@access public
	 *@return DataSet
	*/
	public function getRange($start, $length) {
		$set = new DataSet();
		for($i = $start; $i < ($start + $length); $i++) {
			if(isset($this->dataCache[$i])) 
				$set->push($this->dataCache[$i]);
		}
		return $set;
	}
	
	/**
	 * gets a Range of items as array of this DataSet
	 * pagination is always ignored
	 *
	 *@name getArrayRange
	 *@access public
	 *@return array
	*/
	public function getArrayRange($start, $length) {
		$set = array();
		for($i = $start; $i < ($start + $length); $i++) {
			if(isset($this->dataCache[$i])) 
				$set[] =& $this->dataCache[$i];
		}
		return $set;
	}
	
	/**
	 * activates pagination
	 *
	 *@name activatePagination
	 *@access public
	*/
	public function activatePagination($page = null) {
		if(isset($page)) {
			$this->page = $page;
		}
		if(!isset($this->page)) {
			$this->page = 1;
		}
		$this->pagination = true;
		$this->reRenderSet();
	}
	
	/**
	 * alias for activatePagination
	 *
	 *@name activatePagination
	 *@access public
	*/
	public function activatePages($page = null) {
		$this->activatePagination($page);
	}
	
	/**
	 * disables pagination
	 *
	 *@name disablePagination
	 *@access public
	*/
	public function disablePagination() {
		$this->pagination = true;
		$this->reRenderSet();
	}
	
	/**
	 * remakes the variable currentSet for pagination
	 *
	 *@name reRenderSet
	 *@access public
	*/	
	public function reRenderSet() {
		if($this->pagination) {
			$this->dataCache = (array) $this->dataCache + (array) $this->data;
			$this->data = $this->getArrayRange($this->page * $this->perPage - $this->perPage, $this->perPage);
			reset($this->data);
		}
	}
	/**
	 * sets the Page
	 *
	 *@name setPage
	 *@access public
	 *@param int - page
	 *@param int - per page
	*/
	public function setPage($page = null, $perPage = null) {
		if(isset($page)) $this->page = $page;
		if(isset($perPage)) $this->perPage = $perPage;
		$this->reRenderSet();
	}
	
	/**
	 * gets available pages
	 *
	 *@name getPages
	 *@access public
	*/
	public function getPages() {
		$pages = ceil($this->Count() / $this->perPage);
		return $this->renderPages($pages, $this->page);
	}
	/**
	 * get an array of pages by given pagecount
	 *
	 @name renderPages
	 *@access public
	 *@param int - pagecount
	 *@param int - current page
	*/
	public function renderPages($pagecount, $currentpage = 1) {
		if($pagecount < 2) {
			return array(1 => array(
				"page" 	=> 1,
				"black"	=> true
			));
		} else {
			$data = array();
			if($pagecount < 8) {
				for($i = 1; $i <= $pagecount; $i++) {
					$data[$i] = array(
						"page" 	=> ($i),
						"black"	=> ($i == $currentpage)
					);
				}
			} else {
				for($i = 1; $i <= $pagecount; $i++) {
					if($i < 3 || ($i > $currentpage - 3 && $i < $currentpage + 3) || $i > $pagecount - 3) {
						$data[$i] = array(
							"page" 	=> ($i),
							"black"	=> ($i == $currentpage)
						);
						$lastDots = false;
					} else if(!$lastDots && (($i > 2 && $i < ($currentpage - 2)) || ($i < ($pagecount - 2) && $i > ($currentpage + 2)))) {
						$data[$i] = array(
							"page" 	=> "...",
							"black" => true
						);
						$lastDots = true;
					}
				}
			}
		}
	}
	
	/**
	 * for first records
	*/
	
	public function getOffset($offset, $args = array()) {
		
		if(strtolower($offset) == "count") {
			return $this->Count();
		} else 
		if(Object::method_exists($this->class, $offset) || parent::__canCall($offset, $args)) {
			return parent::getOffset($offset, $args);
		} else {
			if(is_object($this->first())) {
				return $this->first()->getOffset($offset, $args);
			}
		}
	}
	
	public function __cancall($offset) {
		if($offset == "current")
			return true;

		if(strtolower($offset) == "count")
			return true;
		
		return ((Object::method_exists($this->class, $offset) || parent::__cancall($offset)) || (is_object($this->first()) && Object::method_exists($this->first(), $offset)));
	}
	
	public function __set($key, $value) {
		$name = strtolower(trim($key));
		// unset cache
		unset($this->viewcache["_" . $name]);
		unset($this->viewcache["1_" . $name]);
			
		if(Object::method_exists($this->class, "set" . $key)) {
			return call_user_func_array(array($this, "set" . $key), array($value));
		}
		
		if(is_object($this->first())) {
			return $this->first()->__set($key, $value);
		}
		return false;
	}
	
	/**
	 * converts the item to the right format
	 *
	 *@name getConverted
	 *@access protected
	 *@param various - data
	*/
	public function getConverted($item) {
		if(is_array($item)) {
			if(isset($item["class_name"]) && ClassInfo::exists($item["class_name"]))
				return new $item["class_name"]($item);
			else
				return new ViewAccessableData($item);
		} else {
			return $item;
		}
	}
	
	public function makeObject($offset, $data, $cachename = null) {
		if(parent::__cancall($offset)) {
			return parent::makeObject($offset, $data, $cachename);
		} else {
			if(is_object($this->first())) {
				return $this->first()->makeObject($offset, $data, $cachename);
			}
		}
	}
	
	
	
}



class DataObjectSet extends DataSet {
	
	/**
	 * some other props
	*/
	/**
	 * filter for this dataset
	 *
	 *@name filter
	 *@access protected
	*/
	protected $filter = array();
	/**
	 * sorting
	 *
	 *@name sort
	 *@access protected
	*/
	protected $sort;
	/**
	 * limits
	 *
	 *@name limit
	 *@access protected
	*/
	protected $limit;
	/**
	 * joins
	 *
	 *@name join
	 *@access protected
	*/
	protected $join;
	/**
	 * for search
	 *
	 *@name search
	 *@access protected
	*/
	protected $search = array();
	/**
	 * versioning
	 *
	 *@name version
	 *@access protected
	*/
	protected $version;
	
	/**
	 * Object for the DataBase Connection with the following methods:
	 *
	 * - getRecords
	 *
	 *@name DataBaseObject
	 *@access public
	*/
	public $DataBaseObject;
	
	/**
	 * count of the data in this set
	 *
	 *@name count
	 *@access protected
	*/
	protected $count;

	/**
	 * dataobject for this DataObjectSet
	 *
	 *@name dataobject
	 *@access protected
	*/
	public $dataobject;
	
	/**
	 * data
	*/
	protected $data = null;
	/**
	 * controller of this dataobjectset
	 *
	 *@name controller
	*/
	public $controller = "";
	
	/**
	 * constructor
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($class = null,$filter = array(), $sort = array(), $limit = array(), $join = array(), $search = array(), $version = null) {
		parent::__construct(null);
		
		if(isset($class)) {
			if(is_a($class, "DataObjectSet"))
				$class = $class->dataobject;
			
			$this->dataobject = Object::instance($class);
			if($this->dataobject->controller != "")
				$this->controller = $this->dataobject->controller;
			
			$this->filter($filter);
			$this->sort = $sort;
			$this->limit($limit);
			$this->join($join);
			$this->search($search);
			$this->setVersion($version);
		}
	}
	
	/**
	 * this function returns the data as an array
	 *@name ToArray
	 *@access public
	 *@param array - extra fields, which are not in database
	*/
	public function ToArray($additional_fields = array())
	{
			$data = array();
			foreach((array) $this->data as $record) {
				if(is_object($record)) {
					$data[] = $record->toArray($additional_fields);
				} else {
					$data[] = $record;
				}
			}
			return $data;
	}
	/**
	 * generates an array, where the value is a given field
	 *
	 *@name fieldToArray
	 *@access public
	 *@param string - field
	*/
	public function fieldToArray($field = "id") {
		
		$arr = array();
		foreach((array)$this->data as $record) {
			$arr[] = $record[$field];
		}
		unset($record);
		return $arr;
	}
	
	/**
	 * queries the db for records by given range
	 * the data will be stored in the data-var and given back
	 *
	 *@name getRecordsByRange
	 *@access protected
	 *@param int - start
	 *@param int - length
	 *@return array
	*/
	protected function getRecordsByRange($start, $length) {
		if(PROFILE) Profiler::mark("DataObjectSet::getRecordsByRange");
		
		if(isset($this->limits[0], $this->limits[1])) {
			if(($this->limits[0] + $this->limits[1]) <= $start) {
				if(PROFILE) Profiler::unmark("DataObjectSet::getRecordsByRange");
				return array();
			} else if(($this->limits[0] + $this->limits[1]) < ($start + $length)) {
				$length = ($this->limits[0] + $this->limits[1]) - $start;
			}
		}
		
		$data = array();
		for($i = $start; $i < ($start + $length); $i++) {
			if(isset($this->dataCache[$i])) {
				$data[$i] =& $this->dataCache[$i];
			} else {
				$start = $i;
				$length = $length - $i + $start;
				break;
			}
		}
		
		if($length > 0) {
			$count = $start;
			foreach($this->dataobject->getRecords($this->version, $this->filter, $this->sort, array($start, $length), $this->join, $this->search) as $record) {
				$data[$count] = $record;
				$count++;
				unset($record);
			}
			$this->dataCache = $this->dataCache + $data;
			
			if(PROFILE) Profiler::unmark("DataObjectSet::getRecordsByRange");
			return $data;
		} else {
			if(PROFILE) Profiler::unmark("DataObjectSet::getRecordsByRange");
			return $data;
		}
	}
	
	/**
	 * returns the first item
	 *@name first
	 *@access public
	*/
	public function first($forceObject = true)
	{	
			$this->forceData();
			
			if(is_array($this->data) && count($this->data) > 0 && isset($this->data[key($this->data)])) {
				$data = $this->getConverted($this->data[key($this->data)]);
				$this->data[key($this->data)] = $data;
				return $data;
			} else if($forceObject) {
				return $this->dataobject;
			} else {
				return false;
			}
	}
	
	/**
	 * gets a Range of items in a DataSet of this DataSet
	 * pagination is always ignored
	 *
	 *@name getRange
	 *@access public
	 *@return DataSet
	*/
	public function getRange($start, $length) {
		return new DataSet($this->getRecordsByRange($start, $length));
	}
	
	/**
	 * gets a Range of items as array of this DataSet
	 * pagination is always ignored
	 *
	 *@name getArrayRange
	 *@access public
	 *@return array
	*/
	public function getArrayRange($start, $length) {
		return $this->getRecordsByRange($start, $length);
	}
	/**
	 * count
	 *
	 *@name Count
	 *@access public
	*/
	public function Count() {
		if(isset($this->count)) {
			return $this->count;
		} else if(count($this->data) > 0 && (($this->page == 1 && count($this->data) < $this->perPage) || !$this->pagination)) {
			$this->count = count($this->data);
			return $this->count;
		} else {
			$data = $this->dataobject->getAggregate($this->version, 'count(*) as count', $this->filter, array(), $this->limit, $this->join, $this->search);
			if(isset($data[0]["count"])) {
				$this->count = $data[0]["count"];
				return $this->count;
			} else {
				return null;
			}
		}
	}
	
	/**
	 * rewind
	 *
	 *@name rewind
	 *@access public
	*/
	public function rewind() {
		$this->forceData();
		parent::rewind();
	}
	
	/**
	 * gets the current value
	 *@name current
	*/
	public function current()
	{
		$this->forceData();
		if(isset($this->data[$this->position]))
			$data = $this->data[$this->position];
		else {
			// get next range
			$this->data = $this->getRecordsByRange($this->position, $this->perPage);
			$data = $this->data[$this->position];
		}
		$data = $this->getConverted($data);		
		
		if(is_object($data) && is_a($data, "viewaccessabledata"))
			$data->dataSetPosition = $this->position;
		
		return $data;
	}
	
	/**
	 * forces to have the data from the database
	 *
	 *@name forceData
	 *@access public
	 *@param numeric - position
	*/
	public function forceData($position = null) {
		if($this->pagination) {
			if(!isset($this->data)) {
				$this->reRenderSet();
			}
		} else {
			if(!isset($this->data) && isset($this->dataobject)) {
				$this->data = $this->dataobject->getRecords($this->version, $this->filter, $this->sort, $this->limit, $this->join, $this->search);
				$this->reRenderSet();
			}
		}
		
		return $this;
	}
	
	
	/**
	 * check if data exists
	 *@name valid
	*/
	public function valid()
	{
		$this->forceData();
		return parent::valid();
	}
	
	/**
	 * filters the data
	 *
	 *@name filter
	 *@access public 
	*/
	public function filter($filter) {
		$this->filter = $filter;
		$this->purgeData();
		return $this;
	}
	/**
	 * adds a filter
	 *
	 *@name addFilter
	 *@access public
	*/
	public function addFilter($filter) {
		$this->filter = array_merge((array) $this->filter, $filter);
		$this->purgeData();
		return $this;
	}
	/**
	 * group by a specific field
	 *
	 *@name groupBy
	 *@access public
	*/
	public function groupBy($field) {
		return $this->dataobject->getGroupedRecords($this->version, $field, $this->filter, $this->sort, $this->limit, $this->join, $this->search);
	}
	/**
	 * purges current data from this set
	 *
	 *@name purgeData
	 *@access protected
	*/
	protected function purgeData() {
		$this->data = null;
		$this->count = null;
		$this->viewcache = null;
		$this->dataCache = array();
	}
	/**
	 * adds a join
	 *
	 *@name addJoin
	 *@access public
	*/
	public function addJoin($join) {
		$this->join = array_merge((array)$this->join, (array)$join);
		$this->purgeData();
		return $this;
	}
	/**
	 * removes a join by given key
	 *
	 *@name removeJoin
	 *@access public
	*/
	public function removeJoin($key) {
		unset($this->join[$key]);
		$this->purgeData();
		return $this;
	}
	/**
	 * sets the variable join
	 *
	 *@name join
	 *@access public
	*/
	public function join($join) {
		$this->join = $join;
		$this->purgeData();
		return $this;
	}
	
	/**
	 * sets limits
	 *
	 *@name limit
	 *@access public
	*/
	public function limit($limit) {
		if(!isset($limit) || count($limit) == 0)
			return $this;
		
		if(is_array($limit)) {
			$limit = array_values($limit);
			if(isset($limit[0], $limit[1])) {
				$this->limit = $limit;
			} else if($limit[0]) {
				$this->limit = array(0, $limit[0]);
			} else {
				return $this;
			}
		} else if($this->limit) {
			$this->limit = array(0, $limit[0]);
		} else {
			return $this;
		}
		$this->purgeData();
		return $this;
	}
	/**
	 * resorts the data
	 *
	 *@name sort
	 *@access public
	 *@param string - column
	 *@param string - optional - type
	*/
	public function sort($column, $type = "ASC") {
		switch($type) {
			case "DESC":
				$type = "DESC";
			break;
			default:
				$type = "ASC";
			break;
		}
		
		if($this->sort["field"] == $column && $this->sort["type"] == $type) {
			return $this;
		}
		
		$this->sort = array("field" => $column, "type" => $type);
		$this->purgeData();
		
		return $this;
	}
	/**
	 * sets version-type
	 *
	 *@name version
	 *@access public
	*/
	public function setVersion($version) {
		$this->version = $version;
		$this->purgeData();
		return $this;
	}
	/**
	 * search
	 *
	 *@name search
	 *@access public
	*/
	public function search($search) {
		$this->search = $search;
		$this->purgeData();
		return $this;
	}
	
	/**
	 * adds a new record to this set
	 *
	 *@name add
	 *@access public
	*/
	public function add(DataObject $record) {
		foreach($this->filter as $key => $value) {
			$record[$key] = $value;
		}
		
		foreach($this->defaults as $key => $value) {
			if(empty($record[$key]))
				$record[$key] = $value;
		}
		
		array_push($this->data, $record);
		$this->reRenderSet();
		return true;
	}
	
	/**
	 * converts the item to the right format
	 *
	 *@name getConverted
	 *@access protected
	 *@param various - data
	*/
	public function getConverted($item) {
		if(is_array($item)) {
			if(isset($item["class_name"]) && ClassInfo::exists($item["class_name"]))
				return new $item["class_name"]($item);
			else
				return new $this->dataobject->class ($item);
		} else {
			return $item;
		}
	}
	
	
	/**
	 * gets the controller
	 *
	 *@name controller
	 *@access public
	*/
	public function controller($controller = null) {
		
		
		if(isset($controller)) {
			$this->controller = clone $controller;
			$this->controller->model_inst = $this;
			$this->controller->model = $this->dataobject->class;
			return $this->controller;
		}
		
		if(is_object($this->controller))
		{
			return $this->controller;
		}
		
		/* --- */
		
		if($this->controller != "")
		{
				$this->controller = new $this->controller;
				$this->controller->model_inst = $this;
				$this->controller->model = null;
				return $this->controller;
		} else {
			
			if(ClassInfo::exists($this->dataobject->class . "controller"))
			{
					$c = $this->dataobject->class . "controller";
					$this->controller = new $c;
					$this->controller->model_inst = $this;
					$this->controller->model = null;
					return $this->controller;
			} else {
				if(ClassInfo::getParentClass($this->dataobject->class) != "dataobject") {
					$parent = $this->dataobject->class;
					while(($parent = ClassInfo::getParentClass($parent)) != "dataobject") {
						if(!$parent)
							return false;
						
						if(ClassInfo::exists($parent . "controller")) {
							$c = $parent . "controller";
							$this->controller = new $c;
							$this->controller->model_inst = $this;
							$this->controller->model = null;
							return $this->controller;
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * generates a form
	 *
	 *@name form
	 *@access public
	*/
	public function form() {
		return $this->controller()->form();
	}
	/**
	 * generates a form
	 *
	 *@name renderForm
	 *@access public
	*/
	public function renderForm() {
		return $this->controller()->renderForm();
	}
		
	/**
	 * toString
	 *
	 *@name toString
	 *@access public
	*/
	public function __toString() {
		if($controller = $this->controller()) {
			if($controller->template != "")
				return $controller->index();
			else
				return false;
		}
		return "controller not found";
	}
	
	/**
	 * bool - for IF in template
 	 *
	 *@name toBool
	 *@access public
	*/
	public function bool() {
		return ($this->Count() > 0);
	}
	
	/**
	 * write to DB
	 *
	 *@name write
	 *@access public
	 *@param bool - to force insert
	 *@param bool - to force write
	 *@param numeric - priority of the snapshop: autosave 0, save 1, publish 2
	*/
	public function write($forceInsert = false, $forceWrite = false, $snap_priority = 2) {
		$writtenIDs = array();
		if(count($this->data) > 0) {
			foreach($this->data as $record) {
				if(is_object($record) && !isset($writtenIDs[$record->id]) && $record->id != 0) {
					$writtenIDs[$record->id] = true;
					if(!$record->write($forceInsert, $forceWrite, $snap_priority)) {
						return false;
					}
				}
			}
			return true;
		} else
			return $this->dataobject->write();
	}
	
	/**
	 * deletes the records in stack
	 *
	 *@name remove
	 *@access public
	 *@param bool - force delete
	 *@param bool - if cancel on error, or resume
	 *@param bool - if force to delete versions, too
	*/
	public function remove($force = false, $forceAll = false) {
		foreach($this as $key => $record) {
			if($record->write($force, $forceAll)) {
				unset($this->data[$key]);
			}
		}
		return true;
	}
	
	/**
	 * generates a form
	 *
	 *@name form
	 *@access public
	 *@param string - name
	 *@param bool - edit-form
	 *@param bool - disabled
	*/
	public function generateForm($name = null, $edit = false, $disabled = false) {
		
		// if name is not set, we generate a name from this model
		if(!isset($name)) {
			$name = $this->dataobject->class . "_" . $this->dataobject->versionid . "_" . $this->dataobject->id;
		}
		
		$form = new Form($this->controller(), $name);
		if($disabled)
			$form->disable();
			
		// default submission
		$form->setSubmission("submit_form");	
			
		$form->addValidator(new DataValidator($this->dataobject), "datavalidator");
		
		$form->result = clone $this->dataobject;
		
		$form->add(new HiddenField("class_name", $this->dataobject->class));
		
		foreach($this->defaults as $key => $value) {
			$form->add(new HiddenField($key, $value));
		}
		
		// render form
		if($edit) {
			$this->dataobject->getEditForm($form, array());
		} else {
			$this->dataobject->getForm($form, array());
		}
		
		$this->dataobject->callExtending('getForm', $form, $edit);
		$this->dataobject->getActions($form, $edit);
		$this->dataobject->callExtending('getActions', $form, $edit);
		
		if(isset($this->controller) && $this->controller) {
			$this->controller->model_inst = $this->dataobject;
		}
		
		return $form;
	}
	
	/**
	 * for filter
	 *
	 *@name filter
	 *@access public
	*/
	
	public function getOffset($offset, $args = array()) {
		if(parent::__cancall($offset)) {
			return parent::getOffset($offset, $args);
		}
		if(isset($this->filter[$offset])) {
			return $this->filter[$offset];
		}
	}
	
	public function __cancall($offset) {
		$loweroffset = trim(strtolower($offset));
		if($loweroffset == "current")
			return true;

		return (isset($this->filter[$offset]) || parent::__cancall($offset));
	}

}

/**
 * for has-many-relation
 *
 *@name HasMany_DataObjectSet
*/
class HasMany_DataObjectSet extends DataObjectSet {

	/**
	 * field for the relation according to this set, for example: pageid or groupid
	 *
	 *@name field
	 *@access protected
	*/
	protected $field;
	/**
	 * name of the relation
	 *
	 *@name relationName
	 *@access protected
	*/
	protected $relationName;
	/**
	 * sets the relation-props
	 *
	 *@name setRelationENV
	 *@access public
	 *@param string - name
	 *@param string - field
	*/
	public function setRelationENV($name, $field) {
		$this->relationName = $name;
		$this->field = $field;
	}
	
	/**
	 * generates a form
	 *
	 *@name form
	 *@access public
	 *@param string - name
	 *@param bool - edit-form
	 *@param bool - disabled
	*/
	public function generateForm($name = null, $edit = false, $disabled = false) {
		
		$form = parent::generateForm($name, $edit, $disabled);
		
		$form->add(new HiddenField($this->field, $this[$this->field]));
		
		return $form;
	}
}

/**
 * for many-many-relation
 *
 *@name ManyMany_DataObjectSet
*/
class ManyMany_DataObjectSet extends HasMany_DataObjectSet {
	/**
	 * relation-table
	 *
	 *@name relationTable
	 *@access protected
	*/
	protected $relationTable;
	/**
	 * external field, for many-many-relations only
	 *
	 *@name extField
	 *@access protected
	*/
	protected $ownField;
	/**
	 * value of $ownField
	 *
	 *@name ownValue
	 *@access protected
	*/
	protected $ownValue;
	/**
	 * sets the relation-props
	 *
	 *@name setRelationENV
	 *@access public
	 *@param string - name
	 *@param string - field
	 *@param string - table of relation
	 *@param string - own field, not the field where to set the given IDs, the field where to store the current id
	 *@param string - the value of the own field, so the id
	*/
	public function setRelationENV($name, $field, $table, $ownField, $ownValue) {
		parent::setRelationENV($name, $field);
		$this->relationTable = $table;
		$this->ownField = $ownField;
		$this->ownValue = $ownValue;
	}
}