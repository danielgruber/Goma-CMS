<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Tab extends FieldSet
{
		public function __construct($name, $fields, $title = null, $form = null)
		{
				parent::__construct($name, $fields,  $title, $form);
							
				$this->container->setTag("div");
		}
		public function createNode()
		{
				$node = parent::createNode();
				$node->setTag("h2");
				$node->html(strtoupper(substr($this->title, 0, 1)) . substr($this->title, 1));
				return $node;
		}
}