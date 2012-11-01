<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 29.10.2012
  * $Version 2.3
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
		 *
		 *@name POST
		 *@access protected
		*/
		protected $POST = true;
		
		/**
		 * data of this field
		 *
		 *@name data
		 *@access public
		*/
		private $data = array();
		
		/**
		 * the parent field of this field, e.g. a form or a fieldset
		 *
		 *@name parent
		 *@access protected
		*/
		protected $parent;
		
		/**
		 * this var contains the node-object of the input-element
		 *
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
			'$Action//$id/$otherid'	=> '$Action'
		);
		
		/**
		 * value
		 *
		 *@name value
		 *@access public
		*/
		public $value;
		
		/**
		 * name of this field
		 *
		 *@name name
		 *@access public
		*/
		public $name;
		
		/**
		 * overrides the post-name
		 *
		 *@name overridePostName
		 *@access public
		*/
		public $overridePostName;
		
		/**
		 * defines if this field is disabled
		 *
		 *@name disabled
		 *@access public
		*/
		public $disabled = false;
		
		/**
		 * defines if this field should use the full width or not
		 * this is good, for example for textarea or something else to get correct position of info and label-area
		 *
		 *@name fullSizedField
		 *@access public
		*/
		protected $fullSizedField = false;
		
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param mixed - value
		 *@param object - form
		*/
		public function __construct($name = null, $title = null, $value = null, &$parent = null)
		{
				parent::__construct();
				
				/* --- */
				
				$this->randomKey = randomString(3);
				
				$this->name = $name;
				$this->title = $title;
				$this->value = $value;
				$this->parent =& $parent;
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
				
				if($this->fullSizedField)
					$this->container->addClass("fullSize");
				
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
					'name'	=> $this->PostName(),
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
			if(($this->input->getTag() == "input" || $this->input->getTag() == "textarea") && (is_string($this->value) || (is_object($this->value) && Object::method_exists($this->value->class, "__toString"))))
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
		 *
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
				return isset($this->form()->post[$this->PostName()]) ? $this->form()->post[$this->PostName()] : null;
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
						$this->parent =& $form;
						
						$this->form()->registerField($this->name, $this);
						if(is_object($this->input))
							$this->input->name = $this->PostName();
						
						$this->getValue();
						$this->renderAfterSetForm();
						
				} else
						throwError(6, 'PHP-Error', '$form is no object in '.__FILE__.' on line '.__LINE__.'');
		}
		
		/**
		 * gets value if is in result or post-data
		 *
		 *@name getValue
		 *@access public
		*/
		public function getValue() {
			if(!isset($this->hasNoValue) || !$this->hasNoValue) {
				if(!$this->disabled && $this->POST && isset($this->form()->post[$this->PostName()])) {
					$this->value = $this->form()->post[$this->PostName()];
				} else if($this->POST && $this->value == null && is_object($this->form()->result) && is_a($this->form()->result, "ArrayAccess") && isset($this->form()->result[$this->name])) {
					$this->value = ($this->form()->result->doObject($this->name)) ? $this->form()->result->doObject($this->name)->raw() : null;
				} else if($this->POST && $this->value == null && is_array($this->form()->result) && isset($this->form()->result[$this->name])) {
					$this->value = $this->form()->result[$this->name];
				}
			}
		}
		
		/**
		 * renders some field contents after setForm
		 *
		 *@name renderAfterSetForm
		 *@access public
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
				$this->form()->unregisterField($this->name);
		}
		
		/**
		 * generates an id for the field
		 *@name id
		 *@access public
		*/
		public function ID()
		{
			return "form_field_" .  $this->class . "_" . md5($this->form()->name . $this->title) . "_" . $this->name;
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
		 *
		 *@name externalURL
		 *@access public
		*/
		public function externalURL()
		{
				return $this->form()->externalURL() . "/" . $this->name;
		}
		
		/**
		 * returns the post-name
		 *
		 *@name PostName
		 *@access public
		*/
		public function PostName() {
			return isset($this->overridePostName) ? $this->overridePostName : $this->name;
		}
		
		/**
		 * returns the current real form-object
		 *@name form
		 *@access public
		*/
		public function &form()
		{
				if(is_object($this->parent)) {
						$data =& $this->parent->form();
						return $data;
				} else {
						$debug = debug_backtrace(false);
						throwError(6,'PHP-Error', 'No Form for Field '.$this->class.' in '.$debug[0]["file"].' on line '.$debug[0]["line"].'');
				}
		}
		
		/**
		 * disables this field
		 *
		 *@name disable
		 *@access public
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
		
		/**
		 * creates a HTMLNode
		 *
		 *@name createTag
		 *@access public
		*/
		public function createTag($tag, $attr, $content) {
			$node = new HTMLNode($tag, $attr, $content);
			return $node->render();
		}
		
		/**
		 * getter-method for state
		*/
		public function __get($name) {
			if(strtolower($name) == "state") {
				return $this->form()->state->{$this->class . $this->name};
			} else if(isset($this->$name)) {
				return $this->$name;
			} else {
				throwError(6, "Unknowen Attribute", "" . $name . " is not defined in ".$this->class." with name ".$this->name.".");
			}
		}
		
		/**
		 * adds an extra-class to the field
		*/
		public function addExtraClass($class) {
			$this->container->addClass($class);
		}
		
		/**
		 * removes an extra-class from the field
		*/
		public function removeExtraClass($class) {
			$this->container->removeClass($class);
		}
}