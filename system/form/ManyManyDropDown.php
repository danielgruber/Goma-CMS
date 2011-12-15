<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 16.10.2010
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class ManyManyDropDown extends MultiSelectDropDown
{		
		/**
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param array|int - selected items
		 *@param object - parent
		*/
		public function __construct($name = "", $title = null, $showfield = "title", $where = array(), $value = null, $parent = null)
		{
				parent::__construct($name, $title, $value, $parent);
				$this->relation = $name;
				$this->showfield = $showfield;
				$this->where = $where;
		}
		/**
		 * sets the value if not set
		 *
		 *@name getValue
		 *@access public
		*/
		public function getValue() {
			
			parent::getValue();
			
			if(!$this->dataset) {
				if(is_object($this->form()->result) && (isset($this->form()->result->many_many[$this->relation]) || isset($this->form()->result->belongs_many_many[$this->relation]))) {
					$this->_object = (isset($this->form()->result->many_many[$this->relation])) ? $this->form()->result->many_many[$this->relation] : $this->form()->result->belongs_many_many[$this->relation];
					$this->dataset = call_user_func_array(array($this->form()->result, $this->relation), array())->FieldToArray("versionid");
				} else {
					if(isset($this->form()->controller->model_inst->many_many[$this->relation]) || isset($this->form()->controller->model_inst->belongs_many_many[$this->relation])) {
						$this->_object = (isset($this->form()->controller->model_inst->many_many[$this->relation])) ? $this->form()->controller->model_inst->many_many[$this->relation] : $this->form()->controller->model_inst->belongs_many_many[$this->relation];
						$this->dataset = call_user_func_array(array($this->form()->controller->model_inst, $this->relation), array())->FieldToArray("versionid");
					} else {
						throwError(5, "PHP-Error", "".$this->relation." doesn't exist in this form in ".__FILE__." on line ".__LINE__."");
					}
				}
			} else {
				if(is_object($this->form()->result) && (isset($this->form()->result->many_many[$this->relation]) || isset($this->form()->result->belongs_many_many[$this->relation]))) {
					$this->_object = (isset($this->form()->result->many_many[$this->relation])) ? $this->form()->result->many_many[$this->relation] : $this->form()->result->belongs_many_many[$this->relation];
				} else {
					if(isset($this->form()->controller->model_inst->many_many[$this->relation]) || isset($this->form()->controller->model_inst->belongs_many_many[$this->relation])) {
						$this->_object = (isset($this->form()->controller->model_inst->many_many[$this->relation])) ? $this->form()->controller->model_inst->many_many[$this->relation] : $this->form()->controller->model_inst->belongs_many_many[$this->relation];
						
					} else {
						throwError(5, "PHP-Error", "".$this->relation." doesn't exist in this form in ".__FILE__." on line ".__LINE__."");
					}
				}
			}
		}
		/**
		 * renders the data in the input
		*/
		public function renderInput() {
			$data = DataObject::getObject($this->_object, array("versionid" => $this->dataset));
			if($data && $data->_count() > 0) {
				$str = "";
				$i = 0;
				foreach($data as $record) {
					if($i == 0) {
						$i++;
					} else {
						$str .= ", ";
					}
					$str .= $record[$this->showfield];
				}
				unset($data, $record, $i);
				return $str;
			} else {
				return lang("form_dropdown_nothing_select", "Nothing Selected");
			}
		}
		/**
		 * getDataFromModel
		 *
		 *@param numeric - page
		*/
		public function getDataFromModel($p = 1) {
			
			$data = DataObject::_get($this->_object, $this->where, array(), array(), array(), array(), $p);
			
			$arr = array();
			foreach($data as $record) {
				$arr[$record["versionid"]] = text::protect($record[$this->showfield]);
			}			
			$left = ($p > 1);
			
			$right = (ceil($data->_count() / 10) > $p);
			return array("data" => $arr, "left" => $left, "right" => $right);
		}
		
		/**
		 * searches data from the optinos
		 *
		 *@name searchDataFromModel
		 *@param numeric - page
		*/
		public function searchDataFromModel($p = 1, $search = "") {
			$data = DataObject::_search($this->_object, array($search),$this->where, array(), array(), array(), array(), $p);
			$arr = array();
			foreach($data as $record) {
				$arr[$record["versionid"]] = preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", text::protect($record[$this->showfield]));
			}			
			$left = ($p > 1);
			$right = (ceil($data->_count() / 10) > $p);
			return array("data" => $arr, "left" => $left, "right" => $right);
		}
}