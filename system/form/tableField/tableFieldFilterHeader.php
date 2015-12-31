<?php defined("IN_GOMA") OR die();

/**
 * inspiration by Silverstripe 3.0 GridField
 *
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 12.08.2013
 * $Version - 1.1
 */
class TableFieldFilterHeader implements TableField_HTMLProvider, TableField_DataManipulator, TableField_ActionProvider, TableField_ColumnProvider
{
    /**
     * here are some special filters defined if TableFieldDataColumns casts some values to other values.
     */
    public $valueCasting = array();

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
                $searchField = new TextField('filter[' . $columnField . ']', '', $value);
                $searchField->addExtraClass('tablefield-filter');
                $searchField->addExtraClass('no-change-track');

                $searchField->input->attr('placeholder', lang("form_tablefield.filterBy") . $title);

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

            $fields->push(array("field" => $field->exportFieldInfo()->getRenderedField(), "name" => $columnField, "title" => $title));
        }

        return array(
            'header' => $forTemplate->customise(array("fields" => $fields, "visible" => $state->visible))->renderWith("form/tableField/filterHeader.html")
        );
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
            if ($data->canFilterBy($columnName) && $value) {
                if (isset($this->valueCasting[$columnName])) {
                    $values = array();
                    foreach ($this->valueCasting[$columnName] as $key => $orgValue) {
                        if (preg_match('/' . preg_quote($value, "/") . '/i', $key)) {
                            $values[] = $orgValue;
                        }
                    }

                    if (is_array($values) && count($values) > 0) {
                        $data->AddFilter(array($columnName => $values));
                    } else {
                        $data->AddFilter(array($columnName => array("LIKE", "%" . $value . "%")));
                    }
                } else {
                    $data->AddFilter(array($columnName => array("LIKE", "%" . $value . "%")));
                }
            }
        }

        return $data;
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

        if (isset($tableField->form()->post['filter']) && !$state->reset) {
            $hasValue = false;
            foreach ($tableField->form()->post['filter'] as $key => $filter) {
                if($filter && $state->resetColumn != $key) {
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
     * @param type $tableField
     * @return type
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
        if (!isset($this->valueCasting[$field]))
            $this->valueCasting[$field] = $values;
        else
            $this->valueCasting[$field] = array_merge($this->valueCasting[$field], $values);
    }
}