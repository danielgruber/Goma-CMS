<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 24.12.2012
  * $Version 1.0.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TextField extends FormField 
{
		/**
		 * generates the field
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "text";
				return $node;
		}
}