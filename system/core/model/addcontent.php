<?php defined("IN_GOMA") OR die();

/**
 * holds add-contents.
 *
 * @package 	goma framework
 * @link 		http://goma-cms.org
 * @license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 		Goma-Team
 * @Version 	1.0
 *
 * last modified: 25.02.2015
*/
class addcontent
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
			self::$addcontent = "";
		}
}
