<?php defined("IN_GOMA") OR die();

/**
 * Select.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0.5
 */
class Select extends RadioButton
{
	/**
	 * @param string $name
	 * @param string $value
	 * @param string $title
	 * @param null|bool $checked
	 * @param null|bool $disabled
	 * @return HTMLNode
	 */
	public function renderOption($name, $value, $title, $checked = null, $disabled = null)
	{
		if (!isset($checked))
			$checked = false;

		if (!isset($disabled))
			$disabled = false;

		$node = new HTMLNode("option", array("class" => "option", "name" => $name, "value" => $value), array(
				$title
		));

		if ($checked)
			$node->selected = "selected";

		if ($disabled)
			$node->disabled = "disabled";

		if (isset($disabled) && $disabled && $this->hideDisabled)
			$node->css("display", "none");

		$this->callExtending("renderOption", $node, $input, $_title);

		return $node;
	}

	/**
	 * @param FormFieldRenderData $info
	 * @return HTMLNode
	 */
	public function field($info) {
		$container = parent::field($info);

		$node = $container->getNode(1);
		$node->removeClass("inputHolder");
		$node->setTag("select");
		$node->attr("name", $this->PostName());

		$wrapper = new HTMLNode("span", array("class" => "select-wrapper input"));
		$wrapper->append($node);

		$container->content[1] = $wrapper;

		return $container;
	}
}
