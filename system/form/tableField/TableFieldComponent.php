<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  * http://silverstripe.org
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 24.08.2012
  * $Version - 1.0
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 * base-class for all classes, which can be added to tablefield 
*/
interface TableFieldComponent {
	
}

interface TableField_HTMLProvider extends TableFieldComponent {
	/**
	 * provides HTML-fragments
	 *
	 * @param TableField $tableField
	 * @return array
	 */
	public function provideFragments($tableField);
}

interface TableField_DataManipulator extends TableFieldComponent {
	/**
	 * manipulates the dataobjectset
	 * @param TableField $tableField
	 * @param DataSet|DataObjectSet $data
	 * @return
	 */
	public function manipulate($tableField, $data);
}

interface TableField_ColumnProvider extends TableFieldComponent {
	/**
	 * add columns in the order you want to have them in the table
	 * you have full control over all the columns through the reference of $columns
	 * @param TableField $tableField
	 * @param array $columns
	 * @return
	 */
	public function augmentColumns($tableField, &$columns);
	
	/**
	 * similiar to augmentColumns, but with the difference that you just give back an unsorted list of all the columns you handle in this class
	 *
	 * @param TableField $tableField
	*/
	public function getColumnsHandled($tableField);

	/**
	 * returns the content of the given column to the given record
	 * @param TableField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 * @return
	 */
	public function getColumnContent($tableField, $record, $columnName);

	/**
	 * returns the attributes of the given column to the given record
	 * @param TableField $tableField
	 * @param string $columnName
	 * @param DataObject $record
	 * @return
	 */
	public function getColumnAttributes($tableField, $columnName, $record);

	/**
	 * returns the meta-data of the given column for all records
	 * @param TableField $tableField
	 * @param string $columnName
	 * @return
	 */
	public function getColumnMetadata($tableField, $columnName);
}

interface TableField_URLHandler extends TableFieldComponent {
	/**
	 * provides url-handlers as in controller, but without any permissions-functionallity
	 *
	 * this is NOT namespaced, so please be unique
	 * @param TableField $tableField
	 * @return
	 */
	public function getURLHandlers($tableField);
}

interface TableField_ActionProvider extends TableFieldComponent {
	/**
	 * provide some actions of this tablefield
	 * @param TableField $tableField
	 * @return
	 */
	public function getActions($tableField);

	/**
	 * @param TableField $tableField
	 * @param string $actionName
	 * @param array $arguments
	 * @param DatAObject $data
	 * @return mixed
	 */
	public function handleAction($tableField, $actionName, $arguments, $data);
}