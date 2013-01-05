<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 15.11.2012
  * $Version 2.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


/**
 * define some static experssions for your SQL-Lanuage
*/

class SQL extends object
{
		/**
		 * this var contains the last query, for debug
		 *
		 *@name last_query
		 *@access public
		*/
		public static $last_query;
		public static $error;
		public static $errno;
		/**
		 *@access public
		 *@var numeric
		 *@use: count queries
		**/
		static $queries = 0;
		
		/**
		 * factory - selects the sql-driver
		 *@name factory
		 *@param string - name of driver
		 *@access public
		 *@return object
		*/
		static public function factory($name)
		{
				if(file_exists(dirname(__FILE__) . '/driver/' . $name . ".php"))
				{
						require_once(dirname(__FILE__) . '/driver/' . $name . ".php");
						$class_name = $name . "Driver";
						define("SQL_LOADUP", true);
						define("SQL_INIT", true);
						return new $class_name;
				} else
				{
						die('Could not load SQL-Driver');
				}
		}
		/**
		 * inits the db with default settings
		*/
		public static function Init() {
			new SQL();
			
		}
		
		/**
		 * this var contains the current driver
		 *@name driver
		 *@access public
		 *@var object
		*/
		public static $driver = false;
		
		/**
		 *@access public
		 *@use: connect to db
		**/
		public function __construct($driver = null)
		{
				parent::__construct();
				
				/* --- */
				
				if(!isset($driver)) {
					if(defined("SQL_DRIVER_OVERRIDE")) {
						$driver = SQL_DRIVER_OVERRIDE;
					} else {
						$driver = SQL_DRIVER;
					}
				}
				
				if($driver == "mysql")
					$driver = "mysqli";
				elseif($driver == "postgres")
					$driver = "pgsql";
				
				self::$driver = self::factory($driver);
		}
		
		/**
		 *@access public
		 *@use: connect to db
		**/
		static function connect($dbuser, $dbdb, $dbpass, $dbhost)
		{
				$return = self::$driver->connect($dbuser, $dbdb, $dbpass, $dbhost);
				sql::setCharsetUTF8();
				return $return;
		}
		
		/**
		 * tests the connection
		 *@name test
		*/
		static function test($driver,$dbuser, $dbdb, $dbpass, $dbhost)
		{
				if($driver == "mysql")
					$driver = "mysqli";
				
				if(file_exists(dirname(__FILE__) . '/driver/' . $driver . ".php"))
				{
					require_once(dirname(__FILE__) . '/driver/' . $driver . ".php");
					return call_user_func_array(array($driver . "Driver", "test"), array($dbuser, $dbdb, $dbpass, $dbhost));
				} else {
					return false;
				}
		}
		
		/**
		 *@access public
		 *@use: run a query
		**/
		static function query($sql, $unbuffered = false, $track = true) {
				$start = microtime(true);
				
				//$_sql = str_replace(array("\n","\r\n", "\r", "\n\r", "\t"),' ',$sql) . "\n\n\n\n";
				//logging($_sql);
				
				if($track)
					self::$last_query = str_replace(array("\n","\r\n", "\r", "\n\r", "\t"),' ',$sql);
				
				if(PROFILE) Profiler::mark("sql::query");
				self::$queries++; // count queries and make it 1 more
				$result = self::$driver->query($sql, $unbuffered);
				if(PROFILE) Profiler::unmark("sql::query");
				
				
				$time = (microtime(true) - $start) * 1000;
				//echo  $time . "\n\n";
				
				if(defined("SLOW_QUERY") && SLOW_QUERY != -1 && $time > SLOW_QUERY) {
					slow_query_log("Slow SQL-Query: ".$sql." (".$time."ms)");
				}
			
				
				if(!$result && $track) {
					self::$error = self::$driver->error();
					self::$errno = self::$driver->errno();
				}
				return $result;
		}
		
		/*
		 *@access public
		 *@use: show counter
		*/
		static function viewcount()
		{
				return self::$qcounter;
		}
		
