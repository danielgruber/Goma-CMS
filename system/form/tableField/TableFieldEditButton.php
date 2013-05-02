<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  * http://silverstripe.org
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 10.02.2013
  * $Version - 1.0.1
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldEditButton implements TableField_ColumnProvider, TableField_URLHandler, TableField_ActionProvider {
	
	/**
	 * Add a column 'Actions'
	 * 
	 * @param type $tableField
	 * @param array $columns 
	 */
	public function augmentColumns($tableField, &$columns) {
		if(!in_array('Actions', $columns))
			$columns[] = 'Actions';
	}
	
	/**
	 * Return any special attributes that will be used for the column
	 *
	 * @param GridField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 * @return array
	 */
	public function getColumnAttributes($tableField, $record, $columnName) {
		return array('class' => 'col-buttons');
	}
	
	/**
	 * Add the title 
	 * 
	 * @param TableField $tableField
	 * @param string $columnName
	 * @return array
	 */
	public function getColumnMetadata($tableField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}
	
	/**
	 * Which columns are handled by this component
	 * 
	 * @param type $tableField
	 * @return type 
	 */
	public function getColumnsHandled($tableField) {
		return array('Actions');
	}
	
	/**
	 *
	 * @param TableField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 * @return string - the HTML for the column 
	 */
	public function getColumnContent($tableField, $record, $columnName) {
		if(!$record->can("Write")){
			return;
		}
		
		$action = new TableField_FormAction($tableField, "editbtn_" . $record->ID, lang("edit"), "editbtn_redirect", array("id" => $record->ID));
		$action->addExtraClass("tablefield-editbutton");
		
		$data = new ViewAccessableData();
		$data->link = $tableField->externalURL() . "/editbtn/" . $record->ID . URLEND . "?redirect=" . urlencode(getRedirect());
		return $data->customise(array("field" => $action->field()))->renderWith("form/tableField/editButton.html");
	}
	
	/**
	 * provides url-handlers as in controller, but without any permissions-functionallity
	 *
	 * this is NOT namespaced, so please be unique
	 *
	 *@name getURLHandlers
	 *@access public
	*/
	public function getURLHandlers($tableField) {
		return array(
			'editbtn/$id' => "edit"
		);
	}
	
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField) {
		return array("editbtn_redirect");
	}
	
	/**
	 * handles the actions
	*/
	public function handleAction($tableField, $actionName, $arguments, $data) {
		if($actionName == "editbtn_redirect") {
			HTTPResponse::redirect($tableField->externalURL() . "/editbtn/" . $arguments["id"] . URLEND . "?redirect=" . urlencode(getRedirect()));
		}
		return false;
	}
	
	/**
	 * edit-action
	 *
	 *@name edit
	 *@access public
	*/
	public function edit($tableField, $request) {
		$data = clone $tableField->getData();
		$data->filter(array("id" => $request->getParam("id")));
		if($data->Count() > 0) {
			$title = lang("edit");
			if($data->title) {
				$title = $data->title;
			} else if($data->name) {
				$title = $data->name;
			}
			Core::setTitle($title);
			$tableField->form()->controller->request->post_params = $_POST;
			$content = $data->first()->controller($tableField->form()->controller)->edit();
		} else {
			$tableField->Form()->redirectToForm();
			exit;
		}
		
		$controller = $tableField->form()->controller;
		return $controller->serve($content);
	}
}
