<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 31.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

abstract class ComplexTableField extends FormField
{
		public $url_handlers = array(
			"POST update/\$search" 			=> "updateBody",
			"POST setCheckbox/\$id!/\$bool!"=> "updateCheckBox",
			"POST setRadio/\$id!"			=> "SetRadioBox"
		);
		public $allowed_actions = array(
			"edit", "add", "delete", "updateBody", "updateCheckBox", "SetRadioBox"
		);
		
		
		/**
		 * relation-type
		 * values:
		 * * has_many
		 * * has_one
		 * * many_many
		 *
		 *@name relation
		 *@access protected
		*/ 
		protected $relation;
		/**
		 * selected ids
		 *
		 *@name selected
		 *@access public
		*/
		protected $selected;
		/**
		 * submission for edit and add
		 *
		 *@name submission
		 *@access protected
		*/
		protected $submission = "safe";
		/**
		 * dataset
		 *
		 *@name dataset
		 *@access public
		*/
		public $dataset;
		/**
		 * key for dataset
		 *
		 *@var string
		*/
		protected $key;
		/**
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param string - title
		 *@param array - seleted
		 *@param object - class
		 *@param array - show fields
		 *@param string - optional, for adding a special form-function
		 *@param bool - whether this table should contain all available records in the relation or just the records, which points to this record
		 *@parm array - optional where-clause
		 *@param array - disabled actions, available actions are: delete, edit, add
		 *@param optional parent
		*/
		public function __construct($name = "",  $title = "", $selected = array(), $showfields = array(), $formgeneration = null, $justConnected = null, $where = array(), $disabled_actions = array(), $parent = null)
		{
				
				
				$this->showfields = $showfields;
				$this->where = $where;
				$this->disabled_actions = $disabled_actions;
				$this->selected = $selected;
				
				
				if($justConnected === null) {
					if($this->relation == "has_many") {
						$justConnected = true;
					} else {
						$justConnected = false;
					}
				}
				
				$this->justConnected = $justConnected;
				
				if($formgeneration === null) {
					$formgeneration = "getForm";
				}
				$this->formgeneration = $formgeneration;
				
				
				parent::__construct($name, $title, null, $parent);
				
				Resources::add("complexTableField.css", "css");
				Resources::add("system/form/complexTableField.js", "js", "tpl");
				Resources::addData("var lang_search = '".lang("search")."';");
		}
		
		public function setForm(&$form) {
			parent::setForm($form);
			$this->prepareDataSet();
			$this->table->id = $this->id() . "_table";
		}
		
		public function createNode() {
			$this->table = new HTMLNode("table", array("class" => "ComplexTableFieldTable"), array(
				$this->tablehead = new HTMLNode("thead"),
				$this->tablebody = new HTMLNode("tbody")
			));
			return true;
		}
		
		/**
		 * prepares data
		 *
		 *@name prepareDataSet
		 *@accss public
		*/
		
/**
		 * prepares data
		 *
		 *@name prepareDataSet
		 *@accss public
		*/
		
