<?php defined("IN_GOMA") OR die();

/**
 * a basic class for every form-field in a form.
 *
 * @package        Goma\Form-Framework
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version    2.3.4
 */
class TextField extends FormField 
{
	/**
	 * creates field with max-length.
	 * @param string $name
	 * @param string $title
	 * @param int $maxLength
	 * @param null $value
	 * @param null $parent
	 * @return static
	 */
	public static function createWithMaxLength($name, $title, $maxLength, $value = null, $parent = null) {
		$field = static::create($name, $title, $value, $parent);

		$field->maxLength = $maxLength;

		return $field;
	}

	/**
	 * generates the field
	 *
	 * @name createNode
	 * @access public
	 * @return HTMLNode
	 */
	public function createNode()
	{
		$node = parent::createNode();
		$node->type = "text";
		return $node;
	}
}