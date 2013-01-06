<?php

/**
 * 	PostreSQL database driver
 * 	5.1.13	v0.1 Beta
 * 
 * @package goma framework
 * @link http://goma-cms.org
 * @license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
 * @Copyright (C) 2009 - 2012  Goma-Team
 **/
 

class pgsqlDriver extends object implements SQLDriver 
{	
	public function __construct()
	{
		parent::__construct();
		
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
			die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/mysql_connect_error.html')));
		}
		self::setCharsetUTF8();
		unset $conn_string;
		return true;
	}
	
	public function test($dbuser, $dbdb, $dbpass, $dbhost)
	{
		return pg_ping(generate_connection_string($dbhost, $dbdb, $dbuser, $dbpass););
	}
	
	
	public  function query($sql, $unbuffered = false)
	{
		// some parse rules for postgresql
		
		str_replace($sql, "\"", "\'");
		str_replace($sql, "`", "");
		
		if($result = pg_query($sql))
			return $result;
		else {
			$trace = debug_backtrace();
			log_error('SQL-Error in Statement: '.$sql.' in '.$trace[1]["file"].' on line '.$trace[1]["line"].'.');
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
		 * Our PostgreSQL socket is connected to an database
		 * so we can't read out any other db without reconnecting
		 * this is an problem, so
		 * */
		 
		 // to be done !
		
		$list = array();
		if($result = sql::query("SELECT c.relname FROM pg_catalog.pg_class c LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE pg_catalog.pg_table_is_visible(c.oid) AND c.relkind = 'r' AND relname NOT LIKE 'pg_%' ORDER BY 1")) {
			while($row = $this->fetch_array($result)) {
				$list[] = $row[0];
			}
		}
		return $list;
	}
	
	public function getFieldsOfTable($table, $prefix = false, $track = true)
	{
		// to be done !
	}
	
	public function changeField($table, $field, $type, $prefix = false)
	{
		// possible solution: delete column and build a new one
	}
	
	public function addField($table, $field, $type, $prefix = false)
	{
		// note: the types in PGSQL and MySQL may differ
		
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
			
		// to be done
	}
	
	public function _createTable($table, $fields, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		// to be done
	}
	
	public function addIndex($table, $field, $type,$name = null ,$db_prefix = null)
	{
		// to be done
	}
	
	public function dropIndex($table, $name, $db_prefix = null)
	{
		// to be done
	}
	
	public function getIndexes($table, $db_prefix = null)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		// to be done
		
	}
	
	public function showTableDetails($table, $track = true, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		// to be done	
	}
	
	public function requireTable($table, $fields, $indexes, $defaults, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		// to be done	
	}
	
	public function setDefaultSort($table, $field, $type = "ASC", $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
			
		$sql = "";	// to be done
		
		if(SQL::Query($sql))
			return true;
		else
			throwErrorByID(3);
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
		// to be done 
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


				
}
 ?>
