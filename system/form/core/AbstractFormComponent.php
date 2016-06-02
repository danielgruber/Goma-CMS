<?php
defined("IN_GOMA") OR die();

/**
 * Base-Class for FormFields and Form, which handles logic of result and model.
 *
 * @package vorort.news
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
abstract class AbstractFormComponent extends RequestHandler {
    /**
     * this var defines if the value of $this->form()->post[$name] should be set as value if it is set
     *
     * @var boolean
     */
    protected $POST = true;

    /**
     * result of this field.
     *
     * @var string|array
     * @internal
     */
    protected $result;

    /**
     * model of this field.
     *
     * @var array|string|ViewAccessableData
     */
    public $model;

    /**
     * defines if we should use state-data in sub-queries of this Form
     *
     * @var bool
     */
    public $useStateData = false;


    /**
     * the parent field of this field, e.g. a form or a fieldset
     *
     * @var AbstractFormComponent
     */
    protected $parent;

    /**
     * name of this field
     *
     * @var string
     */
    protected $name;

    /**
     * name of the data-relation
     *
     * @var string
     */
    protected $dbname;

    /**
     * defines if this field is disabled
     *
     * @var bool
     */
    public $disabled = false;

    /**
     * overrides the post-name
     *
     * @var string
     */
    public $overridePostName;

    /**
     * @var bool
     */
    public $hasNoValue = false;

    /**
     * sets the parent form-object
     * @param AbstractFormComponent $form
     * @param bool $renderAfterSetForm
     * @return $this
     */
    public function setForm(&$form, $renderAfterSetForm = true)
    {
        $this->parent =& $form;

        return $this;
    }

    /**
     * @var array|string|ViewAccessableData
     * @return $this
     */
    public function setModel($model) {
        if(is_a($model, "viewaccessabledata")) {
            $this->useStateData = ($model->queryVersion == "state");
        }

        $this->model = $model;

        return $this;
    }

    /**
     * @return array|string|ViewAccessableData
     */
    public function getModel() {
        if (!isset($this->hasNoValue) || !$this->hasNoValue) {
            if($this->POST) {
                if (!$this->isDisabled() && $this->parent && ($postData = $this->parent->getFieldPost($this->PostName()))) {
                    return $postData;
                } else if ($this->model == null) {
                    return $this->parent ? $this->parent->getFieldValue($this->dbname) : null;
                }
            }
        }

        return $this->model;
    }

    /**
     * @param string $field
     * @return mixed|null|ViewAccessableData
     */
    public function getFieldValue($field) {
        $model = $this->getModel();
        if(is_a($model, "ViewAccessableData") && isset($model->{$field})) {
            return $model->{$field};
        } else if (is_array($model) && isset($model[$field])) {
            return $model[$field];
        }

        return null;
    }

    /**
     * @param string $field
     * @return null
     */
    public function getFieldPost($field) {
        if($this->parent) {
            return $this->parent->getFieldPost($field);
        }

        return isset($this->getRequest()->post_params[$field]) ? $this->getRequest()->post_params[$field] : null;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function getModelFromRaw($value) {
        return $value;
    }

    /**
     * @return AbstractFormComponent
     */
    public function form() {
        return $this->parent ? $this->parent->form() : $this;
    }

    /**
     * the url for ajax
     *
     * @return string
     */
    public function externalURL()
    {
        if($this->parent) {
            return $this->form()->externalURL() . "/" . $this->name;
        }

        return $this->namespace;
    }

    /**
     * this function returns the result of this field
     *
     * @return mixed
     */
    public function result() {
        return $this->getModel();
    }

    /**
     * disables this field
     */
    public function disable()
    {
        $this->disabled = true;
        return $this;
    }

    /**
     * reenables the field
     */
    public function enable()
    {
        $this->disabled = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled() {
        return $this->disabled || ($this->parent && $this->parent->isDisabled());
    }

    /**
     * returns the post-name
     *
     * @return string
     */
    public function PostName()
    {
        return isset($this->overridePostName) ? strtolower($this->overridePostName) : strtolower($this->name);
    }

    /**
     * returns name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return array|string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request ? $this->request : ($this->parent ? $this->parent->getRequest() : null);
    }

    /**
     * @param Request $request
     * @param bool $subController
     * @return false|null|string
     * @throws Exception
     */
    public function handleRequest($request, $subController = false)
    {
        $oldRequest = $this->request;

        $output = parent::handleRequest($request, $subController);

        $this->request = $oldRequest;

        return $output;
    }
}
