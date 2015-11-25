<?php defined("IN_GOMA") OR die();

/**
 * This is a simple searchable dropdown.
 *
 * It supports the same as Select, but also Search and Pagination for big data.
 *
 * @property HTMLNode widget
 * @package Goma\Form
 *
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author Goma-Team
 *
 * @version 1.6
 */
class DropDown extends FormField {
	/**
	 * url-handlers for controller-part.
	 *
	 * @access public
	 */
	public $url_handlers = array(
		"nojs/\$page" => "nojs",
		"getData/\$page/\$search" => "getData",
		"checkValue/\$value" => "checkValue",
		"uncheckValue/\$value" => "uncheckValue",
	);

	/**
	 * allowed actions for Controller-part.
	 *
	 * @access public
	 */
	public $allowed_actions = array(
		"getData",
		"checkValue",
		"uncheckValue",
		"nojs",
		"saveSort"
	);

	/**
	 * dataset of this dropdown.
	 *
	 * it holds the data for a multiselect-dropdown. Maybe this is also a object
	 * working as an array.
	 *
	 * @access public
	 * @var mixed
	 */
	public $dataset;

	/**
	 * options of this dropdown.
	 *
	 * @access public
	 */
	public $options;

	/**
	 * value.
	 *
	 * @access public
	 */
	public $value = "";

	/**
	 * this field needs to have the full width.
	 *
	 * @access protected
	 */
	protected $fullSizedField = true;

	/**
	 * unique key for this field.
	 *
	 * @access protected
	 */
	protected $key;

	/**
	 * whether multiple values are selectable. This is for subclasses only (@link
	 * MultiSelectDropDown).
	 *
	 * @access protected
	 */
	protected $multiselect = false;

	/**
	 * sortable relationships.
	 */
	public $sortable = false;

	/**
	 * Constructor.
	 *
	 * @param string $name unique name in the form
	 * @param string $title label for the field
	 * @param array $options array of key-value for the data selectable
	 * @param int|string $value a integer or string for the selected item
	 * @param object $parent a Form object if you want to give the form it will be
	 * applied to
	 *
	 * @access public
	 */
	public function __construct($name = "", $title = null, $options = array(), $value = null, &$parent = null) {

		parent::__construct($name, $title, $value, $parent);
		$this->options = $options;
	}

	/**
	 * gets the selected value from the current Form-Model or given data.
	 *
	 * @access public
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
			} else if($this->POST && isset($this->form()->result[$this->dbname]) && $this->value == null) {
				$this->dataset = $this->form()->result[$this->dbname];

				$this->key = randomString(5);
			} else {
				$this->dataset = null;
				$this->key = randomString(5);
			}
			$this->input->value = $this->key;

			if(is_object($this->dataset) && Object::method_exists($this->dataset->classname, "toArray")) {
				$this->dataset = $this->dataset->ToArray();
			}

		} else {
			parent::getValue();
			$this->input->value = $this->value;
		}
	}

	/**
	 * generates a hidden field for storing the data submitted via the form for this
	 * field.
	 *
	 * @access public
	 */
	public function createNode() {
		$node = parent::createNode();
		$node->type = "hidden";
		$node->classname = "value";
		if($this->multiselect)
			$node->value = $this->key;
		else
			$node->value = $this->value;

		return $node;
	}

