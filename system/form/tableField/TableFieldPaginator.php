<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 31.08.2012
  * $Version - 1.0
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldPaginator implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider {
	/**
	 * stores the var, which indicates how much records per page
	 *
	 *@name perPage
	 *@access public
	*/
	public $perPage;
	
	/**
	 * give as parameter records per page
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($perPage = null) {
		if(!isset($perPage)) {
			$perPage = 10;
		}
		$this->perPage = $perPage;
	}
	
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		$state = $tableField->state->tableFieldPaginator;
		$page = is_int($state->page) ? $state->page : 1;
		$whole = $tableField->getData()->Count();
		if($whole <= $this->perPage) {
			$page = 1;
			$state->page = 1;
		}
		$view = new DataSet();
		foreach($tableField->getData()->getPages() as $key => $data) {
			if(!$data["black"]) {
				$action = new TableField_FormAction($tableField, "setPage" . $data["page"], $data["page"], "setPage", array("page" => $data["page"]));
				$view->push(array("field" => $action->field()));
			} else {
				$view->push(array("field" => $data["page"]));
			}
		}
		
		$showStart = $page * $this->perPage - $this->perPage + 1;
		$showEnd = $page * $this->perPage;
		if($showEnd > $whole) {
			$showEnd = $whole;
		}
		
		return array(
			"footer" => $view->customise(array("ColumnCount" => $tableField->getColumnCount(), "showStart" => $showStart, "showEnd" => $showEnd, "whole" => $whole))->renderWith("form/tableField/pagination.html")
		);
	}
	
	/**
	 * manipulates the dataobjectset
	 *
	 *@name manipulate
	*/
	public function manipulate($tableField, $data) {
		$state = $tableField->state->tableFieldPaginator;
		if(is_int($state->page)) {
			$data->activatePagination($state->page, $this->perPage);
		} else {
			$data->activatePagination(1, $this->perPage);
		}
		return $data;
	}
	
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField) {
		return array("setPage");
	}
	public function handleAction($tableField, $actionName, $arguments, $data) {
		$state = $tableField->state->tableFieldPaginator;
		if($actionName == "setpage") {
			$state->page = $arguments["page"];
		}
	}
}