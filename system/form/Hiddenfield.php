<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.01.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HiddenField extends FormField 
{
		public $POST = false;
		public function __construct($name = null, $value = null, $form = null)
		{
				parent::__construct($name, null, $value, $form);
		}
		// get the node
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "hidden";
				return $node;
		}
		/**
		 * gets the result
		*/
		public function result()
		{
				return $this->value;
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				Profiler::mark("FormField::field");
				
				$this->callExtending("beforeField");
				
				$this->setValue();
				
				$this->container->append($this->input);
				$this->container->addClass("hidden");
				$this->callExtending("afterField");
				
				Profiler::unmark("FormField::field");
				
				return $this->container;
		}
}