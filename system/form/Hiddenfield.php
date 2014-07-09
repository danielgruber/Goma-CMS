<?php
/**
  * goma form framework
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 25.05.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HiddenField extends FormField 
{
		/**
		 * it's not allowed to use Posted Data for this field
		 *
		 *@name POST
		 *@access public
		*/
		public $POST = false;
		
		/**
		 * we don't need a title in this field
		 *
		 *@name __construct
		 *@access public
		*/
		public function __construct($name = null, $value = null, &$form = null)
		{
				parent::__construct($name, null, $value, $form);
		}
		
		/**
		 * creates the node
		 * sets the field-type to hidden
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "hidden";
				return $node;
		}
		
		/**
		 * gets the result
		 *
		 *@name result
		 *@access public
		*/
		public function result()
		{
				return $this->value;
		}
		
		/**
		 * sets the value
		 *
		 *@name setValue
		*/
		public function setValue() {
			if(is_string($this->value) || is_int($this->value)) {
				$this->input->val($this->value);
			} else {
				$this->input->val(1);
			}
		}
		
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FormField::field");
				
				$this->callExtending("beforeField");
				
				$this->setValue();
				
				$this->container->append($this->input);
				$this->container->addClass("hidden");
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormField::field");
				
				return $this->container;
		}
}