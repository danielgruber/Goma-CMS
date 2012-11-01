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

class TableFieldFilterHeader implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider {
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
		
		foreach($columns as $columnField) {
			$currentColumn++;
			$metadata = $tableField->getColumnMetadata($columnField);
			$title = $metadata['title'];
		
			
			if($title && $tableField->getData()->canFilterBy($columnField)) {
				$value = '';
				if(isset($filterArguments[$columnField])) {
					$value = $filterArguments[$columnField];
				}
				$field = new TextField('filter['.$columnField.']', '', $value);
				$field->addExtraClass('tablefield-filter');
				$field->addExtraClass('no-change-track');

				$field->input->attr('placeholder', lang("form_tablefield.filterBy") . $title);
				
				$action = new TableField_FormAction($tableField, "reset" . $columnField, lang("form_tablefield.reset"), "reset", null);
				$action->addExtraClass("tablefield-button-reset");
				$action->addExtraClass("no-change-track");
				
				$field = new FieldSet($columnField . "_sortActions", array(
					$field,
					$action
				));
			} else {
				/*if($currentColumn == count($columns)){
					$field = new FieldGroup(
						GridField_FormAction::create($gridField, 'filter', false, 'filter', null)
							->addExtraClass('ss-gridfield-button-filter')
							->setAttribute('title', _t('GridField.Filter', "Filter"))
							->setAttribute('id', 'action_filter_' . $gridField->getModelClass() . '_' . $columnField),
						GridField_FormAction::create($gridField, 'reset', false, 'reset', null)
							->addExtraClass('ss-gridfield-button-close')
							->setAttribute('title', _t('GridField.ResetFilter', "Reset"))
							->setAttribute('id', 'action_reset_' . $gridField->getModelClass() . '_' . $columnField)
					);
					$field->addExtraClass('filter-buttons');
					$field->addExtraClass('no-change-track');
				}else{
					$field = new LiteralField('', '');
				}*/
			}

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
		if(!isset($state->columns)) {
			return $data;
		} 
		
		$filterArguments = $state->columns->toArray();
		foreach($filterArguments as $columnName => $value ) {
			if($data->canFilterBy($columnName) && $value) {
				$data->AddFilter(array($columnName => array("LIKE", $value)));
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
		return array("filter", "reset");
	}
	
	public function handleAction($tableField, $actionName, $arguments, $data) {
		$state = $tableField->state->tableFieldFilterHeader;
		if($actionName === 'filter') {
			if(isset($data['filter'])){
				foreach($data['filter'] as $key => $filter ){
					$state->columns->$key = $filter;
				}
			}
		} elseif($actionName === 'reset') {
			$state->columns = null;
		}
	}
}