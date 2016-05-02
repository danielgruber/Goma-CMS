<?php
/**
 * inspiration by Silverstripe 3.0 GridField
 * http://silverstripe.org
 *
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 25.07.2014
 * $Version - 1.1
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldEditButton implements TableField_ColumnProvider, TableField_URLHandler {

	/**
	 * constructor.
	 */
	public function __construct($title = null, $requirePerm = null) {
		if(!isset($requirePerm)) {
			$requirePerm = "write";
		}

		if(!isset($title)) {
			$title = lang("edit");
		}

		$this->title = $title;

		$this->requirePerm = $requirePerm;
	}

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
	public function getColumnAttributes($tableField, $columnName, $record) {
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
		if($this->requirePerm) {
			if(is_callable($this->requirePerm)) {
				if(!call_user_func_array($this->requirePerm, array($tableField, $record)))
					return;
			} else if(!$record->can($this->requirePerm)){
				return;
			}
		}

		$data = new ViewAccessableData();
		return $data->customise(array("title" => $this->title, "link" => $tableField->externalURL() . "/editbtn/" . $record->ID . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"])))->renderWith("form/tableField/editButton.html");
	}

	/**
	 * provides url-handlers as in controller, but without any permissions-functionallity
	 *
	 * this is NOT namespaced, so please be unique
	 *
	 * @name getURLHandlers
	 * @access public
	 * @return array
	 */
	public function getURLHandlers($tableField) {
		return array(
			'editbtn/$id' => "edit"
		);
	}

	/**
	 * edit-action
	 *
	 * @param TableField $tableField
	 * @param Request $request
	 * @return string
	 */
	public function edit($tableField, $request) {
		$data = clone $tableField->getData();
		$data->filter(array("id" => $request->getParam("id")));
		if($data->Count() > 0) {
			$title = $this->title;
			if($data->title) {
				$title = $data->title;
			} else if($data->name) {
				$title = $data->name;
			}
			Core::setTitle($title);
			if($tableField->form()->getRequest())
				$tableField->form()->getRequest()->post_params = $_POST;

			$controller = $tableField->form()->controller;
			if(is_a($controller, "controller")) {
				/** @var Controller $controller */
				$content = $controller->getWithModel($data->first())->edit();
			} else {
				$content = ControllerResolver::instanceForModel($data->first())->edit();
			}
		} else {
			$tableField->Form()->redirectToForm();
			exit;
		}

		return $content;
	}
}
