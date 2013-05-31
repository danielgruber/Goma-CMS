<?php
/**
 * Table-Field plugin to create a link in the action-column with custom HTML between the a-tags.
 *
 * @package     Goma\Form-Framework\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldActionLink implements TableField_ColumnProvider {
	/**
	 * Constructor.
	 *
	 * @param   string $destination link-URL with params replaced by data of record
	 * @param   string $inner HTML between a-tags
	 * @param	mixed $requirePerm how to check if permissions is required (callback, string, boolean)
	 */
	public function __construct($destination, $inner, $requirePerm) {
		$this->destination = $destination;
		$this->inner = $inner;
		$this->requirePerm = $requirePerm;
	}
	
	
	/**
	 * Add a column 'Actions'.
	 * 
	 * @param TableField $tableField
	 * @param array $columns 
	 */
	public function augmentColumns($tableField, &$columns) {
		if(!in_array('Actions', $columns))
			$columns[] = 'Actions';
	}
	
	/**
	 * Return any special attributes that will be used for the column.
	 *
	 * @param GridField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 *
	 * @return array
	 */
	public function getColumnAttributes($tableField, $record, $columnName) {
		return array('class' => 'col-buttons');
	}
	
	/**
	 * Add the title.
	 * 
	 * @param TableField $tableField
	 * @param string $columnName
	 *
	 * @return array
	 */
	public function getColumnMetadata($tableField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}
	
	/**
	 * Which columns are handled by this component.
	 * 
	 * @param type $tableField
	 *
	 * @return type 
	 */
	public function getColumnsHandled($tableField) {
		return array('Actions');
	}
	
	/**
	 * generates the content of the column "Actions".
	 *
	 * @param TableField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 *
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
		
		$data = $record;
		
		$format = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("TableFieldDataColumns", "convert_vars"), $this->inner);
		$format = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("TableFieldDataColumns", "vars"), $format);
		eval('$value = "' . $format . '";');

		$data = new ViewAccessableData();
		$data->destination = $this->destination;
		$data->inner = $value;
		
		return $data->renderWith("form/tableField/actionLink.html");
	}
}