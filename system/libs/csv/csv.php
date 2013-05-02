<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  *@author Daniel Gruber
  * last modified: 02.11.2010
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class CSV extends Object implements Iterator
{
		/**
		 * this var contains the csv
		 *@name csv
		 *@access protected
		*/
		protected $csv;
		/**
		 * this var contains the data as array
		 *@name csvarr
		 *@access protected
		*/
		protected $csvarr = array();
		/**
		 *@name __construct
		 *@param string - csv
		 *@access public
		*/
		public function __construct($str)
		{
				parent::__construct();
				
				/* --- */
				
				$this->csv = trim($str);
				$this->parse();
		}
		/**
		 * parses the data
		 *@name parse
		 *@access protected
		*/
		protected function parse()
		{
				$str = $this->csv;
				// we do not need \r
				$str = str_replace("\r\n", "\n", $str);
				$rows = explode("\n", $str);
				$i = 1;
				foreach($rows as $row)
				{
						if(substr($row, -1) == ";")
						{
								$row = substr($row, 0, -1);
						}
						$arr = explode(";", $row);
						
						
						
						// validate
						$fields = array();
						$a = 0;
						$b = 1; // counter for field-names
						
						while($a < count($arr))
						{
								$fields[$b] = $arr[$a];
								if(substr($arr[$a], -1) == "\\")
								{
										$fields[$b] = substr($arr[$a], 0, -1) . ";";
										$a++;
										$fields[$b] .= $arr[$a]; 
								}
								$a++;
								$b++;
						}
						$this->csvarr[$i] = $fields;
						$i++;
				}
		}
		/**
		 * gets an field
		 *@name get
		 *@access public
		 *@param numeric - row
		 *@param numeric - field
		*/
		public function get($row, $field)
		{
				return isset($this->csvarr[$row][$field]) ? $this->csvarr[$row][$field] : false;
		}
		/**
		 * gets an row
		 *@name getRow
		 *@access public
		 *@param numeric - row
		*/
		public function getRow($row)
		{
				return isset($this->csvarr[$row]) ? $this->csvarr[$row] : false;
		}
		/**
		 * gets the object as csv
		 *@name csv
		 *@access public
		*/
		public function csv()
		{
				$str = "";
				foreach($this->csvarr as $row)
				{
						$i = 0;
						foreach($row as $val)
						{
								if($i == 0)
								{
										$i++;
								} else
								{
										$str .= ";";
								}
								$str .= CSV::escape($val);
						}
						$str .= "\n";
				}
				return $str;
		}
		/**
		 * escapes a string for csv
		 *@name escape
		 *@param string
		*/
		public static function escape($str)
		{
				$str = str_replace(";","\\;", $str);
				return $str;
		}
		/**
		 * adds a row to the csv
		 *@name addRow
		 *@access public
		 *@param array - row
		*/
		public function addRow($data = array())
		{
				$this->csvarr[] = $data;
				return $this;
		}
		/**
		 * sets the data of an field
		 *@name set
		 *@access public
		 *@param numeric - row
		 *@param numeric - field
		 *@param string - data
		*/
		public function set($row, $field, $data)
		{
				if(!isset($this->csvarr[$row]))
				{
						// generate rows
						$i = count($this->csvarr);
						while($i <= $row)
						{
								$this->csvarr[$i] = array();
								$i++;
						}
				}
				
				$this->csvarr[$row][$field] = $data;
				return $this;
		}
		/**
		 * Magic Methods 
		 * for handling csv like this: $csv->1_1 = "this is field 1 in row 1";
		*/
		/**
		 * for reading
		 *@name __get
		 *@access public
		 *@param string - var
		*/
		public function __get($var)
		{
				if(!strpos("_", $var))
				{
						
				}
				$arr = explode("_", $var);
				return $this->get($arr[0], $arr[1]);
		}
		/**
		 * for writing
		 *@name __set
		 *@access public
		 *@param string - var
		 *@param string - data
		*/
		public function __set($var, $data)
		{
				if(!strpos("_", $var))
				{
						return false;
				}
				$arr = explode("_", $var);
				return $this->set($arr[0], $arr[1], $data);
		}
		/**
		 * Iterator
		 *@link http://php.net/manual/en/class.iterator.php
		*/
		/**
		 * the position of the iterator
		*/
		protected $position = 1;
		/**
		 * this is the position set
		 *@name pos
		*/
		protected $pos = 1;
		/**
		 * checks if valid
		 *@name valid
		*/
		public function valid()
		{
				return isset($this->csvarr[$this->position]);
		}
		/**
		 * rewinds
		 *@name rewind
		*/
		public function rewind($pos = true)
		{
				if($pos)			
						$this->position = $this->pos;
				else
						$this->position = 1;
		}
		/**
		 * gets the current key
		 *@name key
		*/
		public function key()
		{
				return $this->position;
		}
		/**
		 * gets the current value
		 *@name current
		*/
		public function current()
		{
				return $this->csvarr[$this->position];
		}
		/**
		 * goes to the next position
		 *@name next
		*/
		public function next()
		{
				$this->position++;
		}
		/**
		 * sets the position of the iterator
		 *@name setPosition
		 *@param numeric - position
		*/
		public function setPosition($pos)
		{
				if(isset($this->csvarr[$pos]))
				{
						$this->pos = $pos;
				}
		}
		
}