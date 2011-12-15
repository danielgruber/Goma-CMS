<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 27.06.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Text extends Object
{
		/**
		 * the text
		 *@name text
		 *@var string
		*/
		protected $text = "";
		
		/**
		* XSS protection
		*@name: protect
		*@param: string - text
		*@use: protect html entitites
		*@return the protected string
		*/
		public static function protect($str)
		{
				if(is_array($str))
				{
						$new = array();
						foreach($str as $key => $value)
						{
								// we do not convert objects
								if(is_string($value))
										$new[$key] = htmlentities($value, ENT_COMPAT , "UTF-8");
								else
										$new[$key] = $value;
						}
						return $new;
				} else
				{
						return htmlentities($str, ENT_COMPAT , "UTF-8");
				}
		}
		
		/**
		 *@name __construct
		 *@param string - text 
		 *@access public
		*/
		public function __construct($text)
		{
				$this->text = $text;
		}
		/**
		 * __call-overloading
		 *@name __call
		 *@access public
		*/
		public function __call($name, $arguments)
		{
				autoloader::load($name);
				if(classinfo::exists($name) && is_subclass_of($name, "TextTransformer"))
				{
						$class = new $name($this->text);
						$newtext = $class->transform();
						return $newtext;
				}
				
		}
		/**
		 * to use it as a string
		 *@name __toString
		 *@access public
		*/
		public function __toString()
		{
				return $this->text;
		}
}