<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 31.08.2011
  * $Version 2.0.0 - 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ArrayLib extends Object
{
		/**
		 * merge
		 *@name merge
		 *@param array1
		 *@param array2
		 *@return array
		*/
		public static function merge(array $array1, array $array2)
		{
				return array_merge($array1,$array2);
		}
		/**
		 * gets the first value of an array
		 *@name first
		 *@param array - array
		 *@access public
		 *@return mixed  - value
		*/
		public static function first($arr)
		{
				if(!is_array($arr)) 
						return false;
				
				if($arr) {
					$data = array_values($arr);
					return $data[0];
				}
				
				return false;
		}
		/**
		 * gets the first key of an array
		 *@name firstkey
		 *@param array - array
		 *@access public
		 *@return mixed  - key
		*/
		public static function firstkey($arr)
		{
				if(!is_array($arr)) 
						return false;
					
				if($arr) {
					$data = array_keys($arr);
					return $data[0];
				}
				
				return false;
		}
		/**
		 * sets key and value from value
		 *@name key_value
		 *@access public
		 *@param array
		*/
		public static function key_value($arr)
		{
				$array = array();
				if($arr)
				{
						foreach($arr as $value)
						{
								if(is_array($value)) {
									$value = arraylib::first($value);
								}
								$array[$value] = $value;
						}
				}
				return $array;
		}
		/**
		 * sets key and value from value where key is numeric
		 *@name key_value_for_id
		 *@access public
		 *@param array
		*/
		public static function key_value_for_id($arr)
		{
				$array = array();
				if($arr)
				{
						foreach($arr as $key => $value)
						{
								if(_ereg('^[0-9]+$', $key))
										$array[$value] = $value;
								else
										$array[$key] = $value;
						}
				}
				return $array;
		}
		/**
		 * array_map for keys
		 *
		 *@name map_key
		 *@access public
		*/
		public static function map_key($array, $callback) {
			if(is_string($array)) {
				$_callback = $array;
				$array = $callback;
				$callback = $_callback;
				unset($_callback);
			}
			$arr = array();
			foreach($array as $key => $value) {
				$arr[call_user_func_array($callback, array($key))] = $value;
			}
			return $arr;
		}
} 
