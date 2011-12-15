<?php
/**
  * parent class of every text-transformer, e.g. bbcode
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 14.06.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

abstract class TextTransformer extends Object
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