<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 20.09.2012
  * $Version 1.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Select extends FormField 
{
		/**
		 * options of this select-field
		 *
		 *@name options
		 *@access public
		*/
		public $options;
		
		/**
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param string - selected id
		 *@param null|object - parent form
		*/
		public function __construct($name, $title = null, $options = array(), $selected = null, $parent = null)
		{
				$this->options = $options;
				parent::__construct($name, $title, $selected, $parent);
		}
		
		/**
		 * creates the node
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->setTag("select");
				return $node;
		}
		
		/**
		 * gets the options
		 *
		 *@name options
		 *@access public
		*/
		public function options() {
			$this->callExtending("onBeforeOptions");
			return $this->options;
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
					array("for"	=> $this->ID()),
					$this->title
				));
				
				$node = $this->input;
				
				$options = $this->options();
				
				/*if(isset($options[0])) // this is no associative array
				{
						foreach($options as $key => $value)
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
				{*/
						foreach($options as $key => $value)
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
				//}
				
				$this->container->append(new HTMLNode("span", array("class" => "select-wrapper input"),$node));
				
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
		 * validation for security reason
		 *
		 *@name validate
		*/
		public function validate($value) {
			if(!isset($this->options[$value])) {
				return false;
			}
		
			return true;
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