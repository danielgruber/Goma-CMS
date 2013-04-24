<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 09.12.2012
  * $Version: 2.0.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class SelectQuery extends Object
{
		/**
		 * own data
		 *
		 *@name data
		 *@access public
		*/
		public $data = array();
		
		/**
		 * this var contains the SQL-Stament
		 *@name SQL
		 *@access protected
		*/
		protected $sql = "";
		
		/**
		 * this var contains the WHERE-clause-Array
		 *@name filter
		 *@access public
		*/
		public $filter = array();
		
		/**
		 * this var contains the limit
		 * e.g. array(0, 1); or array(1);
		 *@name limit
		 *@access protected
		 *@var array
		*/
		protected $limit = array();
		
		/**
		 * this var contains the result after the query
		 *@name result
		 *@access public
		*/
		public $result;
		
		/**
		 * from-part
		 *@name from
		 *@access public
		 *@var array
		*/
		public $from = array();
		
		/**
		 * orderby
		 *@name orderby
		 *@access public
		 *@var array
		*/
		public $orderby = array();
		
		/**
		 * this var defines if this is a DISTINCT-Query
		 *@name distinct
		 *@var bool
		*/
		public $distinct = false;
		
		/**
		 * this var contains the HAVING-part as array
		 *
		 *@name having
		 *@access public
		 *@var array
		*/
		public $having = array();
		
		/**
		 * group by
		 *@name groupby
		 *@var string
		*/
		public $groupby = array();
		
		/**
		 * the fields 
		 *
		 *@name fields
		 *@access protected
		 *@var array
		*/
		public $fields = array();
		
		/**
		 * here you can define some db_fields, so for example if you want to define, that id is get from a specific table, define it here in the form:
		 * "id" => "myTable"
		 *
		 *@name db_fields
		 *@access public
		*/
		public $db_fields = array();
		
		/**
		 * __construct
		 *@name __consturct
		 *@param string - table
		 *@param array - fields
		 *@param array - where
		*/
		public function __construct($table = "", $fields = array(), $filter = array())
		{
				parent::__construct();
				
				if($table != "")
					$this->from($table);
				$this->fields = $fields;
				$this->filter = $filter;
				
		}
		
		/**
		 * this var adds a table to the from-part
		 *@name from
		 *@access public
		 *@param string
		*/
		public function from($str)
		{
				$this->from[str_replace(array('`', '"'), '', $str)] = DB_PREFIX  . $str . ' AS '.$str.'';
				return $this;
		}
		
		/**
		 * filters
		 *
		 *@name filter
		*/
		public function filter($filter) {
			if(!is_bool($filter))
				$this->filter = $filter;
		}
		
		/**
		 * adds a filter
		 *
		 *@name addFilter
		 *@access public
		*/
		public function addFilter($filter) {
			if(is_string($this->filter)) {
				$this->filter = array($this->filter, $filter);
			} else if(is_array($filter)) {
				foreach($filter as $k => $v) {
					if(is_int($k)) {
						$this->filter[] = $v;
					} else if(isset($this->filter[$k])) {
						if(is_array($this->filter[$k])) {
							$this->filter[$k] = array_intersect($this->filter[$k], $v);
						} else {
							$this->filter[$k] = array_merge((array) $this->filter[$k], (array) $v);
						}
					} else {
						$this->filter[$k] = $v;
					}
				}
			} else {
				$this->filter[] = $filter;
			}
		}
		
		/**
		 * removes filter
		 *
		 *@name removeFilter
		 *@access public
		*/
		public function removeFilter($filter) {
			if(isset($this->filter[$filter]))
				unset($this->filter[$filter]);
			else
			 	foreach($this->filter as $key => $value) {
			 		if(is_array($value)) {
			 			if(isset($value[$filter])) 
			 				unset($this->filter[$key][$filter]);
			 		}
			 		
			 		if($value == $filter) {
			 			unset($this->filter[$key]);
			 		}
			 	}
		}
		
		/**
		 * adds one rule to orderby
		 *@name sort
		 *@access public
		 *@param string - field
		 *@param string - type, default: ASC
		*/
		public function sort($field, $type = "ASC", $order = 0)
		{		
			if(is_array($field)) {
				if(isset($field["field"], $field["type"])) {
					$type = $field["type"];
					$field = $field["field"];
				} else {
					$field = array_values($field);
					if(isset($field[1])) {
						$type = $field[1];
					}
					$field = $field[0];
				}
			} else {
				if(_eregi('^(.*)\s*(asc|desc)$', $field, $matches)) {
					$field = $matches[1];
					$type = $matches[2];
					unset($matches);
				}
				$field = trim($field);
			}
				
			if($field == "")
				return $this;
			
			if(strtolower(trim($type)) == "desc") {
				$type = "DESC";
			} else {
				$type = "ASC";
			}
			
			$order = ($order == 0) ? count($this->orderby) : $order;
			while(isset($this->orderby[$order])) {
				$order++;
			}
			$this->orderby[$order] = array(
				$field,
				$type
			);
			
			return $this;
		}
		
		/**
		 * adds group-by
		 *
		 *@name groupby
		 *@access public
		 *@param string|array fields
		*/
		public function groupby($fields, $prepend = false) {
			if($prepend) {
				$this->groupby = array_merge((array)$fields, $this->groupby);
			} else {
				if(is_array($fields)) {
					$this->groupby = array_merge($this->groupby, $fields);
				} else if(!empty($fields)) {
					$this->groupby[] = $fields;
				}
			}
			
			return $this;
		}
		
		/**
		 * adds one to the having-part
		 *@name having
		 *@param string - clause
		 *@return object
		*/
		public function having($str)
		{
				$this->having[] = $str;
				return $this;
		}
		
		/**
		 * adds a field or more than one field as array to field-list
		 *
		 *@name fields
		 *@access public
		 *@param string|array - fields
		 *@return object
		*/
		public function fields($fields, $table = "")
		{
				if(is_array($fields))
				{
						if($table != "")
						{
								foreach($fields as $key => $field)
								{
										$fields[$key] = $table . "." . $field;
								}
						}
						$this->fields = array_merge($this->fields, $fields);
				} else
				{
						if(is_array($this->fields))
						{
								if($table != "")
								{
										$this->fields[] = $table . ".". $fields;
								} else
								{
										$this->fields[] = $fields;
								}
						} else
						{
								$this->fields .= "," . $fields;
						}
				}
				return $this;
		}
		
		/**
		 * sets the fields
		 *@name setFields
		 *@access public
		 *@param new value
		*/
		public function setFields($fields)
		{
				$this->fields = $fields;
		}
		
		/**
		 * adds an outer-join
		 *
		 *@name outerJoin
		 *@access public
		 *@param string - table
		 *@param string - statement after the ON
		 *@param string - alias: for joining the same table more than one time
		*/
		public function outerJoin($table, $statement, $alias = "")
		{
				$alias = ($alias == "") ? $table : $alias;
				$this->from[$alias] = array("table" => $table, "statement" => " OUTER JOIN ".DB_PREFIX.$table." AS ".$alias." ON ".$statement." ");
				return $this;
		}
		
		/**
		 * adds an inner-join
		 *
		 *@name innerJoin
		 *@access public
		 *@param string - table
		 *@param string - statement after the ON
		*/
		public function innerJoin($table, $statement, $alias = "")
		{
				$alias = ($alias == "") ? $table : $alias;
				$this->from[$alias] = array("table" => $table, "statement" => " INNER JOIN ".DB_PREFIX . $table." AS ".$alias." ON ".$statement." ");
				return $this;
		}
		
		/**
		 * adds an left-join
		 *
		 *@name leftJoin
		 *@access public
		 *@param string - table
		 *@param string - statement after the ON
		*/
		public function leftJoin($table, $statement, $alias = "")
		{
				$alias = ($alias == "") ? $table : $alias;
				$this->from[$alias] = array("table" => $table, "statement" => " LEFT JOIN ".DB_PREFIX . $table." AS ".$alias." ON ".$statement." ");
				return $this;
		}
		
		/**
		 * adds an right-join
		 *
		 *@name rightJoin
		 *@access public
		 *@param string - table
		 *@param string - statement after the ON
		*/
		public function rightJoin($table, $statement, $alias = "")
		{
				$alias = ($alias == "") ? $table : $alias;
				$this->from[$alias] = array("table" => $table, "statement" => " RIGHT JOIN ".DB_PREFIX . $table." AS ".$alias." ON ".$statement." ");
				return $this;
		}
		
		/**
		 * checks if joined
		 *
		 *@name isJoinedTo
		 *@access public
		 *@param string - table
		 *@return bool
		*/
		public function isJoinedTo($table)
		{
				return isset($this->form[$table]);
		}
		
		/**
		 * this is a cache for generating field-list and coliding fields
		 *@name new_field_cache
		 *@access protected
		*/
		protected static $new_field_cache = array();
		
		/**
		 * builds the SQL-Query
		 *
		 *@name build
		 *@access public
		 *@param string - override fields part
		*/
		public function build($fields = null) {
			
			if(PROFILE) Profiler::mark("SelectQuery::build");
			
			
			// first make a index of all fields and check for coliding fields
			// we cache this part, because we need this just one time foreach from-array
			$from = md5(var_export($this->from, true) . implode($this->db_fields));
			if(isset(self::$new_field_cache[$from])) {
				$colidingFields = self::$new_field_cache[$from]["coliding"];
				$DBFields = self::$new_field_cache[$from]["dbfields"];
			} else {
				$DBFields = $this->db_fields;
				$predefinedFields = $DBFields;
				$colidingFields = $this->db_fields;
				
				foreach($this->from as $alias => $statement) {
					if(is_array($statement)) {
						$table = $statement["table"];
						$statement = $statement["statement"];
					} else {
						$table = $alias;
					}
					
					$tablefields = array_keys(ClassInfo::getTableFields($table));
					foreach($tablefields as $field) {
						if(!isset($DBFields[$field])) {
							$DBFields[$field] = $alias;
						} else {
							if(isset($predefinedFields[$field])) {
								//$colidingFields[$field] = $predefinedFields[$field];
							} else {
								if(!isset($colidingFields[$field]))
									$colidingFields[$field] = array($DBFields[$field]);
								$colidingFields[$field][] = $alias;
							}
						}
					}
				}
				self::$new_field_cache[$from]["coliding"] = $colidingFields;
				self::$new_field_cache[$from]["dbfields"] = $DBFields;
				unset($alias, $statement, $tablefields, $field);
			}
			
			// begin SQL
			$sql = "SELECT ";
			
			if($this->distinct)
				$sql .= " DISTINCT ";
			
			// FIELDS
			
			if(!isset($fields))
				$fields = $this->fields;
			
			if(!is_string($fields) && (!is_array($fields) || count($fields) == 0)) {
				$fields = array("*");
			}
			
			if(is_array($fields) && count($fields) > 0) {
				
				if(in_array("*", $fields)) {
					$i = 0;
					
					// join all from-tables
					foreach($this->from as $alias => $statement) {
						if(_ereg("^[0-9]+$", $alias))
							continue;
						
						if($i == 0)
						{
								$i++;
						} else
						{
								$sql .= ", ";
						}
						if(!empty($alias)) {
							$sql .= " ".$alias.".*";
						}
					}
				}
				
				
				// some added caches ;)
				if(isset(self::$new_field_cache[$from]["colidingSQL"])) {
					if(strlen(trim(self::$new_field_cache[$from]["colidingSQL"])) > 0) {
						// comma
						if(isset($i)) {
							if($i != 0) {
								$sql .= ", ";
							}
						}
						
						$sql .= self::$new_field_cache[$from]["colidingSQL"];
					}
						
					// i
					if(isset($i)) {
						$i += self::$new_field_cache[$from]["colidingSQLi"];
					} else {
						$i = self::$new_field_cache[$from]["colidingSQLi"];
					}
					
				} else {	
					$colidingSQL = "";
					$a = 0;
					
					// fix coliding fields
					foreach($colidingFields as $field => $tables) {
						
						if($a == 0)
							$a++;
						else
							$colidingSQL .= ", ";
						
						if(is_string($tables)) {
							if(strpos($tables, ".")) {
								$colidingSQL .= $tables;
							} else {
								$colidingSQL .= $tables . "." . $field . " AS " . $field . " ";
							}
							continue;
						}
						
						$colidingSQL .= " CASE ";
						foreach($tables as $table) {
							
							$colidingSQL .= " WHEN ".$table.".".$field." IS NOT NULL THEN ".$table.".".$field." ";
						}
						$colidingSQL .= " ELSE NULL END AS ".$field."";
					}
					
					self::$new_field_cache[$from]["colidingSQL"] = $colidingSQL;
					self::$new_field_cache[$from]["colidingSQLi"] = $a;
					
					// comma
					if(isset($i) && $a != 0) {
						if($i != 0) {
							$sql .= ", ";
						}
					}
					$sql .= $colidingSQL;
					
					// i
					if(isset($i)) {
						$i += $a;
					} else {
						$i = $a;
					}
					unset($colidingSQL);
				}
				
				foreach($fields as $key => $field) {
					// some basic filter
					if(is_array($field))
						continue;
								
					$field = str_replace("`", "", $field);
					
					if($field == "*")
						continue;
					
					if($i == 0)
						$i++;
					else
						$sql .= ", ";
					
					/* --- */
					
					if(!_ereg('^[0-9]+$', $key)) {
						$alias = $key;
					} else {
						if(isset($DBFields[$field]) && !isset($colidingFields[$field])) {
							$alias = $field;
							$field = $DBFields[$field] . "." . $field;
						} else if(isset($colidingFields[$field])) {
							
							continue;
						} else {
							$sql .= " " . $field . "";
							continue;
						}
					}
					
					$sql .= " ".$field." AS ".$alias." ";
				}
				
				
			} else if(is_string($fields)) {
				$sql .= " ".$fields." ";
			}
			
			
			
			// FROM
				
			$sql .= " FROM ";
			
			$from = $this->from;
			
			// validate from
			foreach($from as $alias => $data) {
				if(is_array($data)) {
					$table = $data["table"];
					$data = $data["statement"];
					$from[$alias] = $data;
				} else {
					$table = $alias;
				}
					
				if(is_string($table) && !preg_match('/^[0-9]+$/', $table)) {
					if(!isset(ClassInfo::$database[$table])) {
						throwError(6, "SQL-Missing-Error", "Table ".$table." doesn't exist!");
					}
				}
			}
			
			$sql .= implode(" ", $from);
			
			// WHERE
			
			$sql .= SQL::extractToWhere($this->filter, true, $DBFields);
			
			
			//$sql .= $this->addWhere;
			// GROUP BY
			if(count($this->groupby) > 0)
			{
				$sql .= " GROUP BY ";
				$sql .= implode(",", $this->groupby);
			}
			
			
			ksort($this->orderby);
			// ORDER BY
			if(count($this->orderby) > 0)
			{
					$sql .= " ORDER BY ";
					$i = 0;
					foreach($this->orderby as $data) {
						if($i == 0)
							$i++;
						else
							$sql .= ",";
						if(isset($DBFields[$data[0]])) {
							$sql .= $DBFields[$data[0]] . "." . $data[0] . " " . $data[1];
						} else {
							$sql .= $data[0] . " " . $data[1];
						}
					}
			}
			
			// HAVING
			if(count($this->having) > 0)
			{
					$sql .= " HAVING ";
					$sql .= SQL::ExtractToWhere($this->having, false, $DBFields);
			}
			
			
			// LIMIT
			if(is_array($this->limit)) {
				if(count($this->limit) > 0 && !empty($this->limit))
				{
						$sql .= " LIMIT ";
						if(count($this->limit) == 2)
						{
							if($this->limit[0] < 0)
								$this->limit[0] = 0;
							
							if($this->limit[1] < 0)
								$this->limit[0] = 0;
														
							$sql .= " ".$this->limit[0].", ".$this->limit[1]."";
						} else
						{
							$sql .= $this->limit[0];
						}
				}
			} else if(!empty($this->limit))
				$sql .= " LIMIT " . $this->limit;
			
			
			unset($DBFields, $colidingFields);
			if(PROFILE) Profiler::unmark("SelectQuery::build");
				
			return $sql;
		}
		
		/**
		 * sets an limit
		 *@name limit
		 *@access public
		 *@param array - limitarr
		*/
		public function limit($limit)
		{
				$this->limit = $limit;
				return $this;
		}
		
		/**
		 * executes the query
		 *@name execute
		 *@access public
		*/
		public function execute($fields = null)
		{
		
				if($result = sql::query($this->build($fields)))
				{
						$this->result = $result;
						return $this;
				} else
				{
						throwErrorById(3);
				}
		}
		
		/**
		 * gets the result as object
		 *@name fetch_object
		 *@access public
		*/
		public function fetch_object()
		{
			return sql::fetch_object($this->result);
		}
		
		/**
		 * gets the result as array
		 *@name fetch_array
		 *@access public
		*/
		public function fetch_assoc()
		{
			return sql::fetch_assoc($this->result);
		}
		
		/**
		 * frees the result and query-cleanup
		 *
		 *@name free
		 *@access public
		*/
		public function free() {
			SQL::free_result($this->result);
			unset($this->result);
		}
}