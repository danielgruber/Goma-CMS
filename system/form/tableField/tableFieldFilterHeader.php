<?php defined("IN_GOMA") OR die();

/**
 * inspiration by Silverstripe 3.0 GridField
 *
 * @package     Goma\Form\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0
 */
class TableFieldFilterHeader implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider, TableField_ColumnProvider
{
    const ID = "TableFieldFilterHeader";

    /**
     * here are some special filters defined if TableFieldDataColumns casts some values to other values.
     */
    public $valueCasting = array();

    /**
     * callback to filter correctly by given data.
     */
    protected $valueCallback = array();

    /**
     * select-list for filter.
     */
    protected $selectList;

    /**
     * sets a value-callback.
     * it can also unset the callback by providing null as callback.
     *
     * @param string $name
     * @param Callback|null $callback
     * @return $this
     */
    public function setValueCallback($name, $callback) {
        $this->valueCallback[strtolower($name)] = $callback;
        return $this;
    }

    /**
     * set select lists.
     * @param array $selectList
     * @return $this
     */
    public function setSelectLists($selectList) {
        foreach($selectList as $list) {
            if(!is_array($list)) {
                throw new InvalidArgumentException("You have to use arrays for SelectLists.");
            }
        }

        $this->selectList = $selectList;
        return $this;
    }

    /**
     * provides HTML-fragments
     *
     * @name provideFragments
     * @return array
     */
    public function provideFragments($tableField)
    {
        $forTemplate = new ViewAccessableData();
        $fields = new DataSet();

        $state = $tableField->state->tableFieldFilterHeader;
        $state->visible = is_object($state->visible) ? false : $state->visible;
        $filterArguments = $state->columns->toArray();
        $columns = $tableField->getColumns();
        $currentColumn = 0;

        foreach ($columns as $columnField) {
            $currentColumn++;
            $metadata = $tableField->getColumnMetadata($columnField);
            $title = $metadata['title'];

            // sortabe column
            if ($title && $tableField->getData()->canFilterBy($columnField)) {
                $value = '';
                if (isset($filterArguments[$columnField])) {
                    $value = $filterArguments[$columnField];
                }
                $searchField = $this->getFilterField($columnField, $value, $title);

                if ($value != "") {
                    $searchAction = new TableField_FormAction($tableField, "resetFields" . str_replace(".", "_", $columnField), '<i title="' . lang("form_tablefield.reset") . '" class="fa fa-times"></i>', "resetFields", $columnField);
                    $searchAction->addExtraClass("tablefield-button-resetFields");
                    $searchAction->addExtraClass("no-change-track");
                    $searchAction->addClass("button-clear");

                    $field = new FieldSet($columnField . "_sortActions", array(
                        $searchField,
                        $searchAction
                    ));
                } else {
                    $field = $searchField;
                }
                // action column
            } else if ($currentColumn == count($columns)) {
                $searchAction = new TableField_FormAction($tableField, "reset" . $columnField, '<i title="' . lang("form_tablefield.reset") . '" class="fa fa-times"></i>', "reset", null);
                $searchAction->addExtraClass("tablefield-button-reset");
                $searchAction->addExtraClass("no-change-track");
                $searchAction->addClass("button-clear");

                $action = new TableField_FormAction($tableField, "filter" . $columnField, '<i title="' . lang("search") . '" class="fa fa-search"></i>', "filter", null);
                $action->addExtraClass("tablefield-button-filter");
                $action->addExtraClass("no-change-track");
                $action->addClass("button-clear");

                $field = new FieldSet($columnField . "_sortActions", array(
                    $action,
                    $searchAction
                ));

                // no searchable column
            } else {
                $field = new HTMLField("", "");
            }
            $field->setForm($tableField->Form());

            $fields->push(array("field" => $field->exportFieldInfo()->ToRestArray(true), "name" => $columnField, "title" => $title));
        }

        return array(
            'header' => $forTemplate->customise(array("fields" => $fields, "visible" => $state->visible))->renderWith("form/tableField/filterHeader.html")
        );
    }

    /**
     * @param string $columnField
     * @param string $value
     * @param string $title
     * @return TextField
     */
    protected function getFilterField($columnField, $value, $title) {
        if(isset($this->selectList[$columnField])) {
            $searchField = new Select(
                "filter[" . $columnField . "]",
                "",
                array("" => "-") + $this->selectList[$columnField],
                $value
            );
        } else {
            $searchField = new TextField('filter[' . $columnField . ']', '', $value);
            $searchField->setPlaceholder($title);
        }

        $searchField->addExtraClass('tablefield-filter');
        $searchField->addExtraClass('no-change-track');

        return $searchField;
    }

