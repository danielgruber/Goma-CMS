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