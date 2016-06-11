<?php
/**
 * inspiration by Silverstripe 3.0 GridField
 *
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
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
	 * @name provideFragments
	 * @return array
	 */
	public function provideFragments($tableField) {
		$state = $tableField->state->tableFieldPaginator;
		$page = is_int($state->page) ? $state->page : 1;
		$whole = $tableField->getData()->CountWholeSet();
		if($whole <= $this->perPage) {
			$page = 1;
			$state->page = 1;
		}
		$view = new DataSet();
		foreach($tableField->getData()->getPages() as $key => $data) {
			if($data["black"] == false) {
				$action = new TableField_FormAction($tableField, "setPage" . $data["page"], $data["page"], "setPage", array("page" => $data["page"]));
				$view->push(array("fieldbutton" => $action->exportFieldInfo()->ToRestArray(true)));
			} else {
				if($data["page"] == "...") {
					$view->push(array("field" => '<span class="left-out-pages">' . $data["page"] . '</span>'));
				} else {
					$view->push(array("field" => '<span class="active-page">' . $data["page"] . '</span>'));
				}
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
	 * @param $tableField
	 * @param DataObjectSet|DataSet $data
	 * @return DataObjectSet|DataSet
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
	 * @return array
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
