<?php defined("IN_GOMA") OR die();

/**
 * This is a simple searchable dropdown, which can be used to select many-many-connections.
 *
 * It supports many-many-relations of DataObjects and MultiSelecting.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5.1
 */
class ManyManyDropDown extends MultiSelectDropDown
{
	/**
	 * the name of the relation of the current field
	 */
	public $relation;

	/**
	 * where clause to filter result in dropdown
	 *
	 * @var array
	 */
	public $where;

	/**
	 * info about relationship.
	 * @var ModelManyManyRelationShipInfo
	 */
	protected $relationInfo;

	/**
	 * @var string
	 */
	protected $_object;

	/**
	 * @param string $name
	 * @param string $title
	 * @param string $showfield
	 * @param array $where
	 * @param string $value
	 * @param Form $parent
	 */
	public function __construct($name = "", $title = null, $showfield = "title", $where = array(), $value = null, $parent = null)
	{
		parent::__construct($name , $title, $value, $parent);
		$this->dbname = $this->dbname . "ids";
		$this->relation = strtolower($name);
		$this->showfield = $showfield;
		$this->where = $where;
	}

	/**
	 * sets the value if not set
	 */
	public function getValue() {
		parent::getValue();

		if (!isset($this->options)) {
			/** @var ModelManyManyRelationShipInfo[] $many_many */
			// get has-one from model
			if (is_object($this->form()->getModel()))
				$many_many = $this->form()->getModel()->ManyManyRelationships();

			if (isset($many_many[$this->relation])) {
				$this->_object = $many_many[$this->relation]->getTargetClass();
				$this->relationInfo = $many_many[$this->relation];
			} else {
				throw new InvalidArgumentException("Could not find ManyMany-Relationship " . $this->relation);
			}

			$this->options = DataObject::get($this->_object, $this->where);
		} else if(is_a($this->options, "DataObjectSet") || is_a($this->options, "DataSet")) {
			$this->_object = $this->options->DataClass();
		} else {
			throw new InvalidArgumentException("Options for ManyManyDataObjectSet must be set of DataObjects.");
		}
	}

	/**
	 * getDataFromModel
	 *
	 * @param int $page
	 * @return array
	 */
	public function getDataFromModel($page = 1) {
		$data = clone $this->options;
		$data->filter($this->where);
		$data->activatePagination($page);

		if($this->form()->useStateData) {
			$data->setVersion("state");
		}

		$arr = array();
		foreach($data as $record) {
			$arr[] = array("value" => convert::raw2text($record[$this->showfield]), "key" => $record["versionid"]);
			if(isset($this->info_field)) {
				if(isset($record[$this->info_field])) {
					$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
				}
			}
		}
		$left = ($page > 1);

		$right = (ceil($data->countWholeSet() / 10) > $page);
		return array("data" => $arr, "left" => $left, "right" => $right);
	}

	/**
	 * searches data from the optinos
	 *
	 * @param int $page
	 * @param string $search
	 * @return array
	 */
	public function searchDataFromModel($page = 1, $search = "") {
		$data = clone $this->options;
		$data->filter($this->where);
		$data->search($search);
		$data->activatePagination($page);

		if($this->form()->useStateData) {
			$data->setVersion("state");
		}

		$arr = array();
		/** @var DataObject $record */
		foreach($data as $record) {
			$arr[] = array(
				"key" => $record->versionid,
				"value" => preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", convert::raw2text($record[$this->showfield]))
			);

			if(isset($this->info_field)) {
				if(isset($record->{$this->info_field})) {
					$arr[count($arr) - 1]["smallText"] = convert::raw2text($record->{$this->info_field});
				}
			}
		}
		$left = ($page > 1);
		$right = (ceil($data->count() / 10) > $page);
		return array(
			"data" => $arr,
			"left" => $left,
			"right" => $right
		);
	}

	/**
	 * @return array
	 */
	public function result() {
		return $this->dataset;
	}

	/**
	 * generates the values displayed in the field, if not dropped down.
	 *
	 * @access protected
	 * @return array values
	 */
	protected function getInput() {
		$data = DataObject::get($this->_object, array("versionid" => $this->dataset));

		if($this->form()->useStateData) {
			$data->setVersion(DataObject::VERSION_STATE);
		} else {
			$data->setVersion(DataObject::VERSION_PUBLISHED);
		}

		if($data && $data->count() > 0) {
			$return = array();
			foreach($data as $record) {
				$return[$record->versionid] = convert::raw2text($record[$this->showfield]);
			}

			$dataReturn = array();
			foreach($this->dataset as $id) {
				if(isset($return[$id])) {
					$dataReturn[$id] = $return[$id];
				}
			}

			return $dataReturn;
		} else {
			return array();
		}
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function validateValue($value) {
		$data = clone $this->options;

		$data->addFilter(array("versionid" => $value));

		return $data->count() > 0;
	}
}
