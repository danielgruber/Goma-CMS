<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableView extends AdminItem {
	public $pages = true;
	public $perPage = 20;
	/**
	 * this is the template for tableview
	*/
	public $template = "admin/tableview.html";
	/**
	 * this 
	*/
	public $actions = array(
		"edit"		=> '<img src="images/icons/fatcow-icons//16x16/edit.png" alt="{$_lang_edit}" title="{$_lang_edit}" />',
		"delete"	=> '<img src="images/icons/fatcow-icons/16x16/delete.png" alt="{$_lang_delete}" title="{$_lang_delete}" />',
		"add"		=> array("{\$_lang_add_data}")
	);
	public $fields = array();
	public $url_handlers = array(
		"deletemany"	=> "deletemany"
	);
	public $allowed_actions = array(
		"deletemany"
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
				if(isset($_GET["order"]) && $_GET["order"] == $name && isset($_GET["ordertype"]) && $_GET["ordertype"] == "desc")  {
					array_push($fields,  array("name" => $name, "title" => parse_lang($title), "order" => true, "orderdesc" => true));
				} else if(isset($_GET["order"]) && $_GET["order"] == $name) {
					array_push($fields,  array("name" => $name, "title" => parse_lang($title), "order" => true));
				} else {
					array_push($fields,  array("name" => $name, "title" => parse_lang($title)));	
				}
				
			}
			
			if(isset($_GET["order"]) && isset($_GET["ordertype"]) && $_GET["ordertype"] == "desc") {
				if(isset($this->fields[$_GET["order"]]))
					$this->model_inst->orderby($_GET["order"], "desc");
			} else if(isset($_GET["order"])) {
				if(isset($this->fields[$_GET["order"]]))
					$this->model_inst->orderby($_GET["order"], "asc");
			}
			
			$_SESSION["deletekey"][$this->class] = randomString(10);

			return $this->model_inst->customise(array("datafields" => $fields,  "action" => $actions, "globalaction" => $globalactions), array_merge(array("deletekey" => $_SESSION["deletekey"][$this->class], "deletable" => isset($this->actions["delete"])), $this->tplVars))->renderWith($this->template);
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