<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 16.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class RadioButton extends FormField
{
		public $disabledNodes = array();
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param string - select
		 *@param object - form
		 *@access public
		*/
		public function __construct($name, $title = null, $options = array(), $selected = null, $form = null)
		{
				$this->options = $options;
				parent::__construct($name, $title, $selected, $form);
				
		}
		/**
		 * renders the node
		 *@name field
		 *@access public
		*/
		public function field()
		{
				$this->callExtending("beforeField");
				
				$this->container->append(new HTMLNode(
					"label",
					array("for"	=> $this->ID()),
					$this->title
				));
				
				$node = new HTMLNode("span");

				
				foreach($this->options as $value => $title)
				{
						if($value == $this->value)
						{
								if($this->disabled || isset($this->disabledNodes[$value]))
									$node->append(new HTMLNode('input', array(
										"type"		=> "radio",
										"name"		=> $this->name,
										"value"		=> $value,
										"checked"	=> "checked",
										"disabled"	=> "disabled"
									), $value));
								else
									$node->append(new HTMLNode('input', array(
									"type"		=> "radio",
									"name"		=> $this->name,
									"value"		=> $value,
									"checked"	=> "checked",
									"id"		=> "radio_" . md5($this->name . "_" . $value)
								), $value));

								$node->append(new HTMLNode("label", array(
									"style"	=> array("display" => "inline"), // inline hack
									"for"	=> "radio_" . md5($this->name . "_" . $value)
								), " " . $title . "&nbsp;"));
						}
						else
						{
								if($this->disabled || isset($this->disabledNodes[$value]))
									$node->append(new HTMLNode('input', array(
										"type"	=> "radio",
										"name"	=> $this->name,
										"value"	=> $value,
										"disabled"
									), $value));
								else
									$node->append(new HTMLNode('input', array(
										"type"	=> "radio",
										"name"	=> $this->name,
										"value"	=> $value,
										"id"		=> "radio_" . md5($this->name . "_" . $value)
									), $value));
								$node->append(new HTMLNode("label", array(
									"style"	=> array("display" => "inline"), // inline hack
									"for" => "radio_" . md5($this->name . "_" . $value)
								), " " . $title . "&nbsp;"));
						}
						//$node->append(new HTMLNode("br"));
				}
			
				$this->container->append($node);
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
		/**
		 * disables a specific radio-button
		 *
		 *@name disableNode
		 *@access public
		*/
		public function disableNode($id) {
			$this->disabledNodes[$id] = true;
		}
		/**
		 * enables a specific node
		*/
		public function enableNode($id) {
			unset($this->disabledNodes[$id]);
		}
}