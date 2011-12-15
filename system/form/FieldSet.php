<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 13.06.2011
  * $Version: 2.0.0 - 004
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FieldSet extends FormField
{
		/**
		 * items of this fieldset
		 *@name items
		 *@access public
		*/
		public $items = array();
		/**
		/**
		 * sort of the items
		 *@name sort
		 *@access public
		*/
		public $sort = array();
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param mixed - value
		 *@param object - form
		*/
		public function __construct($name = "", $fields, $label = null, $parent = null)
		{
				parent::__construct($name, $label, null, $parent);
				
				/* --- */
				
				$this->container->setTag("fieldset");

				$this->fields = $fields;
		}
		/**
		 * sets the form for all subfields, too
		 *@name setForm
		 *@access public
		 *@param form
		*/
		public function setForm($form)
		{
				if(is_object($form))
				{
						$this->parent = $form;
						$this->form()->fields[$this->name] = $this;
						$this->renderAfterSetForm();
				}
				else
						throwError(6, 'PHP-Error', '$form is no object in '.__FILE__.' on line '.__LINE__.'');
				
				$fields = $this->fields;
				
				foreach($fields as $sort => $field)
				{
						$this->sort[$field->name] = $sort;
						$this->items[$field->name] = $field;
						$field->setForm($this);
				}
		}
		/**
		 * creates the legend-element of needed
		 *@name createNode
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
				Profiler::mark("FieldSet::field");
				
				$this->callExtending("beforeField");
				
				$this->container->append($this->input);
				
				// get content
				usort($this->items, array($this, "sort"));
				$i = 0;
				foreach($this->items as $item)
				{
						
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
									
								}
								$this->container->append($div);
						}
				}
				unset($i, $div, $item);
				$this->callExtending("afterField");
				
				$this->container->addClass("hidden");
				Profiler::unmark("FieldSet::field");
				
				return $this->container;
		}
		/**
		 * adds an field
		 *@name add
		 *@access public
		*/
		public function add($field, $sort = 0)
		{
				if($sort == 0)
				{
						$sort = count($this->items);
				}
				
				$this->sort[$field->name] = $sort;
				$this->items[$field->name] = $field;
				$field->setForm($this);
		}
		/**
		 * removes a field or this field
		 *@name remove
		 *@access public
		*/
		public function remove($field = null)
		{
				if($field === null)
				{
						parent::remove();
				} else
				{
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