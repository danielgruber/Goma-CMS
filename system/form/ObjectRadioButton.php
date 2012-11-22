<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 01.08.2012
  * $Version 2.0.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ObjectRadioButton extends RadioButton
{
		
		/**
		 * these fields need javascript
		 *
		 *@name javaScriptNeeded
		 *@access protected
		*/
		protected $javaScriptNeeded = array();
			
		/**
		 * defines if we hide disabled nodes
		 *
		 *@name hideDisabled
		 *@access public
		*/
		public $hideDisabled = true;
		
		/**
		 * renders a option-record
		 *
		 *@name renderOption
		 *@access public
		*/
		public function renderOption($name, $value, $title, $checked = null, $disabled = null, $field = null) { 
			$node = parent::renderOption($name, $value, $title, $checked, $disabled);
			
			$children = $node->children();
			$input = $children[0];
				
			$id = $input->id;
			
			$this->javaScriptNeeded[] = $id;
			
			if(isset($field)) {
				$node->append(new HTMLNode('div', array(
					"id" 	=> "displaycontainer_" . $id,
					"class"	=> "displaycontainer"
				), $field->field()));
				$this->form()->registerRendered($field->name);
			}
			
			return $node;
		}
		
		/**
		 * renders the field
		 *
		 *@name field
		 *@access public
		*/
		public function field()
		{
				$this->callExtending("beforeField");
				
				$this->container->append(new HTMLNode(
					"label",
					array(),
					$this->title
				));
				
				$node = new HTMLNode("div");
				
				if($this->disabled) {
					$this->hideDisabled = false;
				}
				
				if(!$this->fullSizedField)
					$node->addClass("inputHolder");
				
				foreach($this->options as $value => $title) {
					$field = null;
					if(is_array($title) && isset($title[1])) {
						$field = $this->form()->getField($title[1]);
						$title = $title[0];
					}
					
					if($value == $this->value) {
						if($this->disabled || isset($this->disabledNodes[$value])) {
							$node->append($this->renderOption($this->PostName(), $value, $title, true, true, $field));
						} else {
							$node->append($this->renderOption($this->PostName(), $value, $title, true, false, $field));
						}
					} else {
						if($this->disabled || isset($this->disabledNodes[$value])) {
							$node->append($this->renderOption($this->PostName(), $value, $title, false, true, $field));
						} else {
							$node->append($this->renderOption($this->PostName(), $value, $title, false, false, $field));
						}
					}
				}
			
				$this->container->append($node);
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
		
		/**
		 * generates the javascript for this field
		 *
		 *@name JS
		 *@access public
		*/
		public function JS()
		{
				$js = '$(function(){
					var radioids = '.json_encode($this->javaScriptNeeded).';
					for(i in radioids) {
						var id = radioids[i];
						if(!$("#" + id).prop("checked")) {
							$("#displaycontainer_" + id).css("display", "none");
						}
					}
				
					$("#'.$this->divID().' > div > .option > input[type=radio]").click(function(){
						var radioids =  '.json_encode($this->javaScriptNeeded).';
						for(i in radioids)
						{
							var id = radioids[i];
							if(!$("#" + id).prop("checked"))
							{
								var otherid = "displaycontainer_" + radioids[i];
								$("#" + otherid).slideUp("fast");
							}
						}
						
						var currid = "displaycontainer_" + $(this).attr("id");
						$("#" + currid).slideDown("fast");
						if($("#" + currid).find(".form_field:first-child").find(".field").length > 0)
							$("#" + currid).find(".form_field:first-child").find(".field").click();
						else
							$("#" + currid).find(".form_field:first-child").find(".input").click();
					});
				});';
				return $js;
		}
}