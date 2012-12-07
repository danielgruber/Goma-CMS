<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * implementing datasets
  *********
  * last modified: 04.12.2012
  * $Version: 4.6.12
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

classinfo::addSaveVar("DataObject", "extensions");

abstract class DataObject extends ViewAccessableData implements PermProvider, SaveVarSetter
{
	public static $donothing = false;
	/**
	 * OPTIONS PROVIDED TO CONFIGURE THIS CLASS
	*/
	/**
	 * default sorting 
	 *
	 *@name default_sort
	*/
	static $default_sort = "id";
	
	 /**
	 * show read-only edit if not enough rights
	 *
	 *@name showWithoutRight
	 *@access public
	 *@var bool
	 *@default false
	*/
	public $showWithoutRight = false;

	
	/**
	 * database
	 *@name db_fields
	 *@var array
	*/
	public $db_fields = array();
	
	/**
	 * table-name
	 *@name table_name
	 *@var string
	*/
	public $table_name;
	
	/**
	 * casting
	 *
	 *@name casting
	 *@access public
	*/
	public $casting;
	/**
	 * relations
	*/
	
	/**
	 * has one
	 *@name has_one
	 *@var array
	*/
	public $has_one = array();
	
	/**
	 * has many
	 * a row has many childs
	 *@name has_many
	 *@var array
	*/
	public $has_many = array();
	
	/**
	 * many many
	 * many has many
	 *@name many_many
	 *@var array
	*/
	public $many_many = array();
	
	/**
	 * many many
	 * many has many
	 *@name belongs_many_many
	 *@var array
	*/
	public $belongs_many_many = array();
	
	/**
	 * many-many-tables
	 *@name many_many_tables
	 *@access public
	 *@var array
	*/
	public $many_many_tables = array();
	
	/**
	 * many-many-extra-fields
	 *@name many_many_extra_fields
	 *@access public
	 *@var array
	*/
	public $many_many_extra_fields = array();
	
	/**
	 * indexes
	 * you can use indexes to optimize your SQL-Tables
	 * indexes are very useful and can optimize the speed of a query from 30ms to 1ms or more
	 *@name indexes
	 *@access public
	 *@var array
	*/
	public $indexes = array(
		
	);
	
	/**
	 * searchable fields
	 *@name searchable_fields
	 *@access public
	*/
	public $searchable_fields = array();
	
	/**
	 * prefix for table_name
	*/
	public $prefix = "";
	
	/**
	 * RIGHTS
	*/
	
	/**
	 * a personal-field, this must contain the id of the user, so the user can edit it
	 *@name writeField
	 *@access public
	*/
	public $writeField = "";
	
	/**
	 * rights needed for inserting data
	 *@name insertRights
	 *@access public
	*/
	public $insertRights = "";
	
	/**
	 * admin-rights
	 *
	 * admin can do everything, implemented in can-methods
	*/
	public $admin_rights;


	// some form things ;)
	
	/**
	 * cache for results
	*/
	static $results = array();
	
	/**
	 * field-titles
	*/
	public $fieldTitles = array();
	
	/**
	 * info helps users to understand, what the field means, so you should add info to each field, which is not really clear with the title
	 *
	 *@name fieldInfo
	 *@access public
	 *@var array
	*/
	public $fieldInfo = array();
	
	/**
	 * this var specifies if this dataclass will be versioned
	 *
	 *@name versioned
	 *@access public
	*/
	public $versioned = false;
	
	/**
	 * enables or disabled history
	 *
	 *@name history
	 *@access public
	*/
	public static $history = true;
	
	/**
	 * this var identifies with which version a DataObjectSet got the data
	 * THIS doens't provide feedback if the version is published or not
	 *
	 *@name queryVersion
	 *@access public
	*/
	public $queryVersion = "published";
	
	/**
	 * view-cache
	 *
	 *@name viewcache
	 *@access protected
	*/
	protected $viewcache = array();
	
	/**
	 * gets default SQL-Fields
	 *
	 *@name getDefaultSQLFields
	 *@access public
	*/
	public function DefaultSQLFields($class) {
		if(strtolower(get_parent_class($class)) == "dataobject") {
			return array(	
					'id'			=> 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
					'last_modified' => 'date()',
					'class_name' 	=> 'enum("'.implode('","', array_merge(classinfo::getChildren($class), array($class))).'")',
					"created"		=> "date()"
				);
		} else {
			return array(	
					'id'			=> 'INT(10) AUTO_INCREMENT  PRIMARY KEY'
				);
		}
	}
	
	/**
	 * gets all dbfields
	 *
	 *@name DBFields
	 *@access public
	*/
	public function generateDBFields($parents = false) {
		
		
		$fields = $this->db_fields;
		
		// has-one-fields
		foreach($this->generateHas_one(false) as $key => $value) {
			if(!isset($fields[$key . "id"])) // patch if you want to edit field-type of has_one-field
				$fields[$key . "id"] = "int(10)";
				
			unset($key, $value);
		}
		
		
		foreach($this->LocalcallExtending("DBFields") as $dbfields) {
			$fields = array_merge($fields, $dbfields);
			unset($dbfields);
		}
		
		$parent = get_parent_class($this);
		if($parents === true && $parent != "DataObject") {
			$fields = array_merge(Object::instance($parent)->generateDBFields(true), $fields);
		}
		
		if($fields)
			$fields = array_merge($this->DefaultSQLFields(strtolower(get_class($this))), $fields);
		
		return $fields;
	}
	
	/**
	 * gets has_one
	 *
	 *@access public
	*/
	public function generateHas_one($parents = true) {
		
		$has_one = $this->has_one;
		foreach($this->LocalcallExtending("Has_One") as $has_ones) {
			$has_one = array_merge($has_one, $has_ones);
			unset($has_ones);
		}
		$parent = get_parent_class($this);
		if($parents === true && $parent != "DataObject") {
			$has_one = array_merge(Object::instance($parent)->generateHas_one(), $has_one);
		}
		
		$has_one = array_map("strtolower", $has_one);
		return $has_one;
	}
	
	/**
	 * gets has_many
	 *
	 *@access public
	*/
	public function generateHas_many() {
		
		$has_many = $this->has_many;
		foreach($this->LocalcallExtending("Has_Many") as $has_manys) {
			$has_many = array_merge($has_many, $has_manys);
			unset($has_manys);
		}
		$parent = get_parent_class($this);
		if($parent != "DataObject") {
			$has_many = array_merge(Object::instance($parent)->generateHas_many(), $has_many);
		}
		
		$has_many = array_map("strtolower", $has_many);
		return $has_many;
	}
	
	/**
	 * gets many_many
	 *
	 *@name many_many
	 *@access public
	*/
	public function generateMany_many($parents = true) {
		$many_many = $this->many_many;
		foreach($this->LocalcallExtending("many_many") as $many_manys) {
			$many_many = array_merge($many_many, $many_manys);
			unset($many_manys);
		}
		$parent = get_parent_class($this);
		if($parents === true && $parent != "DataObject") {
			$many_many = array_merge(Object::instance($parent)->generateMany_many(), $many_many);
		}
		
		$many_many = array_map("strtolower", $many_many);
		return $many_many;
	}
	
	/**
	 * gets belongs_many_many
	 *
	 *@name belongs_many_many
	 *@access public
	*/
	public function generateBelongs_many_many($parents = true) {
		$belongs_many_many = $this->belongs_many_many;
		foreach($this->LocalcallExtending("belongs_many_many") as $belongs_many_manys) {
			$belongs_many_many = array_merge($belongs_many_many, $belongs_many_manys);
			unset($belongs_many_manys);
		}
		$parent = get_parent_class($this);
		if($parents === true && $parent != "DataObject") {
			$belongs_many_many = array_merge(Object::instance($parent)->generateBelongs_Many_many(), $belongs_many_many);
		}
		
		$belongs_many_many = array_map("strtolower", $belongs_many_many);
		return $belongs_many_many;
	}
	
	/**
	 * generates many-many tables
	 *
	 *@name generateManyManyTables
	 *@access public
	*/
	public function generateManyManyTables() {
		$tables = array();
		
		// many-many
		foreach($this->generateMany_many(false) as $key => $value) {
			// generate extra-fields
			if(isset($this->many_many_extra_fields[$key])) {
				$extraFields = $this->many_many_extra_fields[$key];
			} else {
				$extraFields = array();
			}
			$extendExtraFields = $this->localCallExtending("many_many_extra_fields");
			if(isset($extendExtraFields[$key])) {
				$extraFields = array_merge($extraFields, $extendExtraFields);
			}
			
			if(class_exists($value)) {
				$tables[$key] = array(
					"table"			=> "many_many_".strtolower(get_class($this))."_".  $key . '_' . $value,
					"field"			=> strtolower(get_class($this)) . "id",
					"extfield"		=> $value . "id"
				);
				if($extraFields) {
					$tables[$key]["extraFields"] = $extraFields;
				}
				unset($key, $value);
			} else {
				throwError(6, 'PHP-Error', "Can't create Relationship on not existing class '".$value."'.");
			}
		}
		
		
		// belongs-many-many
		foreach($this->generateBelongs_Many_many(false) as $key => $value) {
			if(is_array($value)) {
				if(isset($value["relation"]) && isset($value["class"])) {
					$relation = $value["relation"];
					$value = $value["class"];
				} else {
					$value = array_values($value);
					$relation = @$value[1];
					$value = $value[0];
				}
			}
			if(class_exists($value)) {
				if(is_subclass_of($value, "DataObject")) {
					$inst = Object::instance($value);
					$relations = $inst->generateMany_Many();
					
					if(is_array($relations)) {
						if(isset($relation)) {
							if(isset($relations[$relation]) && $relations[$relation] == $this->class) {
								// everything okay
							} else {
								throwError(6, "Logcal Error", "Relation ".$relation." does not exist on ".$value.".");
							}
						} else {
							$relation = array_search(strtolower(get_class($this)), $relations);
						}
					} else {
						throwError(6, "Logcal Error", "Relation ".$relation." does not exist on ".$value.".");
					}
					
					// generate extra-fields
					if(isset($inst->many_many_extra_fields[$relation])) {
						$extraFields = $inst->many_many_extra_fields[$relation];
					} else {
						$extraFields = array();
					}
					$extendExtraFields = $inst->localCallExtending("many_many_extra_fields");
					if(isset($extendExtraFields[$relation])) {
						$extraFields = array_merge($extraFields, $extendExtraFields);
					}
					
					
				} else {
					throwError(6, "Logical Exception", $value . " must be subclass of DataObject to be a handler for a many-many-relation.");
				}
				if($relation) {
					$tables[$key] = array(
						"table"			=> "many_many_".$value."_".  $relation . '_' . strtolower(get_class($this)),
						"field"			=> strtolower(get_class($this)) . "id",
						"extfield"		=> $value . "id",
					);
					if($extraFields) {
						$tables[$key]["extraFields"] = $extraFields;
					}
					unset($key, $value);
				}
			} else {
				throwError(6, 'Logical Error', "Can't create Relationship on not existing class '".$value."'.");
			}
		}
		
		
		
		$parent = get_parent_class($this);
		if($parent != "DataObject") {
			$tables = array_merge(Object::instance($parent)->generateManyManyTables(), $tables);
		}
		
		
		return $tables;
		
		
	}
	/**
	 * indexes
	 *
	 *@name generateIndexes
	 *@access public
	*/ 
	public function generateIndexes() {
		$indexes = $this->indexes;
		
		foreach($this->generateHas_one(false) as $key => $value) {
			if(!isset($indexes[$key . "id"])) {
				$indexes[$key . "id"] = "INDEX";
				unset($key, $value);
			}
		}
		if($this->searchable_fields)
				// we add an index for fast searching
				$indexes["searchable_fields"] = array("type" => "INDEX", "fields" => implode(",", $this->searchable_fields), "name" => "searchable_fields");
				
		return $indexes;

	}
	/**
	 * defaults
	 *
	 *@name defaults
	 *@access public
	*/
	public function generateDefaults() {
		$defaults = array();
		// get parents
		$parent = get_parent_class($this);
		if($parent != "DataObject") {
			$defaults = array_merge(Object::instance($parent)->generateDefaults(), $defaults);
		}
		
		foreach($this->LocalcallExtending("defaults") as $defaultsext) {
			$defaults = array_merge($defaults, $defaultsext);
			unset($defaultsext);
		}
		
		// free memory
		unset($parent);
		$defaults = arraylib::map_key($defaults, "strtolower");
		return $defaults;
	}
	
	/**
	 * generates casting
	 *
	 *@name generateCasting
	 *@access public
	*/
	public function generateCasting() {
		$casting = array_merge((array) $this->casting, (array) $this->generateDBFields());
		foreach($this->LocalcallExtending("casting") as $_casting) {
			$casting = array_merge($casting, $_casting);
			unset($_casting);
		}
		
		$parent = get_parent_class($this);
		if($parent != "viewaccessabledata" && !ClassInfo::isAbstract($parent)) {
			$casting = array_merge(Object::instance($parent)->generateCasting(), $casting);
		}
		
		$casting = ArrayLib::map_key("strtolower", $casting);
		return $casting;
	}
	
	/**
	 * STATIC METHODS
	*/
	
	/**
	 * gets an instance of the class
	 *
	 * DEPRECATED!
	 *
	 *@name _get
	 *@access public
	 *@param string - name
	 *@param array - where
	 *@param array - fields
	 *@param array - orderby
	*/
	public static function _get($class, $filter = array(), $fields = array(), $sort = array(), $joins = array(), $limits = array(), $pagination = false, $groupby = false)
	{
			Core::Deprecate(2.0, "DataObject::get");
			$data = $data = self::get($class, $filter, $sort, $limits, $joins, null, $pagination);
			if($groupby !== false) {
				return $data->groupBy($groupby);
			}
			
			return $data;
	}
	
	/*
	 * gets a DataObject versioned
	 *
	 *@name getVersioned
	 *@access public
	*/
	public static function get_versioned($class,$version = "publish" , $filter = array(), $sort = array(), $limits = array(), $joins = array(), $group = false, $pagination = false) {
		$data = self::get($class, $filter, $sort, $limits, $joins, $version, $pagination);
		if($group !== false) {
			return $data->groupBy($group);
		}
		
		return $data;
	}
	
	/**
	 * gets a DataObject versioned
	 *
	 *@name getVersioned
	 *@access public
	*/
	public static function get_version() {
		return call_user_func_array(array("DataObject", "get_Versioned"), func_get_args());
	}
	
