<?php defined("IN_GOMA") OR die();

/**
 * This is a simple searchable dropdown, which can be used to select many-many-connections.
 *
 * It supports many-many-relations of DataObjects and MultiSelecting.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5
 */
class ManyManyDropDown extends MultiSelectDropDown
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
		 * base-model for querying DataBase.
		 *
		 * @name model
		*/
		protected $model;

		/**
		 * info about relationship.
		*/
		protected $relationInfo;
		
		/**
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param array|int - selected items
		 *@param object - parent
		*/
		public function __construct($name = "", $title = null, $showfield = "title", $where = array(), $value = null, $parent = null)
		{
				parent::__construct($name , $title, $value, $parent);
				$this->dbname = $name . "ids";
				$this->relation = strtolower($name);
				$this->showfield = $showfield;
				$this->where = $where;
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
			
			if(!isset($this->dataset)) {
				
				if(is_object($this->form()->result)) {
					// get relations from result
					$many_many_tables = $this->form()->result->ManyManyTables();
				}
				
				if(isset($many_many_tables[$this->relation])) {
					
					$this->_object = $many_many_tables[$this->relation]["object"];
					$this->dataset = call_user_func_array(array($this->form()->result, $this->relation), array())->FieldToArray("versionid");
					$this->relationInfo = $many_many_tables[$this->relation];
				} else if(is_object($this->form()->controller)) {
					// get relations from model of form-controller
					$many_many_tables = $this->form()->controller->modelInst()->ManyManyTables();
					
					if(isset($many_many_tables[$this->relation])) {
						$this->_object = $many_many_tables[$this->relation]["object"];
						$this->dataset = call_user_func_array(array($this->form()->controller->modelInst(), $this->relation), array())->FieldToArray("versionid");
						$this->relationInfo = $many_many_tables[$this->relation];
					} else {
						throw new LogicException("{$this->relation} doesn't exist in this form {$this->form->name}.");
					}
				} else {
					throw new LogicException("{$this->relation} doesn't exist in this form {$this->form->name}.");
				}
			} else {
				if(is_object($this->form()->result)) {
					// get relations from result
					$many_many_tables = $this->form()->result->ManyManyTables();
				}
				
				if(isset($many_many_tables[$this->relation])) {
					
					$this->_object = $many_many_tables[$this->relation]["object"];
					$this->relationInfo = $many_many_tables[$this->relation];
				} else if(is_object($this->form()->controller)) {
					
					// get relations from model of form-controller
					$many_many_tables = $this->form()->controller->modelInst()->ManyManyTables();
					
					if(isset($many_many_tables[$this->relation])) {
						$this->_object = $many_many_tables[$this->relation]["object"];
						$this->relationInfo = $many_many_tables[$this->relation];
					} else {
						throw new LogicException("{$this->relation} doesn't exist in this form {$this->form->name}.");
					}
				} else {
					throw new LogicException("{$this->relation} doesn't exist in this form {$this->form->name}.");
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

			$data = DataObject::get($this->_object, array("versionid" => $this->dataset));
			
			if($this->form()->useStateData) {
				$data->setVersion(DataObject::VERSION_STATE);
			} else {
				$data->setVersion(DataObject::VERSION_PUBLISHED);
			}
			
			if($data && $data->count() > 0) {
				$return = array();
				foreach($data as $record) {
					$return[$record->versionid] = convert::raw2text($record[$this->showfield]);
				}

				$dataReturn = array();
				foreach($this->dataset as $id) {
					$dataReturn[$id] = $return[$id];
				}

				return $dataReturn;
			} else {
				return array();
			}
		}
				
		/**
		 * getDataFromModel
		 *
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
				$arr[] = array("value" => convert::raw2text($record[$this->showfield]), "key" => $record["versionid"]);
				if(isset($this->info_field)) {
					if(isset($record[$this->info_field])) {
						$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
					}
				}
			}			
			$left = ($p > 1);
			
			$right = (ceil($data->count() / 10) > $p);
			return array("data" => $arr, "left" => $left, "right" => $right);
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
				$arr[] = array("key" => $record["versionid"], "value" => preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", convert::raw2text($record[$this->showfield])));
				if(isset($this->info_field)) {
					if(isset($record[$this->info_field])) {
						$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
					}
				}
			}			
			$left = ($p > 1);
			$right = (ceil($data->count() / 10) > $p);
			return array("data" => $arr, "left" => $left, "right" => $right);
		}
		
		public function result() {
			$result = parent::result();

			return $result;
		}
}