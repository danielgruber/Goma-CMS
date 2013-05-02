<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 29.07.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class PasswordField extends FormField 
{
		public $POST = false;
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "password";
				$node->css("width", "250px");
				return $node;
		}
}