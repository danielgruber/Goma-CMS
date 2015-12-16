<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 05.03.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Text extends gObject
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
				Core::deprecate(2.0, "Use convert::raw2text instead");
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
		public function __call($methodName, $arguments)
		{
				if(classinfo::exists($methodName) && is_subclass_of($methodName, "TextTransformer"))
				{
						$class = new $methodName($this->text);
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

abstract class TextTransformer extends gObject
{
		/**
		 * the text
		 *@name text
		 *@var string
		*/
		protected $text = "";
		
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
		 * transforms the text and gives back the result
		 *@name transform
		 *@access public
		*/
		public function transform()
		{
				
		}
		
}