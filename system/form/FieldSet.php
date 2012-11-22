<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 21.11.2012
  * $Version: 2.1.8
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FieldSet extends FormField
{
		/**
		 * items of this fieldset
		 *@name items
		 *@access public
		*/
		protected $items = array();
		
		/**
		 * sort of the items
		 *@name sort
		 *@access public
		*/
		public $sort = array();
		
		/**
		 * fields of this FieldSet
		 *
		 *@name fields
		 *@access public
		 *@var arry
		*/
		public $fields = array();
		
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param mixed - value
		 *@param object - form
		*/
		public function __construct($name = null, $fields, $label = null, &$parent = null)
		{
				parent::__construct($name, $label, null, $parent);
				
				/* --- */
				
				$this->container->setTag("fieldset");

				if(is_array($fields))
					$this->fields = $fields;
				else
					$this->fields = array();
		}
		
		/**
		 * sets the form for all subfields, too
		 *@name setForm
		 *@access public
		 *@param form
		*/
		public function setForm(&$form)
		{
				if(is_object($form))
				{
						$this->parent =& $form;
						$this->state = $this->form()->state->{$this->class . $this->name};
						$this->form()->fields[$this->name] = $this;
						$this->renderAfterSetForm();
				}
				else
						throwError(6, 'PHP-Error', '$form is no object in '.__FILE__.' on line '.__LINE__.'');
				
				foreach($this->fields as $sort => $field)
				{
						$this->items[$field->name] = $field;
						$this->sort[$field->name] = 1 + $sort;
						$field->setForm($this);
				}
		}
		
		/**
		 * creates the legend-element of needed
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				return new HTMLNode("legend", array(), $this->title);
		}
		
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FieldSet::field");
				
				$this->callExtending("beforeField");
				
				$this->container->append($this->input);
				
				// get content
				uasort($this->items, array($this, "sort"));
				
				$i = 0;
				foreach($this->items as $item) {
						
						// if a FieldSet is disabled all subfields should disabled, too
						if($this->disabled) {
							$item->disable();
						}
						
						
						$name = $item->name;
						// if a field is deleted the field does not exist in that array
						if(isset($this->form()->fields[$name]) && !isset($this->form()->renderedFields[$name]))
						{
								$this->form()->renderedFields[$name] = true;
								$div = $item->field();
								if(is_object($div) && !$div->hasClass("hidden")) {
									if($i == 0) {
										$i++;
										$div->addClass("one");
									} else {
										$i = 0;
										$div->addClass("two");
									}
									$div->addClass("visibleField");
								}
								$this->container->append($div);
						}
				}
				unset($i, $div, $item);
				$this->callExtending("afterField");
				
				$this->container->addClass("hidden");
				if(PROFILE) Profiler::unmark("FieldSet::field");
				
				return $this->container;
		}
		
		/**
		 * adds an field
		 *@name add
		 *@access public
		*/
		public function add($field, $sort = 0)
		{
			if($this->parent) {
				if($sort == 0) {
					$sort = 1 + count($this->items);
 				}
				$this->sort[$field->name] = $sort;
				$this->items[$field->name] = $field;
				$field->setForm($this);
			} else {
				if($sort == 0) {
					$sort = 1 + count($this->fields);
					while(isset($this->fields[$sort]))
						$sort++;
				}
				
				$this->fields[$sort] = $field;
			}
		}
		
		/**
		 * removes a field or this field
		 *@name remove
		 *@access public
		*/
		public function remove($field = null)
		{
			if($this->parent) {
				if($field === null)
				{
						parent::remove();
				} else
				{
					if(is_object($field)) {
						$field = $field->name;
					}
					
					if(isset($this->form()->fields[$field]))
					{
							unset($this->form()->fields[$field]);
					}
					
					if(isset($this->items[$field]))
					{
							unset($this->items[$field]);
					}
					
					foreach($this->items as $_field) {
						if(is_subclass_of($_field, "FieldSet")) {
							$_field->remove($field);
						}
					}
				}
			} else {
				foreach($this->fields as $key => $_field) {
					if(is_subclass_of($_field, "FieldSet")) {
						$_field->remove($field);
					} else if($_field->name == $field->name) {
						unset($this->fields[$key]);
					}
				}
			}
		}
		
		/**
		 * sorts the items
		 *@name sort
		 *@access public
		*/
		public function sort($a, $b)
		{
			if($this->sort[$a->name] == $this->sort[$b->name])
			{
				return 0;
			}
			
			return ($this->sort[$a->name] > $this->sort[$b->name]) ? 1 : -1;
		}
}