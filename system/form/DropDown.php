<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 20.06.2011
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class DropDown extends FormField 
{
		/**
		 * request stuff
		*/
		public $url_handlers = array(
			"nojs/\$page"				=> "nojs",
			"getData/\$page/\$search" 	=> "getData",
			"checkValue/\$value"		=> "checkValue",
			"uncheckValue/\$value"		=> "uncheckValue",
		);
		public $allowed_actions = array("getData", "checkValue", "uncheckValue", "nojs");
		/**
		 * dataset
		*/
		public $dataset;
		/**
		 * whether multiple values are selectable
		*/
		public $mutliselect = false;
		/**
		 *@param string - name
		 *@param string - title
		 *@param array - options
		 *@param array|int - selected items
		 *@param object - parent
		*/
		public function __construct($name = "", $title = null, $options = array(), $value = null, $parent = null)
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
				
				if($this->POST && isset($this->form()->post[$this->name]) && session_store_exists("dropdown_" . $this->name . "_" . $this->form()->post[$this->name])) {
					$dataset = session_restore("dropdown_" . $this->name . "_" . $this->key);		
					if(is_array($dataset)) {
						$this->dataset = $dataset;
						$this->key = $this->form()->post[$this->name];
						return true;
					}
				} else if($this->value) {
					if(is_array($this->value)) {
						$this->dataset = $this->value;
					} else {
						$this->dataset = array($this->value);
					}
					$this->key = randomString(5);
				} else if($this->POST && isset($this->form()->result[$this->name]) && $this->value == null) {
					$this->value = $this->form()->result[$this->name];
					$this->key = randomString(5);
				} else {
					$this->value = array();
					$this->key = randomString(5);
				}
				$this->input->value = $this->key;
				
			 } else {
			 	parent::getValue();
			 	$this->input->value = $this->value;
			 }
		}
		/**
		  * creates the input-field
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
		*/
		public function renderAfterSetForm() {
			parent::renderAfterSetForm();
			
			Resources::add("dropdown.css");
			Resources::add("system/form/dropdown.js", "js", "tpl");
			
			
			Resources::addData("var loading_lang = '".lang("loading", "loading...")."';");
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
							"id"			=> $this->ID() . "_serach",
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
					$str .= isset($this->options[$id]) ? text::protect($this->options[$id]) : text::protect($id);
				}
				if($str == "")
					return lang("form_dropdown_nothing_select", "Nothing Selected");
					
				
				return $str;
			} else {
				return ($this->value == "") ? lang("form_dropdown_nothing_select", "Nothing Selected") : isset($this->options[$this->value]) ? text::protect($this->options[$this->value]) : text::protect($this->value);
			}
		}
		/**
		 * returns the result
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
				session_store("dropdown_" . $this->name . "_" . $this->key, $this->dataset);
				$this->callExtending("beforeField");
				
				if(!$this->disabled)
					Resources::addJS("$(function(){ var dropdown_".$this->ID()." = new DropDown('".$this->ID()."', ".var_export($this->externalURL(), true).", ".var_export($this->multiselect, true)."); });");
					Resources::addData("var lang_search = '".lang("search", "search...")."';");
					Resources::addData("var lang_no_result = '".lang("no_result", "There is no data to show.")."';");
				
				if($this->disabled) {
					$this->field->disabled = "disabled";
					$this->field->css("background-color", "#ddd");
				}
				
				$this->container->append(new HTMLNode(
					"label",
					array("for"	=> $this->ID()),
					$this->title
				));
				
				$this->container->append($this->input);
				$this->container->append(new HTMLNode("div", array("class" => "widgetwrapper"), array($this->widget)));
				
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
					return array("data" => array_map(array("text", "protect"), ArrayLib::key_value($this->options)));
				} else {
					return array("data" => array_map(array("text", "protect"), $this->options));
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
					$result[$key] = preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", text::protect($val));
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
			$value = ($this->multiselect) ? $this->dataset : array($this->value);
			
			HTTPResponse::addHeader("content-type", "text/x-json");
			
			if(empty($value) || (isset($value[0]) && $value[0] == null)) {
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
				session_store("dropdown_" . $this->name . "_" . $this->key, $this->dataset);
			} else {
				$this->value = $this->getParam("value");
			}
			if(Core::is_ajax()) {
				return $this->renderInput();
			} else {
				$this->form()->post[$this->name] = $this->value;
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
				session_store("dropdown_" . $this->name . "_" . $this->key, $this->dataset);
			}
			if(Core::is_ajax()) {
				return $this->renderInput();
			} else {
				
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
			
			if($data["right"]) {
				$p = $page + 1;
				$right->href = URL . "?field_action_" . $this->name . "_nojs&page=" . $p;
				$right->removeClass("disabled");
			}
			
			if($data["left"]) {
				$p = $page - 1;
				$left->href = URL . "?field_action_" . $this->name . "_nojs&page=" . $p;
				$left->removeClass("disabled");
			}
			
			if($data["data"]) {
				$list = new HTMLNode("ul");
				foreach($data["data"] as $id => $value) {
					if($this->multiselect) {
						if(in_array($id, $this->dataset)) {
							$list->append("<li><a href=\"".$this->externalURL()."/uncheckValue/".urlencode($id)."\" class=\"checked\" id=\"dropdown_".$this->id()."_".dbescape($id)."\">".$value."</a></li>");
						} else {
							$list->append("<li><a href=\"".$this->externalURL()."/checkValue/".urlencode($id)."\" id=\"dropdown_".$this->id()."_".dbescape($id)."\">".$value."</a></li>");
						}
					} else {
						if($id == $this->value) {
							$list->append("<li><a href=\"".$this->externalURL()."/checkValue/".urlencode($id)."\" class=\"checked\" id=\"dropdown_".$this->id()."_".dbescape($id)."\">".$value."</a></li>");
						} else {
							$list->append("<li><a href=\"".$this->externalURL()."/checkValue/".urlencode($id)."\" id=\"dropdown_".$this->id()."_".dbescape($id)."\">".$value."</a></li>");
						}
					}
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
			
			
			
			return $container->render();
		}
}