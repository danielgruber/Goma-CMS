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
	 * sets the value
	 *@name setValue
	 *@access public
	 */
	public function setValue() {

		if(isset($_POST["form_submit_" . $this->form()->name()]) && $this->POST && isset($_POST[$this->postname()])) {
			$this->value = 1;
		} else if(isset($_POST["form_submit_" . $this->form()->name()])) {
			$this->value = 0;
		}

		if($this->value)
			$this->input->checked = "checked";

		$this->input->value = 1;

	}

	/**
	 * returns the javascript for this field
	 *
	 * @name js
	 * @access public
	 * @return string
	 */
	public function js() {

		Resources::add("system/libs/javascript/checkbox/gCheckBox.js", "js", "tpl");

		return 'var obj = $("#' . $this->ID() . '").gCheckBox();';
	}

	/**
	 * the result of the field
	 *
	 * @name result
	 * @access public
	 * @return bool
	 */
	public function result() {
		return !!parent::result();
	}

}
