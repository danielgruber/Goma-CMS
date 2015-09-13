<?php defined("IN_GOMA") OR die();

/**
 * Table-Field plugin to add a new column with checkboxes.
 *
 * @package     Goma\Form-Framework\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
class TableFieldCheckable implements TableField_ColumnProvider {

	/**
	 * session-prefix.
	 */
	const SESSION_PREFIX = "tablefield_check";

	/**
	 * returns the currently checked values by name of tablefield.
	 *
	 * @param string $checkName
	 * @return array
	 */
	public static function getChecked($checkName = "check") {
		self::updateSession($checkName);

		return GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . $checkName) ?: array();
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
			GlobalSessionManager::globalSession()->remove(self::SESSION_PREFIX . "." . $name);
		}

		if(isset($_POST[$name . "_check"])) {
			$sessionData = self::getSessionDataForKey($name);
			foreach($_POST[$name . "_check"] as $k => $v) {
				if(isset($_POST[$name][$k])) {
					$sessionData[$k] = true;
				} else {
					unset($sessionData[$k]);
				}
			}

			GlobalSessionManager::globalSession()->set(self::SESSION_PREFIX . "." . $name, $sessionData);
		}
	}

	/**
	 * returns session-data for key.
	 *
	 * @param string $name
	 * @return array
	 */
	protected static function getSessionDataForKey($name) {
		if(GlobalSessionManager::globalSession()->hasKey(self::SESSION_PREFIX . "." . $name)) {
			return GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . $name);
		}

		return array();
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
	public function getColumnAttributes($tableField, $columnName, $record) {
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

		$sessionData = self::getSessionDataForKey($this->checkname);
		$data->checked = isset($sessionData[$record->ID]);

		return $data->renderWith("form/tableField/checkbox.html");
	}
}