		public function prepareDataSet() {
			// check if dataset is given cause of From-Submitting
			if($this->POST && isset($this->form()->post[$this->name])) {
				
				if(session_store_exists("dataset_" . $this->name . "_" . $this->form()->post[$this->name])) {
					$this->key = $this->form()->post[$this->name];
					$dataset = session_restore("dataset_" . $this->name . "_" . $this->form()->post[$this->name]);
				}
				
				if(is_object($dataset) && is_subclass_of($dataset, "DataObject")) {
					if($dataset->dataset !== true) {
						$dataset->dataset = true;
					}
					$this->dataset = $dataset;
					$this->savedataset();
					unset($dataset);
					return true;
				}
				
				unset($dataset);
			}
			
			// create new
			if($this->justConnected) {
				$dataset = call_user_func_array(array($this->form()->controller->model_inst, $this->name), array($this->where, array_keys($this->showfields)));
				$dataset->getWholeData();
				foreach($dataset->wholedata as $position => $record) {
					$record["__checked"] = true;
					$dataset->wholedata[$position] = $record;
					unset($record);
				}
				$this->dataset = $dataset;
				unset($dataset);
				return true;
			} else {
				$model = (is_object($this->form()->result)) ? $this->form()->result : $this->form()->controller->model_inst;
				// first get table, which contains all data
				if($this->relation == "has_one") {
					// 
					if(isset($model->has_one[$this->name]))
						$baseclass = $model->has_one[$this->name];
					else 
						throwError(6, "PHP-Error",'Relation '.$this->orgname.' not found on model '.$this->form()->controller->model_inst->class);
				} else if($this->relation == "has_many") {
					if(isset($model->has_many[$this->name]))
						$baseclass = $model->has_many[$this->name];
					else 
						throwError(6, "PHP-Error",'Relation '.$this->orgname.' not found on model '.$this->form()->controller->model_inst->class);
				}  else	if($this->relation == "many_many") {
					if(isset($model->many_many[$this->name]))
						$baseclass = $model->many_many[$this->name];
					else if(isset($model->belongs_many_many[$this->name]))
						$baseclass = $model->belongs_many_many[$this->name];
					else 
						throwError(6, "PHP-Error",'Relation '.$this->orgname.' not found on model '.$this->form()->controller->model_inst->class);
				}
				
				$data = DataObject::_get($baseclass, $this->where,array_keys($this->showfields));
				$data->dataset = true;
				
				// get active ids
				$methodforids = ($this->relation == "has_one") ? $this->name . "id" : $this->name . "ids";
				$ids = call_user_func_array(array($this->form()->controller->model_inst, $methodforids), array($this->where, array_keys($this->showfields)));
				
				// now write active states in dataset
				foreach($data->wholedata as $position => $record) {
					if(in_array($record["id"], $ids)) {
						$record["__checked"] = true;
						$data->wholedata[$position] = $record;
					} else {
						$record["__checked"] = false;
						$data->wholedata[$position] = $record;
					}
					unset($record);
				}
				
				$this->savedataset();
				
				$this->dataset = $data;
				return true;
			}
		}

		
		/**
		 * generates the first column for each record, sometimes it's hidden and sometimes it's a checkbox or radio-box
		 *
		 *@name FirstColumn
		 *@access public
		 *@param array - data
		*/
		public function FirstColumn($data) {
			// has_one
			if($this->disabled)
				return "";
			if($this->relation == "has_one") {
				
				// __checked is set to true if the record is checked
				if(isset($data["__checked"]) && $data["__checked"] === true) {
					return new HTMLNode("input", array("href" => $this->externalURL() . "/setRadio","checked" => "checked","type" => "radio", "name" => $this->name, "class" => "check", "value" => $data["id"]));
				} else {
					return new HTMLNode("input", array("href" => $this->externalURL() . "/setRadio","type" => "radio", "name" => $this->name, "class" => "check", "value" => $data["id"]));
				}
				
			// has-many
			} else if ($this->relation == "has_many") {
				return new HTMLNode("input", array("type" => "hidden", "name" => $this->name . "_check[]","class" => "check", "value" => $data["id"]));
				
			// many-many
			} else if($this->relation == "many_many") {
			
				// __checked is set to true if the record is checked
				if(isset($data["__checked"]) && $data["__checked"] === true) {
					return new HTMLNode("input", array("href" => $this->externalURL() . "/setCheckbox","checked" => "checked","type" => "checkbox", "name" => $this->name . "_check","class" => "check", "value" => $data["id"]));
				} else {
					return new HTMLNode("input", array("href" => $this->externalURL() . "/setCheckbox","type" => "checkbox", "name" => $this->name . "_check","class" => "check", "value" => $data["id"]));
				}
			}
		}
		
