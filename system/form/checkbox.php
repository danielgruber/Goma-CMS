<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.09.2012
  * $Version 1.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class CheckBox extends FormField 
{
		/**
		 * creates the node
		 *
		 *@name createNode
		 *@access public
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
				
				if(isset($_POST["form_submit_" . $this->form()->name()]) && $this->POST && isset($_POST[$this->postname()])) {
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
		 * returns the javascript for this field
		 *
		 *@name js
		 *@access public
		*/
		public function js() {
			
			Resources::add("system/libs/thirdparty/iphone-checkbox/jquery/iphone-style-checkboxes.js", "js", "tpl");
			Resources::add("system/libs/thirdparty/iphone-checkbox/style.css", "css", "combine");
			
			return '$(function(){
				var obj = $("#'.$this->ID().'").iphoneStyle();
				interval = setInterval(function(){
					if($("#'.$this->ID().'").length > 0) {
						$("#'.$this->ID().'").iphoneStyle("initialPosition");
					} else {
						clearInterval(interval);
					}
				}, 500);
				//$("#'.$this->divID().'").addClass("clearfix");
				//$("#'.$this->divID().' .iPhoneCheckContainer").css("float", "right");
			});';
		}
		
		/**
		 * the result of the field
		 *
		 *@name result
		 *@access public
		*/
		public function result()
		{
				return isset($_POST[$this->postname()]) ? true : false;
		}
}