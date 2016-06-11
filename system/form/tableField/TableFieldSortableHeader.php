<?php defined('IN_GOMA') OR die();

/**
 * Sortable Header.
 *
 * Inspiration by Silverstripe 3.0 GridField
 * http://silverstripe.org
 *
 * @package     Goma\Form\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
class TableFieldSortableHeader implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider {
	/**
	 * provides HTML-fragments
	 *
	 * @name provideFragments
	 * @param TableField $tableField
	 * @return array
	 */
	public function provideFragments($tableField) {

		Resources::add("font-awsome/font-awesome.css");

		$forTemplate = new ViewAccessableData();
		$fields = new DataSet();

		$state = $tableField->state->tableFieldSortableHeader;
		$columns = $tableField->getColumns();
		$currentColumn = 0;
		foreach($columns as $columnField) {
			$currentColumn++;
			$metadata = $tableField->getColumnMetadata($columnField);
			$title = $metadata['title'];

			// sortable column
			if($title && $tableField->getData()->canSortBy($columnField)) {

				$title = convert::raw2text($title);
				$nextDirection = "desc";
				if($state->sortColumn == $columnField){
					if($state->sortDirection == 'asc') {
						$title .= ' <i class="fa fa-caret-up"></i>';
					} else {
						$title .= ' <i class="fa fa-caret-down"></i>';
						$nextDirection = "asc";
					}
				} else {
					$title .= ' <i class="fa fa-sort"></i>';
				}

				$field = new TableField_FormAction($tableField, "SetOrder" . $columnField, $title, "sort" . $nextDirection, array("SortColumn" => $columnField));
				$field->addExtraClass("tablefield-sortable");
				$field->addClass("button-clear");

				// last column
			} else if($currentColumn == count($columns) && $tableField->getConfig()->getComponentByType('TableFieldFilterHeader')){
				$field = new TableField_FormAction($tableField, "toggleFilter", '<i title="' . lang("search") . '" class="fa fa-search"></i>', "toggleFilterVisibility", null,
						"var h  = $(this).parents('table').find('.filter-header');if(window['".$tableField->divID()."hasBeenOpened']) { h.addClass('hidden'); window['".$tableField->divID()."hasBeenOpened'] = false; return false; } else if (h.hasClass('hidden')) { window['".$tableField->divID()."hasBeenOpened'] = true; h.removeClass('hidden'); return false; }");
				$field->addExtraClass("tablefield-button-filter");
				$field->addExtraClass("trigger");
				$field->addClass("button-clear");

				// not sortable column
			} else {
				$field = new HTMLField($columnField, '<span class="non-sortable">' . $title . '</span>');
			}

			$fields->push(array("field" => $field->exportFieldInfo()->ToRestArray(true), "name" => $columnField, "title" => $title));
		}

		return array(
			"header" => $forTemplate->customise(array("fields" => $fields))->renderWith("form/tableField/sortableHeader.html")
		);
	}

	/**
	 * manipulates the dataobjectset
	 * @param TableField $tableField
	 * @param DataObjectSet|DataSet $data
	 * @return $this|DataObjectSet|DataSet
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
	 * @name getActions
	 * @access public
	 * @return array
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
