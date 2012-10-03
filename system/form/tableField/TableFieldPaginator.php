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

class TableFieldPaginator implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider {
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		
	}
	
	/**
	 * manipulates the dataobjectset
	 *
	 *@name manipulate
	*/
	public function manipulate($tableField, $data) {
		return $data;
	}
	
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField) {
		
	}
	public function handleAction($tableField, $actionName, $arguments, $data) {
		
	}
}