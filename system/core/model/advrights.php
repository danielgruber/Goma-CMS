<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 20.05.2011
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class advrights extends array_dataobject
{
		/**
		 * connection to adv_rights
		*/
		
		public $belongs_many_many = array('advrights' => 'group');
		public $indexes = array(
			"name"	=> "UNIQUE"
		);
		public $db_fields = array
		(
			'name' 		=> 'varchar(200)',
			'_default'	=> 'int(10)',
			"title"		=> 'varchar(200)'
		);
		
		public function getArray() {
			$advrights = classinfo::$advrights;
			$newadvrights = array();
			foreach($advrights as $key => $data) {
				if(isset($data["default"])) {
					$data["_default"] = $data["default"];
					unset($data["default"]);
				}
				$newadvrights[$key] = $data;
			}
			return $newadvrights;
		}
		
		public function getDefault() {
			return $this->_default;
		}
		
		public function setDefault($value) {
			$this->_default = $value;
		}
}