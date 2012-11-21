<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 21.09.2012
  * $Version 1.4.1
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class DropDown extends FormField 
{
		/**
		 * request stuff
		 *
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array(
			"nojs/\$page"				=> "nojs",
			"getData/\$page/\$search" 	=> "getData",
			"checkValue/\$value"		=> "checkValue",
			"uncheckValue/\$value"		=> "uncheckValue",
		);
		
		/**
		 * allowed actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array("getData", "checkValue", "uncheckValue", "nojs");
		
		/**
		 * dataset of this dropdown
		 *
		 *@name dataset
		 *@access public
		*/
		public $dataset;
		
		/**
		 * whether multiple values are selectable
		 *
		 *@name multiselect
		 *@access protected
		*/
		protected $multiselect = false;
		
		/**
		 * options of this dropdown
		 *
		 *@name options
		 *@access public
		*/
		public $options;
		
		/**
		 * unique key for this field
		 *
		 *@name key
		 *@access protected
		*/
		protected $key;
		
		/**
		 * value
		 *
		 *@name value
		 *@access public
		*/
		public $value = "";
		
		/**
		 * this field needs to have the full width
		 *
		 *@name fullSizedField
		*/
		protected $fullSizedField = true;
		
		
		
		/**
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param array|int - selected items
		 *@param object - parent
		*/
		public function __construct($name = "", $title = null, $options = array(), $value = null, &$parent = null)
		{
				
				parent::__construct($name, $title, $value, $parent);
				$this->options = $options;
		}
		
		/**
		 * generates the key and array of selected data
		 *
		 *@name getValue
		 *@access public
		*/
		public function getValue() {
			// if mutliselect, we have to store an array
			if($this->multiselect) {
				
				if($this->POST && isset($this->form()->post[$this->PostName()]) && session_store_exists("dropdown_" . $this->PostName() . "_" . $this->form()->post[$this->PostName()])) {
					$dataset = session_restore("dropdown_" . $this->PostName() . "_" . $this->form()->post[$this->PostName()]);
					if(is_array($dataset)) {
						$this->dataset = $dataset;
						$this->key = $this->form()->post[$this->PostName()];
						$this->input->value = $this->key;
						return true;
					}
				}
				
				if($this->value !== null && $this->value !== false && !is_object($this->value)) {
					if(is_array($this->value)) {
						$this->dataset = $this->value;
					} else {
						$this->dataset = array($this->value);
					}
					$this->key = randomString(5);
				} else if($this->POST && isset($this->form()->result[$this->name]) && $this->value == null) {
					$this->dataset = $this->form()->result[$this->name];
					$this->key = randomString(5);
				} else {
					$this->dataset = array();
					$this->key = randomString(5);
				}
				$this->input->value = $this->key;
				
			 } else {
			 	parent::getValue();
			 	$this->input->value = $this->value;
			 }
		}
		
		/**
		  * creates the hidden input-field
		  *
		  *@name createNode
		  *@access public
		*/
		public function createNode() {
			$node = parent::createNode();
			$node->type = "hidden";
			$node->class = "value";
			if($this->multiselect)
				$node->value = $this->key;
			else
				$node->value = $this->value;
				
			return $node;
		}
		
		/**
		 * renders after setForm the whole field
		 *
		 *@name renderAfterSetForm
		 *@access public
		*/
		public function renderAfterSetForm() {
			parent::renderAfterSetForm();
			
			Resources::add("dropdown.css");
			Resources::add("system/form/dropdown.js", "js", "tpl");
			
			$this->widget = new HTMLNode("div", array(
				"class"		=> "dropdown_widget",
				"id"		=> $this->ID() . "_widget"
			),array(
				new HTMLNode("input", array(
					"type" 		=> "submit",
					"name"		=> "field_action_".$this->name."_nojs",
					"value"		=> "",
					"class"		=> "hiddenbutton"
				)),
				$this->field = new HTMLNode("div", array(
					"class"		=> "field",
					"id"		=> $this->ID() . "_field"
				), $this->renderInput()),
				$this->dropdown = new HTMLNode("div", array(
					"class"	=> "dropdown"
				), array(
					new HTMLNode("div", array(
						"class"	=> "header"
					), array(
						new HTMLNode("input", array(
							"type"			=> "text",
							"id"			=> $this->ID() . "_search",
							"class"			=> "search",
							"placeholder"	=> lang("search", "search...")
						)),
						new HTMLNode("a", array(
							"href"		=> "javascript:;",
							"class"		=> "cancel"
						)),
						new HTMLNode("div", array("class" => "pagination"), array(
							new HTMLNode("span", array("class" => "left"), array(
								new HTMLNode("a", array(
									"class"	=> "left disabled",
									"href"	=> "javascript:;"
								), "")
							)),
							new HTMLNode("span", array("class" => "right"), array(
								new HTMLNode("a", array(
									"class"	=> "right disabled",
									"href"	=> "javascript:;"
								), "")
							))
						)),
						new HTMLNode("div", array("class" => "clear"))
					)),
					new HTMLNode("div", array(
						"class"	=> "content"
					))
				))
			));
			
			
		}
		
		/**
		 * renders the data in the input
		 *
		 *@name renderInput
		 *@access public
		*/
		public function renderInput() {
			if($this->multiselect) {
				$str = "";
				$i = 0;
				foreach($this->dataset as $id) {
					if($i == 0) {
						$i++;
					} else {
						$str .= ", ";
					}
					$str .= isset($this->options[$id]) ? convert::raw2text($this->options[$id]) : convert::raw2text($id);
				}
				if($str == "")
					return lang("form_dropdown_nothing_select", "Nothing Selected");
					
				
				return $str;
			} else {
				return ($this->value == "") ? lang("form_dropdown_nothing_select", "Nothing Selected") : isset($this->options[$this->value]) ? convert::raw2text($this->options[$this->value]) : convert::raw2text($this->value);
			}
		}
		
		/**
		 * returns the result
		 *
		 *@name result
		 *@access public
		*/
		public function result() {
			if(!$this->disabled)
				if($this->multiselect)
					return $this->dataset;
				else
					return parent::result();
			else
				return null;
		}
		
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FormField::field");
				session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
				$this->callExtending("beforeField");
				
				if(!$this->disabled)
					Resources::addJS("$(function(){ var _".md5("dropdown_".$this->ID()) . " = new DropDown('".$this->ID()."', ".var_export($this->externalURL(), true).", ".var_export($this->multiselect, true)."); });");
				
				if($this->disabled) {
					$this->field->disabled = "disabled";
					$this->field->css("background-color", "#ddd");
					$this->widget->getNode(0)->attr("disabled", "disabled");
				}
				
				$this->container->append(new HTMLNode(
					"label",
					array("for"	=> $this->ID()),
					$this->title
				));
				
				$this->container->append($this->input);
				$this->container->append(new HTMLNode("div", array("class" => "widgetwrapper"), array($this->widget)));
				$this->container->addClass("dropdownContainer");
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormField::field");
				
				return $this->container;
		}
		
		/**
		 * getDataFromModel
		 *
		 *@param numeric - page
		*/
		public function getDataFromModel($p = 1) {
			if(count($this->options) > 10) {
				$start = ($p * 10) - 10;
				$end = $start + 9;
				$i = 0;
				$left = ($p == 1) ? false : true;
				if(isset($this->options[0])) {
					$arr = array();
					foreach($this->options as $value) {
						if($i < $start) {
							$i++;
							continue;
						}
						if($i >= $end) {
							$right = true;
							break;
						}
						$arr[$value] = $value;
						$i++;
					}
				} else {
					$arr = array();
					foreach($this->options as $key => $value) {
						if($i < $start) {
							$i++;
							continue;
						}
						if($i >= $end) {
							$right = true;
							break;
						}
						$arr[$key] = $value;
						$i++;
					}
				}
				// clean up
				unset($i, $start, $end);
				$arr = array_map(array("text", "protect"), $arr);
				return array("data"	=> $arr, "right" => $right, "left" => $left);
			} else {
				if(isset($this->options[0])) {
					return array("data" => array_map(array("convert", "raw2text"), ArrayLib::key_value($this->options)));
				} else {
					return array("data" => array_map(array("convert", "raw2text"), $this->options));
				}
			}
		}
		
		/**
		 * searches data from the optinos
		 *
		 *@name searchDataFromModel
		 *@param numeric - page
		*/
		public function searchDataFromModel($p = 1, $search = "") {
			// first get result
			$data = $this->options;
			$result = array();
			foreach($data as $key => $val) {
				if(_eregi(preg_quote($search), $val)) {
					$result[$key] = preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", convert::raw2text($val));
				}
			}
			// second order result
			if(count($result) > 10) {
				$start = ($p * 10) - 10;
				$end = $start + 9;
				$i = 0;
				$left = ($p == 1) ? false : true;
				if(isset($result[0])) {
					$arr = array();
					foreach($result as $value) {
						if($i < $start) {
							$i++;
							continue;
						}
						if($i >= $end) {
							$right = true;
							break;
						}
						$arr[$value] = $value;
						$i++;
					}
				} else {
					$arr = array();
					foreach($result as $key => $value) {
						if($i < $start) {
							$i++;
							continue;
						}
						if($i >= $end) {
							$right = true;
							break;
						}
						$arr[$key] = $value;
						$i++;
					}
				}
				// clean up
				unset($i, $start, $end);
				
				return array("data"	=> $arr, "right" => $right, "left" => $left);
			} else {
				if(isset($result[0])) {
					return array("data" => ArrayLib::key_value($result));
				} else {
					return array("data" =>  $result);
				}
			}
		}
		/**
		 * gets data
		 *
		 *@name getData
		 *@access public
		*/
		public function getData() {
			$page = $this->getParam("page");
			$search = $this->getParam("search");
			if($search != "" && $search != lang("search", "search...")) {
				$data = $this->searchDataFromModel($page, $search);
			} else {
				$data = $this->getDataFromModel($page);
			}
			
			$arr = $data["data"];
			
			//print_r($this);
			$value = ($this->multiselect) ? array_values($this->dataset) : array($this->value);
			
			HTTPResponse::addHeader("content-type", "text/x-json");
			
			if(empty($value) || $value[0] === null) {
				$value = array();
			} else {
				$value = array_flip($value);
			}
			
			// left and right is pagination (left arrow and right)
			return json_encode(array("data" => $arr, "left" => (isset($data["left"])) ? $data["left"] : false, "right" => (isset($data["right"])) ? $data["right"] : false, "value" => $value));
		}
		
		/**
		 * checks a value
		 *
		 *@name checkValue
		 *@access public
		*/
		public function checkValue() {
			
			if($this->multiselect) {
				$this->dataset[] = $this->getParam("value");
				session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
			} else {
				$this->value = $this->getParam("value");
			}
			if(Core::is_ajax()) {
				return $this->renderInput();
			} else {
				if($this->multiselect)
					$this->form()->post[$this->PostName()] = $this->key;
				else
					$this->form()->post[$this->PostName()] = $this->value;
				$this->form()->redirectToForm();
			}
		}
		
		/**
		 * unchecks a value
		 *
		 *@name checkValue
		 *@access public
		*/
		public function uncheckValue() {
			if($this->multiselect) {
				$key = array_search($this->getParam("value"), $this->dataset);
				unset($this->dataset[$key]);
				session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
			}
			if(Core::is_ajax()) {
				return $this->renderInput();
			} else {
				if($this->multiselect)
					$this->form()->post[$this->PostName()] = $this->key;
				else
					$this->form()->post[$this->PostName()] = $this->value;
				$this->form()->redirectToForm();
			}
		}
		/**
		 * returns data for no js
		 *
		 *@name nojs
		 *@åccess public
		*/
		public function nojs() {
			$page = $this->getParam("page", "get");
			$widget = new HTMLNode("div", array(
				"class"		=> "dropdown_widget",
				"id"		=> $this->ID() . "_widget"
			),array(
				
				$field = new HTMLNode("a", array(
					"href"		=> $this->form()->url,
					"class"		=> "field",
					"id"		=> $this->ID() . "_field",
					"style"		=> "margin: 0;"
				), $this->renderInput()),
				$dropdown = new HTMLNode("div", array(
					"class"	=> "dropdown",
					"style" => array(
						"display" => "block"
					)
				), array(
					new HTMLNode("div", array(
						"class"	=> "header"
					), array(
						new HTMLNode("div", array("class" => "pagination"), array(
							new HTMLNode("span", array("class" => "left"), array(
								$left = new HTMLNode("a", array(
									"class"	=> "left disabled",
									
								), "")
							)),
							new HTMLNode("span", array("class" => "right"), array(
								$right = new HTMLNode("a", array(
									"class"	=> "right disabled"
								), "")
							))
						)),
						new HTMLNode("div", array("class" => "clear"))
					)),
					$content = new HTMLNode("div", array(
						"class"	=> "content"
					))
				))
			));
			$page = ($page === null) ? 1 : $page;
			$data = $this->getDataFromModel($page);
			
			if(isset($data["right"]) && $data["right"]) {
				$p = $page + 1;
				$right->href = URL . "?field_action_" . $this->name . "_nojs&page=" . $p;
				$right->removeClass("disabled");
			}
			
			if(isset($data["left"]) && $data["left"]) {
				$p = $page - 1;
				$left->href = URL . "?field_action_" . $this->name . "_nojs&page=" . $p;
				$left->removeClass("disabled");
			}
			
			if($data["data"]) {
				$list = new HTMLNode("ul");
				foreach($data["data"] as $id => $value) {
					$li = new HTMLNode("li");
					
					if(is_array($value)) {
						$value = array_values($value);
						$smallText = $value[1];
						$value = $value[0];
					}
					
					if(($this->multiselect && in_array($id, $this->dataset)) || $this->value == $id) {
						$li->append("<a href=\"".$this->externalURL()."/uncheckValue/".urlencode($id)."\" class=\"checked\" id=\"dropdown_".$this->id()."_".dbescape($id)."\">".$value."</a>");
					} else {
						$li->append("<a href=\"".$this->externalURL()."/checkValue/".urlencode($id)."\" id=\"dropdown_".$this->id()."_".dbescape($id)."\">".$value."</a>");
					}
					
					if(isset($smallText)) {
						$li->append('<span class="record_info">'.$smallText.'</span>');
						unset($smallText);
					}
					
					$list->append($li);
					unset($li, $value, $id);
				}
			
				$content->append($list);
			} else {
				$content->append('<div class="no_data">' . lang("no_result", "There is no data to show.") . '</div>');
			}
			
			$container = new HTMLNode("div", array("id" => $this->divID(), "class" => $this->container->__get("class")), array(
				new HTMLNode("label", array(), $this->title),
				new HTMLNode("div", array("class" => "widget_wrapper"), array(
					$widget
				))
				
			));
			
			$widget->addClass("nojs");
			
			unset($data);
			
			return $container->render();
		}
		
		/**
		 * validation for security reason
		 *
		 *@name validate
		*/
		public function validate($value) {
			if(!$this->multiselect && $this->options) {
				if(!isset($this->options[$value])) {
					return false;
				}
			}
		
			return true;
		}
}