	/**
	 * generates the whole field with dropdown.
	 *
	 * @access public
	 */
	public function renderAfterSetForm() {
		parent::renderAfterSetForm();

		Resources::add("dropdown.css");
		Resources::add("system/form/dropdown.js", "js", "tpl");
		Resources::add("font-awsome/font-awesome.css", "css", "tpl");

		$this->widget = new HTMLNode("div", array(
			"class" => "dropdown_widget",
			"id" => $this->ID() . "_widget"
		), array(
			new HTMLNode("input", array(
				"type" => "submit",
				"name" => "field_action_" . $this->name . "_nojs",
				"value" => "",
				"class" => "hiddenbutton"
			)),
			$this->field = new HTMLNode("div", array(
				"class" => "field",
				"id" => $this->ID() . "_field"
			), $this->renderInputWidget()),
			$this->dropdown = new HTMLNode("div", array("class" => "dropdown"), array(
				new HTMLNode("div", array("class" => "header"), array(
					new HTMLNode("input", array(
						"type" => "text",
						"autocomplete" => "off",
						"id" => $this->ID() . "_search",
						"class" => "search",
						"placeholder" => lang("search", "search...")
					)),
					new HTMLNode("a", array(
						"href" => "javascript:;",
						"class" => "cancel"
					)),
					new HTMLNode("div", array("class" => "pagination"), array(
						new HTMLNode("span", array("class" => "left"), array(new HTMLNode("a", array(
							"class" => "left disabled fa fa-angle-left fa-3x",
							"href" => "javascript:;"
						), ""))),
						new HTMLNode("span", array("class" => "right"), array(new HTMLNode("a", array(
							"class" => "right disabled fa fa-angle-right fa-3x",
							"href" => "javascript:;"
						), "")))
					)),
					new HTMLNode("div", array("class" => "clear"))
				)),
				new HTMLNode("div", array("class" => "content")),
				new HTMLNode("div", array("class" => "footer"))
			))
		));

		if($this->multiselect) {
			$this->widget->addClass("multi-value");
		} else {
			$this->widget->addClass("single-value");
		}

	}

	/**
	 * It renders the data displayed in the field, if not dropped down.
	 *
	 * @name renderInputWidget
	 * @return HTMLNode Object of HTMLNode, which can be rendered with @link
	 * HTMLNode::render
	 */
	public function renderInputWidget() {
		$data = $this->getInput();

		if($data) {
			$node = new HTMLNode("span", array("class" => "value-holder"));
			foreach($data as $id => $value) {
				$node->append(new HTMLNode("span", array("class" => "value", "id" => $this->name . "_" . $id), array(
					new HTMLNode("span", array("class" => "value-title"), convert::raw2text($value)),
					new HTMLNode("a", array(
						"class" => "value-remove",
						"data-id" => $id
					), "x")
				)));
			}

			$node->append(new HTMLNode("div", array("class" => "clear")));

			return $node;
		} else {
			return new HTMLNode("span", array("class" => "no-value"), lang("form_click_to_select", "Click to select"));
		}

	}

	/**
	 * generates the values displayed in the field, if not dropped down.
	 *
	 * @access protected
	 * @return array values
	 */
	protected function getInput() {
		if($this->multiselect) {
			$data = array();
			foreach($this->dataset as $id) {
				$data[$id] = isset($this->options[$id]) ? $this->options[$id] : $id;
			}
			return $data;

		} else if($this->value == "") {
			return array();
		} else {
			return isset($this->options[$this->value]) ? array($this->options[$this->value]) : array($this->value);
		}
	}

	/**
	 * generates the result of this form-field.
	 *
	 * @access public
	 * @return mixed it is an array in case of multiselect-field or string in
	 * single-select-mode.
	 */
	public function result() {

		$this->getValue();

		if(!$this->disabled) {
			if ($this->multiselect) {
				return $this->dataset;
			} else {
				return parent::result();
			}
		} else {
			return null;
		}
	}

	/**
	 * renders the field for the Form-renderer.
	 *
	 * @access public
	 * @return HTMLNode Object of HTMLNode, which can be rendered with @link
	 * HTMLNode::render
	 */
	public function field() {
		if(PROFILE)
			Profiler::mark("FormField::field");
		session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
		$this->callExtending("beforeField");

		if($this->sortable) {
			gloader::load("sortable");
		}

		if(!$this->disabled) {
			Resources::addJS("$(function(){ var ".$this->javascriptVariable()." = new DropDown('" . $this->ID() . "', " . var_export($this->externalURL(), true) . ", " . var_export($this->multiselect, true) . ", ".var_export($this->sortable, true)."); });");
		}

		if($this->disabled) {
			$this->field->disabled = "disabled";
			$this->field->css("background-color", "#ddd");
			$this->widget->getNode(0)->attr("disabled", "disabled");
		}

		$this->container->append(new HTMLNode("label", array("for" => $this->ID()), $this->title));

		$this->container->append($this->input);
		$this->container->append(new HTMLNode("div", array("class" => "widgetwrapper"), array($this->widget)));
		$this->container->addClass("dropdownContainer");

		$this->callExtending("afterField");

		if(PROFILE)
			Profiler::unmark("FormField::field");

		return $this->container;
	}

