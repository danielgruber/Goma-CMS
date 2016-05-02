<?php defined("IN_GOMA") OR die();

/**
 * This is a simple searchable dropdown, which can be used to select has-one-relations.
 *
 * It supports has-one-realtions of DataObjects and just supports single-select.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.4.1
 */
class HasOneDropdown extends SingleSelectDropDown
{
	/**
	 * the name of the relation of the current field
	 *
	 * @name relation
	 * @access public
	 */
	public $relation;

	/**
	 * where clause to filter result in dropdown
	 *
	 * @name where
	 * @access public
	 */
	public $where;

	/**
	 * base model for querying.
	 *
	 * @var DataObjectSet
	 */
	protected $model;

	/**
	 * @var DataObject
	 */
	protected $_object;

	/**
	 * @param string $name
	 * @param string $title
	 * @param string $showField
	 * @param array $where
	 * @param string $value
	 * @param Form|null $parent
	 * @return HasOneDropdown
	 */
	public static function create($name, $title, $showField = "title", $where = array(), $value = null, $parent = null)
	{
		return new self($name, $title, $showField, $where, $value, $parent);
	}

	public static function createWithInfoField($name, $title, $showField = "title", $infoField = null, $where = array(), $value = null, $parent = null) {
		$field = self::create($name, $title, $showField, $where, $value, $parent);

		$field->info_field = $infoField;

		return $field;
	}

	public function __construct($name = "", $title = null, $showfield = "title", $where = array(), $value = null, &$parent = null)
	{
		parent::__construct($name, $title, $value, $parent);
		$this->relation = strtolower($name);
		$this->showfield = $showfield;
		$this->where = $where;
		$this->dbname = $this->dbname . "id";
	}

	/**
	 * sets the base-model for queriing DB.
	 * @param DataObjectSet $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}

	/**
	 * returns the model.
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * sets the value if not set
	 *
	 * @name getValue
	 * @access public
	 * @return bool|void
	 */
	public function getValue()
	{
		parent::getValue();

		if (!isset($this->value)) {

			// get has-one from result
			if (is_object($this->form()->result)) {
				/** @var ModelHasOneRelationshipInfo[] $has_one */
				$has_one = $this->form()->result->hasOne();
			}

			if (isset($has_one[$this->relation]) && is_object($has_one[$this->relation])) {
				$this->_object = $has_one[$this->relation]->getTargetClass();

				$this->value = isset(call_user_func_array(array($this->form()->result, $this->relation), array())->id) ? call_user_func_array(array($this->form()->result, $this->relation), array())->id : null;
				$this->input->value = $this->value;
			} else {

				// get has-one from model
				if (is_object($this->form()->model)) {
					/** @var ModelHasOneRelationshipInfo[] $has_one */
					$has_one = $this->form()->model->hasOne();
				} else {
					$has_one = null;
				}

				if (isset($has_one[$this->relation])) {
					$this->_object = $has_one[$this->relation]->getTargetClass();


					$this->value = isset(call_user_func_array(array($this->form()->model, $this->relation), array())->id) ? call_user_func_array(array($this->form()->model, $this->relation), array())->id : null;
					$this->input->value = $this->value;
				} else {
					throw new LogicException("{$this->relation} doesn't exist in the form {$this->form()->getName()}.");
				}
			}
		} else {
			// get has-one from result
			if (is_object($this->form()->result)) {
				/** @var ModelHasOneRelationshipInfo[] $has_one */
				$has_one = $this->form()->result->hasOne();
			}

			if (is_object($this->form()->result) && isset($has_one[$this->relation])) {
				$this->_object = $has_one[$this->relation]->getTargetClass();

			} else {

				// get has-one from model
				if (is_object($this->form()->model))
					/** @var ModelHasOneRelationshipInfo[] $has_one */
					$has_one = $this->form()->model->hasOne();
				else
					$has_one = null;

				if (isset($has_one[$this->relation])) {
					$this->_object = $has_one[$this->relation]->getTargetClass();


				} else {
					throw new LogicException("{$this->relation} doesn't exist in the form {$this->form()->getName()}.");
				}
			}
		}

		if (!isset($this->model))
			$this->model = DataObject::get($this->_object);
	}

	/**
	 * generates the values displayed in the field, if not dropped down.
	 *
	 * @access protected
	 * @return array values
	 */
	protected function getInput()
	{
		$data = DataObject::get($this->_object, array("id" => $this->value));

		if ($this->form()->useStateData) {
			$data->setVersion(DataObject::VERSION_STATE);
		} else {
			$data->setVersion(DataObject::VERSION_PUBLISHED);
		}

		if ($record = $data->first()) {
			return array($record->id => $record->{$this->showfield});
		} else {
			return array();
		}
	}

	/**
	 * gets data from the model for the dropdown
	 *
	 * @param int $page
	 * @return array
	 */
	public function getDataFromModel($page = 1)
	{
		$data = clone $this->model;
		$data->filter($this->where);
		$data->activatePagination($page);

		if ($this->form()->useStateData) {
			$data->setVersion("state");
		}

		$arr = array();
		foreach ($data as $record) {
			$arr[] = array("key" => $record["id"], "value" => convert::raw2text($record[$this->showfield]));

			// check for info-field
			if (isset($this->info_field)) {
				if (isset($record[$this->info_field])) {
					$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
				}
			}
		}
		$left = ($page > 1);

		$right = (ceil($data->countWholeSet() / 10) > $page);
		$pageInfo = $data->getPageInfo();
		unset($data);

		return array("data" => $arr, "left" => $left, "right" => $right, "showStart" => $pageInfo["start"], "showEnd" => $pageInfo["end"], "whole" => $pageInfo["whole"]);
	}

	/**
	 * searches data from the optinos
	 * @param int $page
	 * @param string $search
	 * @return array
	 */
	public function searchDataFromModel($page = 1, $search = "")
	{
		$data = clone $this->model;
		$data->filter($this->where);
		$data->search($search);
		$data->activatePagination($page);

		if ($this->form()->useStateData) {
			$data->setVersion("state");
		}

		$arr = array();
		foreach ($data as $record) {

			$arr[] = array(
				"key" => $record->id,
				"value" => preg_replace('/(' . preg_quote($search, "/") . ')/Usi', "<strong>\\1</strong>", convert::raw2text($record[$this->showfield]))
			);

			// check for info-field
			if (isset($this->info_field)) {
				if (isset($record->{$this->info_field})) {
					$arr[count($arr) - 1]["smallText"] = convert::raw2text($record->{$this->info_field});
				}
			}
		}
		$left = ($page > 1);
		$right = (ceil($data->countWholeSet() / 10) > $page);

		$pageInfo = $data->getPageInfo();
		unset($data);

		return array(
			"data" => $arr,
			"left" => $left,
			"right" => $right,
			"showStart" => $pageInfo["start"],
			"showEnd" => $pageInfo["end"],
			"whole" => $pageInfo["whole"]
		);
	}

	/**
	 * validates the value
	 *
	 * @param int $id
	 * @return bool
	 */
	public function validate($id)
	{
		if ($this->classname == "hasonedropdown") {
			if (DataObject::count($this->_object, array("id" => $id)) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function validateValue($value) {
		if($value == 0)
			return true;

		$data = clone $this->getModel();

		$data->addFilter(array("id" => $value));

		return $data->count() > 0;
	}
}
