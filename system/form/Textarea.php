<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 15.03.2012
  * $Version 1.0.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Textarea extends FormField 
{
		/**
		 * height of this textarea
		 *
		 *@name height
		 *@access public
		*/
		public $height = "200px";
		
		/**
		 * width of this textarea
		 *
		 *@name width
		 *@access public
		*/
		public $width = "100%";
		
		/**
		 * this field needs to have the full width
		 *
		 *@name fullSizedField
		*/
		protected $fullSizedField = true;
		
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param string - default-value
		 *@param string - height
		 *@param string - width
		 *@param null|object - form
		*/
		public function __construct($name, $title = null, $value = null, $height = null,$width = null, &$form = null)
		{
			if(isset($height))
				$this->height = $height;
			
			if(isset($width))
				$this->width = $width;
				
			parent::__construct($name, $title, $value, $form);
		}
		/**
		 * generates the field in HTML
		 *
		 *@name createNode
		 *@access public
		*/
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
				if(PROFILE) Profiler::mark("FormField::field");
				
				$this->callExtending("beforeField");
				
				$this->setValue();
				
				$this->container->append($label = new HTMLNode(
					"label",
					array("for"	=> $this->ID()),
					$this->title
				));
				
				$this->container->append($this->input);
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormField::field");
				
				return $this->container;
		}
}