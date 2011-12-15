<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.04.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ObjectRadioButton extends RadioButton
{
		// vars for disabling all or some of this radios
		public $disabled = array();
		public $all_disabled = false;
		
		public $ids = array();
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
				
				$node = new HTMLNode("div");

				foreach($this->options as $value => $title)
				{
						if(is_array($title) && isset($title[1]))
						{
								
								$otherid = $this->form()->fields[$title[1]]->id();
								$this->ids[] = $otherid;
								$name = $title[1];
								$title = $title[0];
										
								if($value == $this->value)
								{
										if($this->all_disabled || isset($this->disabled[$value]))
										{
												$node->append(new HTMLNode('input', array(
													"type"		=> "radio",
													"name"		=> $this->name,
													"value"		=> $value,
													"checked"	=> "checked",
													"id"		=> "radio_out_" . $otherid,
													"disabled"	=> "disabled",
												), $value));
										} else
										{
												$node->append(new HTMLNode('input', array(
													"type"		=> "radio",
													"name"		=> $this->name,
													"value"		=> $value,
													"checked"	=> "checked",
													"id"		=> "radio_out_" . $otherid
												), $value));
										}
										$node->append(new HTMLNode("label", array(
											"style"	=> array("display" => "inline"), // inline hack
											"for"	=> "radio_out_" . $otherid
										), $title));
										$node->append(new HTMLNode("div", array(
											"id"	=> "container_" . $otherid
										), array(
											$this->form()->fields[$name]->field()
										)));					
										$this->form()->renderedFields[$name] = true;	
								} else
								{
										if($this->all_disabled || isset($this->disabled[$value]))
										{
												$node->append(new HTMLNode('input', array(
													"type"		=> "radio",
													"name"		=> $this->name,
													"value"		=> $value,
													"id"		=> "radio_out_" . $otherid,
													"disabled"	=> "disabled"
												), $value));
										} else
										{
												$node->append(new HTMLNode('input', array(
													"type"	=> "radio",
													"name"	=> $this->name,
													"value"	=> $value,
													"id"	=> "radio_out_" . $otherid
												), $value));
										}
										$node->append(new HTMLNode("label", array(
											"style"	=> array("display" => "inline"), // inline hack
											"for"	=> "radio_out_" . $otherid
										), $title));
										$node->append(new HTMLNode("div", array(
											"id"	=> "container_" . $otherid
										), array(
											$this->form()->fields[$name]->field()
										)));					
										$this->form()->renderedFields[$name] = true;	
								}
								
						} else
						{
								if($value == $this->value)
								{
										$key = randomString(3);
										if($this->all_disabled || isset($this->disabled[$value]))
										{
												$node->append(new HTMLNode('input', array(
													"type"		=> "radio",
													"name"		=> $this->name,
													"value"		=> $value,
													"checked"	=> "checked",
													"disabled"	=> "disabled",
													"id"		=> "radio_" . $key
												), $value));
										} else
										{
												$node->append(new HTMLNode('input', array(
													"type"		=> "radio",
													"name"		=> $this->name,
													"value"		=> $value,
													"checked"	=> "checked",
													"id"		=> "radio_" . $key
												), $value));
										}
										$node->append(new HTMLNode("label", array(
											"style"	=> array("display" => "inline"), // inline hack
											"for"	=> "radio_" . $key
										), $title));
								} else
								{
										$key = randomString(3);
										if($this->all_disabled || isset($this->disabled[$value]))
										{
												$node->append(new HTMLNode('input', array(
													"type"		=> "radio",
													"name"		=> $this->name,
													"value"		=> $value,
													"disabled"	=> "disabled",
													"id"		=> "radio_" . $key
												), $value));
										} else
										{
												$node->append(new HTMLNode('input', array(
													"type"	=> "radio",
													"name"	=> $this->name,
													"value"	=> $value,
													"id"		=> "radio_" . $key
												), $value));
										}
										$node->append(new HTMLNode("label", array(
											"style"	=> array("display" => "inline"), // inline hack
											"for"	=> "radio_" . $key
										), $title));
								}
								
								$node->append(new HTMLNode("br"));
						}
						unset($otherid, $value, $title);
				}
			
				$this->container->append($node);
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
		
		public function JS()
		{
				return '$(function(){
					var radioids =  '.json_encode($this->ids).';
					$container = $("#'.$this->divID().'");
					for(i in radioids)
					{	
						
						var id = "radio_out_" + radioids[i];
						if(!$("#" + id).attr("checked"))
						{
							var otherid =  radioids[i] + "_div";
							$("#" + otherid).css("display", "none");
						}
					}
					$container.find(" > div > input[type=radio]").click(function(){
						var radioids =  '.json_encode($this->ids).';
						for(i in radioids)
						{
							var id = "radio_out_" + radioids[i];
							if(!$("#" + id).prop("checked"))
							{
								var otherid = radioids[i] + "_div";
								$("#" + otherid).slideUp("fast");
							}
						}
						
						var currid = $(this).attr("id").replace("radio_out_", "") + "_div";
						$("#" + currid).slideDown("fast");
					});
				});';
		}
		
		/**
		 * disables the field or a given key
		 *@name disable
		 *@access public
		 *@param string - optional, if just field
		*/
		public function disable($num = 0)
		{
				if($num === 0)
				{
						$this->all_disabled = true;
				} else
				{
						$this->disabled[$num] = true;
				}
		}
		/**
		 * reenables the field or a given key
		 *@name enable
		 *@access public
		 *@param string - optional, if just field
		*/
		public function enable($num = 0)
		{
				if($num === 0)
				{
						$this->all_disabled = false;
						$this->disabled = array();
				} else
				{

						unset($this->disabled[$num]);
						
				}
		}
}