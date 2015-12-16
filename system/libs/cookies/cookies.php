<?php
/**
  * this class let you know much about other classes or your class
  * you can get childs or other things
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 08.05.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class cookies extends gObject
{
		/**
		 * This var contains the cookie name
		 *@name name
		 *@access public
		 *@var string
		**/
		public $name;
		/**
		 * This var contains the cookie value
		 *@name value
		 *@access public
		 *@var string
		**/
		public $value;
		/**
		 * This var contains the life time
		 *@name time
		 *@access public
		 *@var int
		**/
		public $time;
		/**
		 * This var contains the selected value of the cookie $name
		 *@name read
		 *@access public
		 *@var string
		**/
		public $read;
		
		/**
		 * This sets $name, $value, $time and readouts the cookie if exists
		 *@name __construct
		 *@param string - cookie name
		 *@param string - cookie value
		 *@param string - life time
		 *@access public
		 *@return bool
		**/
		public function __construct($name = "", $value = "", $time = "")
		{
				parent::__construct();
				
				/* --- */
				
				if(!empty($name))
				{
						$this->name = $name;
						$this->read = $this->read;
				}
				if(!empty($value))
				{
						$this->value = $value;
				}
				if(!empty($time))
				{
						$this->time = $time;
				}
				return true;
		}
		
		/**
		 * This sets the cookie $name
		 *@name set
		 *@param string - cookie name
		 *@param string - cookie value
		 *@param string - life time
		 *@access public
		 *@return bool
		**/
		public function set($name = "", $value = "", $time = "")
		{
				if(empty($this->name) || empty($this->value) || empty($this->time))
				{
						return false;
				}
				if(empty($name))
				{
						$name = $this->name;
				}
				if(empty($value))
				{
						$value = $this->value;
				}
				if(empty($time))
				{
						$time = $this->time;
				}
				setcookie($name, $value, $time);
				return true;
		}
		
		/**
		 * This readouts the cookie $name
		 *@name read
		 *@param string - cookie name
		 *@access public
		 *@return bool
		**/
		public function read($name = "")
		{
				if(!empty($name))
				{
						$this->read = $_COOKIE[$name];
				}
				elseif(!empty($this->name))
				{
						$this->read = $_COOKIE[$this->name];
				}
				else
				{
						return false;
				}
				return $this->read;
		}
		
		/**
		 * This deletes the cookie $name
		 *@name delete
		 *@param string - cookie name
		 *@access public
		 *@return bool
		**/
		public function delete($name = "")
		{
				if(!empty($name))
				{
						setcookie($name, "", -999999);
						return true;
				}
				elseif(!empty($this->name))
				{
						setcookie($this->name, "", -999999);
						return true;
				}
				else
				{
						return false;
				}
		}
}