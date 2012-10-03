<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 25.08.2012
  * $Version 2.1.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FormAction extends FormField implements FormActionHandler
{
		/**
		 * the submission-method on the controller for this form-action
		 *
		 *@name submit
		 *@access protected
		*/
		protected $submit;
		
		/**
		 * defines that these fields doesn't have a value
		 *
		 *@name hasNoValue
		*/
		public $hasNoValue = true;
		
		/**
		 *@name __construct
		 *@access public
	  	 *@param string - name
		 *@param string - title
		 *@param string - optional submission
		 *@param object - form
		*/
		public function __construct($name, $value, $submit = null, $classes = null, &$form = null)
		{
				parent::__construct($name, $value);
				if($submit === null)
						$submit = "@default";
				
				$this->submit = $submit;
				if($form != null)
				{
						$this->parent = $form;
						$this->setForm($form);
				}
				
				if(isset($classes))
					if(is_array($classes))
						foreach($classes as $class)
							$this->addClass($class);
					else
						$this->addClass($class);
		}
		
		/**
		 * generates the node
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "submit";
				$node->addClass("button");
				$node->addClass("formaction");
				return $node;
		}
		
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
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
		
		/**
		 * returns if submit or not
		 *
		 *@name canSubmit
		 *@access public
		 *@param submission
		*/
		public function canSubmit($data) {
			return true;
		}
		
		/**
		 * sets the submit-method
		 *
		 *@name setSubmit
		 *@access public
		*/
		public function setSubmit($submit) {
			$this->submit = $submit;
		}
		
		/**
		 * returns the submit-method
		 *
		 *@name getSubmit
		 *@access public
		*/
		public function getSubmit() {
			return $this->submit;
		}
		
		/**
		 * here you can add classes or remove some
		*/
		
		/**
		 * adds a class to the input
		 *
		 *@name addClass
		 *@access public
		*/
		public function addClass($class) {
			$this->input->addClass($class);
		}
		
		/**
		 * removes a class from the input
		 *
		 *@name removeClass
		 *@access public
		*/
		public function removeClass($class) {
			$this->input->removeClass($class);
		}
}