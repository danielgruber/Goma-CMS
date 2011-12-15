<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 30.05.2011
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class AutoFormField extends FormField {
	/**
	 * real field
	 *
	 *@var object
	*/
	public $field;
	
	public function createNode() {
		return null;
	}
	public function setForm(&$form) {
		parent::setForm($form);
		if(is_object($this->form()->result))
			$this->field = $this->form()->result->doObject($this->name)->formField($this->title);
		else
			$this->field = $this->form()->controller->model_inst->doObject($this->name)->formField($this->name);
			
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