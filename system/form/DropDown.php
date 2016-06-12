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
	 * @var array
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
	 * @var array
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
	 * @var mixed
	 */
	public $dataset;

	/**
	 * options of this dropdown.
	 *
	 * @var array|DataObjectSet
	 */
	public $options;

	/**
	 * field to show in dropdown
	 *
	 * @name showfield
	 * @access public
	 */
	public $showfield = "title";

	/**
	 * info-field
	 *
	 *@name info_field
	 *@access public
	 */
	public $info_field;

	/**
	 * this field needs to have the full width.
	 *
	 * @var bool
	 */
	protected $fullSizedField = true;

	/**
	 * unique key for this field.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * whether multiple values are selectable. This is for subclasses only (@link
	 * MultiSelectDropDown).
	 *
	 * @var bool
	 */
	protected $multiselect = false;

	/**
	 * sortable relationships.
	 *
	 * @var bool
	 */
	public $sortable = false;

	/**
	 * Constructor.
	 *
	 * @param string $name unique name in the form
	 * @param string $title label for the field
	 * @param array $options array of key-value for the data selectable
	 * @param int|string $value a integer or string for the selected item
	 * @param gObject $parent a Form object if you want to give the form it will be
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
			if($this->POST && $this->parent && ($postData = $this->parent->getFieldPost($this->PostName())) && Core::globalSession()->hasKey("dropdown_" . $this->PostName() . "_" . $postData)) {
				$dataset = Core::globalSession()->get("dropdown_" . $this->PostName() . "_" . $postData);
				if(is_array($dataset)) {
					$this->dataset = $dataset;
					$this->key = $postData;
					$this->input->value = $this->key;
					return true;
				}
			}

			if($this->model !== null && $this->model !== false && !is_object($this->model)) {
				if(is_array($this->model)) {
					$this->dataset = $this->model;
				} else {
					$this->dataset = array($this->model);
				}
				$this->key = randomString(5);
			} else if($this->POST && $this->parent && $this->parent->getFieldValue($this->dbname) && $this->model == null) {
				$this->dataset = $this->parent->getFieldValue($this->dbname);

				$this->key = randomString(5);
			} else {
				$this->dataset = null;
				$this->key = randomString(5);
			}
			$this->input->value = $this->key;

			if(is_object($this->dataset) && gObject::method_exists($this->dataset->classname, "toArray")) {
				$this->dataset = $this->dataset->ToArray();
			}

		} else {
			if($model = $this->getModel()) {
				$this->input->value = $model;
			}
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
	 * @param FormFieldRenderData $info
	 * @param bool $notifyField
	 */
	public function addRenderData($info, $notifyField = true)
	{
		$info->addCSSFile("dropdown.css");
		$info->addJSFile("system/form/dropdown.js");
		$info->addCSSFile("font-awsome/font-awesome.css");

		parent::addRenderData($info, $notifyField);
	}

	/**
	 * @return string|DataObject|DataObjectSet
	 */
	protected function getDataFromValue() {
		if(is_a($this->options, "DataObjectSet")) {
			$info = clone $this->options;
			if($this->multiselect) {
				$info->addFilter(array("id" => $this->dataset));

				return $info;
			} else {
				$info->addFilter(array("id" => $this->getModel()));

				return $info->first();
			}
		} else {
			if($this->multiselect) {
				return $this->dataset;
			} else {
				return $this->getModel();
			}
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
			foreach($this->getDataFromValue() as $id) {
				$data[$this->getKeyFromKey($id)] = $this->getIdentifierFromKey($id);
			}
			return $data;

		}

		if($record = $this->getDataFromValue()) {
			return array($this->getKeyFromKey($record) => $this->getIdentifierFromValue($record));
		}

		return array();
	}

	/**
	 * generates the result of this form-field.
	 *
	 * @return mixed it is an array in case of multiselect-field or string in
	 * single-select-mode.
	 * @throws FormInvalidDataException
	 */
	public function result() {
		$this->getValue();

		return $this->getDataFromValue();
	}

	/**
	 * renders the field for the Form-renderer.
	 *
	 * @param FormFieldRenderData $info
	 * @return HTMLNode Object of HTMLNode, which can be rendered with @link
	 * HTMLNode::render
	 */
	public function field($info) {
		if(PROFILE)
			Profiler::mark("FormField::field");

		Core::globalSession()->set("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
		$this->callExtending("beforeField");

		if($this->sortable) {
			gloader::load("sortable");
		}

		if($this->disabled) {
			$this->field->disabled = "disabled";
			$this->container->addClass("disabled", "disabled");
		}

		$this->container->append(new HTMLNode("label", array("for" => $this->ID()), $this->title));

		$this->container->append($this->input);
		$this->container->append(new HTMLNode("div", array("class" => "widgetwrapper"), array($this->widget)));
		$this->container->addClass("dropdownContainer");

		if($this->errors) {
			$this->container->addClass("form-field-has-error");
		}

		$this->callExtending("afterField");

		if(PROFILE)
			Profiler::unmark("FormField::field");

		return $this->container;
	}

	public function JS() {
		if($this->disabled) {
			return parent::JS();
		}

		return "$(function(){ var ".$this->javascriptVariable()." = new DropDown(this, field, '" . $this->ID() . "', " . var_export($this->externalURL(), true) . ", " . var_export($this->multiselect, true) . ", ".var_export($this->sortable, true)."); });" . parent::JS();
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
	 * @return array this is an array which contains the data as data and some
	 * information about paginating.
	 */
	public function getDataFromModel($page = 1) {
		$cloned = is_object($this->options) ? clone $this->options : $this->options;
		return $this->getResultFromData($page, $cloned);
	}

	/**
	 * generates the data, which should be shown in the dropdown if the user
	 * searches.
	 *
	 * @param int $page number of page the user wants to see
	 * @param string $search the phrase to search for
	 *
	 * @return array this is an array which contains the data as data and some
	 * information about paginating.
	 */
	public function searchDataFromModel($page = 1, $search = "") {
		// first get result
		$result = array();
		if(is_array($this->options)) {
			foreach ($this->options as $key => $val) {
				if (preg_match('/' . preg_quote($search, '/') . '/i', $val)) {
					$result[$key] = preg_replace('/(' . preg_quote($search, "/") . ')/Usi', "<strong>\\1</strong>", convert::raw2text($val));
				}
			}
		} else if(is_a($this->options, "DataObjectSet")) {
			$result = clone $this->options;
			$result->search($search);
		}

		return $this->getResultFromData($page, $result);
	}

	/**
	 * creates result out of data.
	 *
	 * @param int $page
	 * @param array $dataSource
	 * @return array
	 */
	protected function getResultFromData($page, $dataSource) {
		// generate paging-data
		$start = ($page * 10) - 10;
		$end = $start + 9;
		$i = 0;
		$left = ($page == 1) ? false : true;
		$right = false;
		$arr = array();

		foreach($dataSource as $key => $value) {
			if($i < $start) {
				$i++;
				continue;
			}
			if($i >= $end) {
				$right = true;
				break;
			}
			$arr[] = array(
				"key" => $this->getKeyFromInfo($dataSource, $key, $value),
				"value" => $this->getIdentifierFromValue($value),
				"smallText" => $this->getInfoFromValue($value)
			);
			$i++;
		}
		unset($i);

		/** @var DataSet|DataObjectSet $dataSource */
		return array(
			"data" => $arr,
			"right" => $right,
			"left" => $left,
			"showStart" => $start,
			"showEnd" => $end,
			"whole" => is_array($dataSource) ? count($dataSource) : $dataSource->countWholeSet()
		);
	}

	/**
	 * @param array|DataObjectSet $dataSource
	 * @param mixed $key
	 * @param mixed $value
	 * @return string
	 */
	protected function getKeyFromInfo($dataSource, $key, $value) {
		if(is_a($dataSource, "DataObjectSet")) {
			return $value->id;
		}

		if(isset($result[0])) {
			if(is_array($value) && isset($value[$this->showfield])) {
				return convert::raw2text($value[$this->showfield]);
			} else if(is_string($value)) {
				return $value;
			}
		}

		return $key;
	}

	/**
	 * @param string|DataObject|array $value
	 * @return string
	 */
	protected function getIdentifierFromValue($value)
	{
		if(is_string($value) || is_int($value)) {
			return convert::raw2text($value);
		}

		if(is_array($value)) {
			if(isset($value[$this->showfield])) {
				return convert::raw2text($value[$this->showfield]);
			} else {
				return "Unnamed Record " . convert::raw2text(print_r($value, true));
			}
		}

		if(is_object($value)) {
			if($value->{$this->showfield}) {
				return convert::raw2text($value->{$this->showfield});
			} else if(gObject::method_exists($value, "__toString")) {
				return (string) $value;
			}
		}

		throw new InvalidArgumentException("Option must have correct DataType: Array, Object or String.");
	}

	/**
	 * @param $id
	 * @return string|void
	 */
	protected function getIdentifierFromKey($id)
	{
		if(is_array($this->options) && isset($this->options[$id])) {
			$id = $this->options[$id];
		}

		return $this->getIdentifierFromValue($id);
	}

	/**
	 * @param DataObject|string $id
	 * @return string
	 */
	protected function getKeyFromKey($id) {
		if(is_a($id, "DataObject")) {
			return $id->id;
		}

		return $id;
	}

	/**
	 * @param mixed $value
	 * @return null|string
	 */
	protected function getInfoFromValue($value) {
		if(isset($this->info_field)) {
			if(is_array($value) && isset($value[$this->info_field])) {
				return $value[$this->info_field];
			}

			if(is_object($value)) {
				return $value->{$this->info_field};
			}
		}

		return null;
	}

	/**
	 * responds to a user-request and generates the data, which should be shown in
	 * the dropdown.
	 *
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
		$value = ($this->multiselect) ? array_values($this->dataset) : array($this->getKeyFromKey($this->getModel()));

		if(empty($value) || $value[0] === null) {
			$value = array();
		} else {
			$value = array_flip($value);
		}

		$response = array(
			"data" => $arr,
			"left" => (isset($data["left"])) ? $data["left"] : false,
			"right" => (isset($data["right"])) ? $data["right"] : false,
			"value" => $value,
			"page" => $page
		);

		if(isset($data["showStart"], $data["showEnd"])) {
			$response["showStart"] = $data["showStart"];
			$response["showEnd"] = $data["showEnd"];
		}

		if(isset($data["whole"])) {
			$response["whole"] = $data["whole"];
		}

		// left and right is pagination (left arrow and right)
		return new JSONResponseBody($response);
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
		Core::globalSession()->set("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
		return "ok";
	}

	/**
	 * responds to a user-request and marks a value as checked.
	 *
	 * @return string rendered dropdown-input
	 * @throws FormInvalidDataException
	 */
	public function checkValue() {
		if(!$this->disabled) {
			if ($this->validateValue($this->getParam("value"))) {
				if ($this->multiselect) {
					if ($this->validateValue($this->getParam("value"))) {
						$this->dataset[] = $this->getParam("value");
						Core::globalSession()->set("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
					} else {
						throw new FormInvalidDataException($this->name, "Value not allowed.");
					}
				} else {
					$this->model = $this->getParam("value");
				}
			}
		}

		return $this->redirectToFormOrRespond();
	}

	/**
	 * responds to a user-request and marks a value as unchecked.
	 *
	 * @return string rendered dropdown-input
	 */
	public function uncheckValue() {
		if(!$this->disabled) {
			if ($this->multiselect) {
				$key = array_search($this->getParam("value"), $this->dataset);
				unset($this->dataset[$key]);
				Core::globalSession()->set("dropdown_" . $this->PostName() . "_" . $this->key, $this->dataset);
			} else {
				if ($this->model == $this->getParam("value"))
					$this->model = null;
			}
		}

		return $this->redirectToFormOrRespond();
	}

	/**
	 * redirects back or renders form when on ajax.
	 *
	 * @return HTMLNode|void
	 */
	protected function redirectToFormOrRespond() {
		if($this->getRequest()->is_ajax()) {
			return $this->renderInputWidget();
		} else {
			if($this->multiselect)
				$this->getRequest()->post_params[$this->PostName()] = $this->key;
			else
				$this->getRequest()->post_params[$this->PostName()] = $this->value;
			return $this->form()->redirectToForm();
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
			if(is_a($value, "DataObject")) {
				$value = $value->id;
			}

			if(!$this->validateValue($value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function validateValue($value) {
		if(is_a($this->options, "DataObjectSet")) {
			$result = clone $this->options;
			$result->addFilter(array("id" => $value));
			return $result->count() > 0;
		}

		return $value === null || isset($this->options[0]) ? in_array($value, $this->options) : isset($this->options[$value]);
	}

	/**
	 * @return array|DataObjectSet
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param array|DataObjectSet $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}
}
