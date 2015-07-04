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
	 *@name createNode
	 *@access public
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
	 * renders the field
	 *@name field
	 *@access public
	 */
	public function field() {
		if(PROFILE)
			Profiler::mark("FormField::field");

		$this->callExtending("beforeField");

		$this->setValue();

		$this->container->append(new HTMLNode("label", array("for" => $this->ID()), $this->title));

		$this->container->append($this->input);

		$this->callExtending("afterField");

		if(PROFILE)
			Profiler::unmark("FormField::field");

		return $this->container;
	}

	/**
	 * returns the javascript for this field
	 *
	 *@name js
	 *@access public
	 */
	public function js() {

		Resources::add("system/libs/javascript/checkbox/gCheckBox.js", "js", "tpl");

		return '$(function(){
				var obj = $("#' . $this->ID() . '").gCheckBox();
			});';
	}

	/**
	 * the result of the field
	 *
	 *@name result
	 *@access public
	 */
	public function result() {
		if($this->disabled || $this->form()->disabled) {
			return $this->value;
		}

		return (isset($this->form()->post[$this->postname()]) && $this->form()->post[$this->postname()]) ? true : false;
	}

}