		/**
		 *@access public
		 *@use: fetch_row
		*/
		static function fetch_row($result)
		{
				return self::$driver->fetch_row($result);
		}
		
		/**
		 *@access public
		 *@use to diconnect
		**/
		static function close()
		{
				return self::$driver->close();
		}
		
		/**
		 *@access public
		 *@use to fetch object
		**/
		static function fetch_object($result)
		{
				return self::$driver->fetch_object($result);
		}
		
		/**
		 *@access public
		 *@use to fetch array
		 */
		static function fetch_array($result)
		{
				return self::$driver->fetch_array($result);
		}
		
		/**
		 *@access public
		 *@use to fetch assoc
		 */
		static function fetch_assoc($result)
		{
				return self::$driver->fetch_assoc($result);
		}
		
		/**
		  *@access public
		  *@use to fetch num rows
		  */
		static function num_rows($result)
		{
				return self::$driver->num_rows($result);
		}
		
		/**
		  *@access public
		  *@use to fetch error
		  */
		static function error()
		{
				return self::$error;
		}
		
		/**
		  *@access public
		  *@use to fetch errno
		  */
		static function errno()
		{
				return self::$errno;
		}
		
		/**
		*@access public
		*@use to fetch insert id
		*/
		static function insert_id()
		{
				return self::$driver->insert_id();
		}
		
		/**
		  *@access public
		  *@use to get memory
		  */
		static function free_result($result)
		{
				return self::$driver->free_result($result);
		}
		
		/**
		*@access public
		*@use to protect
		*/
		static function escape_string($result)
		{
				return self::$driver->escape_string($result);
		}
		
		/**
		  *@access public
		  *@use to protect
		  */
		static function real_escape_string($result)
		{
				return self::$driver->real_escape_string($result);
		}
		
		/**
		  *@access public
		  *@use to protect
		  */
		static function protect($result)
		{
				return self::$driver->protect($result);
		}
		
		/**
		 * returns affected rows after update or delete
		 *
		 *@name affected_rows
		 *@access public
		*/
		static function affected_rows() {
			return self::$driver->affected_rows();
		}
		
		/**
		  *@access public
		  *@use to split queries
		  */
		static function split($sql)
		{
				return preg_split('/;\s*\n/',$sql, -1 , PREG_SPLIT_NO_EMPTY);
		}
		
		/**
		  *@access public
		  *@use to view tables
		  */
		static function list_tables($db)
		{
				return self::$driver->list_tables($db);
		}
		
		/**
		  *@access public
		  *@use to view tablename
	  	  */
		static function tablename($res, $i)
		{
				return self::$driver->tablename($res, $i);
		}
		
		/**
		 * table-functions
		*/
		static function getFieldsOfTable($table, $prefix = false, $track = true)
		{
				return self::$driver->getFieldsOfTable($table, $prefix, $track);
		}
		static function changeField($table, $field, $type, $prefix = false)
		{
				return self::$driver->changeField($table, $field, $type, $prefix);
		}
		static function addField($table, $field, $type, $prefix = false)
		{
				return self::$driver->addField($table, $field, $type, $prefix);
		}
		static function dropField($table, $field, $prefix = false)
		{
				return self::$driver->dropField($table, $field, $prefix );
		}
		static function createTable($table, $fields, $prefix = false)
		{
				return self::$driver->createTable($table, $fields, $prefix);
		}
		static function _createTable($table, $fields, $prefix = false)
		{
				return self::$driver->_createTable($table, $fields, $prefix);
		}
		
		/**
		 * INDEX-functions
		*/
		static function addIndex($table, $field, $type,$name = null ,$db_prefix = null)
		{
				return self::$driver->addIndex($table, $field, $type,$name ,$db_prefix);
		}
		static function dropIndex($table, $name, $db_prefix = null)
		{
				return self::$driver->dropIndex($table, $name, $db_prefix);
		}
		static function getIndexes($table, $db_prefix = null)
		{
				return self::$driver->getIndexes($table, $db_prefix );
		}
		