		/**
		 * generates the header
		 *
		 *@name writeHeader
		 *@access public
		*/
		public function writeHeader() {
			$fields = new HTMLNode("tr", array(), array(
				new HTMLNode("th", array("class" => "firstcolumn"))
			));
			
			// fields
			foreach($this->showfields as $field => $title) {
				$fields->append(new HTMLNode("th", array(), new HTMLNode("a", array("href" => "javascript:;", "class" => "sortlink", "name" => $field),$title)));
			}
			
			// search-field
			if(isset(classinfo::$class_info[$this->baseclass]["searchable_fields"]) && classinfo::$class_info[$this->baseclass]["searchable_fields"]) {
				$fields->append(new HTMLNode("th", array(), array(
					new HTMLNode("span", array("class" => "searchfieldholder"), array(
						new HTMLNode("input", array("href" => $this->externalURL() . "/update", "class" => "search", "type"	=> "text", "name" => "search", "id" => $this->ID() . "_searchfield"))
					))
				)));
			} else {
				$fields->append(new HTMLNode("th", array("style" => array("height" => "25px")), array(
					new HTMLNode("span", array("class" => "searchfieldholder"), array(
						new HTMLNode("input", array("href" => $this->externalURL() . "/update", "class" => "search", "type"	=> "hidden", "id" => $this->ID() . "_searchfield"))
					))
				)));
			}
			$this->tablehead->append($fields);
			unset($fields);
			
		}
		/**
		 * renders the body
		 *
		 *@name renderBody
		 *@access public
		*/
		public function renderBody() {
			$page = $this->getParam("page");
			$search = $this->getParam("search");
			if(isset($_GET["order"]) && isset($_GET["ordertype"])) {
				$orderby = array($_GET["order"], $_GET["ordertype"]);
			} else {
				$orderby = array();
			}
			$data = clone $this->dataset;
			if($search) {
				$data->search(array($search));	
			}
			if(count($orderby) > 0) {
				$data->orderby($orderby[0], $orderby[1]);
			}
			
			
			
			
			$i = 0;
			foreach($data as $record) {
				$this->tablebody->append($recordnode = $this->renderRecord($record));
				if($i == 0) {
					$recordnode->addClass("white");
					$i++;
				} else {
					$recordnode->addClass("gray");
					$i = 0;
				}
			}
			
			
			return $this->tablebody;
		}
		/**
		 * generates some js
		*/
		public function JS() {
			if(isset($_GET["order"]) && isset($_GET["ordertype"])) {
				$sortjs = "self.complex_table_field_".$this->ID().".setSortActive('".$_GET["order"]."', '".strtolower($_GET["ordertype"])."');";
			} else {
				$sortjs = "";
			}
			Resources::addData("var complexTableField_".$this->name."_externalURL = '".$this->externalURL()."'; var complexTableField_".$this->name."_key = '".$this->key."'; var complexTableField_".$this->name."_name = '".$this->name."';");
			
			return "$(function(){ self.complex_table_field_".$this->ID()." = new ComplexTableField($('#".$this->ID()."_table')); self.complex_table_field_".$this->ID().".externalURL = complexTableField_".$this->name."_externalURL; self.complex_table_field_".$this->ID().".name = complexTableField_".$this->name."_name; self.complex_table_field_".$this->ID().".key = complexTableField_".$this->name."_key; ".$sortjs." });";
		}
		/**
		 * renders actions
		 *
		 *@name renderActions
		 *@access public
		 *@param object - record
		*/
		public function renderActions($record) {
			if($this->disabled)
				return "";
			
			$return = "";
			if(!in_array("edit", $this->disabled_actions)) {
				$return .= "<a class=\"icon\" title=\"".lang("edit", "edit")."\" href=\"".$this->externalURL()."/edit/".$record["tempid"]. URLEND . "\" rel=\"ajaxfy\"><img src=\"images/icons/fatcow-icons/16x16/edit.png\" alt=\"".lang("edit", "edit")."\" /></a>";
			}
			
			if(!in_array("delete", $this->disabled_actions)) {
				$return .= "<a class=\"icon\" title=\"".lang("delete", "delete")."\" href=\"".$this->externalURL()."/delete/".$record["tempid"] . URLEND."\" rel=\"ajaxfy\"><img src=\"images/icons/fatcow-icons/16x16/delete.png\" alt=\"".lang("delete", "delete")."\" /></a>";
			}
			
			return $return;
		}
		/**
		 * renders a record
		 *
		 *@name renderRecord
		 *@access public
		 *@param object - record
		*/
		public function renderRecord($record) {
			$tr = new HTMLNode("tr");
			$tr->append(new HTMLNode("td", array("class" => "firstcolumn"), $this->firstColumn($record)));
			foreach($this->showfields as $key => $value) {
				$tr->append(new HTMLNode("td", array(), text::protect($record[$key])));
			}
			$tr->append(new HTMLNode("td", array(), $this->renderActions($record)));
			return $tr;
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				Profiler::mark("FormField::field");
				
				$this->callExtending("beforeField");
				
				// first add header
				$this->writeHeader();
				
				if(!$this->disabled)
					if(!in_array("add", $this->disabled_actions)) {
						$this->table->append(new HTMLNode("tfoot", array(), array(
							new HTMLNode("tr", array(), array(
								new HTMLNode("th", array("colspan" => count($this->showfields) + 2), array(
									new HTMLNode("img", array("src" => "images/16x16/add.png", "height" => 12, "width" => 12)),
									new HTMLNode("a", array("rel" => "ajaxfy","href" => $this->externalURL() . "/add/", "title" => lang("Create", "Create")), lang("Create", "Create"))
								))
							))
						)));
					}
				
				$this->renderBody();
				
				$this->table->css("display", "none");
				
				$this->container->append(new HTMLNode("label", array(), $this->title));
				$this->container->append($this->table);
				$this->container->append('<noscript><div class="notice">'.lang("noscript").'</div></noscript>');
				
				$this->saveDataSet();
				
				$this->container->append(new HTMLNode("input", array("type" => "hidden", "class" => "data", "name" => $this->name, "value" => $this->key)));
				
				$this->callExtending("afterField");
				
				Profiler::unmark("FormField::field");
				
				return $this->container;
		}
		/**
		 * saves the dataset
		 *
		 *@name savedataset
		 *@access public
		*/
		public function saveDataSet() {
			if($this->disabled)
				return false;
				
			
			// set dataset
			if(!$this->key) {
				$this->key = randomString(20);
			}
			session_store("dataset_" . $this->name . "_" . $this->key, $this->dataset);
			
			$this->form()->saveToSession();
		}
		/**
		 * updates the body
		 *
		 *@name updateBody
		 *@access public
		*/
		public function updateBody($search = null, $orderby = null) {
			// reset tablebody
			$this->tablebody = new HTMLNode("tbody");
			
			/* --- */
			
			if($search === null)
				$search = $this->getParam("search");
			if($orderby === null)
				if(isset($_GET["order"]) && isset($_GET["ordertype"])) {
					$orderby = array($_GET["order"], $_GET["ordertype"]);
				} else {
					$orderby = array();
				}
			
			$this->Search = $search;
			$this->orderby = $orderby;
			
			
			$data = clone $this->dataset;
			if(count($orderby) > 0) {
				$data->orderby($orderby[0], $orderby[1]);
			}
			if($search) {
				$data->search(array($search));	
			}
			
			if($data->_count() == 0) {
				$this->tablebody->append(new HTMLNode("tr", array(
					
				), array(
					new HTMLNode("th", array("colspan" => count($this->showfields) + 2, "style"	=> "color:#9f9f9f;text-align: center;"), lang("no_result", "There is no data!"))
				)));
			} else {
			
				foreach($data as $record) {
					$this->tablebody->append($this->renderRecord($record));
				}
				
			}
			
			return $this->tablebody->render();
		}
		/**
		 * edit
		 *
		 *@name edit
		 *@access public
		*/
		public function edit() {
			$response = new AjaxResponse();
			$id = $this->getParam("id");
			
			

			$data = $this->dataset->getRecord($id);
			if(!is_object($data)) {
				$response->exec(new Dialog(lang("less_rights", "Access denied"), lang("less_rights", "Access denied"), 5));
				return $response;
			}
			
			if(!$data->canWrite($data))
			{	
					$response->exec(new Dialog(lang("less_rights", "Access denied"), lang("less_rights", "Access denied"), 5));
					return $response;
			}
			
			if(!$data) {
				return false;
			}
			
			$controller = $data->controller();
			$this->editcontroller = $controller;
			$this->model_inst = $data;
			
			$form = new Form($this, "complex_edit_" . $this->class, array(
				new HiddenField("id", $id)
			));
			// set submission
			$form->setSubmission($this->submission);
			
			// draw form
			$class = $data->getRecordClass();
			$inst = Object::instance($class);
			$inst->sync($data);
			$form->addValidator(new DataValidator($inst), "datavalidator");
			
			$form->result = $inst;
			
			if($this->formgeneration == "getForm") {
				$inst->getEditForm($form, array());
				$inst->callExtending('getEditForm', $form, $this);
			} else {
				call_user_func_array(array($inst, $this->formgeneration), array($form, array()));
				$inst->callExtending('getEditForm', $form, $this);
			}
			
			$dialog = new Dialog("", lang("edit_record"));
			
			$form->add(new HiddenField("dialogid", $dialog->key));
			// we generate new actions
			$form->actions = array();
			$form->addAction(new AjaxSubmitButton("submit", lang("save", "save"), $this->submission));
			$form->addAction(new Button("cancel", lang("cancel", "cancel"), $dialog->getCloseJS(0)));
			
			$this->callExtending("afterForm", $form);
			
			$dialog->content = $form->render();
			
			$response->exec($dialog);
			// clean up
			unset($dialog, $inst, $form, $class, $controller);
			return $response;
		}
		/**
		 * safe-function
		 *
		 *@name safe
		 *@access public
		*/
		public function safe($data, $response, $form) {
			$this->dataset->updateRecord($data["id"], $data);
			
			$this->saveDataset();
			$response->exec($data["dialogid"] . ".close();");
			$response->exec('complex_table_field_'.$this->ID().'.table.find("tbody").remove();
			complex_table_field_'.$this->ID().'.table.append("'.convert::raw2js($this->updateBody($this->Search, $this->orderby)).'");');
			return $response->render();

		}
		/**
		 * safe-function for add
		 *
		 *@name safe_add
		 *@access public
		*/
		public function safe_add($data, $response, $form) {
			$this->dataset->addRecord($data);
			
			$this->saveDataset();
			$response->exec($data["dialogid"] . ".close();");
			$response->exec('complex_table_field_'.$this->ID().'.table.find("tbody").remove();
			complex_table_field_'.$this->ID().'.table.append("'.convert::raw2js($this->updateBody($this->Search, $this->orderby)).'");');
			return $response->render();
		}
		/**
		 * delete-function
		 *
		 *@name delete
		 *@access public
		*/
		public function delete() {
			$id = $this->getParam("id");
				if(!$this->dataset->getRecord($id)->canDelete($this->dataset->getRecord($id)))
					if(Request::isJSResponse()) {
						$response = new AjaxResponse();
						$response->exec($dialog = new Dialog(lang("less_rights", "Access Denied"), lang("less_rights", "Access Denied")));
						$dialog->close(5);
						return $response;
					} else {
						return lang("less_rights", "Access Denied");
					}
			
			
			
			if(Object::instance("controller")->confirm(lang("delete_confirm", "Do you really want to delete this record?"))) {
				$this->dataset->deleteRecord($id);
				$this->saveDataSet();
				if(Request::isJSResponse()) {
					$response = new AjaxResponse();
					
					//$dialog = new Dialog(lang("delete_okay", "Okay"), lang("successful_saved"));
					//$dialog->close(2);
					//$response->exec($dialog);
					$response->exec('complex_table_field_'.$this->ID().'.table.find("tbody").remove();
					complex_table_field_'.$this->ID().'.table.append("'.convert::raw2js($this->updateBody($this->Search, $this->orderby)).'");');
					return $response->render();
				} else {
					return false;
				}
			}
		}
		
		/**
		 * add
		 *
		 *@name add
		 *@access public
		*/
		public function add() {

			$data = new $this->dataset->RecordClass();
			
			if(!$data->canWrite($data))
			{
					return $GLOBALS["lang"]["less_rights"];
			}
			
			
			$controller = $data->controller();
			$this->editcontroller = $controller;
			$this->model_inst = $data;
			
			$form = new Form($this, "complex_edit_" . $this->class);
			// set submission
			$form->setSubmission($this->submission . "_add");
			
			// draw form
			$class = $data->getRecordClass();
			$inst = Object::instance($class);
			$inst->sync($data);
			$form->addValidator(new DataValidator($inst), "datavalidator");
			
			$form->result = $inst;
			
			if($this->formgeneration == "getForm") {
				$inst->getEditForm($form, array());
				$inst->callExtending('getEditForm', $form, $this);
			} else {
				call_user_func_array(array($inst, $this->formgeneration), array($form, array()));
				$inst->callExtending('getEditForm', $form, $this);
			}
			
			$dialog = new Dialog("", lang("add_record", "Add a Record..."));
			
			// we generate new actions
			$form->add(new HiddenField("dialogid", $dialog->key));
			$form->actions = array();
			$form->addAction(new AjaxSubmitButton("submit", lang("save", "save"), $this->submission . "_add"));
			$form->addAction(new Button("cancel", lang("cancel", "cancel"), $dialog->key . " . close();"));
			
			$this->callExtending("afterForm", $form);
			
			$dialog->content = $form->render();
			$response = new AjaxResponse();
			$response->exec($dialog);
			
			// clean up
			unset($dialog, $inst, $class);
			return $response->render();
		}
		/**
		 * result
		 *
		 *@name result
		 *@access public
		*/
		public function result() {
			return $this->dataset;
		}
		/**
		 * sets a state of a record
		 *
		 *@name updateCheckBox
		 *@access public
		*/ 
		public function updateCheckBox() {
			if(!$this->disabled)
				if($this->relation == "many_many") {
					$id = $this->getParam("id");
					$bool = $this->getParam("bool");
					$this->dataset->getRecord($id)->relation_bind();
					$this->saveDataset();
					return true;
				}
			return false;
		}
		/**
		 * sets a state of a record
		 *
		 *@name updateCheckBox
		 *@access public
		*/ 
		public function updateRadioBox() {
			if(!$this->disabled)
				if($this->relation == "has_one") {
					$id = $this->getParam("id");
					foreach($this->dataset->wholedata as $position => $data) {
						$this->dataset->wholedata[$position]["__bind_to_relation"] = false;
					}
					unset($position, $data);
					$this->dataset->getRecord($id)->relation_bind();
					
					$this->saveDataset();
					return true;
				}
			return false;
		}
}