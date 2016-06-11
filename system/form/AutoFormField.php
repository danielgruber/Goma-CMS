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
	 * @var AbstractFormComponent
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
	 * @param AbstractFormComponentWithChildren $form
	 * @return $this
	 */
	public function setForm(&$form) {
		parent::setForm($form);
		if(is_object($this->form()->model))
			$this->field = $this->form()->model->doObject($this->name)->formField($this->title);
		else
			$this->field = $this->form()->getModel()->doObject($this->name)->formField($this->name);

		$this->field->setForm($form);

		return $this;
	}

	/**
	 * generates a field
	 * @param FormFieldRenderData $info
	 * @return HTMLNode
	 */
	public function field($info) {
		return $this->field->field($info);
	}

}
