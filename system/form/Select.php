<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 08.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Select extends FormField 
{
		public function __construct($name, $title = null, $options = array(), $selected = null, $parent = null)
		{
				$this->options = $options;
				parent::__construct($name, $title, $selected, $parent);
		}
		/**
		 * creates the node
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->setTag("select");
				return $node;
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
				
				$node = $this->input;
				if(isset($this->options[0])) // this is no associative array
				{
						foreach($this->options as $key => $value)
						{
								if(_ereg('^[0-9]+$', $key) && is_numeric($key))
								{
										if($value == $this->value)
												$node->append(new HTMLNode('option', array(
													"value"		=> $value,
													"selected"	=> "selected"												
												), $value));
										else
												$node->append(new HTMLNode('option', array(
													"value"		=> $value
												), $value));
								} else
								{
										if($key == $this->value)
												$node->append(new HTMLNode('option', array(
													"value"		=> $key,
													"selected"	=> "selected"													
												), $value));
										else
												$node->append(new HTMLNode('option', array(
													"value"		=> $key
												), $value));
								}
						}
				} else
				{
						foreach($this->options as $key => $value)
						{
								if($key == $this->value)
										$node->append(new HTMLNode('option', array(
											"value"		=> $key,
											"selected"	=> "selected"												
										), $value));
								else
										$node->append(new HTMLNode('option', array(
											"value"		=> $key
										), $value));
						}
				}
				
				$this->container->append($node);
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
		/**
		 * sets the value
		 *@name setValue
		 *@access public
		*/
		public function setValue()
		{
				
				// we already inserted the value
		}
		/**
		 * adds an option
		 *@name addOption
		 *@access public
		 *@param string - title
		 *@param string - if you want to have a own value
		*/
		public function addOption($title, $value = null)
		{
				if($value === null)
				{
						if($this->value == $title)
								$this->input->append(new HTMLNode('option', array(
									'value'		=> $title,
									"selected"	=> "selected"
								), $title));
						else
								$this->input->append(new HTMLNode('option', array(
									'value'	=> $title
								), $title));
				} else
				{
						if($this->value == $value)
								$this->input->append(new HTMLNode('option', array(
									'value'		=> $value,
									"selected"	=> "selected"
								), $title));
						else
								$this->input->append(new HTMLNode('option', array(
									'value'	=> $value
								), $title));
				}
		}
		
}