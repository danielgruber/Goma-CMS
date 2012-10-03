<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 15.04.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)



class addcontent extends object
{
		/**
		 * the addcontent of the current session
		*/
		public static $addcontent;
		/**
		 * adds addcontent
		 *@name add
		 *@param string - content
		*/
		static public function add($content)
		{
				if(!isset($_SESSION['addcontent']))
				{
						$_SESSION['addcontent']	= "";
				}
				$_SESSION['addcontent'] .= $content;
		}
		/**
		 * adds addcontent
		 *@name add
		 *@param string - content
		*/
		static public function addSuccess($content)
		{
				if(!isset($_SESSION['addcontent']))
				{
						$_SESSION['addcontent']	= "";
				}
				$_SESSION['addcontent'] .= '<div class="success">' . $content . '</div>';
		}
		/**
		 * adds addcontent
		 *@name add
		 *@param string - content
		*/
		static public function addError($content)
		{
				if(!isset($_SESSION['addcontent']))
				{
						$_SESSION['addcontent']	= "";
				}
				$_SESSION['addcontent'] .= '<div class="error">' . $content . '</div>';
		}
		/**
		 * adds addcontent
		 *@name add
		 *@param string - content
		*/
		static public function addNotice($content)
		{
				if(!isset($_SESSION['addcontent']))
				{
						$_SESSION['addcontent']	= "";
				}
				$_SESSION['addcontent'] .= '<div class="notice">' . $content . '</div>';
		}
		/**
		 * gets the current addcontent
		 *@name get
		 *@return string
		*/
		public static function get()
		{
				if(!isset($_SESSION['addcontent']))
				{
						$_SESSION['addcontent']	= "";
				}
				$content = $_SESSION['addcontent'];
				self::$addcontent .= $content;
				unset($_SESSION['addcontent']);
				return self::$addcontent;
		}
		/**
		 * flushes the addcontent
		 *
		 *@name flush
		 *@access public
		*/
		public static function flush() {
			unset($_SESSION['addcontent']);
		}
}