<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 22.08.2010
  * $Version 2.0.0 - 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Convert extends Object
{
		/**
		 * converts raw-code to js
		 *@name raw2js
		 *@access public
		 *@param string - raw
		*/
		public static function raw2js($str)
		{
				$str = str_replace("\\","\\\\",$str);
				$str = str_replace("\"","\\\"",$str);
				$str = str_replace("\n",'\n',$str);
				$str = str_replace("\r",'\r',$str);
				$str = str_replace("\t",'\t',$str);
				$str = str_replace("\b",'\b',$str);
				$str = str_replace("\f",'\f',$str);
				$str = str_replace("/",'\/',$str);
				return $str;
		}
		/**
		 * converts raw to sql
		 *@name raw2sql
		 *@access public
		*/
		public static function raw2sql($str)
		{
				if(get_magic_quotes_gpc())
				{
						return sql::escape_string(stripslashes($str));
				}
				$str = sql::escape_string($str);
				return $str;
		}
		/**
		 * converts raw to text
		 *@name raw2text
		 *@access public
		*/
		public function raw2text($str)
		{
				return text::protect($str);
		}
}