		/**
		 * writes the manipulation-array in the database
		 * e.g. array(
		 *		"pages"	=> array(
		 *			"id"		=> "1",
		 *			"command"	=> "UPDATE",
		 * 			"fields"	=> array(
		 *				"path"	=> "neu"
		 *			)
		 * 		)
		 * )
		 *@name writeManipulation
		 *@access public
		 *@param array - manipulation
		*/
		static function writeManipulation($ma)
		{
				return self::$driver->writeManipulation($ma);
		}
		
		/**
		 * the same like writeManipulation
		 *@name manipulate
		 *@access public
		*/
		static function manipulate($ma)
		{
				return self::writeManipulation($ma);
		}
		
		/**
		 * table-functions V2
		*/
		
		/**
		 * gets much information about a table, e.g. field-names, default-values, field-types
		 *
		 *@name showTableDetails
		 *@access public
		 *@param string - table
		 *@param bool - if track query
		 *@param string - prefix
		*/
		static function showTableDetails($table, $track = true, $prefix = false) {
			return self::$driver->showTableDetails($table, $track, $prefix);
		}
		
		/**
		 * requires, that a table is exactly in this form
		 *
		 *@name requireTable
		 *@access public
		 *@param string - table
		 *@param array - fields
		 *@param array - indexes
		 *@param array - defaults
		 *@param string - prefix
		*/
		static function requireTable($table, $fields, $indexes, $defaults, $prefix = false) {
			return self::$driver->requireTable($table, $fields, $indexes, $defaults, $prefix);
		}
		
		/**
		 * deletes a table
		 *
		 *@name dontRequireTable
		 *@access public
		 *@param string - table
		 *@param string - prefix
		*/
		static function dontRequireTable($table, $prefix = false) {
			return self::$driver->dontRequireTable($table, $prefix);
		}
		
		
		/**
		 * sets the charset UTF-8
		 *
		 *@name setCharsetUTF8
		*/
		static function setCharsetUTF8() {
			return self::$driver->setCharsetUTF8();
		}
		
		/**
		 * sets the default sort
		 *
		 *@name setDefaultSort
		 *@access public
		 *@param string - table
		 *@param string - field
		 *@param string - type
		 *@param bool|string - prefix
		*/
		static function setDefaultSort($table, $field, $type = "ASC", $prefix = false) {
			return self::$driver->setDefaultSort($table, $field, $type, $prefix);
		}
		
		/**
		 * extract to where
		 *
		 *@name extractToWhere
		 *@access public
		 *@param array - where
		 *@param bool - if to include the WHERE
		 *@param array - to set field tables if you have various multi-table-fields
		*/
		static function extractToWhere($where, $includeWhere = true, $DBFields = array()) {
			// WHERE
			$sql = "";
			if(is_array($where) && count($where) > 0) {
				$i = 0;
				$a = 0; // check for multiple AND, OR, and so on in a row
				foreach($where as $field => $value) {
					
					if($i == 0) {
						$i++;
						if($includeWhere) {
							$sql .= " WHERE ";
							$includeWhere = false;
						}
					} else if($a == 0) {
						if(_ereg('^[0-9]+$', $field) && ($value == "OR" || $value == "||")) {
							$a++;
							$sql .= " OR ";
							continue;
						} else {
							$a++;
							$sql .= " AND ";
						}
					}
					
					$a = 0;
					$field = trim($field);
					if(_ereg('^[0-9]+$', $field)) {
						if(is_array($value)) {
							$sql .= " ( ".self::extractToWhere($value, false, $DBFields)." ) ";
						} else if(is_string($value)) {
							$sql .= " ( " . $value . " ) ";
						}
						continue;
					}
					
					
					
						
					
					if(isset($DBFields[$field])) {
						$field = $DBFields[$field] . "." . $field;
					}
					
					if(is_array($value) && count($value) == 2 && isset($value[1], $value[0]) && ($value[0] == "LIKE" || $value[0] == ">" || $value[0] == "!=" || $value[0] == "<")) {
						if($value[0] == "LIKE") {
							$sql .= ' '.convert::raw2sql($field).' '.(defined("SQL_LIKE") ? SQL_LIKE : "LIKE").' "'.convert::raw2sql($value[1]).'"';
						} else {
							$sql .= ' '.convert::raw2sql($field).' '.$value[0].' "'.convert::raw2sql($value[1]).'"';
						}
						
					} else if(is_array($value)) {
						$sql .= ' '.convert::raw2sql($field).' IN ("'.implode('","', array_map(array("convert", "raw2sql"), $value)).'")';
					} else {
						$sql .= ' '.convert::raw2sql($field).' = "'.convert::raw2sql($value).'"';
					}
					$sql .= "";
				}
			} else if(is_string($this->where)) {
				if($includeWhere)
					$sql .= " WHERE ";
				
				$sql .= $this->where;
			}
			return $sql;
		}
}

