<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 19.02.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class SortableTableView extends TableView {
	public $pages = true;
	public $perPage = 20;
	
	/**
	 * this is the template for tableview
	*/
	public $template = "admin/sortableTableview.html";

	/**
	 * actions 
	 *
	 *@name actions
	 *@access public
	*/
	public $actions = array(
		"edit"		=> '<img src="images/icons/fatcow-icons//16x16/edit.png" alt="{$_lang_edit}" title="{$_lang_edit}" />',
		"delete"	=> '<img src="images/icons/fatcow-icons/16x16/delete.png" alt="{$_lang_delete}" title="{$_lang_delete}" />',
		"add"		=> array("{\$_lang_add_data}")
	);
	
	/**
	 * fields
	*/
	public $fields = array();
	
	/**
	 * url-handlers
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		"deletemany"	=> "deletemany",
		"saveSort"		=> "saveSort"
	);
	
	/**
	 * sort-field
	 *
	 *@name sort_field
	 *@access public
	*/
	public $sort_field = "";
	
	/**
	 * allowed actions
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array(
		"deletemany",
		"saveSort"
	);	
	/**
	 * this action will be called if no other action was found
	 *
	 *@name index
	 *@access public
	*/
	public function index()
	{
			$globalactions = array();
			$actions = array();
			$fields = array();
			
			foreach($this->actions as $name => $data) {
				if(is_array($data)) {
					$globalactions[] = array(
						"url" 	=> $this->url() . $name,
						"title"	=> parse_lang($data[0])
					);
				} else {
					array_push($actions, array(
						"url" 	=> $this->url() . $name,
						"title"	=> parse_lang($data)
					));
				}
			}
			
			foreach($this->fields as $name => $title) {
				array_push($fields,  array("name" => $name, "title" => parse_lang($title)));	
			}
			
			$this->modelInst()->sort($this->sort_field, "ASC");
			
			$_SESSION["deletekey"][$this->class] = randomString(10);
			
			Resources::addData("var adminURI = ".var_export($this->namespace, true).";");
			
			return $this->model_inst->customise(array("datafields" => $fields,  "action" => $actions, "globalaction" => $globalactions), array_merge(array("deletekey" => $_SESSION["deletekey"][$this->class], "deletable" => isset($this->actions["delete"])), $this->tplVars))->renderWith($this->template);
	}
	
	/**
	 * saves the sort
	 *
	 *@name saveSort
	 *@access public
	*/
	public function saveSort() {
		if(isset($_POST["sort_item"])) {
			$field = $this->sort_field;
			foreach($_POST["sort_item"] as $key => $value) {
				$key += isset($_GET["pa"]) ? ($_GET["pa"] - 1) * $this->perPage : 0;
				DataObject::update($this->models[0], array($field => $key), array("recordid" => $value));
			}
		}
		HTTPResponse::output(1);
		exit;
	}
	
	/**
	 * checks if the user is allowed to call this action
	 *
	 *@name checkPermissions
	 *@access public
	 *@param string - name of the action
	*/
	public function checkPermission($action) {
		
		if(isset($this->actions[$action])) {
			return true;
		}
		return parent::checkPermission($action);
	}
	
	/**
	 * deletes some of the data
	 *
	 *@name deleteMany
	 *@access public
	*/
	public function deleteMany() {
		if(isset($_SESSION["deletekey"][$this->class]) && $_SESSION["deletekey"][$this->class] == $_POST["deletekey"]) {
			$data = $_POST["data"];
			unset($data["all"]);
			foreach($data as $key => $value) {
				DataObject::get($this->model_inst, array("id" => $key))->remove();
			}
			$this->redirectBack();
		}
	}
}