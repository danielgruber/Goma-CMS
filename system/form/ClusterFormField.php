<?php
defined("IN_GOMA") OR die();

/**
 * A cluster form field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0.5
 */
class ClusterFormField extends FieldSet {
    /**
     * controller
     */
    public $controller;

    /**
     * @var bool
     */
    private $fieldsDefined = false;

    /**
     * constructing
     *
     * @param string|null $name
     * @param string|null $title
     * @param array|null $fields
     * @param string|ViewAccessableData|null $value
     * @param Form|null $form
     */
    public function __construct($name = null, $title = null, $fields = null, $value = null, &$form = null)
    {
        parent::__construct($name, $fields, $title, $form);

        $this->model = $value;
        $this->container->setTag("div");
    }

    /**
     *
     */
    protected function defineFields() {

    }

    /**
     * @param null $fieldErrors
     * @return FormFieldRenderData
     */
    public function  exportBasicInfo($fieldErrors = null)
    {
        if(!$this->fieldsDefined) {
            $this->fieldsDefined = true;
            $this->defineFields();
        }

        return parent::exportBasicInfo($fieldErrors);
    }

    /**
     * checks if the action is available
     * we implement sub-namespaces for sub-items here
     *
     * @param string $action
     * @return bool
     */
    public function hasAction($action)
    {
        if (isset($this->fields[strtolower($action)])) {
            return true;
        }

        if (parent::hasAction($action))
            return true;

        return false;
    }

    /**
     * handles the action
     * we implement sub-namespaces for sub-items here
     *
     * @param $action
     * @return false|string
     */
    public function handleAction($action)
    {
        if (isset($this->fields[strtolower($action)])) {
            return $this->fields[strtolower($action)]->handleRequest($this->request);
        }

        return parent::handleAction($action);
    }

    /**
     * @param AbstractFormComponent $field
     * @param null $sort
     * @param null $to
     */
    public function add($field, $sort = null, $to = null)
    {
        $field->overridePostName = $this->PostName() . "_" . $field->PostName();

        parent::add($field, $sort, $to);
    }

    /**
     * generates an id for the field
     *
     * @return string
     */
    public function ID()
    {
        if (Core::is_ajax()) {
            return "form_field_" . $this->classname . "_" . md5($this->form()->getName()) . "_" . $this->name . "_ajax";
        } else {
            return "form_field_" . $this->classname . "_" . md5($this->form()->getName()) . "_" . $this->name;
        }
    }

    /**
     * result
     *
     * @return array|mixed|null
     */
    public function result()
    {
        $result = $this->getModel();
        if(!$result)
            $result = array();

        /** @var AbstractFormComponent $field */
        foreach ($this->fieldList as $field) {
            $field->argumentResult($result);
        }

        return $result;
    }

    /**
     * @param array $result
     */
    public function argumentResult(&$result)
    {
        $result[$this->dbname] = $this->result();
    }

    /**
     * generates an name for this form
     *
     * @return null|string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return array|null|string|ViewAccessableData
     */
    public function getModel()
    {
        if (!isset($this->hasNoValue) || !$this->hasNoValue) {
            if($this->POST) {
                if (!$this->isDisabled() && $this->parent && ($postData = $this->parent->getFieldPost($this->PostName()))) {
                    return $postData;
                } else if ($this->model == null) {
                    $this->model = $this->parent ? $this->parent->getFieldValue($this->dbname) : null;
                }
            }
        }

        return $this->model;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isFieldToRender($name)
    {
        return ((isset($this->fields[strtolower($name)])) && !isset($this->renderedFields[strtolower($name)]));
    }

    /**
     * @return $this
     */
    public function form()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function externalURL()
    {
        if($this->namespace)
            return $this->namespace;

        return parent::form()->externalURL() . "/" . $this->name;
    }
}
