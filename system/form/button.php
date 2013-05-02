<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 24.03.2012
  * $Version 1.1.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Button extends FormAction
{
		/**
		 * action of this button
		 *
		 *@name action
		 *@access public
		*/
		public $action;
		/**
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param string - title
		 *@param string - action in JavaScript
		 *@param object|null - field
		*/
		public function __construct($name, $title = null, $action = null, $classes = null, &$form = null)
		{
				$this->action = $action;
				parent::__construct($name, $title, null, $classes, $form);
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
				if(PROFILE) Profiler::mark("FormAction::field");
				
				$this->callExtending("beforeField");
				$this->input->val($this->title);
				
				$this->container->append($this->input);
				
				$this->container->setTag("span");
				$this->container->addClass("formaction");
				$this->container->removeClass("button");
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
}