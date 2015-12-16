<?php

/**
 * 	PostreSQL database driver
 * 	5.1.13	v0.1 Beta
 * 
 *  This driver should provide the same functions like the MySQL-Driver. 
 *  All function names and many parts are brazenly stolen from it.
 * 
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @Copyright (C) 2009 - 2012  Goma-Team
 **/
 

class pgsqlDriver implements SQLDriver
{	
	public function __construct()
	{
		define("pgsql_connected", false);
				
		/* --- */
		if(!defined("NO_AUTO_CONNECT")) 
		{
			global $dbhost;
			global $dbdb;
			global $dbuser;
			global $dbpass;
			
			if(!pgsql_connected)
			{
				if(!self::connect($dbuser, $dbdb, $dbpass, $dbhost))
				{
					define("pgsql_connected", true);
					return true;
				}
				else
				{
					die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
				}
				
			}
		}
	}



	public function connect($dbuser, $dbdb, $dbpass, $dbhost)
	{
		$conn_string = generate_connection_string($dbhost, $dbdb, $dbuser, $dbpass);
		
		if(!pg_connect($conn_string))
		{
			die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
		}
		self::setCharsetUTF8();
		unset($conn_string);
		return true;
	}


	
	public function test($dbuser, $dbdb, $dbpass, $dbhost)
	{
		return pg_ping(generate_connection_string($dbhost, $dbdb, $dbuser, $dbpass));
	}

	
	
	public  function query($sql, $unbuffered = false, $debug = true)
	{
		// some parse rules for postgresql
		
		str_replace($sql, "\"", "\'");
		str_replace($sql, "`", "");
		
		if($result = pg_query($sql))
			return $result;
		else {
			if($debug) {
				$trace = debug_backtrace();
				log_error('SQL-Error in Statement: '.$sql.' in '.$trace[1]["file"].' on line '.$trace[1]["line"].'.');
			}
			return false;
		}
	}
	
	
	
	public function fetch_row($result)
	{
		return pg_fetch_row($result);
	}
	
	
	
	public function close()
	{
		return pg_close();
	}
	
	
	
	public function fetch_object($result)
	{
		return pg_fetch_object($result);
	}
	
	
	
	public function fetch_array($result)
	{
		return pg_fetch_array($result);
	}
	
	
	
	public function fetch_assoc($result)
	{
		return pg_fetch_assoc($result);
	}
	
	
	
	public function num_rows($result)
	{
		return pg_num_rows($result);
	}
	
	
	
	public  function error()
	{	
		return pg_last_error();			
	}
	
	
		
	public  function errno()
	{
		return -1; // PostgreSQL does not provide any error code
	}
	
	
	
	public  function insert_id()
	{
		return -1; // to be done
	}
	
	
	
	public function free_result($result)
	{
		return pg_free_result($result);
	}
	
	
	
	public function escape_string($str)
	{
		if(is_array($str))
		{
				throwError(6, 'PHP-Error', 'Array is not allowed as given value for escape_string. Expected string.');
		}
		
		if(is_object($str))
		{
				throwError(6, 'PHP-Error', 'Object is not allowed as given value for escape_string. Expected string.');
		}
		
		return pg_escape_string((string)$str);
	}
	
	
	
	public function real_escape_string($str)
	{
		return self::escape_string($str);
	}
	
	
	
	public function protect($str)
	{
		return self::escape_string($str);
	}
	
	
	
	public function split($sql)
	{
		$queries = preg_split('/;\s*\n/',$sql, -1 , PREG_SPLIT_NO_EMPTY);
		return $queries;
	}
	
	
	
	public function affected_rows($result) 
	{
		return pg_affected_rows($result);
	}
	
	
	
