<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 30.08.2012
  * $Version 1.3.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class RequiredFields extends FormValidator
{
		/**
		 *@name __construct
		 *@param mixed - datas
		 *@return object
		*/
		public function __construct($data)
		{
				parent::__construct();
				
				if(!is_array($data)) {
					throwError(6, "Invalid Argument", "RequiredFields requires an array to be given.");
				}
				
				$this->data = $data;
		}
		
		
		/**
		 * validates the data
		 *@name validate
		 *@return bool|string
		*/
		public function validate()
		{
				// get data
				$valid = true;
				$err = "";
				$missing = array();
				foreach($this->data as $field)
				{
						if(isset($this->form->fields[$field]))
						{
								$f = $this->form->fields[$field];
								if(!isset($this->form->result[$field]) || empty($this->form->result[$field]) || (is_object($this->form->result[$field]) && is_a($this->form->result[$field], "ViewAccessableData") && !$this->form->result[$field]->bool()))
								{
										$valid = false;
										$missing[] = $f->title;
								} else
								{
										// own validation
										$v = $this->form->fields[$field]->validate($this->form->result[$field]);
										if($v !== true)
										{
												$valid = false;
												$err .= $v;
												$missing[] = $f->title;
										}
								}
						}
				}
				
				// create response for it
				if($valid === true)
				{
						return true;
				} else
				{
						$text = lang("form_required_fields", "Please fill out the oligatory fields");
						$i = 0;
						foreach($missing as $value)
						{
								if($i == 0)
								{
										$i = 1;
								} else
								{
										$text .= ", ";
								}
								$text .= ' \'' . $value . '\'';
						}
						return $err . $text;
				}
		}
		/**
		 * javascript for client-side validation
		 *
		 *@name JS
		 *@access public
		*/
		public function JS()
		{
				$js = '$(function(){ ';
					$js .= 'if($("#form_'.$this->form->name.'").length > 0)
							{
								$("#form_'.$this->form->name.'").bind("formsubmit", function()
								{
									var require_lang = "<div class=\"err\" style=\"color: #ff0000;\">'.lang("form_required_field").'</div>";
									
									var valid = true;
								';
				foreach($this->data as $field)
				{
						if(!isset($this->form->fields[$field]))
						{
								continue;
						}
						$f = $this->form->fields[$field];
						$key = "v_" . $field;
						$js .= "var ".$key." = function() { " . $f->jsValidation() . "} 
						if(".$key."() === false) {
							valid = false;	
						}";
						$js .= 'if($("#'.$f->ID().'").length > 0)
								{
									if($("#'.$f->ID().'").length > 0)
									{
										// input
										if($("#'.$f->ID().'").val() == "")
										{
											$("#'.$f->ID().'").parent().append(require_lang);
											valid = false;
										}
									}											
									
								}
								';
				}
				// end foreach
				$js .= '		
									if(valid == false)
										return false;
								
								});
							}
						});';
				return $js;
		}
}