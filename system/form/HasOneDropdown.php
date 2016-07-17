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
	 * @var string
	 */
	public $relation;

	/**
	 * where clause to filter result in dropdown
	 *
	 * @var array
	 */
	public $where;

	/**
	 * @var string
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
	 * sets the value if not set
	 *
	 * @name getValue
	 * @access public
	 * @return bool|void
	 */
	public function getValue()
	{
		parent::getValue();

		if (!isset($this->options)) {
			// get has-one from model
			if (is_object($this->form()->getModel()))
				/** @var ModelHasOneRelationshipInfo[] $has_one */
				$has_one = $this->form()->getModel()->hasOne();

			if (isset($has_one[$this->relation])) {
				$this->_object = $has_one[$this->relation]->getTargetClass();
			} else {
				throw new InvalidArgumentException("Could not find HasOne-Relationship " . $this->relation);
			}

			$this->options = DataObject::get($this->_object, $this->where);
		} else if(is_a($this->options, "DataObjectSet") || is_a($this->options, "DataSet")) {
			$this->_object = $this->options->DataClass();
		} else {
			throw new InvalidArgumentException("Options for HasOneDropdown must be set of DataObjects.");
		}
	}

	/**
	 * generates the values displayed in the field, if not dropped down.
	 *
	 * @access protected
	 * @return array values
	 */
	protected function getInput()
	{
		$data = DataObject::get($this->_object, array("id" => $this->getModel()));

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
	 * validates the value
	 *
	 * @param int $id
	 * @return bool
	 */
	public function validate($id)
	{
		if ($this->classname == "hasonedropdown") {
			$data = clone $this->options;

			$data->addFilter(array("id" => $id));

			return $data->count() > 0;
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

		$data = clone $this->options;

		$data->addFilter(array("id" => $value));

		return $data->count() > 0;
	}

	/**
	 * @return int
	 */
	public function result()
	{
		return $this->getModel();
	}
}
