<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 01.11.2012
  * $Version - 1.0
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldFilterHeader implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider, TableField_ColumnProvider {
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		$forTemplate = new ViewAccessableData();
		$fields = new DataSet();
		
		$state = $tableField->state->tableFieldFilterHeader;
		$filterArguments = $state->columns->toArray();
		$columns = $tableField->getColumns();
		$currentColumn = 0;
		
		if($state->visible !== true) {
			return null;
		}
		
		foreach($columns as $columnField) {
			$currentColumn++;
			$metadata = $tableField->getColumnMetadata($columnField);
			$title = $metadata['title'];
			
			if($title && $tableField->getData()->canFilterBy($columnField)) {
				$value = '';
				if(isset($filterArguments[$columnField])) {
					$value = $filterArguments[$columnField];
				}
				$f = new TextField('filter['.$columnField.']', '', $value);
				$f->addExtraClass('tablefield-filter');
				$f->addExtraClass('no-change-track');

				$f->input->attr('placeholder', lang("form_tablefield.filterBy") . $title);
				
				if($value != "") {
					$raction = new TableField_FormAction($tableField, "resetFields" . $columnField, lang("form_tablefield.reset"), "resetFields", null);
					$raction->addExtraClass("tablefield-button-resetFields");
					$raction->addExtraClass("no-change-track");
					
					$field = new FieldSet($columnField . "_sortActions", array(
						$f,
						$raction
					));
				} else {
					$field = $f;
				}
			} else {
				if($currentColumn == count($columns)){
					$raction = new TableField_FormAction($tableField, "reset" . $columnField, lang("form_tablefield.reset"), "reset", null);
					$raction->addExtraClass("tablefield-button-reset");
					$raction->addExtraClass("no-change-track");
					
					$action = new TableField_FormAction($tableField, "filter" . $columnField, lang("search"), "filter", null);
					$action->addExtraClass("tablefield-button-filter");
					$action->addExtraClass("no-change-track");
					
					$field = new FieldSet($columnField . "_sortActions", array(
						$action,
						$raction
					));
				} else {
					$field = new HTMLField("", "");
				}
			}
			$field->setForm($tableField->Form());

			$fields->push(array("field" => $field->field(), "name" => $columnField, "title" => $title));
		}

		return array(
			'header' => $forTemplate->customise(array("fields" => $fields))->renderWith("form/tableField/filterHeader.html")
		);
	}
	
	/**
	 * manipulates the dataobjectset
	 *
	 *@name manipulate
	*/
	public function manipulate($tableField, $data) {
		$state = $tableField->state->tableFieldFilterHeader;
		if($state->visible !== true) {
			return $data;
		}
		if(!$state->reset)
			$this->handleAction($tableField, "filter", array(), $_POST);
		else
			$state->reset = false;
		$state = $tableField->state->tableFieldFilterHeader;
		
		if(!isset($state->columns)) {
			return $data;
		} 
		
		$filterArguments = $state->columns->toArray();
		foreach($filterArguments as $columnName => $value ) {
			if($data->canFilterBy($columnName) && $value) {
				$data->AddFilter(array($columnName => array("LIKE", "%" . $value . "%")));
			}
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
		return array("filter", "reset", "resetFields", "toggleFilterVisibility");
	}
	
	public function handleAction($tableField, $actionName, $arguments, $data) {
		$state = $tableField->state->tableFieldFilterHeader;
		if($actionName === 'filter') {
			if(isset($data['filter'])){
				foreach($data['filter'] as $key => $filter ){
					$state->columns->$key = $filter;
				}
			}
		} else if($actionName === 'reset') {
			$state->columns = null;
			$state->reset = true;
			$state->visible = false;
		} else if($actionName === "resetfields") {
			$state->columns = null;
			$state->reset = true;
		} else if($actionName === "togglefiltervisibility") {
			if($state->visible === true) {
				$state->visible = false;
			} else {
				$state->visible = true;
			}
		}
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
		return array("Actions");
	}
	
	/**
	 * this shouldn't do anything
	*/
	public function getColumnContent($tableField, $record, $columnName) {
		return null;
	}
}