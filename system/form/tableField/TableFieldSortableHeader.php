<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 10.10.2012
  * $Version - 1.0
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldSortableHeader implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider {
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		$forTemplate = new ViewAccessableData();
		$fields = new DataSet();
		
		$state = $tableField->state->tableFieldSortableHeader;
		$columns = $tableField->getColumns();
		$currentColumn = 0;
		foreach($columns as $columnField) {
			$currentColumn++;
			$metadata = $tableField->getColumnMetadata($columnField);
			$title = $metadata['title'];
			if($title && $tableField->getData()->canSortBy($columnField)) {
				$dir = 'asc';
				if($state->sortColumn == $columnField && $state->sortDirection == 'asc') {
					$dir = 'desc';
				}
				
				$field = new TableField_FormAction($tableField, "SetOrder" . $columnField, $title, "sort" . $dir, array("SortColumn" => $columnField));
				
				$field->addExtraClass("tablefield-sortable");

				if($state->sortColumn == $columnField){
					$field->addExtraClass('tablefield-sorted');

					if($state->sortDirection == 'asc')
						$field->addExtraClass('tablefield-sorted-asc');
					else
						$field->addExtraClass('tablefield-sorted-desc');
				}
			} else {
				if($currentColumn == count($columns) && $tableField->getConfig()->getComponentByType('TableFieldFilterHeader')){
					$field = new TableField_FormAction($tableField, "toggleFilter", "", "toggleFilterVisibility", null);
					$field->addExtraClass("tablefield-button-filter");
					$field->addExtraClass("trigger");		
				} else {
					$field = new HTMLField($columnField, '<span class="non-sortable">' . $title . '</span>');
				}
			}
			$fields->push(array("field" => $field->field(), "name" => $columnField, "title" => $title));
		}
		
		return array(
			"header" => $forTemplate->customise(array("fields" => $fields))->renderWith("form/tableField/sortableHeader.html")
		);
	}
	
	/**
	 * manipulates the dataobjectset
	 *
	 *@name manipulate
	*/
	public function manipulate($tableField, $data) {
		$state = $tableField->state->tableFieldSortableHeader;
		
		if ($state->sortColumn == "") {
			return $data;
		}
		
		return $data->sort($state->sortColumn, $state->sortDirection);
	}
	
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField) {
		return array("sortasc", "sortdesc");
	}
	
	/**
	 * handles the actions, so it pushes the states
	 *
	 *@name handleAction
	 *@access public
	*/
	public function handleAction($tableField, $actionName, $arguments, $data) {
		$state = $tableField->state->tableFieldSortableHeader;
		
		switch($actionName) {
			case 'sortasc':
				$state->sortColumn = $arguments['SortColumn'];
				$state->sortDirection = 'asc';
				break;

			case 'sortdesc':
				$state->sortColumn = $arguments['SortColumn'];
				$state->sortDirection = 'desc';
				break;
		}
	}
}