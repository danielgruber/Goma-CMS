<?php
/**
 * Table-Field plugin to add a new column with checkboxes.
 *
 * @package     Goma\Form-Framework\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0.1
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldCheckable implements TableField_ColumnProvider {
	
	/**
	 * returns the currently checked values by name of tablefield.
	*/
	public static function getChecked($checkName = "check") {
		self::updateSession($checkName);
		return isset($_SESSION["tablefield"]["check_" . $checkName]) ? $_SESSION["tablefield"]["check_" . $checkName] : array();
	}
	/**
	 * You can define the name of the Checkboxes here.
	 *
	 * @param String $name name of the checkboxes
	*/
	public function __construct($name = "check") {
		$this->checkname = $name;
		self::updateSession($name);
	}
	
	/**
	 * updates the session-data.
	*/
	public static function updateSession($name) {
		if(!isset($_POST[$name . "_check"])) {
			unset($_SESSION["tablefield"]["check_" . $name]);
		}
		
		if(isset($_POST[$name . "_check"])) {
			isset($_SESSION["tablefield"]["check_" . $name]) OR $_SESSION["tablefield"]["check_" . $name] = array();
			foreach($_POST[$name . "_check"] as $k => $v) {
				if(isset($_POST[$name][$k])) {
					$_SESSION["tablefield"]["check_" . $name][$k] = true;
				} else {
					unset($_SESSION["tablefield"]["check_" . $name][$k]);
				}
			}
		}
	}
	
	/**
	 * Add a column 'Check'.
	 * 
	 * @param TableField $tableField
	 * @param array $columns 
	 */
	public function augmentColumns($tableField, &$columns) {
		if(!in_array('Check', $columns))
			array_unshift($columns, 'Check');
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
		return array('class' => 'col-checkboxes');
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
		if($columnName == 'Check') {
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
		return array('Check');
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
	
		$data = new ViewAccessableData();
		$data->id = $record->id;
		$data->name = $this->checkname;
		$data->checked = isset($_SESSION["tablefield"]["check_" . $this->checkname][$record->ID]);
		
		
		return $data->renderWith("form/tableField/checkbox.html");
	}
}