	/**
	 * returns a (@link DataObjectSet) with the given parameters
	 *
	 *@name get
	 *@access public
	 *@param string - class
	 *@param array - filter
	 *@param array - sort
	 *@param array - limits
	 *@param array - joins
	 *@param null|string|int - version
	 *@param bool - pagination
	*/
	public static function get($class, $filter = null, $sort = null, $limits = null, $joins = null, $version = null, $pagination = null) {
		
		$DataSet = new DataObjectSet($class, $filter, $sort, $limits, $joins, array(), $version);
		
		if(isset($pagination) && $pagination !== false) {
			if(is_int($pagination))
				$DataSet->activatePagination($pagination);
			else
				$DataSet->activePagination();
		}
		
		return $DataSet;
	}
	
	/**
	 * counts the values of an DataObject
	 *@name count
	 *@param array - where
	 *@param array - fields
	 *@param array - forms
	 *@param array - groupby
	*/
	static function count($name = "", $filter = array(), $froms = array(), $groupby = "")
	{			
			$data = self::get($name, $filter, array(), array(), $froms, null);
			
			if($groupby != "") {
				return count($data->GroupBy($groupby));
			}
			return $data->Count();
	}
	
	/**
	 * counts with given where on given table with raw-sql, so you just get back the result for exactly this where
	 * if you use count, goma gets just results from given dataobject, so if you want to know, if just the id in an given dataobject exists,
	 * but the record is not connected exactly with given dataobject, but with other child of base-DataObject, count give back 0, because of not linked exactly with the right DataObject
	 * if you use this function instead, goma don't interpret the data, so you get all results from the table with given where and don't habe comfort
	 * 
	 * ATTENTION: it's NOT recommended to use this function if you don't know the exact difference
	 * if you use it, sometimes you get results, that are unexpected
	 *
	 *@name countRAW
	 *@access public
	 *@param string - DataObject-name
	 *@param array - filter
	*/
	public function countRAW($name, $filter)
	{
			$dataobject = Object::instance($name);
			
			$table_name = $dataobject->table_name;
			
			$where = SQL::ExtractToWhere($filter);
			
			$sql = "SELECT 
						count(*) as count
					FROM 
						".DB_PREFIX.$table_name."
					".$where;
			if($result = SQL::Query($sql))
			{
					$row = SQL::fetch_object($result);
					return $row->count;
			} else
			{
					throwErrorById(3);
			}
	}
	
	/**
	 * updates data
	 *@name update
	 *@param string - controller-object
	 *@param array - data
	 *@param array - where
	 *@param string - optional table
	 *@return bool
	*/
	public static function update($name, $data, $where, $limit = "")
	{
			Core::Deprecate(2.0);
			
			$DataObject = Object::instance($name);
			
			if(is_subclass_of($DataObject, "controller"))
			{
					$DataObject = $DataObject->model_inst;
			}
			if(is_array($where))
			{
					$d = array_merge($where,$data);
			} else
			{
					$d = $data;
			}
					
			
			$table_name =  $DataObject->table_name;
			//deleteTableCache($table_name);
			$updates = "";
			$i = 0;
			if(!isset($data["last_modfied"]))
			{
					$data["last_modified"] = TIME;
			}
			
			foreach($data as $field => $value)
			{
					if(!isset(ClassInfo::$database[$table_name][$field]))
					{
							continue;
					}
					if($i == 0)
					{
							$i = 1;
					} else
					{
							$updates .= ", ";
					}
					$updates .= "".convert::raw2sql($field)." = '".convert::raw2sql($value)."'";
			}
			$where = SQL::ExtractToWhere($where);
			
			
			if($limit != "") {
				if(is_array($limit)) {
					if(count($limit) > 1 && preg_match("/^[0-9]+$/", $limit[0]) && preg_match("/^[0-9]+$/", $limit[1]))
						$limit = " LIMIT ".$limit[0].", ".$limit[1]."";
					else if(count($limit) == 1 && preg_match("/^[0-9]+$/", $limit[0])) 
						$limit = " LIMIT ".$limit[0];
					
				} else if(preg_match("/^[0-9]+$/", $limit)) {
					$limit = " LIMIT ".$limit;
				} else if(preg_match('/^\s*([0-9]+)\s*,\s*([0-9]+)\s*$/', $limit)) {
					$limit = " LIMIT ".$limit;
				} else {
					$limit = "";
				}
			}
			
			$sql = "UPDATE
						".DB_PREFIX . $table_name." AS ".$table_name."
					SET 
						".$updates."
					".$where."
					".$limit."";
			
			if(sql::query($sql))
			{
					return true;
			} else
			{
					throwErrorById(3);
			}
	}

	/**
	 * gets one data
	 *@name get_one
	 *@access public
	 *@param string - name of data-class
	 *@param array - where
	 *@param array - fields
	 *@param array - orderby
	 *@param array - froms
	*/
	public static function get_one($name, $filter = array(), $sort = array(), $joins = array())
	{
		return self::get($name, $filter, $sort, array(1))->first(false);
	}
	
	/**
	 * gets a record by id
	 *
	 *@name get_by_id
	 *@access public
	 *@param string - name
	 *@param numeric - id
	 *@param array - joins
	*/
	public static function get_by_id($class, $id, $joins = array()) {
		return self::get_one($class, array("id" => $id), array(), $joins);
	}
	
	
	/**
	 * searches in a model
	 *
	 *@name search_object
	 *@access public
	 *@param string - name
	 *@param array - words
	 *@param array - where
	 *@param array - fields
	 *@param array - orderby
	*/
	public static function search_object($class, $search = array(),$filter = array(), $sort = array(), $limits = array(), $join = array(), $pagination = false, $groupby = false)
	{
		$DataSet = new DataObjectSet($class, $filter, $sort, $limits, $join, $search);
		
		if($pagination !== false) {
			if(is_int($pagination))
				$DataSet->activatePagination($pagination);
			else
				$DataSet->activePagination();
		}
		
		if($groupby !== false) {
			return $DataSet->getGroupedSet($groupby);
		}
			
		
		return $DataSet;
	}

	
	/**
	 * defines the methods
	 *
	 *@name __setSaveVars
	 *@access public
	*/
	public static function __setSaveVars($class) {
		
		if(isset(ClassInfo::$class_info[$class]["has_many"])) {
			foreach(ClassInfo::$class_info[$class]["has_many"] as $key => $val) {
				Object::LinkMethod($class, $key, array("this", "getHasMany"), true);
				Object::LinkMethod($class, $key . "ids", array("this", "getRelationIDs"), true);
			}
		}
		
		if(isset(ClassInfo::$class_info[$class]["many_many"])) {
			foreach(ClassInfo::$class_info[$class]["many_many"] as $key => $val) {
				Object::LinkMethod($class, $key, array("this", "getManyMany"), true);
				Object::LinkMethod($class, $key . "ids", array("this", "getRelationIDs"), true);
				Object::LinkMethod($class, "set" . $key, array("this", "setManyMany"), true);
				Object::LinkMethod($class, "set" . $key . "ids", array("this", "setManyManyIDs"), true);
			}
		}
		
		if(isset(ClassInfo::$class_info[$class]["belongs_many_many"])) {
			foreach(ClassInfo::$class_info[$class]["belongs_many_many"] as $key => $val) {
				Object::LinkMethod($class, $key, array("this", "getManyMany"), true);
				Object::LinkMethod($class, $key . "ids", array("this", "getRelationIDs"), true);
				Object::LinkMethod($class, "set" . $key, array("this", "setManyMany"), true);
				Object::LinkMethod($class, "set" . $key . "ids", array("this", "setManyManyIDs"), true);
			}
		}
		
		if(isset(ClassInfo::$class_info[$class]["has_one"])) {
			foreach(ClassInfo::$class_info[$class]["has_one"] as $key => $val) {
				Object::LinkMethod($class, $key, array("this", "getHasOne"), true);
			}
		}
	}
	
	
	
	
	
	/**
	 * this defines a right for advrights or rechte, which tests if an user is an admin
	 *@name __construct
	 *@param array
	 *@param string|object
	 *@param array|string - fields
	*/
	public function __construct($record = null)
	{		
				
				
			parent::__construct();
			
			if(self::$donothing)
				return ;
			
			if(PROFILE) Profiler::mark("DataObject::__construct");
			
			if($myinfo = ClassInfo::getInfo($this->class))
			{
					if(isset($myinfo['has_one']))
					{
							$this->has_one = isset($myinfo['has_one']) ? $myinfo['has_one'] : array();
							$this->has_many = isset($myinfo['has_many']) ? $myinfo['has_many'] : array();
							$this->many_many = isset($myinfo['many_many']) ? $myinfo['many_many'] : array();
							$this->belongs_many_many = isset($myinfo['belongs_many_many']) ? $myinfo['belongs_many_many'] : array();
							$this->many_many_tables = isset($myinfo['many_many_tables']) ? $myinfo['many_many_tables'] : array();
							$this->searchable_fields = isset($myinfo['searchable_fields']) ? $myinfo['searchable_fields'] : array();
							$this->defaults = array_merge(ArrayLib::map_key("strtolower", $this->defaults), isset($myinfo['defaults']) ? $myinfo['defaults'] : array());
							$this->table_name = isset($myinfo['table_name']) ? $myinfo['table_name'] : array();
							$this->db_fields = isset($myinfo['db_fields']) ? $myinfo['db_fields'] : array();
							$this->casting = isset($myinfo['casting']) ? $myinfo['casting'] : array();
					}
			}
			
			$this->data = array_merge(array(
				"class_name"	=> $this->class,
				"last_modified"	=> NOW,
				"created"		=> NOW,
				"autorid"		=> member::$id
			), $this->defaults, ArrayLib::map_key("strtolower", (array) $record));
		
			if(PROFILE) Profiler::unmark("DataObject::__construct");
			
			
	}
	
	/**
	 * dev
	 *
	 *@name buildDB
	 *@access public
	*/
	public function buildDB($prefix = DB_PREFIX) {
		$log = "";
		$this->callExtending("beforeBuildDB", $prefix, $log);
		
		$this->indexes = isset(ClassInfo::$class_info[$this->class]["indexes"]) ? ClassInfo::$class_info[$this->class]["indexes"] : array();
		
		// first get all fields with translated types
		$db_fields = $this->db_fields;
		$indexes = $this->indexes;
	
		// add some fields for versioning
		if($this->table_name != "" && $this->table_name == $this->baseTable) {
			if(!isset($db_fields["recordid"]))
				$db_fields["recordid"] = "int(10)";
			
			if($this->versioned) {
				$db_fields["snap_priority"] = "int(10)";
				$this->defaults["snap_priority"] = 2;
			}
			
			if(!isset($indexes["recordid"]))
				$indexes["recordid"] = "INDEX";
		}
		
		if($this->table_name) {
			
			foreach($db_fields as $field => $type) {
				if(isset($this->casting[strtolower($field)])) {
					if($this->casting[strtolower($field)] = DBField::parseCasting($this->casting[strtolower($field)])) {
						$type = call_user_func_array(array($this->casting[strtolower($field)]["class"], "getFieldType"), (isset($this->casting[strtolower($field)]["args"])) ? $this->casting[strtolower($field)]["args"] : array());
						if($type != "")
							$db_fields[$field] = $type;
					}
				}
			}
			
			$this->indexes = isset(ClassInfo::$class_info[$this->class]["indexes"]) ? ClassInfo::$class_info[$this->class]["indexes"] : array();
			
			// now require table
			$log .= SQL::requireTable($this->table_name, $db_fields, $indexes , $this->defaults, $prefix);
		}
		
		// versioned
		if($this->table_name != "" && $this->table_name == $this->baseTable) {
			if(!SQL::getFieldsOfTable($this->baseTable . "_state")) {
				$exists = false;
			} else {
				$exists = true;
			}
			
			// force table
			$log .= SQL::requireTable(	$this->baseTable . "_state", 
											array(	"id" => "int(10) PRIMARY KEY auto_increment", 
													"stateid" => "int(10)", 
													"publishedid" => "int(10)"
												), 
											array(	"publishedid" => array(	"name" => "publishedid", 
																			"fields" => array("publishedid"), 
																			"type" => "index"
																		), 
													"stateid" => array(	"name" => "stateid", 
																		"fields" => array("stateid"), 
																		"type" => "index"
																	)
												), 
											array(), 
											$prefix
										);
			if(!$exists) {
				// now copy records from old table to new
				$sql = "INSERT INTO ".$prefix . $this->baseTable."_state (id, stateid, publishedid) SELECT id AS id, id AS stateid, id AS publishedid FROM ".$prefix . $this->baseTable."";
				if($this->versioned) {
					$sql2 = "UPDATE ".$prefix.$this->baseTable." SET snap_priority = 2, recordid = id, editorid = autorid";
				} else {
					$sql2 = "UPDATE ".$prefix.$this->baseTable." SET recordid = id, editorid = autorid";
				}
				if(sql::query($sql) && sql::query($sql2))
					$log .= "Copying Version-Data\n";
				else
					throwErrorById(3);
			}
			
			// set Database-Record
			ClassInfo::$database[$this->baseTable . "_state"] = array(
				"id" => "int(10)", 
				"stateid" => "int(10)", 
				"publishedid" => "int(10)"
			);
		}
		
		// create many-many-tables
		if(isset(ClassInfo::$class_info[$this->RecordClass]["many_many_tables"])) {
			foreach(ClassInfo::$class_info[$this->RecordClass]["many_many_tables"] as $key => $data) {
				
				// generate fields with extraFields
				$table = $data["table"];
				$fields = array('id' => 'int(10) PRIMARY KEY auto_increment', $data["field"] => 'int(10)', $data["extfield"] => 'int(10)');
				if(isset($data["extraFields"]))
					$fields = array_merge($fields, $data["extraFields"]);
				
				// require Table
				$log .= SQL::requireTable($table, $fields, array(
					"dataindex"	=> array(
						"name" 		=> "dataindex",
						"fields"	=> array($data["field"], $data["extfield"]),
						"type"		=> "INDEX"
					),
					"dataindexunique"	=> array(
						"name"		=> "dataindexunique",
						"type"		=> "UNIQUE",
						"fields"	=> array($data["field"], $data["extfield"])
					)
				), array(), DB_PREFIX);
				ClassInfo::$database[$data["table"]] = $fields;
			}	
		}
		
		// sort of table
		$sort = ClassInfo::getStatic($this->class, "default_sort");
		
		if(is_array($sort)) {
			$field = $sort["field"];
			$type = $sort["type"];
		} else if(preg_match('/^([a-zA-Z0-9_\-]+)\s(DESC|ASC)$/Usi', $sort, $matches)) {
			$field = $sort[1];
			$type = $sort[2];
		} else {
			$field = $sort;
			$type = "ASC";
		}
		if(isset(ClassInfo::$database[$this->table_name][$field])) {
			SQL::setDefaultSort($this->table_name, $field, $type);
		}
		
		$this->callExtending("buildDB", $prefix, $log);
		
		$this->preserveDefaults($prefix, $log);
		$this->cleanUpDB($prefix, $log);
		
		$this->callExtending("afterBuildDB", $prefix, $log);
		
		$output = '<div style="padding-top: 6px;"><div><img src="images/success.png" height="16" alt="Success" /> Checking Database of '.$this->class."</div><div style=\"padding-left: 21px;width: 550px;\">";
		$output .= str_replace("\n", "<br />",$log);
		$output .= "</div>";
		return $output;
	}
	
