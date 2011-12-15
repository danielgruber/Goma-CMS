<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 18.05.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FormAction extends FormField
{
		public $submit;
		/**
		 *@name __construct
		 *@access public
	  	 *@param string - name
		 *@param string - title
		 *@param string - optional submission
		 *@param object - form
		*/
		public function __construct($name, $value, $submit = null, $form = null)
		{
				parent::__construct($name, $value, null, null);
				if($submit === null)
						$submit = "@default";
				
				$this->submit = $submit;
				if($form != null)
				{
						$this->parent = $form;
						$this->setForm($form);
				}
		}
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
				Profiler::mark("FormAction::field");
				
				$this->callExtending("beforeField");
				$this->input->val($this->title);
				
				$this->container->append($this->input);
				
				$this->container->setTag("span");
				$this->container->addClass("formaction");
				
				$this->callExtending("afterField");
				
				Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
		/**
		 * sets the parent form-object
		 *@name setForm
		 *@access public
		*/
		public function setForm($form)
		{
				if(is_object($form))
				{
						$this->parent = $form;
						$this->form()->actions[$this->name] = array(
							'field'	 	=> $this,
							'submit'	=> $this->submit
						);
						$this->form()->fields[$this->name] = $this;
						$this->renderAfterSetForm();
						
				}
				else
						throwError(6, 'PHP-Error', '$form is no object in '.__FILE__.' on line '.__LINE__.'');
		}
		/**
		 * returns if submit or not
		 *
		 *@name canSubmit
		 *@access public
		 *@param submission
		*/
		public function canSubmit($submission) {
			return true;
		}
}