	public  function list_tables($database)
	{
		/**
		 * The PostgreSQL socket is connected to an database
		 * so we can't read out any other db without reconnecting.
		 * This solution is based on reconnecting.
		 * */

		if(!$this->close())
			die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
			
		if(!$this->connect($dbuser, $database, $dbpass, $dbhost))
		{
			if(!$this->connect($dbuser, $dbdb, $dbpass, $dbhost))
				die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
			return false;
		}
		
		$list = array();
		if($result = sql::query("SELECT DISTINCT table_catalog FROM INFORMATION_SCHEMA.TABLES")) {
			while($row = $this->fetch_array($result)) {
				$list[] = $row[0];
			}
		}
		
		if(!$this->close())
			die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
		
		if(!$this->connect($dbuser, $dbdb, $dbpass, $dbhost))
				die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));

		return $list;
	}
	
	
	
	public function getFieldsOfTable($table, $prefix = false, $track = true)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='".$table."'";
		if($result = sql::query($sql, false, $track))
		{
			$fields = array();
			while($row = $this->fetch_object($result))
			{
					$fields[$row->Field] = $row->Type;
			}
			return $fields;
		} 
		else
		{
			return false;
		}
	}
	
	
	
	public function changeField($table, $field, $type, $prefix = false)
	{	
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		$sql = "SELECT ".$field." FROM ".$prefix.$table;
		$result = sql::query($sql);
		if(!$result)
			return false;
			
		$sql = "ALTER TABLE ".$prefix.$table." DROP COLUMN ".$field;
		if(!sql::query($sql))
			return false;
			
		sql::addField($table, $field, $type, $prefix);
		
		// to be done : check if we need to convert types
		
		$data = sql::fetch_array($result);
		$sql = "INSERT INTO ".$prefix.$table." ".$field. "some sql ".$data[$field];
		if(!sql::query($sql))
			return false;
			
		return true;
	}
	
	
	
	public function addField($table, $field, $type, $prefix = false)
	{
		$type = $this->parse_type($type);
		
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		$sql = "ALTER TABLE ".$prefix.$table." ADD COLUMN".$field." ".$type." NOT NULL";
		
		if(!sql::query($sql))
			return false;
		else
			return true;
	}
	
	
	
	public function dropField($table, $field, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		$sql = "ALTER TABLE " .$prefix . $table . " DROP COLUMN ".$field."";
		if(!sql::query($sql))
			return false;
		else
			return true;
	}
	
	
	
	public function createTable($table, $fields, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		$fields_ = "";
		$i = 0;
		foreach($fields as $key => $value)
		{
			if($i != 0)
			{
				$fields_ .= ",\n";
					
			} 
			else
			{
				$i = 1;
			}
			$fields_ .= "".$key." ".$value." NOT NULL ";
		}
		
		$sql = "CREATE TABLE " . $prefix . $table." 
				(
					".$fields_."
				);";
							
		if(sql::query($sql))
			return true;
		else
			return false;
	}
	
	
	
	public function _createTable($table, $fields, $prefix = false)
	{
		return createTable($table, $fields, $prefix);
	}
	
	
	
	public function addIndex($table, $field, $type,$name = null ,$db_prefix = null)
	{
		$type = $this->parse_type($type);
		
		if($prefix === false)
			$prefix = DB_PREFIX;
			
			
		if(is_array($field))
			$field = implode(',', $field);
	 /* *
		* else
		* {
		* 	$field = $field; // ????
		* }
		* */
		
		$name = ($name === null) ? "" : $name;
		
		$sql = "CREATE ".$type." INDEX ".$name." ON ".$table." (".$field.")";
			
		if(sql::query($sql))
			return true;
		else
			throw new SQLException();
	}
	
	
	
	public function dropIndex($table, $name, $db_prefix = null)
	{
		if($db_prefix === null)
			$db_prefix = DB_PREFIX;
	
		$sql = "DROP INDEX ".$name;
		
		if(sql::query($sql))
			return true;
		else
			throw new SQLException();
	}
	
	
	
	public function getIndexes($table, $db_prefix = null)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		if(!$this->close())
			die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
			
		if(!$this->connect($dbuser, $database, $dbpass, $dbhost))
		{
			if(!$this->connect($dbuser, $dbdb, $dbpass, $dbhost))
				die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
			return false;
		}	
			
		$sql = "SELECT i.relname AS index_name FROM pg_class t, pg_class i, pg_index ix,pg_attribute a  WHERE t.oid = ix.indrelid
				AND i.oid = ix.indexrelid AND a.attrelid = t.oid AND a.attnum = ANY(ix.indkey) AND t.relkind = 'r' AND t.relname like 'test%'
				ORDER BY t.relname, i.relname";
				
		// to be done : check if this function is portable to postgresql
		
		
	}
	
	
	
	public function showTableDetails($table, $track = true, $prefix = false)
	{
		// note : PostgreSQL does not provide any other feature than type ...
		
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		if($result = getFieldsOfTable($table, $prefix, $track))
		{
			$fields = array();
			$max = pg_num_fields($result);
			
			for($i = 0; $i < $max; $i++)
			{
				$fields[pg_field_name($result, $i)] = array(
						"type" 		=> pg_field_type($result, $i),
						"key"		=> NULL,
						"default"	=> NULL,
						"extra"		=> NULL
					);
				 
			}
			return $fields;
		}
		else
		{
			return false;
		}
	}
	
	
	
	public function requireTable($table, $fields, $indexes, $defaults, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		$log = "";
		
		if($data = $this->showTableDetails($table, true, $prefix)) 
		{
			$editsql = 'ALTER TABLE '.$prefix . $table .' ';
			foreach($fields as $name => $type)
			{
				if($name == "id")
					continue;
				
				if(!isset($data[$name])) 
				{
					$editsql .= ' ADD COLUMN '.$name.' '.$type.' ';
					if(isset($defaults[$name])) 
						$editsql .= ' DEFAULT "'.addslashes($defaults[$name]).'"';
						
					$editsql .= " NOT NULL,";
					$log .= "ADD Field ".$name." ".$type."\n";
				}
				else
				{
					// correct fields with edited type or default-value
					if(str_replace('"', "'", $data[$name]["type"]) != $type && str_replace("'", '"', $data[$name]["type"]) != $type) 
					{
						$editsql .= " MODIFY ".$name." ".$type.",";
						$log .= "Modify Field ".$name." to ".$type."\n";
					}
					
					if(!preg_match('/enum/i', $fields[$name]))
					{
						if(!isset($defaults[$name]) && $data[$name]["default"] != "") 
						{
							$editsql .= " ALTER COLUMN ".$name." DROP DEFAULT,";
						}
						
						if(isset($defaults[$name]) && $data[$name]["default"] != $defaults[$name]) 
						{
							$editsql .= " ALTER COLUMN ".$name." SET DEFAULT \"".addslashes($defaults[$name])."\",";
						}
					}	
				}
			}
			
			// get fields too much
			foreach($data as $name => $_data) 
			{
				if($name != "id" && !isset($fields[$name])) 
				{
					// patch
					if($name == "default") $name = '`default`';
					if($name == "read") $name = '`read`';
					$editsql .= ' DROP COLUMN '.$name.',';
					$log .= "Drop Field ".$name."\n";
				}
			}
			
			// @todo indexes
			
			$currentindexes = $this->getIndexes($table, $prefix);
			$allowed_indexes = array(); // for later delete
			
			// sort sql, so first drop and then add
			$removeindexsql = "";
			$addindexsql = "";
				
			// check indexes
			foreach($indexes as $key => $data) 
			{
				if(!$data)
					continue;
				
				if(is_array($data)) 
				{
					$name = $data["name"];
					$ifields = $data["fields"];
					$type = $data["type"];
				}
				else if(preg_match("/\(/", $data))
				{
					$name = $key;
					$allowed_indexes[$name] = true;
					if(isset($currentindexes[$key])) 
					{
						$removeindexsql .= " DROP INDEX ".$key.",";
					}
					$addindexsql .= " ADD ".$data . ",";
					continue;
				} 
				else 
				{
					$name = $key;
					$ifields = array($key);
					$type = $data;
				}
				
				$allowed_indexes[$name] = true;
				switch(strtolower($type)) 
				{
					case "unique":
						$type = "UNIQUE";
					break;
					case "fulltext":
						$type = "FULLTEXT";
					break;
					case "index":
						$type = "INDEX";
					break;
				}	
				
				if(!isset($currentindexes[$name])) 
				{ // we have to create the index
					$addindexsql .= " ADD ".$type." ".$name . " (".implode(",", $ifields)."),";
					$log .= "Add Index ".$name."\n";
				} 
				else 
				{
					// create matchable fields
					$mfields = array();
					foreach($ifields as $key => $value) {
					$mfields[$key] = preg_replace('/\((.*)\)/', "", $value);
				}
										
				if($currentindexes[$name]["type"] != $type || $currentindexes[$name]["fields"] != $mfields) 
				{
					$removeindexsql .= " DROP INDEX ".$name.",";
					$addindexsql .= " ADD ".$type." ".$name . "  (".implode(",", $ifields)."),";
					$log .= "Change Index ".$name."\n";
				}
				
				unset($mfields, $ifields);
				}
			}
			
			// check not longer needed indexes
			foreach($currentindexes as $name => $data) 
			{
				if($data["type"] != "PRIMARY" && !isset($allowed_indexes[$name])) 
				{
					// sry, it's a hack for older versions
					if($name == "show") $name = '`'.$name.'`';
					$removeindexsql .= " DROP INDEX ".$name.", ";
					$log .= "Drop Index ".$name."\n";
				}
			}
			
			// add sql
			$editsql .= $removeindexsql;
			$editsql .= $addindexsql;
			unset($removeindexsql, $addindexsql);
			
			// run query
			$editsql = trim($editsql);
			
			if(substr($editsql, -1) == ",") 
				$editsql = substr($editsql, 0, -1);
			
			if(sql::query($editsql)) 
			{
				ClassInfo::$database[$table] = $fields;
				return $log;
			} 
			else
				throwError(3,'SQL-Error', "SQL-Query ".$editsql." failed");	
				
				
		} 
		else 
		{
			$sql = "CREATE TABLE ".$prefix . $table ." ( ";
			$i = 0;
			foreach($fields as $name => $value) 
			{
				if($i == 0) 
					$i++;
				else
					$sql .= ",";

				$sql .= ' '.$name.' '.$value.' ';
				if(isset($defaults[$name])) 
					$sql .= " DEFAULT '".addslashes($defaults[$name])."'";
			}
			
			foreach($indexes as $key => $data) 
			{
				if($i == 0) 
					$i++;
				else
					$sql .= ",";

				if(is_array($data)) 
				{
					$name = $data["name"];
					$type = $data["type"];
					$ifields = $data["fields"];
				}
				else if(preg_match("/\(/", $data))
				{
					$sql .= $data;
					continue;
				} 
				else 
				{
					$name = $field = $key;
					$ifields = array($field);
					$type = $data;
				}
				
				switch(strtolower($type)) 
				{
					case "fulltext":
						$type = "FULLTEXT";
						break;
					case "unique":
						$type = "UNIQUE";
						break;
					case "index":
					default:
						$type = "INDEX";
					break;
				}
				
				$sql .= ''.$type.' '.$name.' ('.implode(',', $ifields).')';
			}
			
			// to be done : check wather this is working 
			
			$sql .= ") DEFAULT CHARACTER SET 'utf8'";
			$log .= $sql . "\n";
			
			if(sql::query($sql)) {
				ClassInfo::$database[$table] = $fields;
				return $log;
			} else {
				throw new SQLException();
			}
		}
			
	}
	
	
	
	public function setDefaultSort($table, $field, $type = "ASC", $prefix = false)
	{
		// not possible in postgresql
	}
	
	
	// deletes a table
	
	public function dontRequireTable($table, $prefix = false) 
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		if($data = $this->showTableDetails($table, true, $prefix)) {
			return sql::query('DROP TABLE '.$prefix . $table.'');
		}
		return true;
	}
	
	
	
	public function writeManipulation($manipulation)
	{
		if(PROFILE) Profiler::mark("PGSQL::writeManipulation");
		foreach($manipulation as $class => $data)
		{
			switch(strtolower($data["command"]))
			{	
				case "update":
					if(isset($data["id"]))
					{
						if(count($data["fields"]) > 0)
						{
							if ((isset($data["table_name"]) && $table_name = $data["table_name"]) || 
								(isset(classinfo::$class_info[$class]["table_name"]) && $table_name = classinfo::$class_info[$class]["table_name"]))
							{
								
								$sql = "UPDATE ".DB_PREFIX.$table_name." SET ";
								$i = 0;
								foreach($data["fields"] as $field => $value)
								{
									if($i == 0)
									{
											$i++;
									} else
									{
											$sql .= " , ";
									}
									$sql .= " ".$field." = '".convert::raw2sql($value)."' ";
										
								}
								unset($i);
								
								if(isset($data["id"])) 
								{
									$id = $data["id"];
									$sql .= " WHERE id = '".convert::raw2sql($id)."'";
								} 
								else if(isset($data["where"])) 
								{
									$where = $data["where"];
									$where = SQL::extractToWhere($where);
									$sql .= $where;
									unset($where);
								} 
								else 
								{
									return false;
								}
								
								if(SQL::query($sql))
								{
									unset($id);
									// everything is fine
								} else
								{
									throw new SQLException();
								}	
							}
						}
					}
				break;
					
				case "insert":
					if(count($data["fields"]) > 0)
					{
						if ((isset($data["table_name"]) && $table_name = $data["table_name"]) ||
							(isset(classinfo::$class_info[$class]["table_name"]) && $table_name = classinfo::$class_info[$class]["table_name"]))
						{
							$sql = 'INSERT INTO '.DB_PREFIX.$table_name.' ';
							$fields = ' (';
							$values = ' VALUES (';
							
							// multi data
							if(isset($data["fields"][0]))
							{
								$a = 0;
								foreach($data["fields"] as $fields_data)
								{
									if($a == 0) 
									{
										// do nothing, it will be done at the end, because we need it above
									} 
									else 
									{
										$values .= " ) , ( ";
									}
									
									$i = 0;
									
									foreach($fields_data as $field => $value) 
									{	
										if($i == 0)	
										{
											$i++;
										} 
										else 
										{
											if($a == 0) 
											{
												$fields .= ",";
											}
												
											$values .= ", ";
										}
										
										if($a == 0) 
										{
											$fields .= convert::raw2sql($field);
										}
										$values .= "'".convert::raw2sql($value)."'";
									}
									
									if($a == 0) 
									{
										$a++; // now we can edit it
									}
									
									unset($i);
								}
								unset($a, $field_data);
							}
							else // just one record
							{
								$i = 0;
								foreach($data["fields"] as $field => $value)
								{	
									if($i == 0)
									{
										$i++;
									} else
									{
										$fields .= ",";
										$values .= ",";
									}
									$fields .= convert::raw2sql($field);
									$values .= "'".convert::raw2sql($value)."'";
								}
								unset($i);
							}
							
							$fields .= ")";
							$values .= ")";
							$sql .= $fields . $values;
							if(sql::query($sql))
							{
								unset($fields, $values);
								// everything is fine
							} 
							else
							{
								throw new SQLException();
							}
						}	
					}
				break;
					
				case "delete":
					if(!isset($data["where"]) && isset($data["id"]))
						$data["where"]["id"] = $data["id"];
					
					if(isset($data["where"])) 
					{
						if ((isset($data["table_name"]) && $table_name = $data["table_name"]) ||
							(isset(ClassInfo::$class_info[$class]["table_name"]) && $table_name = classinfo::$class_info[$class]["table_name"]))
						{
							$where = $data["where"];
							$where = SQL::extractToWhere($where);
									
							$sql = "DELETE FROM ".DB_PREFIX . $table_name.$where;
									
							if(sql::query($sql)) {
								// everything is fine
							} else {
								throw new SQLException();
							}
						}
					}
				break;
				
				default:
					if(PROFILE) Profiler::unmark("PGSQL::writeManipulation");
					return false;
				break;
			}
		}
		
		if(PROFILE) Profiler::unmark("PGSQL::writeManipulation");
		return true;
	}
	
	
	// sets the client charset encoding to UTF8
	
	public function setCharsetUTF8()
	{
		return pg_set_client_encoding("utf8");
	}
	
	
	// generates a connection string used by the pg_connect function
	
	public function generate_connection_string($dbhost, $dbport, $dbdb, $dbuser, $dbpass)
	{
		$raw = split($dbhost, ':', 2);
		
		if(count($raw) != 2)
			return false;
			
		if(!is_int($raw[1]))
			return false;
			
		return "host=".$raw[0] . " port=".$raw[1]." dbname=".$dbdb." user=".$dbuser." password=".$dbpass;
	}
	
	
	
	public function init_types()
	{
		$types = array(
						"int" 		=> "integer",
						"float" 	=> "real",
						"longtext"	=> "text",
						"mediumtext"=> "text"
						);
	}
	
	/**
		 * storage engines.
	*/
	public function listStorageEngines() {
		return array();
	}

	public function setStorageEngine($table, $engine) {
		return false;
	}
	
	// parse function for mysql <-> pgsql type compatibility
	
	public function parse_type($type)
	{
		foreach($types as $regex => $rep)
		{
			if(lower($type) == $regex)
				return $rep;
		}
		return $type;
	}
}
