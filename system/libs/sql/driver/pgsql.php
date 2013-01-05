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
			global $dbport;
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


	public function connect($dbhost, $dbport, $dbdb, $dbuser, $dbpass)
	{
		if(!is_int($dbport))
			return false;
			
		$conn_string = "host=".$dbhost . " port=".$dbport." dbname=".$dbdb." user=".$dbuser." password=".$dbpass;
		
		if(!pg_connect($conn_string))
		{
			die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/mysql_connect_error.html')));
		}
		self::setCharsetUTF8();
		unset $conn_string;
		return true;
	}
	
	
	public  function query($sql, $unbuffered = false)
	{
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
	}
		
	public  function errno()
	{
	}
	
	public  function insert_id()
	{
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
	
	public function affected_rows() 
	{
	
	}
	
	public  function list_tables($database)
	{
		$list = array();
		if($result = sql::query("SHOW TABLES FROM ".$database."")) {
			while($row = $this->fetch_array($result)) {
				$list[] = $row[0];
			}
		}
		return $list;
	}
	
	public function getFieldsOfTable($table, $prefix = false, $track = true)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
				
		
		$sql = "SHOW COLUMNS FROM ".$prefix.$table."";
		if($result = sql::query($sql, false, $track))
		{
			$fields = array();
			while($row = $this->fetch_object($result))
			{
					$fields[$row->Field] = $row->Type;
			}
			return $fields;
		} else
		{
			return false;
		}
	}
	
	public function changeField($table, $field, $type, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
	
		$sql = "ALTER TABLE ".$prefix.$table." MODIFY ".$field." ".$type." ";
		if(sql::query($sql))
		{
			return true;
		} else
		{
			return false;
		}
	}
	
	public function addField($table, $field, $type, $prefix = false)
	{
		if($prefix === false)
			$prefix = DB_PREFIX;
		
		
		$sql = "ALTER TABLE ".$prefix.$table." ADD ".$field." ".$type." NOT NULL";
		if(sql::query($sql))
		{
			return true;
		} else
		{
			return false;
		}
	}
	
	
	
	
	
	
	public function setCharsetUTF8()
	{
		return pg_set_client_encoding("utf8");
	}


				
}
 ?>
