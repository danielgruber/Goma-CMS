<?php
defined("IN_GOMA") OR die();

/**
 * A simple check box.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1
 */
class CheckBox extends FormField {
	/**
	 * creates the node
	 *
	 * @name createNode
	 * @access public
	 * @return HTMLNode
	 */
	public function createNode() {
		$node = parent::createNode();
		$node->type = "checkbox";

		return $node;
	}

	/**
	 * @return array|bool|string|ViewAccessableData
	 */
	public function getModel()
	{
		if($this->POST) {
			if (!$this->isDisabled() && $this->getRequest()->post_params && !$this->parent->getFieldPost($this->PostName())) {
				return false;
			}
		}

		return parent::getModel();
	}

	/**
	 * sets the value
	 */
	public function setValue() {
		if(!!$this->getModel())
			$this->input->checked = "checked";

		$this->input->value = 1;

	}

	public function addRenderData($info, $notifyField = true)
	{
		parent::addRenderData($info, $notifyField);

		$info->addJSFile("system/libs/javascript/checkbox/gCheckBox.js");
		$info->addJSFile("system/form/checkboxForm.js");
	}

	/**
	 * returns the javascript for this field
	 * @return string
	 */
	public function js() {
		return 'form_initCheckbox(field, field.id);';
	}

	/**
	 * the result of the field
	 *
	 * @return bool
	 */
	public function result() {
		return !!parent::result();
	}

}
