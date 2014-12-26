<?php defined("IN_GOMA") OR die();


/**
 * This is a simple searchable multiselect-dropdown.
 *
 * It supports the same as Select, but also Search, Pagination and multi-select for big data.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
 */
class MultiSelectDropDown extends DropDown
{
	/**
	 * multiple values are selectable
	 *
	 *@name multiselect
	 *@access protected
	*/
	protected $multiselect = true;
	
	/**
	 * sortable relationships.	
	*/
	public $sortable = false;
}