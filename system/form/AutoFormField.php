<?php
defined("IN_GOMA") OR die();

/**
 * An auto form field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.0.1
 */
class AutoFormField extends FormField {
	/**
	 * real field
	 *
	 *@name field
	 *@access public
	 *@var gObject
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
			$this->field = $this->form()->model->doObject($this->name)->formField($this->name);

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
