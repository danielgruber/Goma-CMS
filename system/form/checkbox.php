<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 05.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class CheckBox extends FormField 
{
		/**
		 * creates the node
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "checkbox";
				
				return $node;
		}
		/**
		 * sets the value
		 *@name setValue
		 *@access public
		*/
		public function setValue()
		{
				
				if(isset($_POST["form_submit_" . $this->form()->name()]) && $this->POST && isset($_POST[$this->name])) {
					$this->value = 1;
				} else if(isset($_POST["form_submit_" . $this->form()->name()])) {
					$this->value = 0;
				}
				
				if($this->value) 
						$this->input->checked = "checked";
				
				
				$this->input->value = 1;
						
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
				
				$this->container->append(new HTMLNode(
					"label",
					array("for"	=> $this->ID(), "style"	=> array("display" => "inline")),
					$this->title
				));
				
				
				$this->callExtending("afterField");
				
				Profiler::unmark("FormField::field");
				
				return $this->container;
		}
		/**
		 * the result of the field
		*/
		public function result()
		{
				return isset($_POST[$this->name]) ? true : false;
		}
}