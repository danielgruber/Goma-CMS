<?php
/**
  * this class creates dataobjects, which can extended by extending an array or calling add-function
  * class-name = name of global array
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 25.11.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class array_DataObject extends DataObject
{
		/**
		 * gets default SQL-Fields
		 *
		 *@name getDefaultSQLFields
		 *@access public
		*/
		public function DefaultSQLFields($class) {
			if(strtolower(get_parent_class($class)) == "array_dataobject") {
				return array(	
						'id'			=> 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
						'autorid'    	=> 'int(10)',
						'last_modified' => 'int(90)',
						'class_name' 	=> 'enum("'.implode('","', array_merge(classinfo::getChildren($class), array($class))).'")',
						"created"		=> "int(90)"
					);
			} else {
				return array(	
						'id'			=> 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
						'autorid'    	=> 'int(10)'
					);
			}
		}
		/**
		 * gets the array
		*/
		public function getArray() {
			return isset($GLOBALS[$this->class]) ? $GLOBALS[$this->class] : array();
		}
		/**
		 * sets the data of the table
		 *@name preserveDefaults
		 *@access public
		*/
		public function preserveDefaults($prefix = DB_PREFIX)
		{
				
				parent::preserveDefaults($prefix);
				if($this->table_name) {
					DataObject::truncate($this->class);
					DataObject::truncate($this->class . "_state", $this->table_name . "_state");
					
					$data = $this->getArray();
					
					foreach($data as $record) {
						$record["class_name"] = $this->class;
						$class = new $this->class($record);
						$class->write();
					}
				}
				
				return true;
		}
		/**
		 * adds data to the array
		 *@name add
		 *@access public
		 *@param string - name of the array
		 *@param array - data
		*/
		public static function add($name, array $record)
		{
				$GLOBALS[$name][] = $record;
				$record["class_name"] = $name;
				DataObject::add($name, $record);
				return true;
		}
}