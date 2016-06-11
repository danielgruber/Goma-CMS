<?php
defined("IN_GOMA") OR die();

/**
 * A simple button.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1.2
 */
class Button extends FormAction {
	/**
	 * action of this button
	 *
	 *@name action
	 *@access public
	 */
	public $action;

	/**
	 *@name __construct
	 *@access public
	 *@param string - name
	 *@param string - title
	 *@param string - action in JavaScript
	 *@param object|null - field
	 */
	public function __construct($name = null, $title = null, $action = null, $classes = null, &$form = null) {
		$this->action = $action;
		parent::__construct($name, $title, null, $classes, $form);
	}

	/**
	 * creates the Button
	 */
	public function createNode() {
		$node = parent::createNode();
		$node->type = "button";
		$node->value = $this->title;
		$node->onclick = $this->action;
		return $node;
	}

	/**
	 * renders the field
	 * @param FormFieldRenderData $info
	 * @return HTMLNode
	 */
	public function field($info) {
		if(PROFILE)
			Profiler::mark("FormAction::field");

		$this->callExtending("beforeField");
		$this->input->val($this->title);

		$this->container->append($this->input);

		$this->container->setTag("span");
		$this->container->addClass("formaction");
		$this->container->removeClass("button");

		$this->callExtending("afterField");

		if(PROFILE)
			Profiler::unmark("FormAction::field");

		return $this->container;
	}

}
