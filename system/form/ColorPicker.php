<?php defined('IN_GOMA') OR die();

/**
  * a simple implementation of a color-picker with HTML5 Date + Polyfill
  *
  * @package 	goma form framework
  * @link 		http://goma-cms.org
  * @license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  * @author 	Goma-Team
  * @version	1.0
*/
class ColorPicker extends FormField 
{
		/**
		 * generates the field
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				Resources::add("system/libs/spectrum/spectrum.js", "js", "tpl");
				Resources::add("system/libs/spectrum/spectrum.css", "css");
				$node = parent::createNode();
				$node->type = "color";
				return $node;
		}
}