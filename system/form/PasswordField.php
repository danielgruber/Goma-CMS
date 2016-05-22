<?php defined("IN_GOMA") OR die();

/**
 * a basic password-field.
 *
 * @package        Goma\Form-Framework
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version    1.1
 */
class PasswordField extends FormField {

	/**
	 * @return HTMLNode
	 */
	public function createNode()
	{
		$node = parent::createNode();
		$node->type = "password";
		$node->css("width", "250px");
		return $node;
	}

	public function setValue()
	{
	}

	public function getValue()
	{
	}

	/**
	 * @return mixed|null
	 */
	public function result() {
		$result = parent::result();

		if(!$result) {
			return $this->value;
		}

		return $result;
	}
}
