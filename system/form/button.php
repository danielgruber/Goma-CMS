<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 30.07.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Button extends FormAction
{
		public function __construct($name, $title = null, $action = null, $form = null)
		{
				$this->action = $action;
				parent::__construct($name, $title, null, $form);
		}
		/**
		 * creates the Node
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "button";
				$node->value = $this->title;
				$node->onclick = $this->action;
				return $node;
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				Profiler::mark("FormAction::field");
				
				$this->callExtending("beforeField");
				$this->input->val($this->title);
				
				$this->container->append($this->input);
				
				$this->container->setTag("span");
				$this->container->addClass("formaction");
				$this->container->removeClass("button");
				
				$this->callExtending("afterField");
				
				Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
}