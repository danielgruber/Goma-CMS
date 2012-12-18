<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 17.12.2012
  * $Version 1.1.2
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class HasOneDropdown extends SingleSelectDropDown
{
		/**
		 * the name of the relation of the current field
		 *
		 *@name relation
		 *@access public
		*/
		public $realtion;
		
		/**
		 * field to show in dropdown
		 *
		 *@name showfield
		 *@access public
		*/
		public $showfield;
		
		/**
		 * where clause to filter result in dropdown
		 *
		 *@name where
		 *@access public
		*/
		public $where;
		
		/**
		 * info-field
		 *
		 *@name info_field
		 *@access public
		*/
		public $info_field;
		
		/**
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param array|int - selected items
		 *@param object - parent
		*/
		public function __construct($name = "", $title = null, $showfield = "title", $where = array(), $value = null, &$parent = null)
		{
				parent::__construct($name, $title, $value, $parent);
				$this->relation = strtolower($name);
				$this->showfield = $showfield;
				$this->where = $where;
				$this->dbname = $name . "id";
		}
		
		/**
		 * sets the value if not set
		 *
		 *@name getValue
		 *@access public
		*/
		public function getValue() {
			
			parent::getValue();
			
			if(!$this->value) {
				if(is_object($this->form()->result) && isset($this->form()->result->has_one[$this->relation])) {
					$this->_object = $this->form()->result->has_one[$this->relation];
					$this->value = isset(call_user_func_array(array($this->form()->result, $this->relation), array())->id) ? call_user_func_array(array($this->form()->result, $this->relation), array())->id : null;
					$this->input->value = $this->value;
				} else {
					if(isset($this->form()->controller->model_inst->has_one[$this->relation])) {
						$this->_object = $this->form()->controller->model_inst->has_one[$this->relation];
						$this->value = isset(call_user_func_array(array($this->form()->controller->model_inst, $this->relation), array())->id) ? call_user_func_array(array($this->form()->controller->model_inst, $this->relation), array())->id : null;
						$this->input->value = $this->value;
					} else {
						throwError(5, "PHP-Error", "".$this->relation." doesn't exist in this form-model in ".__FILE__." on line ".__LINE__."");
					}
				}
			} else {
				if(is_object($this->form()->result) && isset($this->form()->result->has_one[$this->relation])) {
					$this->_object = $this->form()->result->has_one[$this->relation];
				} else {
					if(isset($this->form()->controller->model_inst->has_one[$this->relation])) {
						$this->_object = $this->form()->controller->model_inst->has_one[$this->relation];
					} else {
						throwError(5, "PHP-Error", "".$this->relation." doesn't exist in this form-model in ".__FILE__." on line ".__LINE__."");
					}
				}
			}
		}
		
		/**
		 * renders the data in the input
		 *
		 *@name renderInput
		 *@access public
		*/
		public function renderInput() {
			$data = DataObject::get($this->_object, array("id" => $this->value));
			
			if($this->form()->useStateData) {
				$data->setVersion("state");
			}
			
			if($data && $data->count() > 0) {
				return $data[$this->showfield];
			} else {
				return lang("form_dropdown_nothing_select", "Nothing Selected");
			}
		}
		
		/**
		 * gets data from the model for the dropdown
		 *
		 *@name getDataFromModel
		 *@access public
		 *@param numeric - page
		*/
		public function getDataFromModel($p = 1) {
			
			$data = DataObject::get($this->_object, $this->where);
			$data->activatePagination($p);
			
			if($this->form()->useStateData) {
				$data->setVersion("state");
			}
			
			$arr = array();
			foreach($data as $record) {
				$val = convert::raw2text($record[$this->showfield]);
				
				if(isset($this->info_field)) {
					if(isset($record[$this->info_field])) {
						$val = array($val, convert::raw2text($record[$this->info_field]));
					}
				}
				
				$arr[$record["id"]] = $val;
				
				unset($record, $val);
			}			
			$left = ($p > 1);
			
			$right = (ceil($data->count() / 10) > $p);
			
			unset($data);
			
			return array("data" => $arr, "left" => $left, "right" => $right);
		}
		
		/**
		 * searches data from the optinos
		 *
		 *@name searchDataFromModel
		 *@param numeric - page
		*/
		public function searchDataFromModel($p = 1, $search = "") {
			$data = DataObject::search_object($this->_object, array($search),$this->where);
			$data->activatePagination($p);
			
			if($this->form()->useStateData) {
				$data->setVersion("state");
			}
			
			$arr = array();
			foreach($data as $record) {
				$val = preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", convert::raw2text($record[$this->showfield]));
				if(isset($this->info_field)) {
					if(isset($record[$this->info_field])) {
						$val = array($val, convert::raw2text($record[$this->info_field]));
					}
				}
				
				$arr[$record["id"]] = $val;
				
				unset($record, $val);
			}			
			$left = ($p > 1);
			$right = (ceil($data->count() / 10) > $p);
			unset($data);
			return array("data" => $arr, "left" => $left, "right" => $right);
		}
	
		/**
		 * validates the value
		 *
		 *@name validate
		 *@param value
		*/
		public function validate($id) {
			if($this->class == "hasonedropdown") {
				if(DataObject::count($this->_object, array("id" => $id)) > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
}