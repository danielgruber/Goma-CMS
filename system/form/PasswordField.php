<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
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