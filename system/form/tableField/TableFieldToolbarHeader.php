<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 25.10.2012
  * $Version - 1.0
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldToolbarHeader implements TableField_HTMLProvider {
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		$view = new ViewAccessableData();
		$view->customise(array("title" => $tableField->title, "ColumnCount" => $tableField->getColumnCount()));
		return array(
			'header' => $view->renderWith("form/tableField/toolbarHeader.html")
		);
	}
}