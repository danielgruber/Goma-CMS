<?php defined("IN_GOMA") OR die();


/**
 * Basic class for getting Data as DataSet from DataBase. It implements all types of DataBase-Queriing and always needs a DataObject to query the DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5
 */
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
	 * count of the data in this set
	 *
	 *@name count
	 *@access protected
	*/
	protected $count;

	/**
	 * dataobject for this DataObjectSet
	 *
	 * @name dataobject
	 * @access protected
     * @var DataObject
	*/
	public $dataobject;

	/**
	 * data
	 *
	 *@name data
	 *@access public
	*/
	public $data = null;

	/**
	 * controller of this dataobjectset
	 *
	 * @var Controller|String|null
	*/
	public $controller = "";

	/**
	 * sort by ids.
	*/
	protected $sortByIds = null;
	protected $idField = null;

	/**
	 * constructor
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($class = null, $filter = null, $sort = null, $limit = null, $join = null, $search = null, $version = null) {
		parent::__construct(null);

		if(isset($class)) {
			if(is_a($class, "DataObjectSet")) {
				$class = $class->dataobject;
			}

			$this->dataobject = Object::instance($class);
			$this->inExpansion = $this->dataobject->inExpansion;
			$this->dataClass = $this->dataobject->classname;
			if($this->dataobject->controller != null) {
				$this->controller($this->dataobject->controller);
			}

			$this->filter($filter);
			$this->sort = (isset($sort) && !empty($sort)) ? $sort : StaticsManager::getStatic($class, "default_sort");
			$this->limit($limit);
			$this->join($join);
			$this->search($search);
			$this->setVersion($version);

			$this->protected_customised = $this->customised;
		}
	}

	/**
	 * sets the data and datacache of this set
	 *
	 *@name setData
	 *@access public
	*/
	public function setData($data = array()) {
		$this->dataCache = $data;
		$this->data = (array) $data;
		$this->count = null;
		$this->reRenderSet();
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
	 * gets query-version
	 *
	 *@name queryVersion
	 *@access public
	*/
	public function queryVersion() {
		return $this->version;
	}

	/**
	 * returns table_name of current dataobject
	 *
	 *@name getTableName
	 *@access public
	*/
	public function getTable_Name() {
		if(!isset($this->dataobject))
			return null;
		return $this->dataobject->Table();
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
				if(!isset($data[$count]))
					$data[$count] = $record;
				$count++;
				unset($record);
			}

			$this->dataCache = $this->dataCache + $data;

			if($this->sortByIds) {
				$data = $this->sortByIds($data, $this->sortByIds, $this->idField);
			}

			if(PROFILE) Profiler::unmark("DataObjectSet::getRecordsByRange");
			return $data;
		} else {

			if($this->sortByIds) {
				$data = $this->sortByIds($data, $this->sortByIds, $this->idField);
			}

			if(PROFILE) Profiler::unmark("DataObjectSet::getRecordsByRange");
			return $data;
		}
	}

	public function sortByIds($data, $ids, $idField) {
		$newData = array();
		foreach($ids as $id) {
			foreach($data as $k => $r) {
				if(is_object($r) && $r[$idField] == $id) {
					$newData[$k] = $r;
					$data[$k] = null;
					break;
				}
			}
		}

		foreach($data as $k => $v) {
			if($v !== null) {
				$newData[$k] = $v;
			}
		}
		return $newData;
	}

    /**
     * returns the first item
     * @name first
     * @access public
     * @return DataObject|null
     */
	public function first($forceObject = true)
	{
        $this->forceData();

        if(is_array($this->data) && count($this->data) > 0 && isset($this->data[key($this->data)])) {
            return $this->current(key($this->data));
        } else if($forceObject) {
            return $this->dataobject;
        } else {
            return null;
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
	 * gets the maximum value of given field in this set.
	 *
	 *@name max
	 *@access public
	 *@param string $field
	*/
	public function Max($field) {
		if(isset(ClassInfo::$database[$this->dataobject->table()][strtolower($field)])) {
			$field = $this->dataobject->table() . "." . $field;
		}

		$data = $this->dataobject->getAggregate($this->version, 'max('.convert::raw2sql($field).') as max', $this->filter, array(), $this->limit, $this->join, $this->search);

		if(isset($data[0]["max"])) {
			return $data[0]["max"];
		} else {
			return null;
		}
	}

	/**
	 * gets the maximum value of given field in this set + returns a count of all fields in this set as a comma-seperated-string.
	 * this is for use in caching.
	 *
	 *@name maxCount
	 *@access public
	 *@param string $field
	*/
	public function MaxCount($field) {
		if(isset(ClassInfo::$database[$this->dataobject->table()][strtolower($field)])) {
			$field = $this->dataobject->table() . "." . $field;
		}

		$data = $this->dataobject->getAggregate($this->version, 'max('.convert::raw2sql($field).') as max, count(*) AS count', $this->filter, array(), $this->limit, $this->join, $this->search);

		if(isset($data[0]["max"])) {
			return $data[0]["max"]  . "," . $data[0]["count"];
		} else {
			return null;
		}
	}

	/**
	 * gets the minimum value of given field in this set.
	 *
	 *@name min
	 *@access public
	 *@param string $field
	*/
	public function Min($field) {

		if(isset(ClassInfo::$database[$this->dataobject->table()][strtolower($field)])) {
			$field = $this->dataobject->table() . "." . $field;
		}


		$data = $this->dataobject->getAggregate($this->version, 'min("'.convert::raw2sql($field).'") as min', $this->filter, array(), $this->limit, $this->join, $this->search);

		if(isset($data[0]["min"])) {
			return $data[0]["min"];
		} else {
			return null;
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
	public function current($position = null)
	{
		if(!isset($position))
			$position = $this->position;

		$this->forceData();
		if(isset($this->data[$position]))
			$data = $this->data[$position];
		else {
			// get next range
			$this->data = $this->getRecordsByRange($position, $this->perPage);
			$data = $this->data[$position];
		}

		$data = $this->getConverted($data);

		if(is_object($data) && is_a($data, "viewaccessabledata"))
			$data->dataSetPosition = $position;

		$data->queryVersion = $this->version;

		$this->data[$position] = $data;

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

		if(!isset($this->dataCache) && isset($this->dataobject)) {
			if(!$this->pagination) {
				$this->dataCache = $this->dataobject->getRecords($this->version, $this->filter, $this->sort, $this->limit, $this->join, $this->search);
			}
			$this->reRenderSet();
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
		if(isset($filter) && $this->filter != $filter) {
			$this->filter = $filter;
			$this->purgeData();
		}
		return $this;
	}

	/**
	 * adds a filter
	 *
	 *@name addFilter
	 *@access public
	*/
	public function addFilter($filter) {
		if(isset($filter)) {
			$this->filter = array_merge((array) $this->filter, (array) $filter);
			$this->purgeData();
		}
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
		$this->dataCache = null;
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
		if(isset($join)) {
			$this->join = $join;
			$this->purgeData();
		}
		return $this;
	}

	/**
	 * sets limits
	 *
	 *@name limit
	 *@access public
	*/
	public function limit($limit) {

		if((is_string($limit) && preg_match('/^[0-9]+$/', $limit)) || is_int($limit)) {
			$limit = array((int) $limit);
		}

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
	 * activates pagination
	 *
	 *@name activatePagination
	 *@access public
	*/
	public function activatePagination($page = null, $perPage = null) {
		if(isset($perPage) && $perPage > 0)
			$this->perPage = $perPage;

		if(isset($page)) {

			// first validate the data
			$pages = ceil($this->Count() / $this->perPage);
			if($pages < $page) {
				$page = $pages;
			}

			$this->page = $page;
		}
		if(!isset($this->page)) {
			$this->page = 1;
		}

		$this->pagination = true;
		$this->purgeData();
	}

    /**
     * sorts with callback.
     *
     * @param $callback
     */
    public function sortWithCallback($callback) {
        usort($this->dataCache, $callback);
    }

    /**
     * resorts the data
     *
     * @name sort
     * @access public
     * @param string - column
     * @param string - optional - type
     * @return $this
     */
	public function sort($column, $type = "") {

		if(is_array($column)) {
			$this->sortByIds = $column;
			$this->sort = null;

			if($type && strtolower($type) != "asc") {
				$this->idField = $type;
			} else {
				$this->idField = "id";
			}

			return $this;
		}

		$this->sortByIds = null;
		$this->idField = null;

		if(!isset($column))
			return $this;

		if(!$this->canSortBy($column))
			return $this;

		switch(strtolower($type)) {
			case "desc":
				$type = "DESC";
			break;
			default:
				$type = "ASC";
			break;
		}

		if(isset($this->sort["field"]) && $this->sort["field"] == $column && $this->sort["type"] == $type) {
			return $this;
		}

		$this->sort = array("field" => $column, "type" => $type);
		$this->purgeData();

		return $this;
	}

	/**
	 * checks if we can sort by a specefied field
	 *
	 *@name canSortBy
	*/
	public function canSortBy($field) {
		return $this->dataobject->canSortBy($field);
	}

	/**
	 * checks if we can sort by a specefied field
	 *
	 *@name canSortBy
	*/
	public function canFilterBy($field) {
		return $this->dataobject->canFilterBy($field); //! TODO: Implement Filter in DataObjectSet
	}

	/**
	 * sets version-type.
	 *
	 * @param	mixed $version type: "published"/"state"/"grouped"/false (get all records not grouped by recordid)/integer
	*/
	public function setVersion($version) {
		$this->version = $version;
		$this->dataobject->queryVersion = $version;
		$this->purgeData();
		return $this;
	}

	/**
	 * returns the current version
	 *
	 *@name getVersion
	 *@access public
	*/
	public function getVersion() {
		return $this->version;
	}

	/**
	 * search
	 *
	 *@name search
	 *@access public
	*/
	public function search($search) {
		if(isset($search)) {
			$this->search = $search;
			$this->purgeData();
		}
		return $this;
	}

	/**
	 * adds a new record to this set
	 *
	 *@name add
	 *@access public
	*/
	public function push(DataObject $record, $write = false) {
		foreach((array) $this->defaults as $key => $value) {
			if(empty($record[$key]))
				$record[$key] = $value;
		}

		if($this->count !== null) {
			$this->count++;
		}

		$return = parent::push($record);
		if($write) {
			$record->write(false, true);
		}
		return $return;
	}

	/**
	 * adds a new record to this set
	 *
	 *@name addMany
	 *@access public
	*/
	public function addMany($data) {
		$addedIDs = array();
		foreach($data as $record) {
			if(is_integer($record)) {
				$_data = DataObject::get_one($this->dataobject, array("id" => $record));
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
	 *@name getConverted
	 *@access protected
	 *@param various - data
	*/
	public function getConverted($item) {
		if(is_array($item)) {
			if(isset($item["class_name"]) && ClassInfo::exists($item["class_name"])) {
                $object = new $item["class_name"]($item);
            } else {
                $object = new $this->dataobject->classname ($item);
            }
		} else {
			$object = $item;
		}

		$object->original = $object->data;

		$object->dataset =& $this;

		if(is_object($object) && Object::method_exists($object, "customise")) {
			$object->customise($this->protected_customised);
			return $object;
		} else {
			return $object;
		}
	}


	/**
	 * gets the controller
	 *
	 * @return Controller
	 */
	public function controller($controller = null) {

		if(is_object($controller)) {
			$this->controller = clone $controller;
			$this->controller->setModelInst($this, $this->dataobject->classname);
			return $this->controller;
		}

		if(is_object($this->controller))
		{
			return $this->controller;
		}

		/* --- */

		if($this->controller != "")
		{
			$this->controller = new $this->controller();
			$this->controller->setModelInst($this, $this->dataobject->classname);
			return $this->controller;
		} else {
			/** @var Controller $controller */
			$controller = $this->dataobject->controller();
			if($controller) {
				$controller->setModelInst($this, $this->dataobject->classname);
				return $controller;
			}
		}

		return null;
	}



	/**
	 * toString
	 *
	 *@name toString
	 *@access public
	*/
	public function __toString() {
        try {
            if($controller = $this->controller()) {
                if($controller->template != "") {
                    return $controller->index();
                } else {
                    return false;
                }
            }
            return "controller not found";
        } catch(Exception $e) {
            Goma_ExceptionHandler($e);
        }
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
	 * returns an array of the values of a specific field
	 *
	 *@name fieldToArray
	 *@access public
	*/
	public function fieldToArray($field) {
		$this->forceData();
		return parent::fieldToArray($field);
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
				if(is_object($record) && (!isset($writtenIDs[$record->id]) || $record->id == 0)) {
					$writtenIDs[$record->id] = true;
					if(!$record->write($forceInsert, $forceWrite, $snap_priority)) {
						return false;
					}
				}
			}
			return true;
		} else if($this->dataobject->hasChanged()) {
			return $this->dataobject->write();
		}
	}

	/**
	 * write to DB with Exceptions.
	 *
	 *@name write
	 *@access public
	 *@param bool - to force insert
	 *@param bool - to force write
	 *@param numeric - priority of the snapshop: autosave 0, save 1, publish 2
	*/
	public function writeToDB($forceInsert = false, $forceWrite = false, $snap_priority = 2) {
		$writtenIDs = array();
		if(count($this->data) > 0) {
			foreach($this->data as $record) {
				if(is_object($record) && (!isset($writtenIDs[$record->id]) || $record->id == 0)) {
					$writtenIDs[$record->id] = true;
					if(!$record->writeToDB($forceInsert, $forceWrite, $snap_priority)) {
						return false;
					}
				}
			}
			return true;
		} else if($this->dataobject->hasChanged()) {
			return $this->dataobject->writeToDB();
		}
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
	private function remove($force = false, $forceAll = false) {
		foreach($this as $key => $record) {
			if($record->remove($force, $forceAll)) {
				unset($this->data[$key]);
				unset($this->dataCache[$key]);
			}
		}
		return true;
	}

	/**
	 * public removal
	*/
	public function getRemove() {
        throw new BadMethodCallException("Method remove is not allowed anymore and DataObjectSet, please select a single DataObject");
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
	public function generateForm($name = null, $edit = false, $disabled = false, $request = null, $controller = null, $submission = null) {

		// if name is not set, we generate a name from this model
		if(!isset($name)) {
			$name = $this->dataobject->classname . "_" . $this->dataobject->versionid . "_" . $this->dataobject->id;
		}

		$controller = isset($controller) ? $controller : $this->controller;

		$form = new Form($controller, $name, array(), array(), array(), $request, $this->dataobject);
		if($disabled)
			$form->disable();

		// default submission
		$form->setSubmission(isset($submission) ? $submission : "submit_form");

		$form->addValidator(new DataValidator($this->dataobject), "datavalidator");

        if(is_object($this->dataobject)) {
            $form->setResult(clone $this->dataobject);
        }

		$form->add(new HiddenField("class_name", $this->dataobject->classname));

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
	 * generates the form via controller
	 *
	 *@name form
	 *@access public
	*/
	public function form() {
		return $this->controller()->form(null, $this);
	}

	/**
	 * generates a form
	 *
	 *@name renderForm
	 *@access public
	*/
	public function renderForm() {
		return $this->controller()->renderForm(null, $this);
	}


	public function __cancall($offset) {
		$loweroffset = trim(strtolower($offset));
		if($loweroffset == "current")
			return true;

		return parent::__cancall($offset);
	}

	// some API patches
	public function isDeleted() {
		return $this->first()->isDeleted();
	}
	public function isPublished() {
		return $this->first()->isPublished();
	}
	public function everPublished() {
		return $this->first()->everPublished();
	}
}