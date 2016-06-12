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
     * @var AbstractFormComponentWithChildren
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
     * @var array[]
     */
    protected $errors;

    /**
     * created field.
     *
     * @param string $name
     * @param mixed $model
     * @param Form|null $parent
     */
    public function __construct($name = null, $model = null, &$parent = null)
    {
        parent::__construct();

        $this->name = $name;
        $this->dbname = strtolower(trim($name));
        $this->setModel($model);

        if ($parent) {
            $parent->add($this);
        }
    }

    /**
     * sets the parent form-object
     * @param AbstractFormComponentWithChildren $form
     * @return $this
     */
    public function setForm(&$form) {
        if(!is_a($form, "AbstractFormComponentWithChildren")) {
            throw new InvalidArgumentException("Form must be a AbstractFormComponentWithChildren");
        }

        $this->parent =& $form;
        $this->parent->registerField($this->name, $this);

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

    static $i = 0;

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
     * @return AbstractFormComponentWithChildren
     */
    public function form() {
        if($this->parent) {
            return $this->parent->form();
        }

        throw new LogicException("Field " . $this->name . " requires a form. ");
    }

    /**
     * the url for ajax
     *
     * @return string
     */
    public function externalURL()
    {
        if($this->namespace)
            return $this->namespace;

        return $this->form()->externalURL() . "/" . $this->name;
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
     * @var array $result
     */
    public function argumentResult(&$result) {
        $result[$this->dbname] = $this->result();
    }

    /**
     * generates an id for the field
     *
     * @return string
     */
    public function ID()
    {
        $formId = $this->parent ? $this->form()->getName() : "";
        return "form_field_" . $this->classname . "_" . $formId . "_" . $this->name;
    }

    /**
     * generates an id for the div
     *
     * @return string
     */
    public function divID()
    {
        return $this->ID() . "_div";
    }

    /**
     *
     */
    public function remove() {
        if($this->parent) {
            $this->parent->remove($this->name);
        }
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
        return isset($this->overridePostName) ? strtolower($this->overridePostName) : $this->dbname;
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

    /**
     * exports basic field info.
     *
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null) {
        if(isset($fieldErrors[strtolower($this->name)])) {
            $this->errors = $fieldErrors[strtolower($this->name)];
        }

        return $this->createsRenderDataClass()
            -> setIsDisabled($this->disabled)
            -> setField($this)
            -> setHasError(count($this->errors) > 0);
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true) {
        try {
            if($this->parent) {
                $this->parent->registerRendered($info->getName());
            }

            $this->callExtending("beforeRender", $info);

            $fieldData = $this->field($info);

            $info->setRenderedField($fieldData)
                ->setJs($this->js());

            if ($notifyField) {
                $this->callExtending("afterRenderFormResponse", $info);
            }
        } catch(Exception $e) {
            if($info->getRenderedField() == null) {
                $info->setRenderedField(new HTMLNode("div", array("class" => "form_field")));
            }
            $info->getRenderedField()->append('<div class="error">' . $e->getMessage() . '</div>');
        }
    }

    /**
     * this function generates some JSON for using client side stuff.
     *
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    final public function exportFieldInfo($fieldErrors = null) {
        $info = $this->exportBasicInfo($fieldErrors);

        $this->addRenderData($info);

        return $info;
    }

    /**
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    abstract public function field($info);

    /**
     * @return string
     */
    abstract public function js();

    /**
     * @return FormFieldRenderData
     */
    protected function createsRenderDataClass() {
        return FormFieldRenderData::create($this->name, $this->classname, $this->ID(), $this->divID());
    }

    /**
     * getter-method for state
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (strtolower($name) == "state") {
            return $this->form()->state->{$this->classname . $this->name};
        } else if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new LogicException("\$" . $name . " is not defined in " . $this->classname . " with name " . $this->name . ".");
        }
    }

    /**
     * @return string
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * @return AbstractFormComponentWithChildren
     */
    public function getParent()
    {
        return $this->parent;
    }
}
