<?php defined("IN_GOMA") OR die();


/**
 * Basic class for all models with DB-Connection of Goma.
 *
 * this is a Basic class for all Models that need DataBase-Connection
 * it creates tables based on db-fields, has-one-, has-many- and many-many-connections
 * it gets data and makes it available as normal attributes
 * it can write and remove data
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     4.7.13
 */
abstract class DataObject extends ViewAccessableData implements PermProvider
{

	/**
	 * default sorting 
	 *
	 *@name default_sort
	*/
	static $default_sort = "id";
	
	/**
	 * enables or disabled history
	 *
	 *@name history
	 *@access public
	*/
	static $history = true;
	
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
	 * DEPRECATED API
	*/
	public $versioned;
	
	//!Global Static Methods
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
		
        if(PROFILE) Profiler::mark("DataObject::get");
        
		$DataSet = new DataObjectSet($class, $filter, $sort, $limits, $joins, array(), $version);
		
		if(isset($pagination) && $pagination !== false) {
			
			if(is_int($pagination))
				$DataSet->activatePagination($pagination);
			else
				$DataSet->activePagination();
		}
        
        if(PROFILE) Profiler::unmark("DataObject::get");
		
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
			
			$table_name = $dataobject->Table();
			
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
					throw new MySQLException();
			}
	}
	
	/**
	 * updates data raw in the table and has not version-managment or multi-table-managment.
	 *
	 * You have to be familiar with the structure of goma when you use this method. It is much faster than all the other methods of writing, but also more complex.
	 *
	 * @param String $name Model or Table
	 * @param array $data data to update
	 * @param array $where where-clause
	 * @param string $limit optional limit
	 * @param boolean $silent if to change last-modified-date
	 *
	 * @return boolean
	*/
	public static function update($name, $data, $where, $limit = "", $silent = false)
	{
			if(PROFILE) Profiler::mark("DataObject::update");
			//Core::Deprecate(2.0);
			
			if(ClassInfo::exists($name) && is_subclass_of($name, "DataObject")) {
				$DataObject = Object::instance($name);
				$table_name = $DataObject->Table();
			} else if(isset(ClassInfo::$database[$name])) {
				$table_name = $name;
			} else {
				throwError(6, "Table not found", "Table or model '" . $name . "' does not exist.");
			}
			
			if(!isset($data["last_modfied"]) && !$silent)
			{
					$data["last_modified"] = NOW;
			}
			
			$updates = "";
			$i = 0;
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
			
			if(SQL::query($sql))
			{
					if(PROFILE) Profiler::unmark("DataObject::update");
					return true;
			} else
			{
					throw new MySQLException();
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
		if(PROFILE) Profiler::mark("DataObject::get_one");
		
		$name = strtolower($name);

		$output = self::get($name, $filter, $sort, array(1))->first(false);
		
		if(PROFILE) Profiler::unmark("DataObject::get_one");

		return $output;
		
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

	//!Init
	
	/**
	 * this defines a right for advrights or rechte, which tests if an user is an admin
	 *@name __construct
	 *@param array
	 *@param string|object
	 *@param array|string - fields
	*/
	public function __construct($record = null) {			
			parent::__construct();

			$this->data = array_merge(array(
				"class_name"	=> $this->classname,
				"last_modified"	=> NOW,
				"created"		=> NOW,
				"autorid"		=> member::$id
			), (array) $this->defaults, ArrayLib::map_key("strtolower", (array) $record));
	}
	
	/**
	 * defines the methods.
	 *
	 * @access protected
	*/
	protected function defineStatics() {
		
		if($has_many = $this->hasMany())
			foreach($has_many as $key => $val) {
				Object::LinkMethod($this->classname, $key, array("this", "getHasMany"), true);
				Object::LinkMethod($this->classname, $key . "ids", array("this", "getRelationIDs"), true);
			}
		
		
		if($many_many = $this->ManyMany())
			foreach($many_many as $key => $val) {
				Object::LinkMethod($this->classname, $key, array("this", "getManyMany"), true);
				Object::LinkMethod($this->classname, $key . "ids", array("this", "getRelationIDs"), true);
				Object::LinkMethod($this->classname, "set" . $key, array("this", "setManyMany"), true);
				Object::LinkMethod($this->classname, "set" . $key . "ids", array("this", "setManyManyIDs"), true);
			}
		
		
		if($belongs_many_many = $this->BelongsManyMany()) {
			foreach($belongs_many_many as $key => $val) {
				Object::LinkMethod($this->classname, $key, array("this", "getManyMany"), true);
				Object::LinkMethod($this->classname, $key . "ids", array("this", "getRelationIDs"), true);
				Object::LinkMethod($this->classname, "set" . $key, array("this", "setManyMany"), true);
				Object::LinkMethod($this->classname, "set" . $key . "ids", array("this", "setManyManyIDs"), true);
			}
		}
		
		if($has_one = $this->HasOne()) {
			foreach($has_one as $key => $val) {
				Object::LinkMethod($this->classname, $key, array("this", "getHasOne"), true);
			}
		}
	}
	
	//!Permissions
	
	/**
	 * Permssions for dataobjects
	*/
	public function providePerms()
	{
			return array(
				"DATA_MANAGE"	=> array(
					"title"		=> '{$_lang_data_manage}',
					"default"	=> array(
						"type" => "admins"
					)
				)
			);
	}
	
	/**
	 * public function to make permission-calls
	 *
	 *@name can
	 *@access public
	 *@param name(s) of permission
	 *@param optional - record
	*/
	public function can($permissions, $record = null) {
		
		if($this->classname != "permission") {
			if(Permission::check("superadmin"))
				return true;
		}
		
		if(!is_array($permissions)) {
			$permissions = array($permissions);
		}
		
		$r = isset($record) ? $record : $this;
		foreach($permissions as $perm) {
			$perm = strtolower($perm);
			$can = false;
			
			if(isset(Permission::$providedPermissions[$this->baseClass . "::" . $perm])) {
				$can = Permission::check($this->baseClass . "::" . $perm);
			}
			
			if(Object::method_exists($this->classname, "can" . $perm)) {
				$c = call_user_func_array(array($this, "can" . $perm), array($r));
				if(is_bool($c)) {
					$can = $c;
				}
			}
			
			$this->callExtending("can" . $perm, $can, $r);

			if($can === true)
				return true;
		}
		
		return false;
	}
	
	/**
	 * returns if you can access a specific history-record
	 *
	 *@name canViewHistory
	 *@access public
	*/
	public static function canViewHistory($record = null) {
		if(is_object($record)) {
			if($record->oldversion && $record->newversion) {
				return ($record->oldversion->can("Write", $record->oldversion) && $record->newversion->can("Write", $record->newversion));
			} else if($record->newversion) {
				return $record->newversion->can("Write", $record->newversion);
			} else if($record->record) {
				return $record->record->can("Write", $record->record);
			}
		}
		
		if(is_object($record)) {
			$c = new $record->dbobject;
		} else if(is_string($record)) {
			$c = new $record;
		} else {
			throwError("6", "Invalid Argument Error", "Invalid Argument for DataObject::canViewRecord Object or Class_name required");
		}
		return $c->can("Write");
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
			if(!self::Versioned($this->classname) && $this->can("Write"))
			{
					return true;
			} else if ($this->can("Publish")) {
				return true;
			} else if($this->can("Delete"))
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
	
	//!Events
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
	 * will be called after write
	 *@name onAfterWrite
	 *@access public
	*/
	public function onAfterWrite()
	{
		
	}
	
	
	/**
	 * before manipulating the data
	 *
	 *@name onbeforeManipulate
	 *@access public
	 *@param manipulation
	*/
	public function onbeforeManipulate(&$manipulation, $job)
	{
		
	}
	
	/**
	 * before manipulating many-many data over @link ManyMany_DataObjectSet::write
	 *
	 *@name onBeforeManipulateManyMany
	*/
	public function onBeforeManipulateManyMany(&$manipulation, $dataset, $writtenIDs, $writeExtraFields) {
		
	}
	
	/**
	 * before updating data-tables to write data
	 *
	 *@name onBeforeWriteData
	 *@access public
	*/
	public function onBeforeWriteData() {
		
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
	
	//!Data-Manipulation
	
	/**
	 * writes changed data
	 *
	 *@name write
	 *@access public
	 *@param bool - to force insert (default: false)
	 *@param bool - to force write (default: false)
	 *@param numeric - priority of the snapshop: autosave 0, save 1, publish 2
	 *@param bool - if to force publishing also when not permitted (default: false)
	 *@param bool - whether to track in history (default: true)
	 *@param bool - whether to write silently, so without chaning anything automatically e.g. last_modified (default: false)
	 *@return bool
	*/
	public function write($forceInsert = false, $forceWrite = false, $snap_priority = 2, $forcePublish = false, $history = true, $silent = false)
	{
		if(!defined("CLASS_INFO_LOADED")) {
			throwError(6, "Logical Exception", "Calling DataObject::write without loaded classinfo is not allowed.");
		}
		
		// check if table in db and if not, create it
		if($this->baseTable != "" && !isset(ClassInfo::$database[$this->baseTable])) {
			foreach(array_merge(ClassInfo::getChildren($this->classname), array($this->classname)) as $child) {
				Object::instance($child)->buildDB();
			}
			ClassInfo::write();
		}
		
		if($this->data === null)
			return true;
		
		if(PROFILE) Profiler::mark("DataObject::write");
		
		// if we insert, we don't have an ID
		if($forceInsert) {
			$this->consolidate();
			$this->data["id"] = 0;
			$this->data["versionid"] = 0;
		}
		
		if(isset(ClassInfo::$class_info[$this->classname]["baseclass"]))
			$baseClass = ClassInfo::$class_info[$this->classname]["baseclass"];
		else
			$baseClass = $this->classname;
		
		$oldid = 0;
		
		self::$datacache[$this->baseClass] = array();
		
		// Generate Data
		// if we don't insert we merge the old record with the new one
		if($forceInsert || $this->versionid == 0) {
			
			// check rights
			if(!$forceInsert && !$forceWrite) {
				if(!$this->can("Insert", $this)) {
					if($snap_priority == 2 && !$this->can("Publish", $this))
						return false;
				}
			}
			
			$this->onBeforeWrite();
			
			$command = "insert";
			
			// get new data
			$newdata = $this->data;
		} else {
			// get old record
			$data = DataObject::get_one($baseClass, array("versionid" => $this->versionid));
			
			if($data) {
				// check rights
				if(!$forceWrite)
					if(!$this->can("Write", $this))
						if($snap_priority == 2 && !$this->can("Publish", $this))
							return false;
				
				$this->onBeforeWrite();
				
				$command = "update";
				$newdata = array_merge($data->ToArray(), $this->data);
				$this->data = $data->ToArray();
				$newdata["created"] = $data["created"]; // force
				$newdata["autorid"] = $data["autorid"];
				$oldid = $data->versionid;
				
				// copy many-many-relations
				foreach($this->ManyMany() as $name => $class) {
					if(!isset($newdata[$name . "ids"]) && !isset($newdata[$name]))
						$newdata[$name . "ids"] = $this->getRelationData($name);
					else if(!isset($newdata[$name . "ids"]) && is_array($newdata[$name]))
						$newdata[$name . "ids"] = $newdata[$name];
				}
				foreach($this->BelongsManyMany() as $name => $class) {
					if(!isset($newdata[$name . "ids"]) && !isset($newdata[$name]))
						$newdata[$name . "ids"] = $this->getRelationData($name);
					else if(!isset($newdata[$name . "ids"]) && is_array($newdata[$name]))
						$newdata[$name . "ids"] = $newdata[$name];
				}
				unset($data);
			} else {

				// check rights
				if(!$forceInsert)
					if(!$this->can("Insert", $this->data))
						return false;
				
				$this->onBeforeWrite();
				
				// old record doesn't exist, so we create a new record
				$command = "insert";
				$newdata = $this->data;
			}	
		}
		
		// first step: decorate the important fields
		if($command == "insert") {
			$newdata["created"] = NOW;
			$newdata["autorid"] = member::$id;
			$newdata["last_modified"] = NOW;
			$newdata["editorid"] = member::$id;
		} else
		
		if(!$silent) {
			$newdata["last_modified"] = NOW;
			$newdata["editorid"] = member::$id;
		}
		
		$newdata["snap_priority"] = $snap_priority;
		$newdata["class_name"] = $this->isField("class_name") ? $this->fieldGET("class_name") : $this->classname;
		
		// find out if we should write data
		if($command != "insert" && !$forceWrite) {
			if(!$this->checkForChange($snap_priority, $newdata, $changed)) {
				return true;
			}
		}
		
		// WE CAN WRITE!
		
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
				throw new PermissionException();
			}
		}
				
		
		// generate has-one-data
		if($has_one = $this->hasOne()) {
			foreach($has_one as $key => $value) {
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
		$many_many_tables = $this->ManyManyTables();
		
		// here the magic for many-many happens
		if($many_many = $this->ManyMany()) {
			foreach($many_many as $key => $value) {
				if(isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "ManyMany_DataObjectSet")) {
					$many_many_objects[$key] = $newdata[$key];
					$many_many_data[$key] = $value;
					unset($newdata[$key]);
				}
				unset($key, $value);
			}
		}
		
		if($belongs_many_many = $this->BelongsManyMany()) {
			foreach($belongs_many_many as $key => $value) {
				if(isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "ManyMany_DataObjectSet")) {
					$many_many_objects[$key] = $newdata[$key];
					$many_many_data[$key] = $value;
					unset($newdata[$key]);
				}
				unset($newdata[$key], $key, $value);
			}
		}
		
		unset($newdata["versionid"]);
		
		// now set the correct data
		$this->data = $newdata;
		$this->viewcache = array();
		
		// write data
		
		// generate the write-manipulation
		$manipulation = array(
			$baseClass => array(
				"command"	=> "insert",
				"fields"	=> array_merge(array(
					"class_name"	=> $this->classname,
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
							"table_name"=> ClassInfo::$class_info[$class]["table"],
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
			$object->setRelationENV($key, $many_many_tables[$key]["extfield"], $many_many_tables[$key]["table"], $many_many_tables[$key]["field"], $this->data["versionid"], isset($many_many_data[$key]["extraFields"]) ? $many_many_data[$key]["extraFields"] : array());
			$object->write(false, true);
			unset($this->data[$key . "ids"]);
		}
		
		// many-many
		if($this->ManyMany())
			foreach($this->ManyMany() as $name => $table)
			{
					if(isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
					{
							$manipulation = $this->set_many_many_manipulation($manipulation, $name, $this->data[$name . "ids"]);
					}
					
					
			}
		
		if($this->BelongsManyMany())
			foreach($this->BelongsManyMany() as $name => $table)
			{
					
					if(isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
					{
							$manipulation = $this->set_many_many_manipulation($manipulation, $name, $this->data[$name . "ids"]);
					}
					
					
			}
		
		// has-many
		if($this->hasMany())
			foreach($this->hasMany() as $name => $class)
			{
				if(isset($this->data[$name]) && is_object($this->data[$name]) && is_a($this->data[$name], "HasMany_DataObjectSet")) {	
					$key = array_search($this->classname, ClassInfo::$class_info[$class]["has_one"]);
					if($key === false)
					{
							$c = $this->classname;
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
					$this->data[$name]->setRelationENV($name, $key . "id", $this->ID);
					if(!$this->data[$name]->write($forceInsert, $forceWrite, $snap_priority))
						return false;
				} else {
					if(isset($this->data[$name]) && !isset($this->data[$name . "ids"]))
						$this->data[$name . "ids"] = $this->data[$name];
					if(isset($this->data[$name . "ids"]) && is_array($this->data[$name . "ids"]))
					{
							// find field
							$key = array_search($this->classname, ClassInfo::$class_info[$class]["has_one"]);
							if($key === false)
							{
									$c = $this->classname;
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
			}
			
	
		
		// add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
		if($oldid != 0) {
			$class = $this->classname;
			while($class != "dataobject") {
				if(isset(ClassInfo::$class_info[$class]["belongs_many_many_extra"])) {
					foreach(ClassInfo::$class_info[$class]["belongs_many_many_extra"] as $data) {
						if(isset(ClassInfo::$database[$data["table"]])) {
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
				}
				$class = ClassInfo::getParentClass($class);
			}
		}
		
		self::$datacache[$this->baseClass] = array();
		
		// get correct oldid for history
		if($data = DataObject::get_one($this->classname, array("id" => $this->RecordID))) {
			$historyOldID = ($data["publishedid"] == 0) ? $data["publishedid"] : $data["stateid"];
		} else {
			$historyOldID = isset($oldid) ? $oldid : 0;
		}
		
		// fire events!
		$this->onBeforeWriteData();
		$this->callExtending("onBeforeWriteData");
		$this->onBeforeManipulate($manipulation, $b = "write");
		$this->callExtending("onBeforeManipulate", $manipulation, $b = "write");
		

		self::$datacache[$this->baseClass] = array();

		// fire manipulation to DataBase
		if(SQL::manipulate($manipulation)) {
			
			if($this->versionid == 0)
				return false;
			
			
			// update state-table
			if(!self::Versioned($this->classname) || $snap_priority == 2) {
				if(self::Versioned($this->classname)) {
					$this->onBeforePublish();
					$this->callExtending("onBeforePublish");
					if(!$forcePublish) {
						if(!$forceWrite) {
							if(!$this->can("Publish")) {
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
				
				if($command != "insert")
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
			
			$this->onBeforeManipulate($manipulation, $b = "write_state");
			$this->callExtending("onBeforeManipulate", $manipulation, $b = "write_state");
			if(SQL::manipulate($manipulation)) {
				
				if(self::getStatic($this->classname, "history") && $history) {
					if($command == "insert" || !isset($changed)) {
						$changed = $this->data;
					}
					History::push($this->classname, $historyOldID, $this->versionid, $this->id, $command, $changed);
				}
				unset($manipulation);
				
				$this->onAfterWrite();
				$this->callExtending("onAfterWrite");
				
				// HERE CLEAN-UP for non-versioned-tables happens
				// if we don't version this dataobject, we need to delete the old record
				if(!self::Versioned($this->classname) && isset($oldid) && $command != "insert") {
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
					foreach($this->ManyManyTables() as $data) {
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
	 * checks for writing
	 *
	 *@name checkForChange
	 *@access public
	 *@param snap-priority
	 *@param newdata
	 *@param param to write changed into
	*/
	public function checkForChange($snap_priority, $newdata, &$changed = array(), $includeAll = false) {

		$newdata = ArrayLib::map_key("strtolower", $newdata);
		
		// first check if this record is important
		if(!$this->isField("stateid") || !$this->isField("publishedid")) {
			$query = new SelectQuery($this->baseTable . "_state", array("publishedid", "stateid"), array("id" => $this->recordid));
			if($query->execute()) {
				while($row = $query->fetch_object()) {
					$this->publishedid = $row->publishedid;
					$this->stateid = $row->stateid;
					break;
				}
				if(!isset($this->data["publishedid"])) {
					$this->publishedid = 0;
					$this->stateid = 0;
				}
			} else {
				throw new MySQLException();
			}
		}
		
		// try and find out whether to write cause of state
		if($this->publishedid != 0 && $this->stateid != 0 && ($this->stateid == $this->versionid || $snap_priority == 1) && ($this->publishedid == $this->versionid || $snap_priority == 2)) {
			
			$forceChange = false;
			
			// first calculate change-count
			foreach($this->data as $key => $val) {
				if(isset($newdata[$key])) {
					$comparableTypes = array("boolean", "integer", "string", "double");
					if(in_array(gettype($newdata[$key]), $comparableTypes) && in_array(gettype($val), $comparableTypes))
					{
						if($newdata[$key] != $val) {
							$changed[$key] = $newdata[$key];
						}
					} else if(gettype($newdata[$key]) != gettype($val) || $newdata[$key] != $val) {
						$changed[$key] = $newdata[$key];
					}
				}
			}
			
			// has-one
			if(!$forceChange && $has_one = $this->hasOne()) {
				foreach($has_one as $key => $value) {
					if(isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "DataObject")) {
						$forceChange = true;
						break;
					}
				}
			}
			
			// many-many
			if(!$forceChange && $this->ManyMany())
				foreach($this->ManyMany() as $name => $table)
				{
						if((isset($newdata[$name . "ids"]) && is_array($newdata[$name . "ids"])) || (isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "ManyMany_DataObjectSet")))
						{
								if($includeAll) $changed[$name] = (isset($newdata[$name . "ids"])) ? $newdata[$name . "ids"] : $newdata[$name];
								$forceChange = true;
								break;
						}
				}
			
			// many-many
			if(!$forceChange && $this->BelongsManyMany())
				foreach($this->BelongsManyMany() as $name => $table)
				{
						if((isset($newdata[$name . "ids"]) && is_array($newdata[$name . "ids"])) || (isset($newdata[$key]) && is_object($newdata[$key]) && is_a($newdata[$key], "ManyMany_DataObjectSet")))
						{
								if($includeAll) $changed[$name] = (isset($newdata[$name . "ids"])) ? $newdata[$name . "ids"] : $newdata[$name];
								$forceChange = true;
								break;
						}	
				}
			
			// has-many
			if(!$forceChange && $this->hasMany())
				foreach($this->hasMany() as $name => $class)
				{
						if(isset($newdata[$name]) && !isset($newdata[$name . "ids"]))
							$newdata[$name . "ids"] = $newdata[$name];
						if(isset($newdata[$name . "ids"]) && is_array($newdata[$name . "ids"]))
						{
								if($includeAll) $changed[$name] = $newdata[$name . "ids"];
								$forceChange = true;				
						}						
				}
			
			// now check if we should write or not
			if(!$forceChange && ((count($changed) == 1 && isset($changed["last_modified"])) || (count($changed) == 2 && isset($changed["last_modified"], $changed["editorid"])))) {
				// should we really write this?! No!
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	
	/**
	 * writes changed data silently, so without chaning last-modified and other stuff than manually changed
	 *
	 *@name writeSilent
	 *@access public
	 *@param bool - to force insert (default: false)
	 *@param bool - to force write (default: false)
	 *@param numeric - priority of the snapshop: autosave 0, save 1, publish 2
	 *@param bool - if to force publishing also when not permitted (default: false)
	 *@param bool - whether to track in history (default: true)
	 *@param bool - whether to write silently, so without chaning anything automatically e.g. last_modified (default: false)
	 *@return bool
	*/
	public function writeSilent($forceInsert = false, $forceWrite = false, $snap_priority = 2, $forcePublish = false, $history = true)
	{
		return $this->write($forceInsert, $forceWrite, $snap_priority, $forcePublish, $history, true);
	}
	
	/**
	 * gets field-value-pairs for a given class-table of the current data
	 * it returns the data for the table of the given class
	 * this is used for seperating data in write to correct tables
	 *
	 *@name getFieldValues
	 *@access protected
	 *@param string - class or table-name
	 *@param string - command
	*/
	protected function getFieldValues($class, $command, $silent = false)
	{
			$arr = array();
			if(isset(ClassInfo::$class_info[$class]["db"])) {
				$fields = ClassInfo::$class_info[$class]["db"];
				if(isset(ClassInfo::$database[ClassInfo::$class_info[$class]["table"]])) {
					foreach(ClassInfo::$database[ClassInfo::$class_info[$class]["table"]] as $field => $type)
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
					
					if(isset($fields["last_modified"]) && !$silent)
						$arr["last_modified"] = NOW;
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
				
				if(isset(ClassInfo::$database[$class]["last_modified"]) && !$silent)
					$arr["last_modified"] = NOW;
			}
			
			return $arr;
	}
	
	/**
	 * is used for writing many-many-relations in DataObject::write
	 *
	 *@name set_many_many_manipulation
	 *@access protected
	 *@param array - manipulation
	 *@param string - relation
	 *@param array - ids of relation
	*/
	protected function set_many_many_manipulation($manipulation, $relation, $ids)
	{
			$many_many = $this->ManyMany();
			$belongs_many_many = $this->BelongsManyMany();
			$many_many_tables = $this->ManyManyTables();
			
			if(isset($many_many[$relation])) {
					$object = $many_many[$relation];

					$table_name = ClassInfo::$class_info[$object]["table"];
					
			} else if(isset($belongs_many_many[$relation]))
			{
					$object = $belongs_many_many[$relation];

					$table_name = ClassInfo::$class_info[$object]["table"];
					
			}
			if(isset($many_many_tables[$relation]))
			{
					$table = $many_many_tables[$relation]["table"];
					$data = $many_many_tables[$relation];
			} else
			{
					return false;
			}
			
			// check for existing entries
			$sql = "SELECT ". $data["extfield"] . ""." FROM ".DB_PREFIX . $table." WHERE ".$data["field"]." = ".$this["versionid"];
			if($result = SQL::Query($sql)) {
				$existing = array();
				while($row = SQL::fetch_object($result)) {
					$existing[] = $row->{$data["extfield"]};
				}
			} else {
				throw new PermissionException();
			}
			
			
			$mani_insert = array(
				"table_name"	=> $table,
				"command"		=> "insert",
				"fields"		=> array(
					
				)
			);
			
			foreach($ids as $id)
			{
				if(is_array($id)) {
					$extraData = $id;
					$id = $extraData["id"];
					unset($extraData["id"], $extraData["versionid"]);
				} else {
					$extraData = array();
				}
				
				if(!in_array($id, $existing)) {
					$mani_insert["fields"][] = array_merge($extraData, array(
						$data["field"] 	=> $this["versionid"],
						$object . "id" 	=> $id
					));
				}
			}
			
			$manipulation[$table . "_insert"] = $mani_insert;
			
			return $manipulation;
	}
	
	/**
	 * unpublishes the record
	 *
	 *@name unpublish
	 *@access public
	*/
	public function unpublish($force = false, $history = true) {
		if((!$this->can("Publish")) && !$force)
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
		
		$this->onBeforeManipulate($manipulation, $b = "unpublish");
		$this->callExtending("onBeforeManipulate", $manipulation, $b = "unpublish");
		
		if(SQL::manipulate($manipulation)) {
			if(self::getStatic($this->classname, "history") && $history) {
				History::push($this->classname, 0, $this->versionid, $this->id, "unpublish");
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
		if((!$this->can("Publish")) && !$force)
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
		
		$this->onBeforeManipulate($manipulation, $b = "publish");
		$this->callExtending("onBeforeManipulate", $manipulation, $b = "publish");
		
		if(SQL::manipulate($manipulation)) {
			if(self::getStatic($this->classname, "history") && $history) {
				History::push($this->classname, 0, $this->versionid, $this->id, "publish");
			}
			return true;
		}
		
		return false;
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
			foreach(array_merge(ClassInfo::getChildren($this->classname), array($this->classname)) as $child) {
				Object::instance($child)->buildDB();
			}
			ClassInfo::write();
		}
		
		$manipulation = array();
		$baseClass = ClassInfo::$class_info[$this->RecordClass]["baseclass"];
			
		if(!isset($this->data))
			return true;
		
		if($force || $this->can("Delete"))
		{
				// get the ids which are needed
				$ids = array();
				$query = new SelectQuery($this->baseTable, array("id"), array("recordid" => $this->id));
				if($query->execute()) {
					while($row = $query->fetch_object())
						$ids[] = $row->id;
				} else {
					throw new MySQLException();
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
				if(!self::Versioned($this->classname) || $forceAll || !isset($this->data["stateid"])) {
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
					if($classes = ClassInfo::dataclasses($this->classname))
					{							
							foreach($classes as $class => $table)
							{
									if(isset(ClassInfo::$database[$table]) && $class != $this->classname)
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
		
		self::$datacache[$this->caseClass] = array();
		
		$this->onBeforeRemove($manipulation);
		$this->callExtending("onBeforeRemove", $manipulation);
		if(SQL::manipulate($manipulation)) {
			if(self::getStatic($this->classname, "history") && $history) {
				History::push($this->classname, 0, $this->versionid, $this->id, "remove");
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
	
	//!Current Data-State
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
			$query = new SelectQuery($this->baseTable . "_state", array("publishedid", "stateid"), array("id" => $this->recordid));
			if($query->execute()) {
				while($row = $query->fetch_object()) {
					$this->publishedid = $row->publishedid;
					$this->stateid = $row->stateid;
					break;
				}
				if(isset($this->data["publishedid"])) {
					return ($this->publishedid != 0 && $this->versionid == $this->publishedid);
				} else {
					$this->publishedid = 0;
					$this->stateid = 0;
					return false;
				}
			} else {
				throw new MySQLException();
			}
		}
	}
	
	/**
	 * returns if original version of the record is published
	 *
	 *@name isrOrgPublished
	 *@access public
	*/
	public function isOrgPublished() {
		
		if(isset($this->original["publishedid"])) {
			return ($this->original["publishedid"] != 0 && $this->original["versionid"] == $this->original["publishedid"]);
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
		if($this->isPublished())
			return true;
		
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
		if($this->isPublished() || (isset($this->data["publishedid"]) && $this->data["publishedid"] != 0 && $this->data["stateid"] != 0)) {
			return false;
		} else
			return true;
	}

	//!Forms
	
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
	 * geteditform
	 *@name geteditform
	 *@param object
	 *@param array - data
	*/
	public function getEditForm(&$form)
	{
	
			$form->setResult($this);
			$this->getForm($form);			
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
		$this->fieldInfo = array_merge($this->fieldInfo, $this->getFieldInfo());
		
		foreach($this->DataBaseFields() as $field => $type) {
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
	 * gets on the fly generated field-info
	*/
	public function getFieldInfo() {
		return array();
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
			$name = $this->classname . "_" . $this->versionid . "_" . $this->id;
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
		
		$form->add(new HiddenField("class_name", $this->classname));
		
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
	 * gets a list of all fields with according titles of this object
	 *
	 *@name summaryFields
	 *@access public
	*/
	public function summaryFields() {
		$f = ArrayLib::key_value(array_keys($this->DataBaseFields()));
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
	 * relation-management
	*/
	
	//!Relation Methods
	
	/**
	  * set many-many-connection-data
	  *
	  *@name set_many_many
	  *@param stirng - name of connection
	  *@param array - ids to connect with current id
	  *@param bool - override permission system
	  *@access public
	*/
	public function set_many_many($name, $data, $force = false)
	{
		if($this->can("Write", $this)) {
			if(!$this->isPublished() || $this->can("Publish", $this)) {
				$manipulation = $this->set_many_many_manipulation(array(), $name, $data);
				
				$this->onBeforeManipulate($manipulation, $b = "set_many_many");
				$this->callExtending("onBeforeManipulate", $manipulation, $b = "set_many_many");
				
				return SQL::manipulate($manipulation);
			}
		}
		
		return false;
	}
	
	/**
	 * checks if the current id is connected with the given id
	 *
	 *@name is_many_many
	 *@access public
	 *@param string - connection
	 *@param numeric - id
	*/
	public function is_many_many($name, $id)
	{
			$many_many = $this->ManyMany();
			$belongs_many_many = $this->belongsManyMany();
			$many_many_tables = $this->ManyManyTables();
			// there are two ways defining a many-many-relation: with belongs_many_many and many_many
			if(isset($many_many[$name]))
			{
					$relationObject = $many_many[$name]; // object refering to
					$relationObjectTable = ClassInfo::$class_info[ClassInfo::$class_info[$relationObject]["baseclass"]]["table"];; // table of object refering to
					
			} else if(isset($belongs_many_many[$name]))
			{
					$relationObject = $belongs_many_many[$name]; // object refering to
					$relationObjectTable = ClassInfo::$class_info[ClassInfo::$class_info[$relationObject]["baseclass"]]["table"]; // table of Object refering to
					
			}
			
			/**
			 * there is the var many_many_tables, which contains data for the table, which stores the relation
			 * for exmaple: array(
			 * "table"	=> "my_many_many_table_generated_by_system",
			 * "field"	=> "myclassid"
			 * )
			*/
			
			if(isset($many_many_tables[$name]))
			{
					$relationTable = $many_many_tables[$name]["table"];
					$data = $many_many_tables[$name];
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
						DataObject::count(	$relationObject, 
											array(	$relationObjectTable.'.id' 				=> $id, 
													$this->BaseTable() . '.id' 	=> $this["versionid"]), 
											array(
												' INNER JOIN '.DB_PREFIX . $relationTable.' AS '.$relationTable.' ON '.$relationTable.'.'.$relationObject . 'id = '.$relationObjectTable.'.id ', // Join other table with many-many-table
												' INNER JOIN '.DB_PREFIX . $this->BaseTable().' AS '.$this->Table().' ON '.$relationTable.'.'. $data["field"] . ' = '.$this->Table().'.id ' // join this table with many-many-table
											)
										) > 0
					);
	
	}
	
	/**
	 * gets relation ids
	 *
	 *@name getRelationIDs
	 *@access public
	*/
	public function getRelationIDs($relname) {
		$relname = trim(strtolower($relname));
		
		if(substr($relname, -3) == "ids") {
			$relname = substr($relname, 0, -3);
		}
		
		// get all config
		$has_many = $this->hasMany();
		$many_many = $this->ManyMany();
		$belongs_many_many = $this->BelongsManyMany();
		$many_many_tables = $this->ManyManyTables();
		
		if(isset($has_many[$relname])) {
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
		} else if(isset($many_many[$relname]) || isset($belongs_many_many[$relname])) {
			if(isset($this->data[$relname . "ids"]))
				return $this->data[$relname . "ids"];
			
			if(isset($this->data[$relname]) && is_a($this->data[$relname], "ManyMany_DataObjectSet")) {
				
			}
			
			/**
			 * there is the var many_many_tables, which contains data for the table, which stores the relation
			 * for exmaple: array(
			 * "table"	=> "my_many_many_table_generated_by_system",
			 * "field"	=> "myclassid"
			 * )
			*/
			
			if(isset($many_many_tables[$relname]))
			{
					$table = $many_many_tables[$relname]["table"]; // relation-table
					$data = $many_many_tables[$relname];
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
	 * gets relation-data
	 *
	 *@name getRelationData
	 *@access public
	*/
	public function getRelationData($relname) {
		$relname = trim(strtolower($relname));
		
		if(substr($relname, -3) == "ids") {
			$relname = substr($relname, 0, -3);
		}
		
		// get all config
		$has_many = $this->hasMany();
		$many_many = $this->ManyMany();
		$belongs_many_many = $this->BelongsManyMany();
		$many_many_tables = $this->ManyManyTables();
		
		if(isset($has_many[$relname])) {
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
		} else if(isset($many_many[$relname]) || isset($belongs_many_many[$relname])) {
			if(isset($this->data[$relname . "_data"]))
				return $this->data[$relname . "_data"];
			
			/**
			 * there is the var many_many_tables, which contains data for the table, which stores the relation
			 * for exmaple: array(
			 * "table"	=> "my_many_many_table_generated_by_system",
			 * "field"	=> "myclassid"
			 * )
			*/
			
			if(isset($many_many_tables[$relname]))
			{
					$table = $many_many_tables[$relname]["table"]; // relation-table
					$data = $many_many_tables[$relname];
			} else
			{
					return false;
			}

			$query = new SelectQuery($table, array("*"), array($data["field"] => $this["versionid"]));	
			
			$query->execute();			
			$arr = array();
			$i = 0;
			while($row = $query->fetch_assoc()) {
					$arr[$i] = array("versionid" => $row[$data["extfield"]], "id" => $row[$data["extfield"]]);
					if(isset($data["extraFields"]))
						foreach($data["extraFields"] as $field => $pattern) {
							$arr[$i][$field] = $row[$field];
						}
					
					$i++;
			}
			$this->data[$relname . "_data"] = $arr;
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
	public function getHasMany($name, $filter = null, $sort = null, $limit = null) {
		
		$name = trim(strtolower($name));
		
		$has_many = $this->hasMany();
		if(!isset($has_many[$name]))
		{
			$debug = debug_backtrace();
			throwError(6, "PHP-Error", "No Has-many-relation '".$name."' on ".$this->classname." in ".$trace[1]["file"]." on line ".$trace[1]["line"].".");
		}
		
		$cache = "has_many_{$name}_".var_export($filter, true)."_".var_export($sort, true) . "_" . var_export($limit, true);
		if(isset($this->viewcache[$cache])) {
			return $this->viewcache[$cache];
		}
		
		$class = $has_many[$name];
		$key = array_search($this->classname, ClassInfo::$class_info[$class]["has_one"]);
		if($key === false)
		{
			$c = $this->classname;
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

		$filter[$key . "id"] = $this["id"];
		$set = new HasMany_DataObjectSet($class, $filter, $sort, $limit);
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
	public function getHasOne($name, $filter = null) {
		
		$name = trim(strtolower($name));
		
		if(PROFILE) Profiler::mark("getHasOne");
		
		$cache = "has_one_{$name}_".var_export($filter, true) . $this[$name . "id"];
		if(isset($this->viewcache[$cache])) {
			if(PROFILE) Profiler::unmark("getHasOne", "getHasOne viewcache");
			return $this->viewcache[$cache];
		}
		
		$has_one = $this->hasOne();
		if(isset($has_one[$name])) {
			if($this->isField($name) && is_object($this->fieldGet($name)) && is_a($this->fieldGet($name), $has_one[$name]) && !$filter) {
				if(PROFILE) Profiler::unmark("getHasOne");
				return $this->fieldGet($name);
			}
			
			if($this[$name . "id"] == 0) {
				if(PROFILE) Profiler::unmark("getHasOne");
				return false;
			}
			
			$filter["id"] = $this[$name . "id"];
		
			if(isset(self::$datacache[$this->baseClass][$cache])) {
				if(PROFILE) Profiler::unmark("getHasOne", "getHasOne datacache");
				$this->viewcache[$cache] = clone self::$datacache[$this->baseClass][$cache];
				return $this->viewcache[$cache];
			}
			
			$response = DataObject::get($has_one[$name], $filter);
			
			if($this->queryVersion == "state") {
				$response->setVersion("state");
			}
			
			if(($this->viewcache[$cache] = $response->first(false))) {
				self::$datacache[$this->baseClass][$cache] = clone $this->viewcache[$cache];
				if(PROFILE) Profiler::unmark("getHasOne");
				return $this->viewcache[$cache];
			} else {
				if(PROFILE) Profiler::unmark("getHasOne");
				return null;
			}
		} else {
			$debug = debug_backtrace();
			throwError(6, "PHP-Error", "No Has-one-relation '".$name."' on ".$this->classname." in ".$trace[1]["file"]." on line ".$trace[1]["line"].".");
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
		
		// get config
		$many_many = $this->ManyMany();
		$belongs_many_many = $this->BelongsManyMany();
		$many_many_tables = $this->ManyManyTables();
		
		// first we get the object for this connection
		if(isset($many_many[$name]))
		{
			$object = $many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table"]; // table
		} else if(isset($belongs_many_many[$name]))
		{
			$object = $belongs_many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table"]; // table
		} else {
			throwError(6, 'PHP-Error', "Many-Many-Relation ".convert::raw2text($name)." does not exist!");
		}
		
		$data = $many_many_tables[$name]; // this contains the field for this object and the other
		
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
		
		// sometimes there is an bug with $this->Table(), so get from registry
		$baseClass = ClassInfo::$class_info[$this->classname]["baseclass"];
		$baseTable = ClassInfo::$class_info[$baseClass]["table"];
		
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
		
		// get config
		$many_many = $this->ManyMany();
		$belongs_many_many = $this->BelongsManyMany();
		$many_many_tables = $this->ManyManyTables();
		
		// first we get the object for this connection
		if(isset($many_many[$name]))
		{
			$object = $many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table"]; // table
		} else if(isset($belongs_many_many[$name]))
		{
			$object = $belongs_many_many[$name]; // object
			$table = ClassInfo::$class_info[$object]["table"]; // table
		} else {
			throwError(6, 'Logical Exception', "Many-Many-Relation ".convert::raw2text($name)." does not exist!");
		}
		
		$data = $many_many_tables[$name];
		
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
		
		// get config
		$many_many = $this->ManyMany();
		$belongs_many_many = $this->BelongsManyMany();
		$many_many_tables = $this->ManyManyTables();
		
		// first we get the object for this connection
		if(!isset($many_many[$name]) && !isset($belongs_many_many[$name])) {
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
	//!GETTERS AND SETTERS
	
	/**
	 * gets versions of this ordered by time DESC
	 *
	 *@name versions
	 *@access public
	*/
	public function versions($limit = null, $where = array(), $orderasc = false) {
		$ordertype = ($orderasc === true) ? "ASC" : "DESC";
		return DataObject::get_versioned($this->classname, false, array_merge($where,array(
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
			return DataObject::get_one("user",array('id' => $this['editorid']));
		} else
			return $this->autor();
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
	 * gets the class of the current record
	 *@name getRecordClass
	 *@access public
	*/
	public function RecordClass()
	{
			return $this->classname;	
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
	 * returns the representation of this record
	 *
	 *@name generateResprensentation
	 *@access public
	*/
	public function generateRepresentation($link = false) {
		if($this->title)
			$title = $this->title;
		
		else if($this->name)
			$title = $this->name;
		else {
			$fields = array_values($this->DataBaseFields());
			if(isset($fields[0]))
				$title = $this[$fields[0]];
			else
				return null;
		}
		
		if(ClassInfo::findFile(self::getStatic($this->classname, "icon"), $this->classname)) {
			$title = '<img src="'.ClassInfo::findFile(self::getStatic($this->classname, "icon"), $this->classname).'" /> ' . $title;
		}
		
		return $title;
	}
	
	//!Queries
	
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
				foreach(array_merge(ClassInfo::getChildren($this->classname), array($this->classname)) as $child) {
					Object::instance($child)->buildDB();
				}
				ClassInfo::write();
			}
			
			if(PROFILE) Profiler::mark("DataObject::buildQuery hairy");
			
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
			
			if(PROFILE) Profiler::unmark("DataObject::buildQuery hairy");
			
			if(is_array($filter)) {
				if(isset($filter["versionid"])) {
					$filter["".$this->baseTable.".id"] = $filter["versionid"];
					unset($filter["versionid"]);					
					$version = false;
				}
			}
			
			if($version !== false && self::versioned($this->classname)) {
				if(isset($_GET[$baseClass . "_version"]) && $this->_canVersion($version)) {
					$version = $_GET[$baseClass . "_version"];
				}
				
				if(isset($_GET[$baseClass . "_state"]) && $this->_canVersion($version)) {
					$version = "state";
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
						
						if(isset($query->filter["id"])) {
							$query->filter["recordid"] = $query->filter["id"];
						}
						
						unset($query->filter["id"]);
						
						
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
			
			$hasOnes = array();
			$has_one = $this->hasOne();
			
			// some specific addons for relations
			if(is_array($filter))
			{
					foreach($filter as $key => $value)
					{
							// many-many
							if(isset(ClassInfo::$class_info[$this->classname]["many_many"][$key]) || isset(ClassInfo::$class_info[$this->classname]["belongs_many_many"][$key]))
							{										
									if(is_array($value))
									{
											$object = isset(ClassInfo::$class_info[$this->classname]["many_many"][$key]) ? ClassInfo::$class_info[$this->classname]["many_many"][$key] : ClassInfo::$class_info[$this->classname]["belongs_many_many"][$key];
											if($object)
											{
													$objectTable = ClassInfo::$class_info[ClassInfo::$class_info[$object]["baseclass"]]["table"];
													if(isset(ClassInfo::$class_info[$this->classname]["many_many_tables"][$key]))
													{
															$table = ClassInfo::$class_info[$this->classname]["many_many_tables"][$key]["table"];
															$data = ClassInfo::$class_info[$this->classname]["many_many_tables"][$key];
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
																			'.DB_PREFIX . $objectTable.' 
																		AS 
																			'.$objectTable.' 
																		ON 
																			'.$table.'.'.$objectTable . 'id = '.$objectTable.'.id 
																		 '; // join other table with many-many-table
											}
											foreach($value as $field => $val)
											{
													$filter[$objectTable . '.' . $field] = $val;
											}
											
											$query->removeFilter($key);
									} else
									{
											if(ClassInfo::$class_info[$this->classname]["many_many"][$key])
											{
													if(isset(ClassInfo::$class_info[$this->classname]["many_many_tables"][$key]))
													{
															$table = ClassInfo::$class_info[$this->classname]["many_many_tables"][$key]["table"];
															$data = ClassInfo::$class_info[$this->classname]["many_many_tables"][$key];
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
							
							// has-one
							
							if(strpos($key, ".") !== false) {
								if(isset($has_one[strtolower(substr($key, 0, strpos($key, ".")))])) {
									$has_oneField = substr($key, strpos($key, ".") + 1);
									$hasOnes[strtolower(substr($key, 0, strpos($key, ".")))][$has_oneField] = $value;
									$query->removeFilter($key);
								}
							}
							unset($key, $value, $table, $data, $__table, $_table);
					}
			}
			
			if(count($hasOnes) > 0) {
				foreach($hasOnes as $key => $fields) {
					$c = $has_one[$key];
					$table = ClassInfo::$class_info[$c]["table"];
					
					foreach($fields as $field => $val) {
						$fields[$table . "." . $field] = $val;
						unset($fields[$field]);
					}
					
					$query->from[] = ' INNER JOIN 
													'.DB_PREFIX . $table.' 
												AS 
													'.$table.' 
												ON  
												 '.$table.'.id = '.$this->Table().'.'.$key.'id AND ('.SQL::ExtractToWhere($fields, false).')';
				}
			}
			
			// sort
			if(!empty($sort))			
				$query->sort($sort);
			else
				$query->sort(self::getStatic($this->classname, "default_sort"));
			
			
			// limiting
			$query->limit($limit);
			
			if($join)
				foreach($join as $table => $statement)
				{
					if(preg_match('/^[0-9]+$/', $table) && is_numeric($table))
						$query->from[] = $statement;
					else if($statement == "")
						$query->from[$table] = "";	
					else if(strpos(strtolower($statement), "join")) 
						$query->from[$table] = $statement;
					else
						$query->from[$table] = " LEFT JOIN ".DB_PREFIX.$table." AS ".$table." ON " . $statement;
				}
			
			// don't forget filtering on class-name
			if($forceClasses) {
				$class_names = array_merge(array($this->classname), ClassInfo::getChildren($this->classname));		
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
	 * returns whether canVersion or not
	 *
	 *@name _canVersion
	 *@access public
	*/
	public function _canVersion($version) {
		$canVersion = ($version === true) ? true : $this->can("viewVersions");
		if(!$canVersion && member::login()) {
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
		
		return $canVersion;
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
				$table_name = ClassInfo::$class_info[$this->baseClass]["table"];
				if($table_name != "")
				{
					if($this->searchFields())
						foreach($this->searchFields() as $field) {		
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
						$table_name = ClassInfo::$class_info[$class]["table"];
						if($table_name != "") {
							if($this->searchFields())
								foreach($this->searchFields() as $field) {
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
			
			if($filter)
				$query->addFilter(array($filter));
			else
				throw new LogicException("Could not search for ".$searchQuery.". No Search-Fields defined in {$this->baseClass}.");
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
			if(!isset(ClassInfo::$class_info[$this->baseClass]["table"]) || !ClassInfo::$class_info[$this->baseClass]["table"] || !defined("SQL_LOADUP"))
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
				if(isset(self::$datacache[$this->baseClass][$hash])) {
					return self::$datacache[$this->baseClass][$hash];
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
				if(isset($basehash)) self::$datacache[$this->baseClass][$basehash . md5(serialize(array("id" => $row["id"])))] = array($row);
				
				// cleanup
				unset($row);
			}
			
			self::$datacache[$this->baseClass][$hash] = $arr;
			
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
		if(!isset(ClassInfo::$class_info[$this->baseClass]["table"]) || !ClassInfo::$class_info[$this->baseClass]["table"] || !defined("SQL_LOADUP"))
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
				
				$data[$row[$groupField]] = DataObject::get($this->classname, $filter, $sort, array(), $joins, $version);
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
		if(!isset(ClassInfo::$class_info[$this->baseClass]["table"]) || !ClassInfo::$class_info[$this->baseClass]["table"] || !defined("SQL_LOADUP"))
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
	
	//!Connection to the Controller
	
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
				$this->controller->model = $this->classname;
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
					$this->controller->model = $this->classname;
					return $this->controller;
			} else {
				
				if(ClassInfo::exists($this->classname . "controller"))
				{
						$c = $this->classname . "controller";
						$this->controller = new $c;
						$this->controller->model_inst = $this;
						$this->controller->model = $this->classname;
						return $this->controller;
				} else {
					if(ClassInfo::getParentClass($this->classname) != "dataobject") {
						$parent = $this->classname;
						while(($parent = ClassInfo::getParentClass($parent)) != "dataobject") {
							if(!$parent)
								return false;
							
							if(ClassInfo::exists($parent . "controller")) {
								$c = $parent . "controller";
								$this->controller = new $c;
								$this->controller->model_inst = $this;
								$this->controller->model = $this->classname;
								return $this->controller;
							}
						}
					}
				}
			}
			return false;
	}
	
	//! APIs
	
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
		$fields = $this->DataBaseFields(true);
		return isset($fields[$field]);
	}
	
	/**
	 * checks if we can filter by a specified field
	 *
	 *@name canSortBy
	 *@access public
	*/
	public function canFilterBy($field) {
		if(strpos($field, ".") !== false) {
			$has_one = $this->HasOne();
			
			if(isset($has_one[strtolower(substr($field, 0, strpos($field, ".")))])) {
				return true;
			}
		}
		
		$fields = $this->DataBaseFields(true);
		return isset($fields[$field]);
	}
	
	/**
	 * this method consolidates all relation data in data
	 *
	 *@name consolidate
	 *@access public
	*/
	public function consolidate() {
		foreach($this->manyManyTables() as $name => $data) {
			$this->getRelationIDs($name);
		}
		return $this;
	}
	
	/**
	 * gets a object of this record with id and versionid set to 0
	 *
	 *@name duplicate
	 *@access public
	*/
	public function duplicate() {
		$this->consolidate();
		$data = clone $this;
		
		$data->id = 0;
		$data->versionid = 0;
		
		return $data;
	}
	
	/**
	 * duplicates given number of this model and writes them to the database
	 *
	 *@name duplicateWrite
	 *@access public
	*/
	public function duplicateWrite($num = 1, $fieldToRise = null, $forceWrite = false, $snap_priority = 2) {
		$fieldValue = array();
		for($i = 0; $i < $num; $i++) {
			$data = $this->duplicate();
			if(isset($fieldToRise)) {
				if(is_array($fieldToRise)) {
					foreach($fieldToRise as $field) {
						$val = isset($fieldValue[$field]) ? $fieldValue[$field] : $data[$field];
						if(preg_match('/^(.*)([0-9]+)$/Us', $val, $m)) {
							$data[$field] = $m[1] . ($m[2] + $i + 1);
						} else {
							$data[$field] = $val . " " . ($i + 1);
						}
					}
				} else {
					$field = $fieldToRise;
					$val = isset($fieldValue[$field]) ? $fieldValue[$field] : $data[$field];
					if(preg_match('/^(.*)([0-9]+)$/Us', $val, $m)) {
						$data[$field] = $m[1] . ($m[2] + $i + 1);
					} else {
						$data[$field] = $val . " " . ($i + 1);
					}

				}
			}
			
			if(!$data->write(false, $forceWrite, $snap_priority)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * bool
	*/
	public function bool() {
		return (array_merge(array(
				"class_name"	=> $this->classname,
				"last_modified"	=> NOW,
				"created"		=> NOW,
				"autorid"		=> member::$id
			), (array) $this->defaults) != $this->data);
	}
	
	/**
	 * clears the data-cache
	 *
	 *@name clearDataCache
	 *@access public
	*/
	public static function clearDataCache($class = null) {
		if(isset($class)) {
			$class = strtolower($class);
			self::$datacache[$class] = array();
		} else {
			self::$datacache = array();
		}
	}
	
	//!API for Config
	
	/**
	 * returns DataBase-Fields of this record
	 *
	 *@name DataBaseFields
	*/
	public function DataBaseFields($recursive = false) {
		if($recursive) {
			$db = array();
			if(isset(ClassInfo::$class_info[$this->baseClass]["db"]))
				$db = array_merge($db, ClassInfo::$class_info[$this->baseClass]["db"]);
			
			if(ClassInfo::DataClasses($this->baseClass))
				foreach(ClassInfo::DataClasses($this->baseClass) as $class) {
					if(isset(ClassInfo::$class_info[$class]["db"]))
						$db = array_merge($db, ClassInfo::$class_info[$class]["db"]);
				}
			
			return $db;
		} else
			return isset(ClassInfo::$class_info[$this->classname]["db"]) ? ClassInfo::$class_info[$this->classname]["db"] : array();
	}
	
	/**
	 * returns the indexes
	 *
	 *@name indexes
	*/
	public function indexes() {
		return isset(ClassInfo::$class_info[$this->classname]["index"]) ? ClassInfo::$class_info[$this->classname]["index"] : array();
	}
	
	/**
	 * returns the search-fields
	 *
	 *@name searchFields
	*/
	public function searchFields() {
		return isset(ClassInfo::$class_info[$this->classname]["search"]) ? ClassInfo::$class_info[$this->classname]["search"] : array();
	}
	
	/**
	 * table
	 *
	 *@name Table
	*/
	public function Table() {
		return isset(ClassInfo::$class_info[$this->classname]["table"]) ? ClassInfo::$class_info[$this->classname]["table"] : false;
	}
	
	/**
	 * table
	 *
	 *@name hasTable
	*/
	public function hasTable() {
		return ((isset(ClassInfo::$class_info[$this->classname]["table_exists"]) ? ClassInfo::$class_info[$this->classname]["table_exists"] : false) && $this->Table());
	}
	
	/**
	 * has-one-relations
	 *
	 *@name hasOne
	*/
	public function hasOne($component = null) {
		if($component === null) {
			$has_one = (isset(ClassInfo::$class_info[$this->classname]["has_one"]) ? ClassInfo::$class_info[$this->classname]["has_one"] : array());
			
			if($classes = ClassInfo::dataclasses($this->classname)) {
				foreach($classes as $class) {
					if(isset(ClassInfo::$class_info[$class]["has_one"]))
						$has_one = array_merge(ClassInfo::$class_info[$class]["has_one"], $has_one);
				}
			}
			
			return $has_one;
		} else {
			if(isset(ClassInfo::$class_info[$this->classname]["has_one"][$component])) {
				return ClassInfo::$class_info[$this->classname]["has_one"][$component];
			}
		}
	}
	
	/**
	 * has-many-relations
	 *
	 *@name hasMany
	*/
	public function hasMany() {
		$has_many = (isset(ClassInfo::$class_info[$this->classname]["has_many"]) ? ClassInfo::$class_info[$this->classname]["has_many"] : array());
		
		if($classes = ClassInfo::dataclasses($this->classname)) {
			foreach($classes as $class) {
				if(isset(ClassInfo::$class_info[$class]["has_many"]))
					$has_many = array_merge(ClassInfo::$class_info[$class]["has_many"], $has_many);
			}
		}
		
		return $has_many;
	}
	
	/**
	 * many-many-relations
	 *
	 *@name ManyMany
	*/
	public function ManyMany() {
		$many_many = (isset(ClassInfo::$class_info[$this->classname]["many_many"]) ? ClassInfo::$class_info[$this->classname]["many_many"] : array());
		
		if($classes = ClassInfo::dataclasses($this->classname)) {
			foreach($classes as $class) {
				if(isset(ClassInfo::$class_info[$class]["many_many"]))
					$many_many = array_merge(ClassInfo::$class_info[$class]["many_many"], $many_many);
			}
		}
		
		return $many_many;
	}
	
	/**
	 * many-many-relations belonging to this
	 *
	 *@name BelongsManyMany
	*/
	public function BelongsManyMany() {
		$belongs_many_many = (isset(ClassInfo::$class_info[$this->classname]["belongs_many_many"]) ? ClassInfo::$class_info[$this->classname]["belongs_many_many"] : array());
		
		if($classes = ClassInfo::dataclasses($this->classname)) {
			foreach($classes as $class) {
				if(isset(ClassInfo::$class_info[$class]["many_many"]))
					$belongs_many_many = array_merge(ClassInfo::$class_info[$class]["belongs_many_many"], $belongs_many_many);
			}
		}
		
		return $belongs_many_many;
	}
	
	/**
	 * many-many-tables belonging to this
	 *
	 *@name ManyManyTables
	*/
	public function ManyManyTables() {
		return (isset(ClassInfo::$class_info[$this->classname]["many_many_tables"]) ? ClassInfo::$class_info[$this->classname]["many_many_tables"] : array());
	}
	
	
	/**
	 * returns if a DataObject is versioned
	 *
	 *@name Versioned
	*/
	public static function Versioned($class) {
		if(self::hasStatic($class, "versions") && self::getStatic($class, "versions") == true)
			return true;
		
		if(Object::instance($class)->versioned == true)
			return true;
			
		return false;
	}
	
	/**
	 * gets the baseclass of the current record
	 *@name getBaseClass
	 *@access public
	*/
	public function BaseClass()
	{
			return isset(ClassInfo::$class_info[$this->classname]["baseclass"]) ? ClassInfo::$class_info[$this->classname]["baseclass"] : $this->classname;	
	}
	
	/**
	 * gets the base-table
	 *
	 *@name getBaseTable
	 *@access public
	*/
	public function BaseTable() {
		return (ClassInfo::$class_info[ClassInfo::$class_info[$this->classname]["baseclass"]]["table"]);
	}
	
	//!Dev-Area: Generation of DataBase
	
	/**
	 * dev
	 *
	 *@name buildDB
	 *@access public
	*/
	public function buildDB($prefix = DB_PREFIX) {
		$log = "";
		$this->callExtending("beforeBuildDB", $prefix, $log);
		
		// first get all fields with translated types
		$db_fields = $this->DataBaseFields();
		$indexes = $this->indexes();
		$casting = $this->casting();
	
		// add some fields for versioning
		if($this->Table() && $this->Table() == $this->baseTable) {
			if(!isset($db_fields["recordid"]))
				$db_fields["recordid"] = "int(10)";
			
			if(self::Versioned($this->classname)) {
				$db_fields["snap_priority"] = "int(10)";
			}
			
			if(!isset($indexes["recordid"]))
				$indexes["recordid"] = "INDEX";
		}
		
		if($this->Table()) {
			
			// get correct SQL-Types for Goma-Field-Types
			foreach($db_fields as $field => $type) {
				if(isset($casting[strtolower($field)])) {
					if($casting[strtolower($field)] = DBField::parseCasting($casting[strtolower($field)])) {
						$type = call_user_func_array(array($casting[strtolower($field)]["class"], "getFieldType"), (isset($casting[strtolower($field)]["args"])) ? $casting[strtolower($field)]["args"] : array());
						if($type != "")
							$db_fields[$field] = $type;
					}
				}
			}
			
			// now require table
			$log .= SQL::requireTable($this->table(), $db_fields, $indexes , $this->defaults, $prefix);
		}
		
		// versioned
		if($this->hasTable() && $this->table() == $this->baseTable) {
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
				if(self::Versioned($this->classname)) {
					$sql2 = "UPDATE ".$prefix.$this->baseTable." SET snap_priority = 2, recordid = id, editorid = autorid";
				} else {
					$sql2 = "UPDATE ".$prefix.$this->baseTable." SET recordid = id, editorid = autorid";
				}
				if(sql::query($sql) && sql::query($sql2))
					$log .= "Copying Version-Data\n";
				else
					throw new MySQLException();
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
				), array(), $prefix);
				ClassInfo::$database[$data["table"]] = $fields;
			}	
		}
		
		// sort of table
		$sort = self::getStatic($this->classname, "default_sort");
		
		if(is_array($sort)) {
			if(isset($sort["field"], $sort["type"])) {
				$field = $sort["field"];
				$type = $sort["type"];
			} else {
				$sort = array_values($sort);
				$field = $sort[0];
				$type = isset($sort[1]) ? $sort[1] : "ASC";
			}
		} else if(preg_match('/^([a-zA-Z0-9_\-]+)\s(DESC|ASC)$/Usi', $sort, $matches)) {
			$field = $sort[1];
			$type = $sort[2];
		} else {
			$field = $sort;
			$type = "ASC";
		}
		if(isset(ClassInfo::$database[$this->Table()][$field])) {
			SQL::setDefaultSort($this->Table(), $field, $type);
		}
		
		$this->callExtending("buildDB", $prefix, $log);
		
		$this->preserveDefaults($prefix, $log);
		$this->cleanUpDB($prefix, $log);
		
		$this->callExtending("afterBuildDB", $prefix, $log);
		
		$output = '<div style="padding-top: 6px;"><div><img src="images/success.png" height="16" alt="Success" /> Checking Database of '.$this->classname."</div><div style=\"padding-left: 21px;width: 550px;\">";
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
		
		$this->callExtending("generateClassInfo");
	}
	
	/**
	 * preserve Defaults
	 *
	 *@name preserveDefaults
	 *@åccess public
	*/
	public function preserveDefaults($prefix = DB_PREFIX, &$log) {
		$this->callExtending("preserveDefaults", $prefix);
		
		if($this->hasTable()) {			
			//@todo bugfix
			if(count($this->defaults) > 0) {
				foreach($this->defaults as $field => $value) {
					if(isset(ClassInfo::$database[$this->Table()][$field])) {
						$sql = "UPDATE ".DB_PREFIX . $this->Table()." SET ".$field." = '".$value."' WHERE ".$field." = '' AND ".$field." != '0'";
						if(!sql::query($sql, false, $prefix)) {
							return false;
						}
					}
				}
			}
			
			if($this->baseClass == $this->classname) {
				// set record ids
				$sql = "UPDATE ".DB_PREFIX . $this->Table()." SET recordid = id WHERE recordid = 0";
				SQL::query($sql);
			
				$sql = "UPDATE ".DB_PREFIX . $this->Table()." SET editorid = autorid WHERE editorid = 0";
				SQL::query($sql);
			}
		}
		
		if($this->Table() == $this->baseTable) {
			// clean-up recordid-0s
			$sql = "SELECT * FROM ".DB_PREFIX . $this->Table()." WHERE recordid = '0'";
			if($result = SQL::Query($sql)) {
				while($row = SQL::fetch_object($result)) {
					$_sql = "SELECT * FROM ".DB_PREFIX . $this->baseTable."_state WHERE publishedid = '".$row->id."' OR stateid = '".$row->id."'";
					if($_result = SQL::Query($_sql)) {
						if($_row = SQL::fetch_object($_result)) {
							$update = "UPDATE ".DB_PREFIX . $this->Table()." SET recordid = '".$_row->id."' WHERE id = '".$row->id."'";
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
		
		if(self::Versioned($this->classname) && $this->baseClass == $this->classname) {
			$recordids = array();
			$ids = array();
			// first recordids
			$sql = "SELECT * FROM ".DB_PREFIX.$this->classname."_state";
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
			
			$log .= 'Checking for old versions of '.$this->classname."\n";
			if(count($deleteids) > 10) {
				// now delete
        				
				// first generate tables
				$tables = array($this->baseTable);
				foreach(ClassInfo::dataClasses($this->classname) as $class => $table) {
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
			foreach($this->manyManyTables() as $name => $data) {
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
	
	//!Generate Information for ClassInfo
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
					'class_name' 	=> 'enum("'.implode('","', array_merge(Classinfo::getChildren($class), array($class))).'")',
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
		
		$fields = array();
		if(self::hasStatic($this->classname, "db")) {
			$fields = (array) self::getStatic($this->classname, "db");
		}
		
		if(isset($this->db_fields)) {
			$fields = $this->db_fields;
			Core::deprecate("2.0", "Class ".$this->classname." uses old db_fields-Attribute, use static \$db instead.");
		}
		
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
		
		$has_one = (array) self::getStatic($this->classname, "has_one");
		foreach($this->LocalcallExtending("Has_One") as $has_ones) {
			$has_one = array_merge($has_one, $has_ones);
			unset($has_ones);
		}
		
		$parent = strtolower(get_parent_class($this));
		if($parents === true && $parent != "dataobject") {
			$has_one = array_merge(Object::instance($parent)->generateHas_one(), $has_one);
		}
		
		if($parent == "dataobject") {
			$has_one["autor"] = "user";
			$has_one["editor"] = "user";
		}
		
		$has_one = array_map("strtolower", $has_one);
		$has_one = ArrayLib::map_key("strtolower", $has_one);

		return $has_one;
	}
	
	/**
	 * gets has_many
	 *
	 *@access public
	*/
	public function generateHas_many() {
		
		$has_many = (array) self::getStatic($this->classname, "has_many");
		foreach($this->LocalcallExtending("Has_Many") as $has_manys) {
			$has_many = array_merge($has_many, $has_manys);
			unset($has_manys);
		}
		$parent = get_parent_class($this);
		if($parent != "DataObject") {
			$has_many = array_merge(Object::instance($parent)->generateHas_many(), $has_many);
		}
		
		$has_many = array_map("strtolower", $has_many);
		$has_many = ArrayLib::map_key("strtolower", $has_many);
		return $has_many;
	}
	
	/**
	 * gets many_many
	 *
	 *@name many_many
	 *@access public
	*/
	public function generateMany_many($parents = true) {
		$many_many = (array) self::getStatic($this->classname, "many_many");
		foreach($this->LocalcallExtending("many_many") as $many_manys) {
			$many_many = array_merge($many_many, $many_manys);
			unset($many_manys);
		}
		$parent = get_parent_class($this);
		if($parents === true && $parent != "DataObject") {
			$many_many = array_merge(Object::instance($parent)->generateMany_many(), $many_many);
		}
		
		$many_many = array_map("strtolower", $many_many);
		$many_many = ArrayLib::map_key("strtolower", $many_many);
		return $many_many;
	}
	
	/**
	 * gets belongs_many_many
	 *
	 *@name belongs_many_many
	 *@access public
	*/
	public function generateBelongs_many_many($parents = true) {
		$belongs_many_many = (array) self::getStatic($this->classname, "belongs_many_many");
		foreach($this->LocalcallExtending("belongs_many_many") as $belongs_many_manys) {
			$belongs_many_many = array_merge($belongs_many_many, $belongs_many_manys);
			unset($belongs_many_manys);
		}
		$parent = get_parent_class($this);
		if($parents === true && $parent != "DataObject") {
			$belongs_many_many = array_merge(Object::instance($parent)->generateBelongs_Many_many(), $belongs_many_many);
		}
		
		$belongs_many_many = array_map("strtolower", $belongs_many_many);
		$belongs_many_many = ArrayLib::map_key("strtolower", $belongs_many_many);
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
			} else if(self::isStatic($this->classname, "many_many_extra_fields")) {
				$extraFields = self::getStatic($this->class, "many_many_extra_fields");
				if(isset($extraFields[$key]))
					$extraFields = $extraFields[$key];
				else
					$extraFields = array();
			} else {
				$extraFields = array();
			}
			$extendExtraFields = $this->localCallExtending("many_many_extra_fields");
			if(isset($extendExtraFields[$key])) {
				$extraFields = array_merge($extraFields, $extendExtraFields);
			}
			
			if(class_exists($value)) {
				
				$table = "many_many_".strtolower(get_class($this))."_".  $key . '_' . $value;
				if(!SQL::getFieldsOfTable($table)) {
					$table = "many_".strtolower(get_class($this))."_".  $key;
				}
			
				$tables[$key] = array(
					"table"			=> $table,
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
							if(isset($relations[$relation]) && $relations[$relation] == $this->classname) {
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
					$table = "many_many_".$value."_".  $relation . '_' . strtolower(get_class($this));
					if(!SQL::getFieldsOfTable($table))
						$table = "many_" . $value . "_" . $relation;
					
					
					$tables[$key] = array(
						"table"			=> $table,
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
		$indexes = array();
		
		if(self::hasStatic($this->classname, "index"))
			$indexes = (array) self::getStatic($this->classname, "index");
		
		if(isset($this->indexes)) {
			$indexes = $this->indexes;
			Core::deprecate("2.0", "Class ".$this->classname." uses old indexes-Attribute, use static \$index instead.");
		}
		
		foreach($this->generateHas_one(false) as $key => $value) {
			if(!isset($indexes[$key . "id"])) {
				$indexes[$key . "id"] = "INDEX";
				unset($key, $value);
			}
		}
		
		$searchable_fields = Object::hasStatic($this->classname, "search_fields") ? Object::getStatic($this->classname, "search_fields") : array();
		if(isset($this->searchable_fields))
			$searchable_fields = array_merge($searchable_fields, $this->searchable_fields);
		
		
		if($searchable_fields)
			// we add an index for fast searching
			$indexes["searchable_fields"] = array("type" => "INDEX", "fields" => implode(",", $searchable_fields), "name" => "searchable_fields");
		
		// validate
		foreach($indexes as $name => $type) {
			if(is_array($type)) {
				if(isset($type["type"], $type["fields"])) {
					// okay
				} else {
					throwError(6, "Invalid Index", "Index ".$name." in DataObject ".$this->classname." invalid. Type and Fields required!");
				}
			}
		}
		
		$db = $this->generateDBFields(false);
		if(isset($db["last_modified"]))
			$indexes["last_modified"] = "INDEX";
		
		return $indexes;

	}
	
	/**
	 * generates casting
	 *
	 *@name generateCasting
	 *@access public
	*/
	public function generateCasting() {
		$casting = array_merge((array) self::getStatic($this->classname, "casting"), (array) $this->generateDBFields());
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
		 * gets DBFields
		*/
		public function DBFields() {
			return (self::hasStatic($this->classname, "db")) ? self::getStatic($this->classname, "db") : (isset($this->db_fields) ? $this->db_fields : array());
		}
		/**
		 * gets has_one
		*/ 
		public function has_one() {
			return (self::hasStatic($this->classname, "has_one")) ? self::getStatic($this->classname, "has_one") : (isset($this->has_one) ? $this->has_one : array());
		}
		/**
		 * gets has_many
		*/ 
		public function has_many() {
			return (self::hasStatic($this->classname, "has_many")) ? self::getStatic($this->classname, "has_many") : (isset($this->has_many) ? $this->has_many : array());
		}
		/**
		 * many-many
		*/
		public function many_many() {
			return (self::hasStatic($this->classname, "many_many")) ? self::getStatic($this->classname, "many_many") : (isset($this->many_many) ? $this->many_many : array());
		}
		public function belongs_many_many() {
			return (self::hasStatic($this->classname, "belongs_many_many")) ? self::getStatic($this->classname, "belongs_many_many") : (isset($this->belongs_many_many) ? $this->belongs_many_many : array());
		}
		/**
		 * defaults
		 *
		 *@name defaults
		 *@access public
		*/
		public function defaults() {
			return (self::hasStatic($this->classname, "default")) ? self::getStatic($this->classname, "default") : (isset($this->defaults) ? $this->defaults : array());
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
		
		/**
		 * defaults
		 *
		 *@name defaults
		 *@access public
		*/
		public function generateDefaults() {		
			return array();
		}
}