	/**
	 * generates some ClassInfo
	 *
	 *@name generateClassInfo
	 *@access public
	*/
	public function generateClassInfo() {
		if(defined("SQL_LOADUP") && SQL::getFieldsOfTable($this->baseTable . "_state")) {
			// set Database-Record
			ClassInfo::$database[$this->baseTable . "_state"] = array(
				"id" => "int(10)", 
				"stateid" => "int(10)", 
				"publishedid" => "int(10)"
			);
		}
	}
	
	/**
	 * preserve Defaults
	 *
	 *@name preserveDefaults
	 *@åccess public
	*/
	public function preserveDefaults($prefix = DB_PREFIX, &$log) {
		$this->callExtending("preserveDefaults", $prefix);
		
		if($this->table_name) {			
			//@todo bugfix
			if(count($this->defaults) > 0) {
				foreach($this->defaults as $field => $value) {
					if(isset(ClassInfo::$database[$this->table_name][$field])) {
						$sql = "UPDATE ".DB_PREFIX . $this->table_name." SET ".$field." = '".$value."' WHERE ".$field." = '' AND ".$field." != '0'";
						if(!sql::query($sql, false, $prefix)) {
							return false;
						}
					}
				}
			}
			
			if($this->baseClass == $this->class) {
				// set record ids
				$sql = "UPDATE ".DB_PREFIX . $this->table_name." SET recordid = id WHERE recordid = 0";
				SQL::query($sql);
			
				$sql = "UPDATE ".DB_PREFIX . $this->table_name." SET editorid = autorid WHERE editorid = 0";
				SQL::query($sql);
			}
		}
		
		if($this->table_name == $this->baseTable) {
			// clean-up recordid-0s
			$sql = "SELECT * FROM ".DB_PREFIX . $this->table_name." WHERE recordid = '0'";
			if($result = SQL::Query($sql)) {
				while($row = SQL::fetch_object($result)) {
					$_sql = "SELECT * FROM ".DB_PREFIX . $this->baseTable." WHERE publishedid = '".$row->id."' OR stateid = '".$row->id."'";
					if($_result = SQL::Query($_sql)) {
						if($_row = SQL::fetch_object($_result)) {
							$update = "UPDATE ".DB_PREFIX . $this->table_name." SET recordid = '".$_row->id."' WHERE id = '".$row->id."'";
							SQL::Query($update);
						}
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * clean up DB
	 *
	 *@name cleanUpDB
	 *@åccess public
	*/
	public function cleanUpDB($prefix = DB_PREFIX, &$log) {
		$this->callExtending("cleanUpDB", $prefix);
		
		if($this->versioned && $this->baseClass == $this->class) {
			$recordids = array();
			$ids = array();
			// first recordids
			$sql = "SELECT * FROM ".DB_PREFIX.$this->class."_state";
			if($result = SQL::Query($sql)) {
				while($row = SQL::fetch_object($result)) {
					$recordids[$row->id] = $row->id;
					$ids[$row->publishedid] = $row->publishedid;
					$ids[$row->stateid] = $row->stateid;
				}
			}
			
			$deleteids = array();
			
			$last_modified = NOW-(180*24*60*60);
			
			// now generate ids to delete
			$sql = "SELECT id FROM ".DB_PREFIX . $this->baseTable." WHERE (id NOT IN('".implode("','", $ids)."') OR recordid NOT IN ('".implode("','", $recordids)."')) AND (snap_priority = 1 AND last_modified < ".$last_modified.")";
			if($result = SQL::Query($sql)) {
				while($row = SQL::fetch_object($result)) {
					$deleteids[] = $row->id;
				}
			}
			
			$log .= 'Checking for old versions of '.$this->class."\n";
			if(count($deleteids) > 10) {
				// now delete
        				
				// first generate tables
				$tables = array($this->baseTable);
				foreach(ClassInfo::dataClasses($this->class) as $class => $table) {
					if($this->baseTable != $table && isset(ClassInfo::$database[$table])) {
						$tables[] = $table;
					}
				}
				
				foreach($tables as $table) {
					$sql = "DELETE FROM " . DB_PREFIX . $table . " WHERE id IN('".implode("','", $deleteids)."')";
					if(SQL::Query($sql))
						$log .= 'Delete old versions of '.$table."\n";
					else
						$log .= 'Failed to delete old versions of '.$table."\n";
				}	        
			}
			
			// clean up many-many-tables
			foreach($this->many_many_tables as $name => $data) {
				$sql = "DELETE FROM ". DB_PREFIX . $data["table"] ." WHERE ". $data["field"] ." = 0 OR ". $data["extfield"] ." = 0";
				if(SQL::Query($sql)) {
					if(SQL::affected_rows() > 0)
						$log .= 'Clean-Up Many-Many-Table '.$data["table"].'' . "\n";
				} else {
					$log .= 'Failed to clean-up Many-Many-Table '.$data["table"].'' . "\n";
				}
			}
		}
	}
	
	/**
	 * right-management
	*/
	
	/**
	 * returns if you can access a specific history-record
	 *
	 *@name canViewHistory
	 *@access public
	*/
	public static function canViewHistory($record = null) {
		if(is_object($record)) {
			if($record->oldversion && $record->newversion) {
				return ($record->oldversion->canWrite($record->oldversion) && $record->newversion->canWrite($record->newversion));
			} else if($record->newversion) {
				return $record->newversion->canWrite($record->newversion);
			} else if($record->record) {
				return $record->record->canWrite($record->record);
			}
		}
		
		if(is_object($record)) {
			$c = new $record->dbobject;
		} else if(is_string($record)) {
			$c = new $record;
		} else {
			throwError("6", "Invalid Argument Error", "Invalid Argument for DataObject::canViewRecord Object or Class_name required");
		}
		return $c->canWrite();
	}
	
	/**
	 * returns if a given record can be written to db
	 *
	 *@name canWrite
	 *@access public
	 *@param array - record
	*/
	public function canWrite($row = null)
	{		
		$provided = $this->providePerms();
		if(count($provided) == 1) {
			$keys = array_keys($provided);
			
			if(Permission::check($keys[0]))
				return true;
		} else if(count($provided) > 1) {
			foreach($provided as $key => $arr)
			{
				if(preg_match("/all$/i", $key))
				{
					if(Permission::check($key))
						return true;
				}
				
				if(preg_match("/write$/i", $key))
				{
					if(Permission::check($key))
						return true;
				}
			}
		}
		
		if(is_object($row) && $row->admin_rights) {
			return Permission::check($row->admin_rights);
		}
		
		if($this->admin_rights) {
			return Permission::check($this->admin_rights);
		}
		
		return false;
	}
	
	/**
	 * returns if a given record can deleted in database
	 *
	 *@name canDelete
	 *@access public
	 *@param array - reocrd
	*/
	public function canDelete($row = null)
	{
		$provided = $this->providePerms();
		if(count($provided) == 1) {
			$keys = array_keys($provided);
			
			if(Permission::check($keys[0]))
				return true;
		} else if(count($provided) > 1) {
			foreach($provided as $key => $arr)
			{
				if(preg_match("/all$/i", $key))
				{
					if(Permission::check($key))
						return true;
				}
				
				if(preg_match("/delete$/i", $key))
				{
					if(Permission::check($key))
						return true;
				}
			}
		}
		
		if(is_object($row) && $row->admin_rights) {
			return Permission::check($row->admin_rights);
		}
		
		if($this->admin_rights) {
			return Permission::check($this->admin_rights);
		}
		
		return false;
	}
	
	/**
	 * returns if a given record can be inserted in database
	 *
	 *@name canInsert
	 *@access public
	 *@param array - reocrd
	*/
	public function canInsert($row = null)
	{
		if($this->insertRights) {
			if(Permission::check($this->insertRights))
				return true;
		}
		$provided = $this->providePerms();
		if(count($provided) == 1) {
			$keys = array_keys($provided);
			
			if(Permission::check($keys[0]))
				return true;
		} else if(count($provided) > 1) {
			foreach($provided as $key => $arr)
			{
				if(preg_match("/all$/i", $key))
				{
					if(Permission::check($key))
						return true;
				}
				
				if(preg_match("/insert$/i", $key))
				{
					if(Permission::check($key))
						return true;
				}
			}
		}
		
		if(is_object($row) && $row->admin_rights) {
			return Permission::check($row->admin_rights);
		}
		
		if($this->admin_rights) {
			return Permission::check($this->admin_rights);
		}
		
		return false;
	}
	
	/**
	 * gets the writeaccess
	 *@name getWriteAccess
	 *@access public
	*/
	public function getWriteAccess()
	{
			if($this->canWrite($this))
			{
					return true;
			} else if($this->canDelete($this))
			{
					return true;
			}
			
			return false;
	}
	
	/**
	 * returns if publish-right is available
	 *
	 *@name canPublish
	 *@access public
	*/
	public function canPublish($record = null) {
		return true;
	}
	
	/**
	 * right-management
	*/
	
	/**
	 * events
	*/
	/**
	 *@name onBeforeDelete
	 *@return bool
	*/
	public function onBeforeRemove(&$manipulation)
	{
		
	}
	
	/**
	 *@name onAfterRemove
	 *@return bool
	*/
	public function onAfterRemove()
	{
			
	}
	
	/**
	 *@name beforeRead
	 *@return bool
	*/
	public function onbeforeRead(&$data)
	{
			$this->callExtending("onBeforeRead", $data);
	}
	
	/**
	 * will be called before write
	 *@name onBeforeWrite
	 *@access public
	*/
	public function onBeforeWrite()
	{
		$dummy = null;
		$this->callExtending("onBeforeWrite", $dummy);
	}
	
	/**
	 * before manipulating the data
	 *@name onbeforeManipulate
	 *@access public
	 *@param manipulation
	*/
	public function onbeforeManipulate(&$manipulation)
	{
		$this->callExtending("onBeforeManipulate", $manipulation);
	}
	
	/**
	 * before updating data-tables to write data
	 *
	 *@name onBeforeWriteData
	 *@access public
	*/
	public function onBeforeWriteData() {
		$this->callExtending("onBeforeWriteData");
	}
	
	/**
	 * is called before unpublish
	*/
	public function onBeforeUnpublish() {
		
	}
	
	/**
	 * is called before publish
	*/
	public function onBeforePublish() {
		
	}
	
	/**
	 * write data
	 *@name write
	 *@access public
	 *@param bool - to force insert
	 *@param bool - to force write
	 *@param numeric - priority of the snapshop: autosave 0, save 1, publish 2
	 *@return bool
	*/
	public function write($forceInsert = false, $forceWrite = false, $snap_priority = 2, $forcePublish = false, $history = true)
	{
		if(!defined("CLASS_INFO_LOADED")) {
			throwError(6, "Logical Exception", "Calling DataObject::write without loaded classinfo is not allowed.");
		}
		
		// check if table in db and if not, create it
		if($this->baseTable != "" && !isset(ClassInfo::$database[$this->baseTable])) {
			foreach(array_merge(ClassInfo::getChildren($this->class), array($this->class)) as $child) {
				Object::instance($child)->buildDB();
			}
			ClassInfo::write();
		}
		
		if($this->data === null)
			return true;
		
		if(
			!$forceWrite &&
			!$forceInsert &&  
			$this->original == $this->data && 
				(
				$this->versioned == false || 
				$snap_priority < 2 || 
					(
					$this->isPublished() &&
					$this->stateid == $this->versionid
					)
				)
			) 
		{
			return true;
		}
		
		if(PROFILE) Profiler::mark("DataObject::write");
		
		// if we insert, we don't have an ID
		if($forceInsert) {
			$this->consolidate();
			$this->data["id"] = 0;
			$this->data["versionid"] = 0;
		}
		
		$this->onBeforeWrite();
		
		if(isset(ClassInfo::$class_info[$this->class]["baseclass"]))
			$baseClass = ClassInfo::$class_info[$this->class]["baseclass"];
		else
			$baseClass = $this->class;
		
		$oldid = 0;
		
		self::$query_cache = array();
		
		// if we don't insert we merge the old record with the new one
		if($forceInsert || $this->versionid == 0) {
			
			// check rights
			if(!$forceInsert && !$forceWrite) {
				if(!$this->canInsert($this)) {
					if($snap_priority == 2 && !$this->canPublish($this))
						return false;
				}
			}
			
			$command = "insert";
			
			// get new data
			$newdata = $this->data;
		} else {
			// get old record
			$data = DataObject::get($baseClass, array("versionid" => $this->versionid));
			
			if($data->count() > 0) {
				// check rights
				if(!$forceWrite)
					if(!$this->canWrite($this))
						if($snap_priority == 2 && !$this->canPublish($this))
							return false;
				
				$command = "update";
				$newdata = array_merge($data->first()->ToArray(), $this->data);
				$this->data = $data->first()->ToArray();
				$newdata["created"] = $data["created"]; // force
				$newdata["autorid"] = $data["autorid"];
				$oldid = $data->versionid;
				
				// copy many-many-relations
				foreach($this->many_many as $name => $class) {
					if(!isset($newdata[$name . "ids"]) && !isset($newdata[$name]))
						$newdata[$name . "ids"] = $this->getRelationIDs($name);
					else if(!isset($newdata[$name . "ids"]) && is_array($newdata[$name]))
						$newdata[$name . "ids"] = $newdata[$name];
				}
				foreach($this->belongs_many_many as $name => $class) {
					if(!isset($newdata[$name . "ids"]) && !isset($newdata[$name]))
						$newdata[$name . "ids"] = $this->getRelationIDs($name);
					else if(!isset($newdata[$name . "ids"]) && is_array($newdata[$name]))
						$newdata[$name . "ids"] = $newdata[$name];
				}
				unset($data);
			} else {

				// check rights
				if(!$forceInsert)
					if(!$this->canInsert($this->data))
						return false;
				// old record doesn't exist, so we create a new record
				$command = "insert";
				$newdata = $this->data;
			}
			
			
		}
		
		// first step: decorate the important fields
		if($command == "insert") {
			$newdata["created"] = NOW;
			$newdata["autorid"] = member::$id;
		}
		
		$newdata["last_modified"] = NOW;
		$newdata["editorid"] = member::$id;
		$newdata["snap_priority"] = $snap_priority;
		$newdata["class_name"] = $this->isField("class_name") ? $this->fieldGET("class_name") : $this->class;
		
		// second step: if insert, add new record in state-table and get the auto-increment-id
		if($command == "insert") {
			$manipulation = array(
				$baseClass . "_state" => array(
					"table_name"=> $this->baseTable . "_state",
					"command"	=> "insert",
					"fields"	=> array(
						"stateid"		=> 0,
						"publishedid"	=> 0
					)
				)
			);
			
			SQL::writeManipulation($manipulation);
			unset($manipulation);
			$id = sql::insert_id();
			$newdata["id"] = $id;
			$newdata["recordid"] = $id;
			unset($id);
		} else if(!isset($this->data["publishedid"])) {
			$query = new SelectQuery($this->baseTable . "_state", array("id"), array("id" => $this->recordid));
			if($query->execute()) {
				$data = $query->fetch_assoc();
				if(!isset($data["id"])) {
					$manipulation = array(
						$baseClass . "_state" => array(
							"table_name"=> $this->baseTable . "_state",
							"command"	=> "insert",
							"fields"	=> array(
								"id"	=> $this->recordid
							)
						)
					);
					SQL::writeManipulation($manipulation);
					unset($manipulation);
					$newdata["id"] = $this->recordid;
				}
					
			} else {
				if(PROFILE) Profiler::unmark("DataObject::writeRecord");
				throwErrorByID(5);
			}
		}
				
		if($this->has_one) {
			foreach($this->has_one as $key => $value) {
				if(isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "DataObject")) {
					// we just write this if we have really new data
					// first check if there is data
					// then if data was changed or data is inserted
					if($newdata[$key]->bool() && ($newdata[$key]->original != $newdata[$key]->data || $command == "insert")) {
						$newdata[$key]->write(false, true, $snap_priority);
					 	$newdata[$key . "id"] = $newdata[$key]->id;
					} else {
						$newdata[$key . "id"] = $newdata[$key]->id;
					}
					unset($newdata[$key]);
				}
			}
		}
		
		$many_many_objects = array();
		$many_many_data = array();
		
		// here the magic for many-many happens
		if($this->many_many) {
			foreach($this->many_many as $key => $value) {
				if(isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "ManyMany_DataObjectSet")) {
					$many_many_objects[$key] = $newdata[$key];
					$many_many_data[$key] = $value;
					unset($newdata[$key]);
				}
				unset($key, $value);
			}
		}
		
		if($this->belongs_many_many) {
			foreach($this->belongs_many_many as $key => $value) {
				if(isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "ManyMany_DataObjectSet")) {
					$many_many_objects[$key] = $newdata[$key];
					$many_many_data[$key] = $value;
					unset($newdata[$key]);
				}
				unset($newdata[$key], $key, $value);
			}
		}
		
		unset($newdata["versionid"]);
		
		// find out if we should write data
		if($command != "insert") {
			$changed = array();
			$forceChange = $forceWrite;
			
			// first calculate change-count
			foreach($this->data as $key => $val) {
				if(isset($newdata[$key]) && $newdata[$key] != $val) {
					$changed[$key] = $newdata[$key];
				}
			}
			
			// check for relation
			if(count($many_many_objects) > 0) {
				$forceChange = true;
			}
			
			// many-many
			if(!$forceChange && $this->many_many)
				foreach($this->many_many as $name => $table)
				{
						if(isset($newdata[$name . "ids"]) && is_array($newdata[$name . "ids"]))
						{
								$forceChange = true;
								break;
						}
				}
			
			// many-many
			if(!$forceChange && $this->belongs_many_many)
				foreach($this->belongs_many_many as $name => $table)
				{
						
						if(isset($newdata[$name . "ids"]) && is_array($newdata[$name . "ids"]))
						{
								$forceChange = true;
								break;
						}	
				}
			
			// has-many
			if(!$forceChange && $this->has_many)
				foreach($this->has_many as $name => $class)
				{
						if(isset($newdata[$name]) && !isset($newdata[$name . "ids"]))
							$newdata[$name . "ids"] = $newdata[$name];
						if(isset($newdata[$name . "ids"]) && is_array($newdata[$name . "ids"]))
						{
								$forceChange = true;				
						}						
				}
			
			// now check if we should write or not
			if(!$forceChange && ((count($changed) == 1 && isset($changed["last_modified"])) || (count($changed) == 2 && isset($changed["last_modified"], $changed["editorid"])))) {
				// should we really write this?! No!
				return true;
			}
		}
		
		// WE CAN WRITE!
		
		// now set the correct data
		$this->data = $newdata;
		$this->viewcache = array();
		
		// write data
		
		
		$manipulation = array(
			$baseClass => array(
				"command"	=> "insert",
				"fields"	=> array_merge(array(
					"class_name"	=> $this->class,
					"last_modified" => NOW
				), $this->getFieldValues($baseClass, $command))
			)
		);
		
		SQL::manipulate($manipulation);
		$this->data["versionid"] = SQL::Insert_ID();
		
		$manipulation = array();
		
		if($dataclasses = ClassInfo::DataClasses($baseClass)) 
		{
				foreach($dataclasses as $class => $table)
				{
						$manipulation[$class . "_clean"] = array(
							"command"	=> "delete",
							"table_name"=> ClassInfo::$class_info[$class]["table_name"],
							"id"		=> $this->versionid
						);
						$manipulation[$class] = array(
							"command"	=> "insert",
							"fields"	=> array_merge(array(
								"id" 			=> $this->versionid
							), $this->getFieldValues($class, $command))
						);
				
				}
		}
		
		// relation-data
		
		foreach($many_many_objects as $key => $object) {
			$object->setRelationENV($key, $this->many_many_tables[$key]["extfield"], $this->many_many_tables[$key]["table"], $this->many_many_tables[$key]["field"], $this->data["versionid"], isset($many_many_data[$key]["extraFields"]) ? $many_many_data[$key]["extraFields"] : array());
			$object->write(false, true);
			unset($this->data[$key . "ids"]);
		}
		
		// many-many
		if($this->many_many)
			foreach($this->many_many as $name => $table)
			{
					if(isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
					{
							$manipulation = $this->set_many_many_manipulation($manipulation, $name, $this->data[$name . "ids"]);
					}
					
					
			}
		
		if($this->belongs_many_many)
			foreach($this->belongs_many_many as $name => $table)
			{
					
					if(isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
					{
							$manipulation = $this->set_many_many_manipulation($manipulation, $name, $this->data[$name . "ids"]);
					}
					
					
			}
		
		// has-many
		if($this->has_many)
			foreach($this->has_many as $name => $class)
			{
					if(isset($this->data[$name]) && !isset($this->data[$name . "ids"]))
						$this->data[$name . "ids"] = $this->data[$name];
					if(isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
					{
							// find field
							$key = array_search($this->class, ClassInfo::$class_info[$class]["has_one"]);
							if($key === false)
							{
									$c = $this->class;
									while($c = strtolower(get_parent_class($c)))
									{
											if($key = array_search($c, ClassInfo::$class_info[$class]["has_one"]))
											{
													break;
											}
									}
							}
				
							if($key === false)
							{
									return false;
							}
							
							foreach($this->data[$name . "ids"] as $id) {
								$editdata = DataObject::get($class, array("id" => $id));
								$editdata[$key . "id"] = $this->id;
								$editdata->write(false, true, $snap_priority);
								unset($editdata);
							}				
					}						
			}
			
	
		
		// add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
		if($oldid != 0) {
			$class = $this->class;
			while($class != "dataobject") {
				if(isset(ClassInfo::$class_info[$class]["belongs_many_many_extra"])) {
					foreach(ClassInfo::$class_info[$class]["belongs_many_many_extra"] as $data) {
						$manipulation[$data["table"]] = array(
							"command" 		=> "insert",
							"table_name"	=> $data["table"],
							"fields"		=> array(
							
							)
						);
						
						// get data from table
						$query = new SelectQuery($data["table"], array($data["extfield"]), array($data["field"] => $oldid));
						if($query->execute()) {
							while($result = $query->fetch_assoc()) {
								$manipulation[$data["table"]]["fields"][] = array(
									$data["field"] => $this->versionid,
									$data["extfield"] => $result[$data["extfield"]]
								);
							}
						}	
					}
				}
				$class = ClassInfo::getParentClass($class);
			}
		}
		
		self::$query_cache = array();
		
		// get correct oldid for history
		if($data = DataObject::get_one($this->class, array("id" => $this->RecordID))) {
			$historyOldID = ($data["publishedid"] == 0) ? $data["publishedid"] : $data["stateid"];
		} else {
			$historyOldID = isset($oldid) ? $oldid : 0;
		}
		
		// fire events!
		$this->onBeforeWriteData();
		$this->onBeforeManipulate($manipulation);
		
		// fire manipulation to DataBase
		if(SQL::manipulate($manipulation)) {
			
			if($this->versionid == 0)
				return false;
			
			
			// update state-table
			if(!$this->versioned || $snap_priority == 2) {
				if($this->versioned) {
					$this->onBeforePublish();
					$this->callExtending("onBeforePublish");
					if(!$forcePublish) {
						if(!$forceWrite) {
							if(!$this->canPublish($this)) {
								if(PROFILE) Profiler::unmark("DataObject::write");
								return false;
							}
						}
					}
				}
				
				$manipulation = array($baseClass . "_state" => array(
					"command"		=> "update",
					"table_name" 	=> $this->baseTable . "_state",
					"id"			=> $this->id,
					"fields"		=> array(
						"publishedid"	=> $this->versionid,
						"stateid"		=> $this->versionid
					)
				));
				
				$command = "publish";
			} else {
				$manipulation = array($baseClass . "_state" => array(
					"command"		=> "update",
					"table_name" 	=> $this->baseTable . "_state",
					"id"			=> $this->id,
					"fields"		=> array(
						"stateid"		=> $this->versionid
					)
				));
			}
			
			$this->onBeforeManipulate($manipulation);
			if(SQL::manipulate($manipulation)) {
				
				if(ClassInfo::getStatic($this->class, "history") && $history) {
					if($command == "insert" || !isset($changed)) {
						$changed = $this->data;
					}
					History::push($this->class, $historyOldID, $this->versionid, $this->id, $command, $changed);
				}
				unset($manipulation);
				// if we don't version this dataobject, we need to delete the old record
				if(!$this->versioned && isset($oldid) && $command != "insert") {
					$manipulation = array(
						$baseClass => array(
							"command"	=> "delete",
							"where" 	=> array(
								"id" => $oldid
							)
						)
					);
					
					if($dataclasses = ClassInfo::DataClasses($baseClass)) 
					{
						foreach($dataclasses as $class => $table)
						{
							$manipulation[$class] = array(
								"command"	=> "delete",
								"where" 	=> array(
									"id" => $oldid
								)
							);
						}
					}
					
					// clean-up-many-many
					foreach($this->generateManyManyTables() as $data) {
						$manipulation[$data["table"]] = array(
							"table" 	=> $data["table"],
							"command"	=> "delete",
							"where"		=> array(
								$data["field"] => $oldid
							)
						);
					}
					
					$this->callExtending("deleteOldVersions", $manipulation, $oldid);
					
					SQL::manipulate($manipulation);
					if(PROFILE) Profiler::unmark("DataObject::write");
					return true;
				} else {
					if(PROFILE) Profiler::unmark("DataObject::write");
					return true;
				}
			} else {
				if(PROFILE) Profiler::unmark("DataObject::write");
				return false;
			}
			
		} else {
			if(PROFILE) Profiler::unmark("DataObject::write");
			return false;
		}
			
	}
	
	/**
	 * unpublishes the record
	 *
	 *@name unpublish
	 *@access public
	*/
	public function unpublish($force = false, $history = true) {
		if((!$this->canPublish($this)) && !$force)
			return false;
		
		$manipulation = array(
			$this->baseTable . "_state" => array(
				"table_name" 	=> $this->baseTable . "_state",
				"command"		=> "update",
				"id"			=> $this->recordid,
				"fields"		=> array(
					"publishedid"	=> 0
				)
			)
		);
		
		$this->onBeforeUnPublish();
		$this->callExtending("OnBeforeUnPublish");
		
		$this->onBeforeManipulate($manipulation);
		
		if(SQL::manipulate($manipulation)) {
			if(ClassInfo::getStatic($this->class, "history") && $history) {
				History::push($this->class, 0, $this->versionid, $this->id, "unpublish");
			}
			return true;
		}
		
		return false;
	}
	
	/**
	 * publishes the record
	 *
	 *@name publish
	 *@access public
	*/
	public function publish($force = false, $history = true) {
		if((!$this->canPublish($this)) && !$force)
			return false;
		
		if($this->isPublished())
			return true;
		
		$manipulation = array(
			$this->baseTable . "_state" => array(
				"table_name" 	=> $this->baseTable . "_state",
				"command"		=> "update",
				"id"			=> $this->recordid,
				"fields"		=> array(
					"publishedid"	=> $this->versionid
				)
			)
		);
		
		$this->onBeforePublish();
		$this->callExtending("OnBeforePublish");
		
		$this->onBeforeManipulate($manipulation);
		
		if(SQL::manipulate($manipulation)) {
			if(ClassInfo::getStatic($this->class, "history") && $history) {
				History::push($this->class, 0, $this->versionid, $this->id, "publish");
			}
			return true;
		}
		
		return false;
	}
	
	/**
	 * returns if this version of the record is published
	 *
	 *@name isPublished
	 *@access public
	*/
	public function isPublished() {
		
		if(isset($this->data["publishedid"])) {
			return ($this->publishedid != 0 && $this->versionid == $this->publishedid);
		} else {
			return false;
		}
	}
	
	/** 
	 * gives back if ever published
	 *
	 *@name isPublished
	 *@access public
	*/
	public function everPublished() {
		
		if(isset($this->data["publishedid"]) && $this->data["publishedid"]) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * returns if baseRecord is deleted
	 *
	 *@name isDeleted
	 *@access public
	*/
	public function isDeleted() {
		if(isset($this->data["publishedid"]) || DataObject::count("pages", array("id" => $this->id) > 0)) {
			return false;
		} else
			return true;
	}
	
	/**
	 * gets versions of this ordered by time DESC
	 *
	 *@name versions
	 *@access public
	*/
	public function versions($limit = null, $where = array(), $orderasc = false) {
		$ordertype = ($orderasc === true) ? "ASC" : "DESC";
		return DataObject::get_versioned($this->class, false, array_merge($where,array(
			"recordid"	=> $this->recordid
		)),  array($this->baseTable . ".id", $ordertype));
	}
	
	/**
	 * gets versions of this ordered by time ASC
	 *
	 *@name versions
	 *@access public
	*/
	public function versionsASC($limit = null, $where = array()) {
		return $this->versions($limit, $where, true);
	}
	
	/**
	 * gets the editor
	 *
	 *@name editor
	 *@access public
	*/
	public function editor() {
		if($this->fieldGet("editorid") != 0) {
			return DataObject::get($this->has_one["editor"],array('id' => $this['autorid']));
		} else
			return $this->autor();
	}
	
	//!TODO: Where is this necessary???
	/**
	 * gets field-value-pairs for a given class of the current data
	 * so you can get an array for each class of the fields
	 *
	 *@name getFieldValues
	 *@access public
	 *@param string - class or table-name
	 *@param string - command
	*/
	public function getFieldValues($class, $command)
	{
			$arr = array();
			if(isset(ClassInfo::$class_info[$class]["db_fields"]))
			{
				if(isset(ClassInfo::$database[ClassInfo::$class_info[$class]["table_name"]])) {
					foreach(ClassInfo::$database[ClassInfo::$class_info[$class]["table_name"]] as $field => $type)
					{
						if($field != "id") {
							if(isset($this->data[$field])) {
								if(is_object($this->data[$field])) {
									if(Object::method_exists($this->data[$field], "raw")) {
										$arr[$field] = $this->data[$field]->raw();
									} else {
										$arr[$field] = $this->data[$field];
									}
								} else {
									$arr[$field] = $this->data[$field];
								}
								
							} else if(isset($this->data[strtolower($field)])) {
								if(is_object($this->data[strtolower($field)])) {
									if(Object::method_exists($this->data[strtolower($field)], "raw")) {
										$arr[$field] = $this->data[strtolower($field)]->raw();
									} else {
										$arr[$field] = $this->data[strtolower($field)];
									}
								} else {
									$arr[$field] = $this->data[strtolower($field)];
								}
								
							} else if($command == "insert" && isset($this->defaults[$field])) {
								$arr[$field] = $this->defaults[$field];
							}
						}
					}
					
					if(isset(classinfo::$class_info[$class]["db_fields"]["last_modfied"]))
						$arr["last_modfied"] = NOW;
				}
			} else if(isset(ClassInfo::$database[$class])) {
				foreach(ClassInfo::$database[$class] as $field => $type)
				{
					if(strtolower($field) != "id") {
						if(isset($this->data[$field])) {
							if(is_object($this->data[$field])) {
								if(Object::method_exists($this->data[$field], "raw")) {
									$arr[$field] = $this->data[$field]->raw();
								} else {
									$arr[$field] = $this->data[$field];
								}
							} else {
								$arr[$field] = $this->data[$field];
							}
						} else if(isset($this->data[strtolower($field)])) {
							if(is_object($this->data[strtolower($field)])) {
								if(Object::method_exists($this->data[strtolower($field)], "raw")) {
									$arr[$field] = $this->data[strtolower($field)]->raw();
								} else {
									$arr[$field] = $this->data[strtolower($field)];
								}
							} else {
								$arr[$field] = $this->data[strtolower($field)];
							}
						} else if($command == "insert" && isset($this->defaults[$field])) {
							$arr[$field] = $this->defaults[$field];
						}
					}
				}
				
				if(isset(ClassInfo::$class_info[$class]["db_fields"]["last_modfied"]))
					$arr["last_modfied"] = NOW;
			}
			
			return $arr;
	}
	
	/**
	 * modfies the manipulation-var for the given many_many-relation
	 *
	 *@name set_many_many_manipulation
	 *@access protected
	 *@param array - manipulation
	 *@param string - relation
	 *@param array - ids of relation
	*/
	public function set_many_many_manipulation($manipulation, $relation, $ids)
	{
			if(isset($this->many_many[$relation]))
			{
					$object = $this->many_many[$relation];

					$table_name = ClassInfo::$class_info[$object]["table_name"];
					
			} else if(isset($this->belongs_many_many[$relation]))
			{
					$object = $this->belongs_many_many[$relation];

					$table_name = ClassInfo::$class_info[$object]["table_name"];
					
			}
			if(isset($this->many_many_tables[$relation]))
			{
					$table = $this->many_many_tables[$relation]["table"];
					$data = $this->many_many_tables[$relation];
			} else
			{
					return false;
			}
			
			// check for existing entries
			$sql = "SELECT ".$object . "id"." FROM ".DB_PREFIX . $table." WHERE ".$data["field"]." = ".$this["versionid"]."";
			if($result = SQL::Query($sql)) {
				$existing = array();
				while($row = SQL::fetch_object($result)) {
					$existing[] = $row->{$object . "id"};
				}
			} else {
				throwErrorByID(5);
			}
			
			
			$mani_insert = array(
				"table_name"	=> $table,
				"command"		=> "insert",
				"fields"		=> array(
					
				)
			);
			
			foreach($ids as $id)
			{
				if(!in_array($id, $existing)) {
					$mani_insert["fields"][] = array(
						$data["field"] 	=> $this["versionid"],
						$object . "id" 	=> $id
					);
				}
			}
			
			$manipulation[$table . "_insert"] = $mani_insert;
			
			
			return $manipulation;
	}
	
	
	/**
	 * deletes the record
	 *
	 *@name remove
	 *@access public
	 *@param bool - force delete
	 *@param bool - if cancel on error, or resume
	 *@param bool - if force to delete versions, too
	*/
	public function remove($force = false, $forceAll = false, $history = true)
	{
		// check if table in db and if not, create it
		if($this->baseTable != "" && !isset(ClassInfo::$database[$this->baseTable])) {
			foreach(array_merge(ClassInfo::getChildren($this->class), array($this->class)) as $child) {
				Object::instance($child)->buildDB();
			}
			ClassInfo::write();
		}
		
		$manipulation = array();
		$baseClass = ClassInfo::$class_info[$this->RecordClass]["baseclass"];
			
		if(!isset($this->data))
			return true;
		
		if($force || $this->canDelete($this))
		{
				// get the ids which are needed
				$ids = array();
				$query = new SelectQuery($this->baseTable, array("id"), array("recordid" => $this->id));
				if($query->execute()) {
					while($row = $query->fetch_object())
						$ids[] = $row->id;
				} else {
					throwErrorByID(3);
				}
				// delete connection in state-table
				
				// base class
				if(!isset($manipulation[$baseClass . "_state"]))
					$manipulation[$baseClass . "_state"] = array(
						"command"		=> "delete",
						"table_name"	=> $this->baseTable . "_state",
						"where"			=> array(
						
					));
					
				$manipulation[$baseClass . "_state"]["where"]["id"][] = $this->id;
				
				// if not versioning, delete data, too
				if(!$this->versioned || $forceAll || !isset($this->data["stateid"])) {
					// clean up data-tables
					
					if(!isset($manipulation[$baseClass])) {
						$manipulation[$baseClass] = array(
							"command"	=> "delete",
							"where" 	=> array()
						);
					}
					if(!isset($manipulation[$baseClass]["where"]["id"]))
						$manipulation[$baseClass]["where"]["id"] = array();
					
					$manipulation[$baseClass]["where"]["id"] = array_merge($manipulation[$baseClass]["where"]["id"], $ids);
					
					// subclasses
					if($classes = ClassInfo::dataclasses($this->class))
					{							
							foreach($classes as $class => $table)
							{
									if(isset(ClassInfo::$database[$table]) && $class != $this->class)
									{
											if(!isset($manipulation[$class])) {
												$manipulation[$class] = array(
													"command"	=> "delete",
													"where" 	=> array()
												);
											}
											if(!isset($manipulation[$class]["where"]["id"]))
												$manipulation[$class]["where"]["id"] = array();
					
											$manipulation[$class]["where"]["id"] = array_merge($manipulation[$class]["where"]["id"], $ids);
									}
							}
					}
					
					// clean-up-many-many
					foreach($this->generateManyManyTables() as $data) {
						$manipulation[$data["table"]] = array(
							"table" 	=> $data["table"],
							"command"	=> "delete",
							"where"		=> array(
								$data["field"] => $ids
							)
						);
					}
					
				}
		} else {
			return false;
		}
		
		$this->disconnect();
		
		self::$query_cache = array();
		
		$this->onBeforeRemove($manipulation);
		$this->callExtending("onBeforeRemove", $manipulation);
		if(SQL::manipulate($manipulation)) {
			if(ClassInfo::getStatic($this->class, "history") && $history) {
				History::push($this->class, 0, $this->versionid, $this->id, "remove");
			}
			$this->onAfterRemove($this);
			$this->callExtending("onAfterRemove", $this);
			unset($this->data);
			return true;
		} else {
			return false;
		}
		
		
	}
	
	/**
	 * disconnects from relation
	 *
	 *@name disconnect
	 *@access public
	*/
	public function disconnect($val = null) {
		if(isset($this->dataset)) {
			$this->dataset->removeRecord($this->dataSetPosition);
			if(isset($val))
				$val->removeRecord($this->dataSetPosition);
		}
	}

	/*Form-Management*/
	/**
	 * gets the form
	 *@name getForm
	 *@param object - form-object
	*/
	public function getForm(&$form)
	{
			$form->setResult($this);
	
	}
	
	/**
	 * gets the form-actions
	 *@name getActions
	 *@param object - form-object
	*/
	public function getActions(&$form, $edit = false)
	{
			$form->setResult($this);
	
	}
	
	/**
	 * returns a list of fields you want to show if we use the history-compare-view
	 *
	 *@name getVersionedFields
	 *@access public
	*/
	public function getVersionedFields() {
		return array();
	}
	
	/**
	 * getFormFromDB
	 * generates the form-fields from the db-fields
	 *
	 *@name getFormFromDB
	 *@access public
	*/
	public function getFormFromDB(&$form) {
		$this->fieldTitles = array_merge($this->fieldTitles, $this->getFieldTitles());
		foreach($this->db_fields as $field => $type) {
			if(isset($this->fieldTitles[$field])) {
				$form->add($formfield = $this->doObject($field)->formField($this->fieldTitles[$field]));
				if(isset($this->fieldInfo[$field])) {
					$formfield->info = parse_lang($this->fieldInfo[$field]);
				}
				unset($formfield);
			}
		}
	}
	
	/**
	 * gets on the fly generated field titles
	*/
	public function getFieldTitles() {
		return array();
	}
	
	/**
	 * geteditform
	 *@name geteditform
	 *@param object
	 *@param array - data
	*/
	public function getEditForm(&$form)
	{
			$this->getForm($form);			
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
	public function generateForm($name = null, $edit = false, $disabled = false, $request = null) {
		
		// if name is not set, we generate a name from this model
		if(!isset($name)) {
			$name = $this->class . "_" . $this->versionid . "_" . $this->id;
		}
		
		$form = new Form($this->controller(), $name, array(), array(), array(), $request, $this);
		
		// default submission
		$form->setSubmission("submit_form");	
			
		$form->addValidator(new DataValidator($this), "datavalidator");
		
		$form->setResult(clone $this);
		
		// some default fields
		if($this->recordid) {
			$form->add(new HiddenField("id", $this->recordid));
			$form->add(new HiddenField("versionid", $this->versionid));
			$form->add(new HiddenField("recordid", $this->recordid));
		}
		
		$form->add(new HiddenField("class_name", $this->class));
		
		// render form
		if($edit) {
			$this->getEditForm($form);
		} else {
			$this->getForm($form);
		}
		
		$this->callExtending('getForm', $form, $edit);
		$this->getActions($form, $edit);
		$this->callExtending('getActions', $form, $edit);
		
		if($disabled) {
			$form->disable();
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
	
	/**
	 * gets a list of all fields with according titles of this object
	 *
	 *@name summaryFields
	 *@access public
	*/
	public function summaryFields() {
		$f = ArrayLib::key_value(array_keys($this->db_fields));
		$this->fieldTitles = array_merge($this->fieldTitles, $this->getFieldTitles());
		
		unset($f["autorid"]);
		unset($f["editorid"]);
		
		$fields = array();
		
		foreach($f as $field) {
			$field = trim($field);
			
			if(isset($this->fieldTitles[$field])) {
				$fields[$field] = parse_lang($this->fieldInfo[$field]);
			} else {
				if($field == "name") {
					$fields[$field] = lang("name");
				} else if($field == "title") {
					$fields[$field] = lang("title");
				} else if($field == "description") {
					$fields[$field] = lang("description");
				} else if($field == "content") {
					$fields[$field] = lang("content");
				} else if($field == "filename") {
					$fields[$field] = lang("filename");
				} else if($field == "email") {
					$fields[$field] = lang("email");
				} else {
					$fields[$field] = $field;
				}
			}
		}
		
		return $fields;
	}
	
	/**
	  * set many-many-connection-data
	  *
	  *@name set_many_many
	  *@param stirng - name of connection
	  *@param array - ids to connect with current id
	  *@param bool - override permission system
	  *@access public
	*/
	public function set_many_many($name, $ids, $force = false)
	{
		if($this->canWrite($this)) {
			if(!$this->isPublished() || $this->canPublish($this)) {
				$manipulation = $this->set_many_many_manipulation(array(), $name, $ids);
				
				$this->onBeforeManipulate($manipulation);
				
				return SQL::manipulate($manipulation);
			}
		}
		
		return false;
	}
	
	/**
	 * checks if the current id is connected with the given id
	 *@name is_many_many
	 *@access public
	 *@param string - connection
	 *@param numeric - id
	*/
	public function is_many_many($name, $id)
	{
			// there are two ways defining a many-many-relation: with belongs_many_many and many_many
			if(isset($this->many_many[$name]))
			{
					$_table = $this->many_many[$name];

					$__table = classinfo::$class_info[$_table]["table_name"];
					
			} else if(isset($this->belongs_many_many[$name]))
			{
					$_table = $this->belongs_many_many[$name];

					$__table = classinfo::$class_info[$_table]["table_name"];
					
			}
			
			/**
			 * there is the var many_many_tables, which contains data for the table, which stores the relation
			 * for exmaple: array(
			 * "table"	=> "my_many_many_table_generated_by_system",
			 * "field"	=> "myclassid"
			 * )
			*/
			
			if(isset($this->many_many_tables[$name]))
			{
					$table = $this->many_many_tables[$name]["table"];
					$data = $this->many_many_tables[$name];
			} else
			{
					return false;
			}
			
			// use count-function:
			// - first argument: object
			// - second argument: where-clause - defines the where-conditions between the tables
			// - third argument: Joins - connects the tables through the relation-table
			// - fourth: groupby - not used here
			return (
						DataObject::count(	$_table, 
											array(	$__table.'.id' 				=> $id, 
													$this->table_name . '.id' 	=> $this["versionid"]), 
											array(
												' INNER JOIN '.DB_PREFIX . $table.' AS '.$table.' ON '.$table.'.'.$_table . 'id = '.$__table.'.id ', // Join other table with many-many-table
												' INNER JOIN '.DB_PREFIX . $this->table_name.' AS '.$this->table_name.' ON '.$table.'.'. $data["field"] . ' = '.$this->table_name.'.id ' // join this table with many-many-table
											)
										) > 0
					);
	
	}
	
	/**
	 * new get method
	 *
	 *@name __get
	 *@access public
	*/
	public function __get($offset) {
		
		if(strtolower($offset) == "basetable")
			return $this->BaseTable();
		
		$data = parent::__get($offset);
		$offset = strtolower($offset);
		if($this->convertDefault === false || !isset($this->casting[$offset])) {
			return $data;
		}
		
		return DBField::convertByCastingIfDefault($this->casting[$offset], $offset, $data);
	}
	/**
	 * checks if a method "set" . $offset exists
	 *@name isSetMethod
	 *@access public
	 *@param string - offset
	*/
	public function isSetMethod($offset)
	{
			return (Object::method_exists($this->class, "set" . $offset) && !in_array(strtolower("set" . $offset), self::$notViewableMethods));
	}
	/**
	 * calls a method "set" . $offset
	 *@name callSetMethod
	 *@access public
	 *@param string - offset
	 *@param mixed - value
	*/
	public function callSetMethod($offset, $value)
	{
		$func = "set" . $offset;
		return call_user_func_array(array($this, $func), array($value));
		
	}
	
	/**
	 * gets relation ids
	 *
	 *@name getRelationIDs
	 *@access public
	*/
	public function getRelationIDs($relname) {
		$relname = trim(strtolower($relname));
		
		if(isset($this->has_many[$relname])) {
				// has-many
				/**
				 * getMany returns a DataObject
				 * parameters:
				 * name of relation
				 * where
				 * fields
				 */
				if($data = $this->getHasMany($relname)) {
					
					// then get all data in one array with key - id pairs
					
					$arr = array();
					foreach($data->ToArray() as $key => $value)
					{
							$arr[] = $value["id"];
					}
					return $arr;
				} else {
					return array();
				}
		} else if(isset($this->many_many[$relname]) || isset($this->belongs_many_many[$relname])) {
			if(isset($this->data[$relname . "ids"]))
				return $this->data[$relname . "ids"];
			
			/**
			 * there is the var many_many_tables, which contains data for the table, which stores the relation
			 * for exmaple: array(
			 * "table"	=> "my_many_many_table_generated_by_system",
			 * "field"	=> "myclassid"
			 * )
			*/
			
			if(isset($this->many_many_tables[$relname]))
			{
					$table = $this->many_many_tables[$relname]["table"]; // relation-table
					$data = $this->many_many_tables[$relname];
			} else
			{
					return false;
			}

			$query = new SelectQuery($table, array($data["extfield"]), array($data["field"] => $this["versionid"]));	
			
			$query->execute();			
			$arr = array();
			while($row = $query->fetch_assoc())
			{
					$arr[] = $row[$data["extfield"]];
			}
			$this->data[$relname . "ids"] = $arr;
			return $arr;
		} else {
			return false;
		}
	}
	
	/**
	 * gets the data object for a has-many-relation
	 *
	 *@name getHasMany
	 *@access public
	*/
	public function getHasMany($name) {
		
		$name = trim(strtolower($name));
		
		if(!isset($this->has_many[$name]))
		{
			$debug = debug_backtrace();
			throwError(6, "PHP-Error", "No Has-many-relation '".$name."' on ".$this->class." in ".$trace[1]["file"]." on line ".$trace[1]["line"].".");
		}
		
		$cache = "has_many_{$name}";
		if(isset($this->viewcache[$cache])) {
			return $this->viewcache[$cache];
		}
		
		$class = $this->has_many[$name];
		$key = array_search($this->class, ClassInfo::$class_info[$class]["has_one"]);
		if($key === false)
		{
			$c = $this->class;
			while($c = ClassInfo::getParentClass($c))
			{
				if($key = array_search($c, ClassInfo::$class_info[$class]["has_one"]))
				{
					break;
				}
			}
		}

		if($key === false)
		{
				return false;
		}

		$where[$key . "id"] = $this["id"];
		$set = new HasMany_DataObjectSet(Object::instance($class), $where);
		$set->setRelationENV($name, $key . "id");
		
		if($this->queryVersion == "state") {
			$set->setVersion("state");
		}
		
		$this->viewcache[$cache] = $set;
		
		return $set;
	}
	
	/**
	 * gets a has-one-dataobject
	 *
	 *@name getHasOne
	 *@access public
	*/
	public function getHasOne($name, $filter = array(), $sort = array()) {
		$name = trim(strtolower($name));
		
		$cache = "has_one_{$name}_".var_export($filter, true)."_".var_export($sort, true);
		if(isset($this->viewcache[$cache])) {
			return $this->viewcache[$cache];
		}
		
		if(isset($this->has_one[$name])) {
			if($this->isField($name) && is_object($this->fieldGet($name)) && is_a($this->fieldGet($name), $this->has_one[$name])) {
				return $this->fieldGet($name);
			}
			if($this[$name . "id"] == 0)
				return false;
			
			$response = DataObject::get($this->has_one[$name], array("id" => $this[$name . "id"]));
			
			if($this->queryVersion == "state") {
				$response->setVersion("state");
			}
			
			$this->viewcache[$cache] = $response->first(false);
			return $this->viewcache[$cache];
		} else {
			$debug = debug_backtrace();
			throwError(6, "PHP-Error", "No Has-one-relation '".$name."' on ".$this->class." in ".$trace[1]["file"]." on line ".$trace[1]["line"].".");
		}
	}
	
	/**
	 * gets many-many-objects
	 *
	 *@name getManyMany
	 *@access public
	*/
	public function getManyMany($name, $filter = null, $sort = null, $limit = null) {
		
		$name = trim(strtolower($name));
		
		// first a little bit of caching ;)
		$cache = "many_many_".$name."_".md5(var_export($filter, true))."_".md5(var_export($sort, true))."_".md5(var_export($limit, true))."";
		if(isset($this->viewcache[$cache])) {
			return $this->viewcache[$cache];
		}
		
		
		// first we get the object for this connection
		if(isset($this->many_many[$name]))
		{
			$object = $this->many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table_name"]; // table
		} else if(isset($this->belongs_many_many[$name]))
		{
			$object = $this->belongs_many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table_name"]; // table
		} else {
			throwError(6, 'PHP-Error', "Many-Many-Relation ".convert::raw2text($name)." does not exist!");
		}
		
		$data = $this->many_many_tables[$name]; // this contains the field for this object and the other
		
		$where = (array) $filter;
		// if we know the ids
		if(isset($this->data[$name . "ids"]))
		{
			$where["versionid"] = $this->data[$name . "ids"];
			// this relation was modfied, so we use the data from the datacache
			$instance = new ManyMany_DataObjectSet($object, $where, $sort, $limit);
			$instance->setRelationEnv($name, $data["extfield"], $data["table"], $data["field"], $this->data["versionid"], isset($data["extraFields"]) ? $data["extraFields"] : array());
			if($this->queryVersion == "state") {
				$instance->setVersion("state");
			}
			
			$this->viewcache[$cache] =& $instance;
			
			return $instance;
		}
		
		// else we use INNER-JOINS to connect the tables
		
		// sometimes there is an bug with $this->table_name, so get from registry
		$baseClass = ClassInfo::$class_info[$this->class]["baseclass"];
		$baseTable = ClassInfo::$class_info[$baseClass]["table_name"];
		
		$where[$data["field"]] = $this["versionid"];
		
		$instance = new ManyMany_DataObjectSet($object, $where, $sort, $limit, array(
			' INNER JOIN '.DB_PREFIX . $data["table"].' AS '.$data["table"].' ON '.$data["table"].'.'.$object . 'id = '.$table.'.id ' // Join other Table with many-many-table
		));
		$instance->setRelationEnv($name, $data["extfield"], $data["table"], $data["field"], $this->data["versionid"], isset($data["extraFields"]) ? $data["extraFields"] : array());
		if($this->queryVersion == "state") {
			$instance->setVersion("state");
		}
		
		$this->viewcache[$cache] = $instance;
		
		return $instance;
	}
	
	/**
	 * sets many-many-data
	 *
	 *@name setManyMany
	 *@access public
	*/
	public function setManyMany($name, $value) {
		$name = substr($name, 3);
		// first we get the object for this connection
		if(isset($this->many_many[$name]))
		{
			$object = $this->many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table_name"]; // table
		} else if(isset($this->belongs_many_many[$name]))
		{
			$object = $this->belongs_many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table_name"]; // table
		} else {
			throwError(6, 'Logical Exception', "Many-Many-Relation ".convert::raw2text($name)." does not exist!");
		}
		
		$data = $this->many_many_tables[$name];
		
		if(is_object($value)) {
			if(is_a($value, "DataObjectSet")) {
				$instance = new ManyMany_DataObjectSet($object);
				$instance->setRelationEnv($name, $data["extfield"], $data["table"], $data["field"], $this->data["versionid"], isset($data["extraFields"]) ? $data["extraFields"] : array());
				$instance->addMany($value);
				$this->setField($name, $instance);
				
				unset($instance);
				return true;
			}
		}
		
		unset($this->data[$name . "ids"]);
		return $this->setField($name, $value);
	}

	/**
	 * sets many-many-ids
	 *
	 *@name setManyManyIDs
	 *@access public
	*/
	public function setManyManyIDs($name, $ids) {
		
		if(!is_array($ids))
			return false;
		
		$name = substr($name, 3, -3);
		
		$name = trim(strtolower($name));
		
		// first we get the object for this connection
		if(isset($this->many_many[$name]))
		{
			$object = $this->many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table_name"]; // table
		} else if(isset($this->belongs_many_many[$name]))
		{
			$object = $this->belongs_many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table_name"]; // table
		} else {
			throwError(6, 'Logical Exception', "Many-Many-Relation ".convert::raw2text($name)." does not exist!");
		}
		
		if(isset($this->data[$name]) && is_object($this->data[$name]) && is_a($this->data[$name], "ManyMany_DataObjectSet")) {
			unset($this->data[$name]);
		}
		
		return $this->setField($name . "ids", $ids);
	}

	/**
	 * GETTERS AND SETTERS
	*/
	
	/**
	 * gets the class of the current record
	 *@name getRecordClass
	 *@access public
	*/
	public function RecordClass()
	{
			return $this->class;	
	}
	/**
	 * gets the id
	 *
	 *@name getID
	 *@access public
	*/
	public function ID() {
		return ($this->isField("recordid")) ? $this->fieldGet("recordid") : $this->fieldGet("id");
	}
	/**
	 * gets the baseclass of the current record
	 *@name getBaseClass
	 *@access public
	*/
	public function BaseClass()
	{
			return isset(ClassInfo::$class_info[$this->class]["baseclass"]) ? ClassInfo::$class_info[$this->class]["baseclass"] : $this->class;	
	}
	/**
	 * gets the base-table
	 *
	 *@name getBaseTable
	 *@access public
	*/
	public function BaseTable() {
		return (ClassInfo::$class_info[ClassInfo::$class_info[$this->class]["baseclass"]]["table_name"]);
	}
	/**
	 * gets the versionid
	 *@name getVersionId
	 *@access public
	*/
	public function VersionId()
	{
			if(isset($this->data["versionid"]))
			{
					return $this->data["versionid"];
			} else
			{
					return isset($this->data["id"]) ? $this->data["id"] : 0;
			}	
	}
	

	/**
	 * sets the values of the record and then calls write
	 *
	 *@name setRecord
	 *@access public
	 *@param array - new data
	*/
	public function setRecord(array $data)
	{
			foreach($data as $key => $value)
			{
					$this[$key] = $value;
			}
			
			$this->write();
			return $this;
	}
	
	/**
	 * sets the class-name
	 *
	 *@name setClassName
	 *@access public
	*/
	public function setClassName($value) {
		$this->setField("class_name", $value);
	}
	
	/**
	 * sets the id
	 *
	 *@name setID
	 *@access public
	*/
	public function setID($val) {
		if($val == 0) {
			$this->setField("id", 0);
			$this->setField("versionid", 0);
			$this->setField("recordid", 0);
		} else {
			$this->setField("id", $val);
			$this->setField("recordid", $val);
			
			$vID = 0;
			$query = new SelectQuery($this->baseTable . "_state", array("publishedid"), array("id" => $val));
			if($query->execute()) {
				if($row = $query->fetch_object()) {
					$vID = $row->publishedid;
				}
			}
			
			$this->setField("versionid", $vID);
		}
	}
	
	/**
	 * gets the class as the given class-name
	 *
	 *@name getClassAs 
	 *@access public
	*/
	public function getClassAs($value) {
		if(is_subclass_of($value, $this->baseClass))
			return new $value(array_merge($this->data, array("class_name" => $value)));
		
		return $this;
	}
	
	/**
	 * checks if we can sort by a specified field
	 *
	 *@name canSortBy
	 *@access public
	*/
	public function canSortBy($field) {
		return isset($this->db_fields[$field]);
	}
	
	/**
	  * extensions
	*/
	
	/**
	 * cache the part, which is the same every DataObject
	 *@name query_cache
	 *@access protected
	 *@var array
	*/
	protected static $query_cache = array();
	/**
	 * builds the Query
	 *@name buildQuery
	 *@access public
	 *@param string|int - version
	 *@param array - filter
	 *@param array - sort
	 *@param array - limit
	 *@param array - joins
	 *@param bool - if to include class-filter
	*/
	public function buildQuery($version, $filter, $sort = array(), $limit = array(), $join = array(), $forceClasses = true)
	{
			if(PROFILE) Profiler::mark("DataObject::buildQuery");		
			
			
			// check if table in db and if not, create it
			if($this->baseTable != "" && !isset(ClassInfo::$database[$this->baseTable])) {
				foreach(array_merge(ClassInfo::getChildren($this->class), array($this->class)) as $child) {
					Object::instance($child)->buildDB();
				}
				ClassInfo::write();
			}
			
			$baseClass = $this->baseClass;
			$baseTable = $this->baseTable;
			
			// cache the most hairy part
			if(!isset(self::$query_cache[$this->baseClass]))
			{
					$query = new SelectQuery($baseTable);
					
					if($classes = ClassInfo::dataclasses($this->baseClass)) 
					{
						
							foreach($classes as $class => $table) 
							{
									if($class != $baseClass && isset(ClassInfo::$database[$table]) && ClassInfo::$database[$table])
									{
											$query->leftJoin($table, " ".$table.".id = ".$baseTable.".id");
									}
							}
					}
					
					self::$query_cache[$this->baseClass] = $query;
					
					
			}

			$query = clone self::$query_cache[$this->baseClass];
			
			if(is_array($filter)) {
				if(isset($filter["versionid"])) {
					$filter["".$this->baseTable.".id"] = $filter["versionid"];
					unset($filter["versionid"]);					
					$version = false;
				}
			}
			
			if($this->versioned) {
				$canVersion = false;
				if(member::login()) {
					$perms = $this->providePerms();
					foreach($perms as $key => $val) {
						if(preg_match("/publish/i", $key) || preg_match("/edit/i", $key) || preg_match("/write/i", $key)) {
							if(Permission::check($key)) {
								$canVersion = true;
								break;
							}
						}
					}
				}
				
				if($canVersion) {
					if(isset($_GET[$baseClass . "_version"])) {
						$version = $_GET[$baseClass . "_version"];
					}
					
					if(isset($_GET[$baseClass . "_state"])) {
						$version = "state";
					}
				}
			}
			
			// some specific fields
			$query->db_fields["autorid"] = $baseTable;
			$query->db_fields["editorid"] = $baseTable;
			$query->db_fields["last_modified"] = $baseTable;
			$query->db_fields["class_name"] = $baseTable;
			$query->db_fields["created"] = $baseTable;
			$query->db_fields["versionid"] = $baseTable . ".id AS versionid";
			
			// set filter
			$query->filter($filter);
			
			// VERSIONS
			// join state-table, also if we don't have versioned enabled ;)
			if(isset(ClassInfo::$database[$baseTable . "_state"])) {
				if($version !== false) {
					// if we get as normal, so just published records
					if($version === null || $version == "published") {
						$query->data["includedVersionTable"] = true;
						$query->innerJoin($baseTable . "_state", " ".$baseTable."_state.publishedid = ".$baseTable.".id");
						$query->db_fields["id"] = $baseTable . "_state";
					
					// if we use state mode
					} else if($version == "state") {
						$query->data["includedVersionTable"] = true;
						$query->innerJoin($baseTable . "_state", " ".$baseTable."_state.stateid = ".$baseTable.".id");
						$query->db_fields["id"] = $baseTable . "_state";
					
					// if we prefer specific versions
					} else if(preg_match('/^[0-9]+$/', $version)) {
						$query->addFilter($baseTable.'.id = (
							SELECT where_'.$baseTable.'.id FROM '.DB_PREFIX . $baseTable.' AS where_'.$baseTable.' WHERE where_'.$baseTable.'.recordid = '.$baseTable.'.recordid ORDER BY (where_'.$baseTable.'.id = '.$version.') DESC LIMIT 1
						)');
						
						
						// unmerge deleted records
						$query->innerJoin($baseTable . "_state", " ".$baseTable."_state.id = ".$baseTable.".recordid");
						
					// if we just get all, but we group
					} else if($version == "group") {
						$query->addFilter($baseTable.'.id IN (
							SELECT max(where_'.$baseTable.'.id) FROM '.DB_PREFIX . $baseTable.' AS where_'.$baseTable.' WHERE where_'.$baseTable.'.recordid = '.$baseTable.'.recordid GROUP BY where_'.$baseTable.'.recordid
						)');
						
						// unmerge deleted records
						$query->leftJoin($baseTable . "_state", " ".$baseTable."_state.id = ".$baseTable.".recordid");
					}	
				} else {
					// if we make no versioning, we just get all records matching state-table.id to table.recordid
					// unmerge deleted records
					$query->leftJoin($baseTable . "_state", " ".$baseTable."_state.id = ".$baseTable.".recordid");
					$query->db_fields["id"] = $baseTable . "_state";
				}
			}
			
			
			
			// some specific addons for relations
			if(is_array($filter))
			{
					foreach($filter as $key => $value)
					{
							// many-many
							if(isset(ClassInfo::$class_info[$this->class]["many_many"][$key]) || isset(ClassInfo::$class_info[$this->class]["belongs_many_many"][$key]))
							{										
									if(is_array($value))
									{
											$_table = isset(ClassInfo::$class_info[$this->class]["many_many"][$key]) ? ClassInfo::$class_info[$this->class]["many_many"][$key] : ClassInfo::$class_info[$this->class]["belongs_many_many"][$key];
											if($_table)
											{
													$__table = ClassInfo::$class_info[$_table]["table_name"];
													if(isset(ClassInfo::$class_info[$class]["many_many_tables"][$key]))
													{
															$table = ClassInfo::$class_info[$this->class]["many_many_tables"][$key]["table"];
															$data = ClassInfo::$class_info[$this->class]["many_many_tables"][$key];
													} else
													{
															continue;
													}
													$query->from[] = ' INNER JOIN 
																			'.DB_PREFIX . $table.' 
																		AS 
																			'.$table.' 
																		ON 
																			'.$table.'.'.$data["field"]. ' = '.$baseTable.'.id
																		'; // join many-many-table with BaseTable table
													$query->from[] = ' INNER JOIN 
																			'.DB_PREFIX . $__table.' 
																		AS 
																			'.$__table.' 
																		ON 
																			'.$table.'.'.$__table . 'id = '.$__table.'.id 
																		 '; // join other table with many-many-table
											}
											foreach($value as $field => $val)
											{
													$filter[$__table . '.' . $field] = $val;
											}
											
											$query->removeFilter($key);
									} else
									{
											$_table = ClassInfo::$class_info[$this->class]["many_many"][$key];
											if($_table)
											{
													$__table = ClassInfo::$class_info[$_table]["table_name"];
													if(isset($this->many_many_tables[$key]))
													{
															$table = ClassInfo::$class_info[$this->class]["many_many_tables"][$key]["table"];
															$data = ClassInfo::$class_info[$this->class]["many_many_tables"][$key];
													} else
													{
															continue;
													}
													$query->from[] = ' INNER JOIN 
																			'.DB_PREFIX . $table.' 
																		AS 
																			'.$table.' 
																	ON  
																		 '.$table.'.'.$data["field"] . ' = '.$baseTable.'.id
																		 '; // join BaseTable with many-many-table
											}
											$query->removeFilter($key);
									}
							} else
							
							/*// has-one
							
							if(isset($this->has_one[$key]))
							{
									if(is_array($value))
									{
											$c = $this->has_one[$key];
											$table = ClassInfo::$class_info[$c]["table_name"];
											$query->from[] = ' INNER JOIN '.DB_PREFIX . $table.' AS '.$table.' ON '.$table.'.id = '.$this->table_name.'.'.$key.'id ';
											unset($filter[$key]);
									}
									
									
							}*/
							
							unset($key, $value, $table, $data, $__table, $_table);
					}
			}
			
			
			
			// sort
			if(!empty($sort))			
				$query->sort($sort);
			else
				$query->sort(ClassInfo::getStatic($this->class, "default_sort"));
			
			
			// limiting
			$query->limit($limit);
			
			if($join)
				foreach($join as $table => $statement)
				{
					if(preg_match('/^[0-9]+$/', $table) && is_numeric($table))
						$query->from[] = $statement;
					else if($statement == "")
						$query->from[$table] = "";	
					else
						$query->from[$table] = " LEFT JOIN ".DB_PREFIX.$table." AS ".$table." ON " . $statement;
				}
			
			// don't forget filtering on class-name
			if($forceClasses) {
				$class_names = array_merge(array($this->class), ClassInfo::getChildren($this->class));		
				$query->addFilter(array("class_name" => $class_names));
			}
			
			
			// free memory
			unset($baseClass, $baseTable, $sort, $filter);
			
			if(PROFILE) Profiler::unmark("DataObject::buildQuery");
			
			
			return $query;
	}
	/**
	 * builds a SearchQuery and adds Search-Filter
	 * after that decorates the query with argumentQuery and argumentSelectQuery on Extensions and local
	 *
	 *@name buildSearchQuery
	 *@access public
	 *@param array - search
	 *@param array - filter
	 *@param array - sort
	 *@param array - limit
	 *@param array - join
	 *@param string|int|false - version
	*/
	public function buildSearchQuery($searchQuery = array(), $filter = array(), $sort = array(), $limit = array(), $join = array(), $version = false) {
		if(PROFILE) Profiler::mark("DataObject::buildSearchQuery");
		
		$query = $this->buildQuery($version, $filter, $sort, $limit, $join);
		
		$query = $this->decorateSearchQuery($query, $searchQuery);
					
		foreach($this->getextensions() as $ext)
		{
				if(Object::method_exists($ext, "argumentSQL")) {
					$newquery = $this->getinstance($ext)->setOwner($this)->argumentQuery($query, $version, $filter, $sort, $limit, $joins, $forceClasses);
					if(is_object($newquery) && (strtolower(get_class($newquery)) == "dbquery" || is_subclass_of($newquery, "DBQuery"))) {
						$query = $newquery;
						unset($newquery);
					}
				}
				
				if(Object::method_exists($ext, "argumentSelectSQL")) {
					$newquery = $this->getinstance($ext)->setOwner($this)->argumentSelectQuery($query, $searchQuery, $version, $filter, $sort, $limit, $joins, $forceClasses);
					if(is_object($newquery) && (strtolower(get_class($newquery)) == "dbquery" || is_subclass_of($newquery, "DBQuery"))) {
						$query = $newquery;
						unset($newquery);
					}
				}
				unset($ext);
		}
		$this->argumentQuery($query);
		
		if(PROFILE) Profiler::unmark("DataObject::buildSearchQuery");
		
		return $query;
	}
	/**
	 * decorates a query with search
	 *
	 *@name decorateSearchQuery
	 *@access public
	 *@param object - query
	 *@param words
	*/
	public function decorateSearchQuery($query, $searchQuery) {
		if($searchQuery) {
			$filter = array();
			foreach($searchQuery as $word) {
				$i = 0;
				$table_name = ClassInfo::$class_info[$this->baseClass]["table_name"];
				if($table_name != "")
				{
					if(isset(ClassInfo::$class_info[$this->baseClass]["searchable_fields"]))
						foreach(ClassInfo::$class_info[$this->baseClass]["searchable_fields"] as $field) {		
							if(isset(ClassInfo::$database[$table_name][$field])) {
								if($i == 0) {
									$i++;
								} else {
									$filter[] = "OR";
								}
						
								$filter[$table_name . "." . $field] = array(
									"LIKE",
									"%" . $word . "%"
								);								
							}
						}
				}
				
				if($classes = ClassInfo::DataClasses($this->baseClass)) {
					foreach($classes as $class => $table) {
						$table_name = ClassInfo::$class_info[$class]["table_name"];
						if($table_name != "") {
							if(isset(ClassInfo::$class_info[$class]["searchable_fields"]))
								foreach(ClassInfo::$class_info[$class]["searchable_fields"] as $field) {
									if(isset(ClassInfo::$database[$table_name][$field])) {
										if($i == 0) {
											$i++;
										} else {
											$filter[] = "OR";
										}
										$filter[$table_name . "." . $field] = array(
											"LIKE",
											"%" . $word . "%"
										);
									}
								}														
						}
					}
				}
				
				
			}
			
			$query->addFilter(array($filter));
		}
		return $query;
	}
	/**
	 * local argument sql
	 *
	 *@name argumentQuery
	 *@access public
	*/
	
	public function argumentQuery(&$query) {
		
	}
	/**
	 * builds an SQL-Query and arguments it through extensions
	 *
	 *@name buildQuery
	 *@access public
	 *@param string|int|false - version
	 *@param array - filter
	 *@param array - sort
	 *@param array - limit
	 *@param array - joins
	 *@param bool - to force recordclass is part of this class or a subclass
	 *@return SelectQuery-Object
	*/
	public function buildExtendedQuery($version, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $forceClasses = true)
	{
			
			if(PROFILE) Profiler::mark("DataObject::buildExtendedQuery");
			$query = $this->buildQuery($version, $filter, $sort, $limit, $joins, $forceClasses);
			foreach($this->getextensions() as $ext)
			{
					if(Object::method_exists($ext, "argumentSQL")) {
						$newquery = $this->getinstance($ext)->setOwner($this)->argumentQuery($query, $version, $filter, $sort, $limit, $joins, $forceClasses);
						if(is_object($newquery) && (strtolower(get_class($newquery)) == "dbquery" || is_subclass_of($newquery, "DBQuery"))) {
							$query = $newquery;
							unset($newquery);
						}
					}
					unset($ext);
			}
			$this->argumentQuery($query);
			if(PROFILE) Profiler::unmark("DataObject::buildExtendedQuery");
			return $query;
	}
	
	/**
	 * cache
	*/
	protected static $datacache = array();
	
	/**
	 * gets all the records of one query as an array
	 *@name getRecords
	 *@param string|int|false - version
	 *@param array - filter
	 *@param array - sort
	 *@param array - limit
	 *@param array - joins
	 *@param array - search
	 *@return array
	*/
	public function getRecords($version, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array())
	{
			if(!isset(ClassInfo::$class_info[$this->baseClass]["table_name"]) || !ClassInfo::$class_info[$this->baseClass]["table_name"] || !defined("SQL_LOADUP"))
				return array();
			
			if(PROFILE) Profiler::mark("DataObject::getRecords");
			
			$data = array();
			
			/* --- */
			
			// generate hash for caching
			if(empty($groupby)) {
				if(PROFILE) Profiler::mark("getRecords::hash");
				$limithash = (is_array($limit)) ? implode($limit) : $limit;
				$joinhash = (empty($joins)) ? "" : implode($joins);
				$searchhash = (is_array($search)) ? implode($search) : $search;
				$basehash = "record_" . $limithash . serialize($sort) . $joinhash . $searchhash . $version;
				if(is_array($filter)) {
					$hash = $basehash . md5(serialize($filter));
				} else {
					$hash = $basehash . "_all_" . md5($filter);
				}
				unset($limithash, $joinhash, $searchhash);
				if(PROFILE) Profiler::unmark("getRecords::hash");
				if(isset(self::$datacache[$this->class][$hash])) {
					return self::$datacache[$this->class][$hash];
				}
			}
			
			/* --- */
			
			
			if(empty($search))
				$query = $this->buildExtendedQuery($version, $filter, $sort, $limit, $joins);
			else
				$query = $this->buildSearchQuery($search, $filter, $sort, $limit, $joins, $version);
			
			// validate from
			foreach($query->from as $table => $data) {
				if(is_string($table) && !preg_match('/^[0-9]+$/', $table)) {
					if(!isset(ClassInfo::$database[$table])) {
						// try to create the tables
						$this->buildDev();
					}
				}
			}
			
			$query->execute();
			
			$arr = array();
			
			while($row = sql::fetch_assoc($query->result))
			{
				$arr[] = $row;
				
				
				// store id in cache
				if(isset($basehash)) self::$datacache[$this->class][$basehash . md5(serialize(array("id" => $row["id"])))] = array($row);
				
				// cleanup
				unset($row);
			}
			
			self::$datacache[$this->class][$hash] = $arr;
			
			$query->free();
			unset($hash, $basehash, $limits, $sort, $filter, $query); // free memory
			if(PROFILE) Profiler::unmark("DataObject::getRecords");
			
			return $arr;
	}
	
	/**
	 * gets records grouped
	 *
	 *@name getGroupedRecords
	 *@access public
	 *@param int|false|string - version
	 *@param string - field to group
	 *@param array - filter
	 *@param array - sort
	 *@param array - limits
	 *@param array - joins
	 *@param array - search
	*/
	public function getGroupedRecords($version, $groupField, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array()) {
		if(!isset(ClassInfo::$class_info[$this->baseClass]["table_name"]) || !ClassInfo::$class_info[$this->baseClass]["table_name"] || !defined("SQL_LOADUP"))
			return array();
			
		if(PROFILE) Profiler::mark("DataObject::getGroupedRecords");
		
		$data = array();
		
		
		if(empty($search))
			$query = $this->buildExtendedQuery($version, $filter, $sort, $limit, $joins);
		else
			$query = $this->buildSearchQuery($search, $filter, $sort, $limit, $joins, $version);
			
		$query->distinct = true;
		
		$query->fields = array($groupField);
		
		$query->execute();
		
		while($row = $query->fetch_assoc()) {
			if(isset($row[$groupField])) {
				if(is_array($filter)) {
					$filter[$groupField] = $row[$groupField];
				} else {
					$filter = array($groupField => $row[$groupField], $filter);
				}
				
				$data[$row[$groupField]] = DataObject::get($this->class, $filter, $sort, array(), $joins, $version);
			}
			unset($row);
		}
		$query->free();
		
		if(PROFILE) Profiler::unmark("DataObject::getGroupedRecords");
		
		return $data;
	}
	
	/**
	 * this is the most flexible method of all the methods, but you need to know much
	 * you can define here fields and groupby at once and get an array as result back
	 *
	 *@name getAggregate
	 *@access public
	 *@param false|int|string - version
	 *@param string|array - fields
	 *@param array - filter
	 *@param array - sort
	 *@param array - limits
	 *@param array - joins
	 *@param array - search
	 *@param array - groupby
	*/
	public function getAggregate($version, $aggregate, $filter = array(), $sort = array(), $limit = array(), $joins = array(), $search = array(), $groupby = array()) {
		if(!isset(ClassInfo::$class_info[$this->baseClass]["table_name"]) || !ClassInfo::$class_info[$this->baseClass]["table_name"] || !defined("SQL_LOADUP"))
			return array();
		
		if(PROFILE) Profiler::mark("DataObject::getAggregate");
		
		$data = array();
		
		if(empty($search))
			$query = $this->buildExtendedQuery($version, $filter, $sort, $limit, $joins);
		else
			$query = $this->buildSearchQuery($search, $filter, $sort, $limit, $joins, $version);
			
		$query->groupby($groupby);
		
		if($query->execute($aggregate)) {
		
			while($row = $query->fetch_assoc()) {
				$data[] = $row;
				unset($row);
			}
		
		}
		
		if(PROFILE) Profiler::unmark("DataObject::getAggregate");
		
		return $data;
	}
	
	/**
	 * controller
	 *@name controller
	 *@access public
	*/
	public $controller = "";
	/**
	 * sets the controller
	 *@name setController
	 *@access public
	*/
	public function setController(Object &$controller)
	{
			$this->controller = $controller;
	}
	/**
	 * gets the controller for this class
	 *@name controller
	 *@access public
	*/
	public function controller($controller = null)
	{
			if(isset($controller)) {
				$this->controller = clone $controller;
				$this->controller->model_inst = $this;
				$this->controller->model = $this->class;
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
					$this->controller->model = $this->class;
					return $this->controller;
			} else {
				
				if(ClassInfo::exists($this->class . "controller"))
				{
						$c = $this->class . "controller";
						$this->controller = new $c;
						$this->controller->model_inst = $this;
						$this->controller->model = $this->class;
						return $this->controller;
				} else {
					if(ClassInfo::getParentClass($this->class) != "dataobject") {
						$parent = $this->class;
						while(($parent = ClassInfo::getParentClass($parent)) != "dataobject") {
							if(!$parent)
								return false;
							
							if(ClassInfo::exists($parent . "controller")) {
								$c = $parent . "controller";
								$this->controller = new $c;
								$this->controller->model_inst = $this;
								$this->controller->model = $this->class;
								return $this->controller;
							}
						}
					}
				}
			}
			return false;
	}
	/**
	 * resets the DataObject
	 *@name reset
	 *@access public
	*/
	public function reset()
	{
			parent::reset();
			$this->viewcache = array();
			$this->data = array();
	}
	/**
	 * Permssions for dataobjects
	*/
	public function providePerms()
	{
			return array(
				"DATA_WRITE"	=> array(
					"title"		=> '{$_lang_dataobject_edit}',
					"default"	=> array(
						"type" => "admins"
					)
				),
				"DATA_DELETE"	=> array(
					"title"		=> '{$_lang_dataobject_delete}',
					"default"	=> array(
						"type" => "admins"
					)
				),
				"DATA_INSERT"	=> array(
					"title"		=> '{$_lang_dataobject_add}',
					"default"	=> array(
						"type" => "admins"
					)
				),
			);
	}
	
	/**
	 * TREE-Implementation
	*/
	
	/**
	 * renders a tree
	 *
	 *@name renderTree
	 *@access public
	 *@param string - href
	 *@param numeric - id of active-marked node
	 *@param array|numeric - if array -> searchtree - array for words; if numeric the number gives the parentid
	 *@param bool - if getting inactive sites
	*/
	public function renderTree($href, $activenode = 0, $words_parentid = 0, $params = array(), $withul = true) {
		// first check if the methods exist
		if(Object::method_exists($this, "getTree") && Object::method_exists($this, "searchTree")) {
			Resources::add("tree.css", "css");
			gloader::load("tree");
			// first get fields from href
			$fields = array();
			preg_match_all('/\$([a-zA-Z0-9_]+)/', $href, $vars);
			foreach($vars[1] as $key => $field) {
				$fields[] = $field;
			}
			
			// now get data
			if(!is_array($words_parentid) && preg_match("/^[0-9]+$/", $words_parentid)) {
				$data = $this->getTree($words_parentid, $fields, $activenode, $params);
			} else if(is_array($words_parentid) && $words_parentid[0] == "") {
				$data = $this->getTree($words_parentid, $fields, $activenode, $params);
			} else if(is_array($words_parentid)){
				$data = $this->searchTree($words_parentid, $fields, $activenode);
			} else {
				return false;
			}
			// rendering
			
			$this->callExtending("beforeRenderTree", $data);
			
			$tree = $this->renderSubTreesHelper($data, $fields,$params, $activenode,  $href);
			
			$this->callExtending("afterRenderTree", $tree);
			
			if($withul) {
				return "<ul class=\"tree\">" . implode("\n", $tree) . "</ul>";
			} else {
				return  implode("\n", $tree);
			}
		} else {
			return false;
		}
	}
	
	/**
	 * helper-function for rendering
	 *
	 *@name renderSubTreesHelper
	 *@access protected
	*/
	protected function renderSubTreesHelper($data, $fields, $params, $activenode,  $href) {

		$container = array();
		$i = 1;
		foreach($data as $nodedata) {
			if(!$nodedata["data"])
				continue;
			
			$id = isset($nodedata["id"]) ? $nodedata["id"] : $nodedata["data"]["class_name"] . "_" . $nodedata["data"]["recordid"];
			$node = new HTMLNode("li", array("id"	=> "treenode_" . $id));
			
			// the link
			$generated_href = $href;
			foreach($fields as $field) {
				if($field == "id")	
					$generated_href = str_replace("\$".$field, $nodedata["data"]["recordid"], $generated_href);
				else
					$generated_href = str_replace("\$".$field, $nodedata["data"][$field], $generated_href);
			}
			$hoverspan = new HTMLNode("span", array("class"	=> "a", "title" => $nodedata["title"]), array(
				$linespan = new HTMLNode("span", array("class"	=> "b"))
			));
			
			if(strlen($nodedata["title"]) > 23) {
				$title = mb_substr($nodedata["title"], 0, 20, 'UTF-8') . "…";
			} else {
				$title = $nodedata["title"];
			}
			$title = " " . $title;
			
			$link = new HTMLNode("a", array_merge($nodedata["attributes"],array("href" => $generated_href, "nodeid" => $id)), array(
				new HTMLNode("span", array(), convert::raw2text($title))
			));
			
			$link->removeClass($nodedata["data"]["class_name"]);
			$link->addClass($nodedata["data"]["class_name"]);
			$link->addClass("treelink");
			
			
			if($i == 1) {
				$link->addClass("first");
			}
			
			if(preg_match('/^[0-9]+$/', $activenode)) {
				if($activenode == $nodedata["data"]["recordid"]) {
					$hoverspan->addClass("marked");
				}
			} else {
				if($activenode == $nodedata["data"]["class_name"] . "_" . $nodedata["data"]["recordid"] || $activenode == $id) {
					$hoverspan->addClass("marked");
				}
			}
			
			// + or -
			// ajax
			if(
				(
					$nodedata["children"] == "ajax" || 
					(is_array($nodedata["children"]) && count($nodedata["children"]) > 0)
				) && 
				(!isset($nodedata["collapsable"]) || $nodedata["collapsable"] === true) // if not collapsable, we don't need + or -
			) {
				if($nodedata["children"] == "ajax") {
					if(isset($nodedata["collapsed"])) {
						$status = (!$nodedata["collapsed"]);
					} else {
						if(isset($_SESSION["treestatus_" . $this->class . "_" . $nodedata["data"]["recordid"]])) {
							$status = $_SESSION["treestatus_" . $this->class . "_" . $nodedata["data"]["recordid"]];
						} else
						{
							$status = false;
						}
					}
					
					// subtree is open
					if($status) {
						// get data
						
						$node->addClass("expanded");
						$add_data = $this->getTree($nodedata["data"]["recordid"], $fields, $activenode, $params);
						
						$nodedata["children"] = $add_data;
						$linespan->append(new HTMLNode("div", array("class" => "hitarea expanded"), array(
							new HTMLNode("a", array("name" => $this->class, "id" => $nodedata["data"]["recordid"],"href" => ROOT_PATH . "treeserver/setCollapsed/".$this->class."/".$nodedata["data"]["recordid"]."/?redirect=".urlencode(Core::activeURL())))
						)));
					} else {
						$node->addClass("collapsed");
						$linespan->append(new HTMLNode("div", array("class" => "hitarea collapsed ajax"), array(
							new HTMLNode("a", array("name" => $this->class,"id" => $nodedata["data"]["recordid"],"href" => ROOT_PATH . "treeserver/getSubTree/".$this->class."/".$nodedata["data"]["recordid"]."/?href=".urlencode($href)."&redirect=".urlencode(Core::activeURL())))
						)));
					}
				} else {
					if(isset($nodedata["collapsed"])) {
						$status = (!$nodedata["collapsed"]);
					} else {
						if(isset($_SESSION["treestatus_" . $this->class . "_" . $nodedata["data"]["recordid"]])) {
							$status = $_SESSION["treestatus_" . $this->class . "_" . $nodedata["data"]["recordid"]];
						} else
						{
							$status = false;
						}
					}
					
					// subtree is open
					if($status) {
						$node->addClass("expanded");
						$linespan->append(new HTMLNode("div", array("class" => "hitarea expanded"), array(
							new HTMLNode("a", array("name" => $this->class,"id" => $nodedata["data"]["recordid"],"href" => ROOT_PATH . "treeserver/setCollapsed/".$this->class."/".$nodedata["data"]["recordid"]."/?redirect=".urlencode(Core::activeURL()))
						))));
					} else {
						$node->addClass("collapsed");
						$linespan->append(new HTMLNode("div", array("class" => "hitarea collapsed"), array(
							new HTMLNode("a", array("name" => $this->class,"id" => $nodedata["data"]["recordid"],"href" => ROOT_PATH . "treeserver/setExpanded/".$this->class."/".$nodedata["data"]["recordid"]."/?redirect=".urlencode(Core::activeURL())))
						)));
					}
				}
			}
			
			$linespan->append($link);
			// append
			$node->append($hoverspan);
			unset($link, $hoverspan, $linespan);
			
			// children
			if($nodedata["children"] != "ajax" && (is_array($nodedata["children"]) && count($nodedata["children"]) > 0)) {
				$node->append(new HTMLNode("ul", array(), $this->renderSubTreesHelper($nodedata["children"], $fields, $params, $activenode, $href)));
			}
			
			// if last
			if($i == count($data)) {
				$node->addClass("last");
			}
			$container[] = $node;
			unset($node);
			$i++;
		}
		return $container;			
	}
	
	/**
	 * bool
	*/
	public function bool() {
		return (array_merge(array(
				"class_name"	=> $this->class,
				"last_modified"	=> NOW,
				"created"		=> NOW,
				"autorid"		=> member::$id
			), $this->defaults) != $this->data);
	}
	
	/**
	 * this method consolidates all relation data in data
	 *
	 *@name consolidate
	 *@access public
	*/
	public function consolidate() {
		foreach($this->many_many_tables as $name => $data) {
			$this->getRelationIDs($name);
		}
		return $this;
	}
	
	/**
	 * gets a object of this record with id and versionid set to 0
	 *
	 *@name _clone
	 *@access public
	*/
	public function _clone() {
		$this->consolidate();
		$data = clone $this;
		
		$data->id = 0;
		$data->versionid = 0;
		
		return $data;
	}
}


/**
 * extension base class
 *
 *@name DataObjectExtension
 *@access public
 *@type abstract
*/
abstract class DataObjectExtension extends Extension
{
		/**
		 * has_one
		*/
		public $has_one = array();
		/**
		 * important db-fields are for example fields you need to validate something in canRead or sth else you need to get specific data, which is very important
		 *
		 *@name important_db_fields
		 *@access public
		 *@var array
		*/
		public $important_db_fields = array();
		/**
		 * has-many
		 *
		 *@name has_many
		 *@access public
		*/
		public $has_many = array();
		/**
		 * many-many
		*/
		public $many_many = array();
		public $belongs_many_many = array();
		/**
		 * defaults
		*/
		public $defaults = array();
		/**
		 * gets DBFields
		*/
		public function DBFields() {
				return isset($this->db_fields) ? $this->db_fields : array();
		}
		/**
		 * gets has_one
		*/ 
		public function has_one() {
			return $this->has_one;
		}
		/**
		 * gets has_many
		*/ 
		public function has_many() {
			return $this->has_many;
		}
		/**
		 * many-many
		*/
		public function many_many() {
			return $this->many_many;
		}
		public function belongs_many_many() {
			return $this->belongs_many_many;
		}
		/**
		 * defaults
		 *
		 *@name defaults
		 *@access public
		*/
		public function defaults() {
			return $this->defaults;
		}
		
		/**
		 * own setOwner method with check if $object is a subclass of dataobject
		 *@name setOwner
		 *@access public
		 *@param object
		*/
		public function setOwner($object)
		{
				if(!is_subclass_of($object, 'DataObject'))
				{
						throwError(6, 'PHP-Error', '$object isn\'t subclass of dataobject in '.__FILE__.' on line '.__LINE__.'');
				}
				parent::setOwner($object);
				return $this;
		}
}