/**
 * interface for all SQL-Drivers
 *
 *@name SQLDriver
*/
interface SQLDriver
{
		/**
		 *@name __construct
		 *@access public
		*/
		public function __construct();
		/**
		 * connects to db
		 *@name connect
		 *@param username
		 *@param databasename
		 *@param password
		 *@param hostname
		*/
		public function connect($dbuser, $dbdb, $dbpass, $host);
		/**
		 * runs a query
		 *@name query
		 *@access public
		 *@param string - query
		 *@param bool - if unbuffered
		*/
		public function query($sql, $unbuffered = false);
		/**
		 * the following functions are simly sql-functions
		*/
		public function fetch_row($result);
		public function close();
		public function fetch_object($result);
		public function fetch_array($result);
		public function fetch_assoc($result);
		public function num_rows($result);
		public function error();
		public function errno();
		public function insert_id();
		public function free_result($result);
		public function escape_string($str);
		public function real_escape_string($str);
		public function affected_rows();
		public function protect($str);
		/**
		 * splits more than one query at the ;
		 *@name split
		 *@access public
		 *@param string - queries
		*/
		public function split($sql);
		public function list_tables($database);
		/**
		 * table-functions
		*/
		public function getFieldsOfTable($table, $prefix = false, $track = true);
		
		// DEPRECATED!!
		public function changeField($table, $field, $type, $prefix = false);
		// DEPRECATED!!
		public function addField($table, $field, $type, $prefix = false);
		// DEPRECATED!!
		public function dropField($table, $field, $prefix = false);
		// DEPRECATED!!
		function createTable($table, $fields, $prefix = false);
		// DEPRECATED!!
		function _createTable($table, $fields, $prefix = false);
		
		/**
		 * table-functions V2
		*/
		public function showTableDetails($table, $track = true, $prefix = false);
		public function requireTable($table, $fields, $indexes, $defaults, $prefix = false);
		public function dontRequireTable($table, $prefix = false);
		
		public function setDefaultSort($table, $field, $type = "ASC", $prefix = false);
		
		/**
		 * INDEX-functions
		*/
		public function addIndex($table, $field, $type,$name = null ,$db_prefix = null);
		public function dropIndex($table, $name, $db_prefix = null);
		public function getIndexes($table, $db_prefix = null);
		public function writeManipulation($manipulation);
		
		public function setCharsetUTF8();
		
}

/**
 * logs slow queries
 *
 * this information may uploaded to the goma-server for debug-use
 *
 *@name slow_query_log
 *@access public
 *@param string - debug-string
*/
function slow_query_log($data) {
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/slow_queries/");
	$date_format = (defined("DATE_FORMAT")) ? DATE_FORMAT : "Y-m-d H:i:s";
	FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/slow_queries/".date("m-d-y"));
	$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/slow_queries/".date("m-d-y")."/" . date("H_i_s");
	$file = $folder . "-1.log";
	$i = 1;
	while(file_exists($folder. "-" . $i.".log")) {
		$i++;
		$file = $folder. "-" . $i.".log";
	}

	FileSystem::write($file,$data, null, 0777);
}
