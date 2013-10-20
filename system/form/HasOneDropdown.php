<?php defined("IN_GOMA") OR die();

/**
 * This is a simple searchable dropdown, which can be used to select has-one-relations.
 *
 * It supports has-one-realtions of DataObjects and just supports single-select.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.4
 */
class HasOneDropdown extends SingleSelectDropDown
{
		/**
		 * the name of the relation of the current field
		 *
		 *@name relation
		 *@access public
		*/
		public $relation;
		
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
		 * base model for queriing DataBase.
		 *
		 *@name model
		*/
		protected $model;
		
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
		 * sets the base-model for queriing DB.
		*/
		public function setModel(DataObjectSet $model) {
			$this->model = $model;
		}
		
		/**
		 * returns the model.
		*/
		public function getModel() {
			return $this->model;
		}
		
		/**
		 * sets the value if not set
		 *
		 *@name getValue
		 *@access public
		*/
		public function getValue() {
			
			parent::getValue();
			
			if(!isset($this->value)) {
				
				// get has-one from result
				if(is_object($this->form()->result))
					$has_one = $this->form()->result->hasOne();
				
				if(isset($has_one[$this->relation]) && is_object($has_one[$this->relation])) {
					$this->_object = $has_one[$this->relation];
					
					
					
					$this->value = isset(call_user_func_array(array($this->form()->result, $this->relation), array())->id) ? call_user_func_array(array($this->form()->result, $this->relation), array())->id : null;
					$this->input->value = $this->value;
				} else {
					
					// get has-one from controller
					if(is_object($this->form()->controller))
						$has_one = $this->form()->controller->model_inst->hasOne();
					else
						$has_one = null;
						
					if(isset($has_one[$this->relation])) {
						$this->_object = $has_one[$this->relation];
						
						

						$this->value = isset(call_user_func_array(array($this->form()->controller->model_inst, $this->relation), array())->id) ? call_user_func_array(array($this->form()->controller->model_inst, $this->relation), array())->id : null;
						$this->input->value = $this->value;
					} else {
						throw new LogicException("{$this->relation} doesn't exist in the form {$this->form->name}.");
					}
				}
			} else {
				// get has-one from result
				if(is_object($this->form()->result))
					$has_one = $this->form()->result->hasOne();
					
				if(is_object($this->form()->result) && isset($has_one[$this->relation])) {
					$this->_object = $has_one[$this->relation];
					
				} else {
				
					// get has-one from controller
					if(is_object($this->form()->controller))
						$has_one = $this->form()->controller->model_inst->hasOne();
					else
						$has_one = null;
						
					if(isset($has_one[$this->relation])) {
						$this->_object = $has_one[$this->relation];
						

					} else {
						throw new LogicException("{$this->relation} doesn't exist in the form {$this->form->name}.");
					}
				}
			}
			
			if(!isset($this->model))
				$this->model = DataObject::get($this->_object);
		}
		
		/**
		 * generates the values displayed in the field, if not dropped down.
		 *
		 * @access protected
		 * @return array values
		*/
		protected function getInput() {
			$data = DataObject::get($this->_object, array("id" => $this->value));
			
			if($this->form()->useStateData) {
				$data->setVersion("state");
			}
			
			if($data && $data->count() > 0) {
				return array($data->id => $data[$this->showfield]);
			} else {
				return array();
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
			
			$data = clone $this->model;
			$data->filter($this->where);
			$data->activatePagination($p);
			
			if($this->form()->useStateData) {
				$data->setVersion("state");
			}
			
			$arr = array();
			foreach($data as $record) {
				
				
				$arr[] = array("key" => $record["id"], "value" => convert::raw2text($record[$this->showfield]));
				
				// check for info-field
				if(isset($this->info_field)) {
					if(isset($record[$this->info_field])) {
						$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
					}
				}
			}			
			$left = ($p > 1);
			
			$right = (ceil($data->count() / 10) > $p);
			$pageInfo = $data->getPageInfo();
			unset($data);
			
			
			return array("data" => $arr, "left" => $left, "right" => $right, "showStart" => $pageInfo["start"], "showEnd" => $pageInfo["end"], "whole" => $pageInfo["whole"]);
		}
		
		/**
		 * searches data from the optinos
		 *
		 *@name searchDataFromModel
		 *@param numeric - page
		*/
		public function searchDataFromModel($p = 1, $search = "") {
			$data = clone $this->model;
			$data->filter($this->where);
			$data->search($search);
			$data->activatePagination($p);
			
			if($this->form()->useStateData) {
				$data->setVersion("state");
			}
			
			$arr = array();
			foreach($data as $record) {
				
				$arr[] = array("key" => $record["id"], "value" => preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", convert::raw2text($record[$this->showfield])));
				
				// check for info-field
				if(isset($this->info_field)) {
					if(isset($record[$this->info_field])) {
						$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
					}
				}
			}			
			$left = ($p > 1);
			$right = (ceil($data->count() / 10) > $p);
			
			$pageInfo = $data->getPageInfo();
			unset($data);
			
			return array("data" => $arr, "left" => $left, "right" => $right, "showStart" => $pageInfo["start"], "showEnd" => $pageInfo["end"], "whole" => $pageInfo["whole"]);
		}
	
		/**
		 * validates the value
		 *
		 *@name validate
		 *@param value
		*/
		public function validate($id) {
			if($this->classname == "hasonedropdown") {
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