	/**
	 * public javascript-variable-name.
	 *
	 * @return string
	 */
	public function javascriptVariable() {
		return "_" . md5("dropdown_" . $this->ID());
	}

	/**
	 * generates the data, which should be shown in the dropdown.
	 *
	 * @param int $page number of page the user wants to see
	 *
	 * @access public
	 * @return array this is an array which contains the data as data and some
	 * information about paginating.
	 */
	public function getDataFromModel($page = 1) {
		$start = ($page * 10) - 10;
		$end = $start + 9;
		$i = 0;
		$left = ($page == 1) ? false : true;
		// check if this is an array with numeric indexes or not
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
				$arr[] = array(
					"key" => $value,
					"value" => convert::raw2text($value)
				);
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
				$arr[] = array(
					"key" => $key,
					"value" => convert::raw2text($value)
				);
				$i++;
			}
		}

		return array(
			"data" => $arr,
			"right" => $right,
			"left" => $left,
			"showStart" => $start,
			"showEnd" => $end,
			"whole" => count($this->options)
		);
	}

	/**
	 * generates the data, which should be shown in the dropdown if the user
	 * searches.
	 *
	 * @param int $p number of page the user wants to see
	 * @param string $search the phrase to search for
	 *
	 * @access public
	 * @return array this is an array which contains the data as data and some
	 * information about paginating.
	 */
	public function searchDataFromModel($p = 1, $search = "") {
		// first get result
		$data = $this->options;
		$result = array();
		foreach($data as $key => $val) {
			if(preg_match('/' . preg_quote($search, '/') . '/i', $val)) {
				$result[$key] = preg_replace('/(' . preg_quote($search, "/") . ')/Usi', "<strong>\\1</strong>", convert::raw2text($val));
			}
		}

		// generate paging-data
		$start = ($p * 10) - 10;
		$end = $start + 9;
		$i = 0;
		$left = ($p == 1) ? false : true;
		$right = false;

		// check if this is an array with numeric indexes or not
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
				$arr[] = array(
					"key" => $value,
					"value" => convert::raw2text($value)
				);
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
				$arr[] = array(
					"key" => $key,
					"value" => convert::raw2text($value)
				);
				$i++;
			}
		}
		// clean up
		unset($i);

		return array(
			"data" => $arr,
			"right" => $right,
			"left" => $left,
			"showStart" => $start,
			"showEnd" => $end,
			"whole" => count($result)
		);
	}

	/**
	 * responds to a user-request and generates the data, which should be shown in
	 * the dropdown.
	 *
	 * @access public
	 * @return string json-data to send to the client.
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

		$return = array(
			"data" => $arr,
			"left" => (isset($data["left"])) ? $data["left"] : false,
			"right" => (isset($data["right"])) ? $data["right"] : false,
			"value" => $value,
			"page" => $page
		);

		if(isset($data["showStart"], $data["showEnd"])) {
			$return["showStart"] = $data["showStart"];
			$return["showEnd"] = $data["showEnd"];
		}

		if(isset($data["whole"])) {
			$return["whole"] = $data["whole"];
		}

		// left and right is pagination (left arrow and right)
		return json_encode($return);
	}

	/**
	 * saves sort.
	 */
	public function saveSort() {
		$newSet = array();
		if(isset($_POST["sorted"])) {
			foreach($_POST["sorted"] as $id) {
				$newSet[] = $id;
			}
		}
		$this->dataset = $newSet;
		session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
		return "ok";
	}

	/**
	 * responds to a user-request and marks a value as checked.
	 *
	 * @access public
	 * @return string rendered dropdown-input
	 */
	public function checkValue() {

		if($this->multiselect) {
			$this->dataset[] = $this->getParam("value");
			session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
		} else {
			$this->value = $this->getParam("value");
		}
		if(Core::is_ajax()) {
			return $this->renderInputWidget();
		} else {
			if($this->multiselect)
				$this->form()->post[$this->PostName()] = $this->key;
			else
				$this->form()->post[$this->PostName()] = $this->value;
			$this->form()->redirectToForm();
		}
	}

	/**
	 * responds to a user-request and marks a value as unchecked.
	 *
	 * @access public
	 * @return string rendered dropdown-input
	 */
	public function uncheckValue() {
		if($this->multiselect) {
			$key = array_search($this->getParam("value"), $this->dataset);
			unset($this->dataset[$key]);
			session_store("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
		} else {
			if($this->value == $this->getParam("value"))
				$this->value = null;
		}

		if(Core::is_ajax()) {
			return $this->renderInputWidget();
		} else {
			if($this->multiselect)
				$this->form()->post[$this->PostName()] = $this->key;
			else
				$this->form()->post[$this->PostName()] = $this->value;
			$this->form()->redirectToForm();
		}
	}

	/**
	 * responds to a user-request and generates the whole field as no-javascript for
	 * NO-JS-Users.
	 *
	 * @access public
	 * @return string HTML Response
	 */
	public function nojs() {
		$page = $this->getParam("page", "get");
		$widget = new HTMLNode("div", array(
			"class" => "dropdown_widget",
			"id" => $this->ID() . "_widget"
		), array(

			$field = new HTMLNode("a", array(
				"href" => $this->form()->url,
				"class" => "field",
				"id" => $this->ID() . "_field",
				"style" => "margin: 0;"
			), $this->renderInputWidget()),
			$dropdown = new HTMLNode("div", array(
				"class" => "dropdown",
				"style" => array("display" => "block")
			), array(
				new HTMLNode("div", array("class" => "header"), array(
					new HTMLNode("div", array("class" => "pagination"), array(
						new HTMLNode("span", array("class" => "left"), array($left = new HTMLNode("a", array("class" => "left disabled", ), ""))),
						new HTMLNode("span", array("class" => "right"), array($right = new HTMLNode("a", array("class" => "right disabled"), "")))
					)),
					new HTMLNode("div", array("class" => "clear"))
				)),
				$content = new HTMLNode("div", array("class" => "content"))
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
					$li->append("<a href=\"" . $this->externalURL() . "/uncheckValue/" . urlencode($id) . "\" class=\"checked\" id=\"dropdown_" . $this->id() . "_" . convert::raw2sql($id) . "\">" . $value . "</a>");
				} else {
					$li->append("<a href=\"" . $this->externalURL() . "/checkValue/" . urlencode($id) . "\" id=\"dropdown_" . $this->id() . "_" . convert::raw2sql($id) . "\">" . $value . "</a>");
				}

				if(isset($smallText)) {
					$li->append('<span class="record_info">' . $smallText . '</span>');
					unset($smallText);
				}

				$list->append($li);
				unset($li, $value, $id);
			}

			$content->append($list);
		} else {
			$content->append('<div class="no_data">' . lang("no_result", "There is no data to show.") . '</div>');
		}

		$container = new HTMLNode("div", array(
			"id" => $this->divID(),
			"class" => $this->container->__get("class")
		), array(
			new HTMLNode("label", array(), $this->title),
			new HTMLNode("div", array("class" => "widget_wrapper"), array($widget))
		));

		$widget->addClass("nojs");

		unset($data);

		return $container->render();
	}

	/**
	 * validation for security reason, that the user can't check values aren't
	 * existing.
	 *
	 * @param mixed $value
	 * @return bool
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
