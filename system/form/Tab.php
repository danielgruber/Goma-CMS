<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 21.12.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Tab extends FieldSet
{
		/**
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param array - fields in this set
		 *@param string - title
		 *@param null|object - form
		*/
		public function __construct($name, $fields, $title = null, &$form = null)
		{
				parent::__construct($name, $fields,  $title, $form);
							
				$this->container->setTag("div");
		}
		/**
		 * generates the DOM
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->setTag("h2");
				$node->html(strtoupper(substr($this->title, 0, 1)) . substr($this->title, 1));
				return $node;
		}
}