<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  * http://silverstripe.org
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
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
	 *@name provideFragments
	*/
	public function provideFragments($tableField);
}

interface TableField_DataManipulator extends TableFieldComponent {
	/**
	 * manipulates the dataobjectset
	 *
	 *@name manipulate
	*/
	public function manipulate($tableField, $data);
}

interface TableField_ColumnProvider extends TableFieldComponent {
	/**
	 * add columns in the order you want to have them in the table
	 * you have full control over all the columns through the reference of $columns
	 *
	 *@name augmentColumns
	 *@access public
	*/
	public function augumentColumns($tableField, &$columns);
	
	/**
	 * similiar to augmentColumns, but with the difference that you just give back an unsorted list of all the columns you handle in this class
	 *
	 *@name getColumnsHandled
	 *@access public
	*/
	public function getColumnsHandled($tableField);
	
	/**
	 * returns the content of the given column to the given record
	 *
	 *@name getColumnContent
	 *@access public
	*/
	public function getColumnContent($tableField, $record, $columnName);
	
	/**
	 * returns the attributes of the given column to the given record
	 *
	 *@name getColumnAttributes
	 *@access public
	*/
	public function getColumnAttributes($tableField, $record, $columnName);
	
	/**
	 * returns the meta-data of the given column for all records
	 *
	 *@name getColumnMetadata
	 *@access public
	*/
	public function getColumnMetadata($tableField, $columnName);
}

interface TableField_URLHandler extends TableFieldComponent {
	/**
	 * provides url-handlers as in controller, but without any permissions-functionallity
	 *
	 * this is NOT namespaced, so please be unique
	 *
	 *@name getURLHandlers
	 *@access public
	*/
	public function getURLHandlers($tableField);
}

interface TableField_ActionProvider extends TableFieldComponent {
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField);
	public function handleAction($tableField, $actionName, $arguments, $data);
}