    /**
     * manipulates the dataobjectset
     *
     * @param TableField $tableField
     * @param DataObjectSet $data
     * @return DataSet
     */
    public function manipulate($tableField, $data)
    {
        $this->filter($tableField);

        $state = $tableField->state->tableFieldFilterHeader;
        if ($state->visible !== true) {
            return $data;
        }

        if (!isset($state->columns)) {
            return $data;
        }

        $filterArguments = $state->columns->toArray();
        foreach ($filterArguments as $columnName => $value) {
            if(isset($this->valueCallback[strtolower($columnName)])) {
                call_user_func_array($this->valueCallback[strtolower($columnName)], array($filterArguments, $data));
            } else if ($data->canFilterBy($columnName) && $this->isValueValid($value)) {
                $values = $this->getValueCastingForValue($columnName, $value);
                if (is_array($values) && count($values) > 0) {
                    $data->AddFilter(array($columnName => $values));
                } else {
                    $data->AddFilter(array($columnName => array("LIKE", "%" . $value . "%")));
                }
            }
        }

        return $data;
    }

    /**
     * @param string $columnName
     * @param string $value
     * @return mixed
     */
    protected function getValueCastingForValue($columnName, $value) {
        $values = array();
        if(isset($this->selectList[$columnName])) {
            if(isset($this->valueCasting[$value])) {
                return array($value);
            } else if($key = array_search($value, $this->valueCasting)) {
                return array($key);
            } else {
                return array($value);
            }
        } else {
            if (isset($this->valueCasting[$columnName])) {
                foreach ($this->valueCasting[$columnName] as $key => $orgValue) {
                    if (preg_match('/' . preg_quote($value, "/") . '/i', $key)) {
                        if (is_array($orgValue)) {
                            $values = array_merge($values, $orgValue);
                        } else {
                            $values[] = $orgValue;
                        }
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param TableField $tableField
     */
    public function Init($tableField) {
        $state = $tableField->state->tableFieldFilterHeader;
        if(!is_bool($state->visible)) {
            $state->visible = false;
        }
    }

    /**
     * provide some actions of this tablefield
     *
     * @name getActions
     * @access public
     * @return array
     */
    public function getActions($tableField)
    {
        return array("filter", "reset", "resetFields", "toggleFilterVisibility");
    }

    /**
     * @param TableField $tableField
     * @param string $actionName
     * @param string $arguments
     * @param $data
     */
    public function handleAction($tableField, $actionName, $arguments, $data)
    {
        $state = $tableField->state->tableFieldFilterHeader;

        if ($actionName === 'reset') {
            $state->columns = null;
            $state->reset = true;
            $state->visible = false;
        } else if ($actionName === "resetfields") {
            $state->resetColumn = $arguments;
        } else if ($actionName === "togglefiltervisibility") {
            if ($state->visible === true) {
                $state->visible = false;
            } else {
                $state->visible = true;
            }
        }
    }

    /**
     * Add a column 'Actions'
     *
     * @param type $tableField
     * @param array $columns
     */
    public function augmentColumns($tableField, &$columns)
    {
        if (!in_array('Actions', $columns))
            $columns[] = 'Actions';
    }

    /**
     * @param TableField $tableField
     */
    public function filter($tableField) {
        $state = $tableField->state->tableFieldFilterHeader;

        if (isset($tableField->getRequest()->post_params['filter']) && !$state->reset) {
            $hasValue = false;
            foreach ($tableField->getRequest()->post_params['filter'] as $key => $filter) {
                if($this->isValueValid($filter) && $state->resetColumn != $key) {
                    $hasValue = true;
                    $state->columns->$key = $filter;
                } else if($state->columns->$key || $state->resetColumn == $key) {
                    $state->columns->$key = null;
                }
            }

            if($hasValue) {
                if ($state->visible === false) {
                    $state->visible = true;
                }
            }
        }

        if($state->reset) {
            $state->reset = false;
        }

        if($state->resetColumn) {
            $state->resetColumn = null;
        }
    }

    /**
     * Return any special attributes that will be used for the column
     *
     * @param GridField $tableField
     * @param DataObject $record
     * @param string $columnName
     * @return array
     */
    public function getColumnAttributes($tableField, $columnName, $record)
    {
        return array('class' => 'col-buttons');
    }

    /**
     * Add the title
     *
     * @param TableField $tableField
     * @param string $columnName
     * @return array
     */
    public function getColumnMetadata($tableField, $columnName)
    {
        if ($columnName == 'Actions') {
            return array('title' => '');
        }
    }

    /**
     * Which columns are handled by this component
     *
     * @param TableField $tableField
     * @return array
     */
    public function getColumnsHandled($tableField)
    {
        return array("Actions");
    }

    /**
     * this shouldn't do anything
     */
    public function getColumnContent($tableField, $record, $columnName)
    {
        return null;
    }

    /**
     * adds casted field-values.
     *
     * @param    string $field field
     * @param    array $values values
     */
    public function addCastedValues($field, $values)
    {
        if (!isset($this->valueCasting[$field])) {
            $this->valueCasting[$field] = $values;
        } else {
            $this->valueCasting[$field] = array_merge($this->valueCasting[$field], $values);
        }
    }

    /**
     * sets casted values for field.
     *
     * @param string $field
     * @param array $values
     */
    public function setCastedValues($field, $values) {
        $this->valueCasting[$field] = $values;
    }

    /**
     * @param string|int $value
     * @return bool
     */
    protected function isValueValid($value) {
        return $value || $value === 0 || $value === "0";
    }
}
