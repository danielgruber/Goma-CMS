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
	 *
	 *@name relation
	 *@access public
	*/
	public $relation;

	/**
	 * field to show in dropdown
	 *
	 *@name showfield
	 *@access public
	*/
	public $showfield;

	/**
	 * where clause to filter result in dropdown
	 *
	 *@name where
	 *@access public
	*/
	public $where;

	/**
	 * info-field
	 *
	 *@name info_field
	 *@access public
	*/
	public $info_field;

	/**
	 * base-model for querying DataBase.
	 *
	 * @name model
	 * @var DataObjectSet
	*/
	protected $model;

	/**
	 * info about relationship.
	 * @var ModelManyManyRelationShipInfo
	*/
	protected $relationInfo;

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
	 * returns the model.
	 *
	 * @return DataObjectSet
	*/
	public function getModel() {
		return $this->model;
	}

	/**
	 * sets the base-model for queriing DB.
	*/
	public function setModel(DataObjectSet $model) {
		$this->model = $model;
	}

	/**
	 * sets the value if not set
	*/
	public function getValue() {

		parent::getValue();

		if(!isset($this->dataset)) {

			if(is_object($this->form()->result)) {
				/** @var ModelManyManyRelationShipInfo[] $many_many_tables */
				$many_many_tables = $this->form()->result->ManyManyRelationships();
			}

			if(isset($many_many_tables[$this->relation])) {

				$this->_object = $many_many_tables[$this->relation]->getTarget();
				$this->dataset = call_user_func_array(array($this->form()->result, $this->relation), array())->FieldToArray("versionid");
				$this->relationInfo = $many_many_tables[$this->relation];
			} else if(is_object($this->form()->model)) {
				/** @var ModelManyManyRelationShipInfo[] $many_many_tables */
				$many_many_tables = $this->form()->model->ManyManyRelationships();

				if(isset($many_many_tables[$this->relation])) {
					$this->_object = $many_many_tables[$this->relation]->getTarget();
					$this->dataset = call_user_func_array(array($this->form()->model, $this->relation), array())->FieldToArray("versionid");
					$this->relationInfo = $many_many_tables[$this->relation];
				} else {
					throw new LogicException("{$this->relation} doesn't exist in this form {$this->form()->getName()}.");
				}
			} else {
				throw new LogicException("{$this->relation} doesn't exist in this form {$this->form()->getName()}.");
			}
		} else {
			if(is_object($this->form()->result)) {
				// get relations from result
				/** @var ModelManyManyRelationShipInfo[] $many_many_tables */
				$many_many_tables = $this->form()->result->ManyManyRelationships();
			}

			if(isset($many_many_tables[$this->relation])) {
				$this->_object = $many_many_tables[$this->relation]->getTarget();
				$this->relationInfo = $many_many_tables[$this->relation];
			} else if(is_object($this->form()->model)) {

				// get relations from model of form-controller
				/** @var ModelManyManyRelationShipInfo[] $many_many_tables */
				$many_many_tables = $this->form()->model->ManyManyRelationships();

				if(isset($many_many_tables[$this->relation])) {
					$this->_object = $many_many_tables[$this->relation]->getTarget();
					$this->relationInfo = $many_many_tables[$this->relation];
				} else {
					throw new LogicException("{$this->relation} doesn't exist in this form {$this->form()->getName()}.");
				}
			} else {
				throw new LogicException("{$this->relation} doesn't exist in this form {$this->form()->getName()}.");
			}
		}

		if(!isset($this->model))
			$this->model = DataObject::get($this->_object);
	}
		
    /**
     * getDataFromModel
     *
     * @param int $page
     * @return array
     */
    public function getDataFromModel($page = 1) {

        $data = clone $this->getModel();
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

        $right = (ceil($data->count() / 10) > $page);
        return array("data" => $arr, "left" => $left, "right" => $right);
    }

	/**
	 * searches data from the optinos
	 *
	 * @name searchDataFromModel
	 * @param numeric - page
	 * @return array
	 */
	public function searchDataFromModel($page = 1, $search = "") {

		$data = clone $this->getModel();
		$data->filter($this->where);
		$data->search($search);
		$data->activatePagination($page);

		if($this->form()->useStateData) {
			$data->setVersion("state");
		}

		$arr = array();
		foreach($data as $record) {
			$arr[] = array("key" => $record["versionid"], "value" => preg_replace('/('.preg_quote($search, "/").')/Usi', "<strong>\\1</strong>", convert::raw2text($record[$this->showfield])));
			if(isset($this->info_field)) {
				if(isset($record[$this->info_field])) {
					$arr[count($arr) - 1]["smallText"] = convert::raw2text($record[$this->info_field]);
				}
			}
		}
		$left = ($page > 1);
		$right = (ceil($data->count() / 10) > $page);
		return array("data" => $arr, "left" => $left, "right" => $right);
	}

	/**
	 * @return array
	 */
	public function result() {
		$result = parent::result();

		return $result;
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
}