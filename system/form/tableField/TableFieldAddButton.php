<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 -  2014 Goma-Team
  * last modified: 02.11.2014
  * $Version 1.0.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldAddButton implements TableField_HTMLProvider, TableField_URLHandler {
	
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		if(!$tableField->getData()->dataobject->can("Write")){
			return;
		}
		
		$view = new ViewAccessableData();
		if($tableField->getConfig()->getComponentByType('TableFieldPaginator')) {
			return array(
				"pagination-footer-right" => $view->customise(array("link" => $tableField->externalURL() . "/addbtn" . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"])))->renderWith("form/tableField/addButton.html")
			);
		} else {
			return array("footer" => $view->customise(array("link" => $tableField->externalURL() . "/addbtn" . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"])))->renderWith("form/tableField/addButtonWithFooter.html"));
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
	 * edit-action
	 *
	 *@name edit
	 *@access public
	*/
	public function add($tableField, $request) {
		$obj = $tableField->getData();
		$tableField->form()->controller->request->post_params = $_POST;

		$submit = $tableField->form()->useStateData ? "submit_form" : "publish";

		$content = $obj->controller($tableField->form()->controller)->form("add", null, array(), false, $submit);

		Core::setTitle(lang("add_record"));
		
		$controller = $tableField->form()->controller;
		return $controller->serve($content);
	}
}
