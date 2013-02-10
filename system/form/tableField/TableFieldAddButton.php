<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 -  2013 Goma-Team
  * last modified: 10.02.2013
  * $Version 1.0.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldAddButton implements TableField_HTMLProvider, TableField_ActionProvider, TableField_URLHandler {
	
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		if(!$tableField->getData()->dataobject->can("Write")){
			return;
		}
		
		$action = new TableField_FormAction($tableField, "addbtn", lang("add_record"), "addbtn_redirect");
		$view = new ViewAccessableData();
		if($tableField->getConfig()->getComponentByType('TableFieldPaginator')) {
			return array(
				"pagination-footer-right" => $view->customise(array("field" => $action->field()))->renderWith("form/tableField/addButton.html")
			);
		} else {
			return array("footer" => $view->customise(array("field" => $action->field()))->renderWith("form/tableField/addButtonWithFooter.html"));
		}
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
			'addbtn' => "add"
		);
	}
	
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField) {
		return array("addbtn_redirect");
	}
	
	/**
	 * handles the actions
	*/
	public function handleAction($tableField, $actionName, $arguments, $data) {
		if($actionName == "addbtn_redirect") {
			HTTPResponse::redirect($tableField->externalURL() . "/addbtn" . URLEND . "?redirect=" . urlencode(getRedirect()));
		}
		return false;
	}
	
	/**
	 * edit-action
	 *
	 *@name edit
	 *@access public
	*/
	public function add($tableField, $request) {
		$class = $tableField->getData()->dataClass;
		$obj = new $class;
		$tableField->form()->controller->request->post_params = $_POST;
		$content = $obj->controller($tableField->form()->controller)->form("add");
		
		Core::setTitle(lang("add_record"));
		
		$controller = $tableField->form()->controller;
		return $controller->serve($content);
	}
}