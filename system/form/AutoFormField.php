<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@author Goma-Team
  * last modified: 31.08.2012
  * $Version 2.0.1
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class AutoFormField extends FormField {
	/**
	 * real field
	 *
	 *@name field
	 *@access public
	 *@var object
	*/
	public $field;
	
	/**
	 * do nothing here
	*/
	public function createNode() {
		return null;
	}
	
	/**
	 * calls setForm on the form-field of this class
	 *
	 *@name setForm
	 *@access public
	*/
	public function setForm(&$form) {
		parent::setForm($form);
		if(is_object($this->form()->result))
			$this->field = $this->form()->result->doObject($this->name)->formField($this->title);
		else
			$this->field = $this->form()->controller->modelInst()->doObject($this->name)->formField($this->name);
			
		$this->field->setForm($form);
	}
	
	/**
	 * generates a field
	 *
	 *@name field
	 *@access public
	*/
	public function field() {
		return $this->field->field();
	}
}