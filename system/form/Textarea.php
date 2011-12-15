<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 07.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Textarea extends FormField 
{
		public function __construct($name, $title, $value = null, $height = "200px",$width = "97%", $form = null)
		{
				$this->height = $height;
				$this->width = $width;
				parent::__construct($name, $title, $value, $form);
		}
		public function createNode()
		{
				$node = parent::createNode();
				$node->css("height",$this->height);
				$node->css("width",$this->width);
				$node->removeAttr("type");
				$node->setTag("textarea");
				return $node;
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
				
				$this->container->append($label = new HTMLNode(
					"label",
					array("for"	=> $this->ID(), "style"	=> "display: block;"),
					$this->title
				));
				
				$label->css("display", "block");
				
				$this->container->append($this->input);
				
				$this->callExtending("afterField");
				
				Profiler::unmark("FormField::field");
				
				return $this->container;
		}
}