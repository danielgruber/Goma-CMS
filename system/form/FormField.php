<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FormField extends RequestHandler
{
		/**
		 * secret key for this form field
		 *
		 *@name randomKey
		 *@access public
		*/
		public $randomKey = "";
		/**
		 * this var defines if the value of $this->form()->post[$name] should be set as value if it is set
		 *@name POST
		 *@access protected
		*/
		protected $POST = true;
		/**
		 * data of this field
		 *@name data
		 *@access public
		*/
		private $data = array();
		/**
		 * the parent field of this field, e.g. a form or a fieldset
		 *@access protected
		*/
		protected $parent;
		/**
		 * this var contains the node-object of the input-element
		 *@see HTMLNode
		 *@name input
		 *@access public
		 *@var object
		*/
		public $input;
		/**
		 * this var contains the container
		 *@see HTMLNode
		 *@name container
		 *@access public
		 *@var object
		*/
		public $conatiner;
		
		public $url_handlers = array(
			'$Action/$id/$otherid'	=> '$Action'
		);
		
		public $name;
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param mixed - value
		 *@param object - form
		*/
		public function __construct($name = null, $title = null, $value = null, $parent = null)
		{
				parent::__construct();
				
				/* --- */
				
				$this->randomKey = randomString(3);
				
				$this->name = $name;
				$this->title = $title;
				$this->value = $value;
				$this->parent = $parent;
				if($parent)
				{
						$this->form()->fields[$name] = $this;
						if(is_a($this->parent, "form"))
						{
								$this->parent->showFields[$name] = $this;
								$this->parent->fieldSort[$name] = count($this->parent->fieldSort);
								
						} else
						{
								$this->parent->items[$name] = $this;
								$this->parent->sort[$name] = count($this->parent->sort);
						}
						
				}
				
				$this->input = $this->createNode();
				
				$this->container = new HTMLNode("div",array(
					"class"	=> "form_field ". $this->class ." form_field_".$name.""
				));
				
				if($this->parent)
					$this->renderAfterSetForm();
				
				
		}
		/**
		 * creates the Node
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				return new HTMLNode("input", array(
					'name'	=> $this->name,
					"class"	=> "input",
					"type"	=> "text"
				));
		}
		/**
		 * sets the value
		 *@name setValue
		 *@access public
		*/
		public function setValue()
		{
				$this->input->val($this->value);
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
				
				$this->container->append(new HTMLNode(
					"label",
					array("for"	=> $this->ID()),
					$this->title
				));
				
				$this->container->append($this->input);
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormField::field");
				
				return $this->container;
		}
		/**
		 * field function for mobile version
		 *
		 *@name mobileField
		 *@access public
		*/
		public function mobileField() {
			return $this->field();
		}
		/**
		 * this function generates some JavaScript for this formfield
		 *@name js
		 *@access public
		*/
		public function js()
		{
				return "";
		}
		/**
		 * this function generates some JavaScript for the validation of the field
		 *@name jsValidation
		 *@access public
		*/
		public function jsValidation()
		{
				return "";
		}
		/**
		 * this is the validation for this field if it is required
		 *@name validation
		 *@access public
		*/
		public function validate($value)
		{
				return true;
		}
		/**
		 * this function returns the result of this field
		 *@name result
		 *@access public
		*/
		public function result()
		{
			
			if($this->disabled)
				return $this->value;
			else
				return isset($this->form()->post[$this->name]) ? $this->form()->post[$this->name] : null;
		}
		/**
		 * sets the parent form-object
		 *@name setForm
		 *@access public
		*/
		public function setForm(&$form)
		{
				if(is_object($form))
				{
						$this->parent = $form;
						$this->form()->fields[$this->name] = $this;
						$this->getValue();
						$this->renderAfterSetForm();
						
				}
				else
						throwError(6, 'PHP-Error', '$form is no object in '.__FILE__.' on line '.__LINE__.'');
		}
		/**
		 * gets value if is in result or post-data
		*/
		public function getValue() {
			
			
			
			if(!$this->disabled && $this->POST && isset($this->form()->post[$this->name])) {
				$this->value = $this->form()->post[$this->name];
			} else if($this->POST && $this->value == null && isset($this->form()->result[$this->name]) && is_object($this->form()->result)) {
				$this->value = ($this->form()->result->doObject($this->name)) ? $this->form()->result->doObject($this->name)->raw() : null;
			} else if($this->POST && $this->value == null && isset($this->form()->result[$this->name]))
				$this->value = $this->form()->result[$this->name];
				
			
				
			

		}
		/**
		 * renders some field contents after setForm
		*/
		public function renderAfterSetForm() {
			if(is_object($this->input)) $this->input->id = $this->ID();
			if(is_object($this->container)) $this->container->id = $this->divID();
		}
		/**
		 * removes this field
		 *@name remove
		 *@access public
		*/
		public function remove()
		{
				unset($this->form()->fields[$this->name]);
		}
		/**
		 * generates an id for the field
		 *@name id
		 *@access public
		*/
		public function ID()
		{
				if(Core::is_ajax()) {
					return "form_field_" .  $this->class . "_" . md5($this->form()->name . $this->title) . "_" . $this->name . "_ajax";
				} else {
					return "form_field_" .  $this->class . "_" . md5($this->form()->name . $this->title) . "_" . $this->name . "_ajax";
				}
		}
		/**
		 * generates an id for the div
		 *@name divID
		 *@access public
		*/
		public function divID()
		{
				return $this->ID() . "_div";
		}
		/**
		 * the url for ajax
		*/
		public function externalURL()
		{
				return ROOT_PATH . BASE_SCRIPT . "system/forms/" . $this->form()->name . "/" . $this->name;
		}
		/**
		 * Overloading
		*/
		
		/**
		 * get
		 *@name __get
		 *@access public
		*/
		public function __get($offset)
		{
				// patching
				if (isset($this->data[$offset]) && is_array($this->data[$offset])) {
					return (array) $this->data[$offset];
        		}
				return (isset($this->data[$offset])) ? $this->data[$offset] : false;
		}
		/**
		 * set
		 *@name __set
		 *@access public
		*/
		public function __set($offset, $value)
		{
			
				$this->data[$offset] = $value;
		}
		/**
		 * isset
		 *@name __isset
		 *@access public
		*/
		public function __isset($offset)
		{
				return (isset($this->data[$offset]));
		}
		/**
		 * unset
		 *@name __unset
		 *@access public
		*/
		public function __unset($offset)
		{
				unset($this->data[$offset]);
		}
		/**
		 * returns the current real form-object
		 *@name form
		 *@access public
		*/
		public function &form()
		{
				if(is_object($this->parent))
						return $this->parent->form();
				else {
						$debug = debug_backtrace(false);
						throwError(6,'PHP-Error', 'No Form for Field '.$this->class.' in '.$debug[0]["file"].' on line '.$debug[0]["line"].'');
				}
		}
		
		/**
		 * disables this field
		*/
		public function disable()
		{
			if(is_object($this->input))
				$this->input->disabled = "disabled";
				
			$this->disabled = true;
		}
		/**
		 * reenables the field
		 *@name enable
		 *@access public
		*/
		public function enable()
		{
				unset($this->input->disabled);
				$this->disabled = false;
		}
}