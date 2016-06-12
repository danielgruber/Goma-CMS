<?php
defined("IN_GOMA") OR die();

/**
 * Base-Class for FormFields and Form, which handles logic of result and model.
 *
 * @property FormState state
 * @package vorort.news
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
abstract class AbstractFormComponentWithChildren extends AbstractFormComponent {
    /**
     * @var ArrayList<AbstractFormComponent>
     */
    protected $fieldList;

    /**
     * @var FormField[]
     */
    public $fields;

    /**
     * @var bool[]
     */
    protected $renderedFields = array();

    /**
     * @var FormState
     */
    protected $_state;

    /**
     * @var string
     */
    protected $template = "form/FieldSet.html";

    /**
     * @var ViewAccessableData
     */
    protected $templateView;

    /**
     * @var string
     */
    public $url;

    /**
     * AbstractFormComponentWithChildren constructor.
     * @param null|string $name
     * @param AbstractFormComponent[] $fields
     * @param mixed|null $model
     * @param Form|null $parent
     */
    public function __construct($name, $fields = null, $model = null, $parent = null)
    {
        parent::__construct($name, $model, $parent);

        $this->fieldList = new ArrayList();
        $this->templateView = new ViewAccessableData();

        if($fields) {
            foreach ($fields as $field) {
                $this->add($field);
            }
        }
    }

    /**
     * @param AbstractFormComponent $field
     * @param null|int $sort
     * @param null|string $to
     */
    public function add($field, $sort = null, $to = null) {
        if($to == "this" || !isset($to)) {
            // if it already exists, we should remove it.
            if($this->fieldList->find("name", $field->name)) {
                $this->fieldList->remove($this->fieldList->find("name", $field->name));
            }

            if(isset($sort))
                $this->fieldList->move($field, $sort, true);
            else
                $this->fieldList->add($field);

            $field->setForm($this);
        } else if($fieldToAddTo = $this->getField($to)) {
            /** @var AbstractFormComponentWithChildren $fieldToAddTo */
            if(!is_a($fieldToAddTo, "AbstractFormComponentWithChildren")) {
                throw new InvalidArgumentException("Can't add field to field " . $to);
            }

            $fieldToAddTo->add($field, $sort);
        }
    }

    /**
     * adds a field to a given fieldset.
     *
     * @param 	String $fieldname fieldset
     * @param 	FormField $field the field
     * @param 	int $sort
     */
    public function addToField($fieldname, $field, $sort = 0) {
        $this->add($field, $sort, $fieldname);
    }

    /**
     * adds a field. alias to @see Form::add.
     * @param AbstractFormComponent $field
     * @param null $sort
     * @param null $to
     */
    public function addField($field, $sort = null, $to = null) {
        $this->add($field, $sort, $to);
    }

    /**
     * @return array|string|ViewAccessableData
     */
    public function getModel()
    {
        return isset($this->model) ? (isset($this->parent) ? $this->parent->getModel() : $this->model) : null;
    }

    /**
     * gets the field by the given name or returns null.
     *
     * @param string $name
     * @return AbstractFormComponent|null
     */
    public function getField($name) {

        return (isset($this->fields[strtolower($name)])) ? $this->fields[strtolower($name)] : null;
    }

    /**
     * returns if a field exists in this form
     *
     * @param string $name
     * @return bool
     */
    public function hasField($name) {
        return (isset($this->fields[strtolower($name)]));
    }

    /**
     * removes a field
     * @param string $field
     */
    public function remove($field) {
        if(!is_object($field) && !is_array($field)) {
            if (isset($this->fields[$field])) {
                unset($this->fields[$field]);
            }

            if (is_string($field)) {
                $this->fieldList->remove($this->fieldList->find("name", $field, true));
            }
        }

        if($this->fieldList->count() > 0) {
            foreach ($this->fieldList as $myField) {
                if (is_subclass_of($myField, "AbstractFormComponentWithChildren")) {
                    $myField->remove($field);
                }
            }
        }
    }

    /**
     * @param AbstractFormComponentWithChildren $form
     * @return $this
     */
    public function setForm(&$form)
    {
        parent::setForm($form);

        if($this->fields) {
            foreach ($this->fields as $name => $field) {
                $this->parent->registerField($name, $field);
            }
        }

        if($this->renderedFields) {
            foreach ($this->renderedFields as $field => $boolean) {
                $this->parent->registerRendered($field);
            }
        }

        if($this->_state) {
            $this->parent->state->{$this->classname . $this->name} = $this->_state;
        }

        return $this;
    }

    /**
     * returns a field in this form by name
     * it's not relevant how deep the field is in this form if the field is *not*
     * within a ClusterFormField
     * @param string $offset
     * @return array|string
     */
    public function __get($offset) {
        if($offset == "result") {
            Core::Deprecate(2.0, "getResult");

            return $this->result;
        }

        if($offset == "state") {
            if($this->parent) {
                return $this->parent->state->{$this->classname . $this->name};
            }

            $this->_state = new FormState();
            return $this->_state;
        }

        return $this->getField($offset);
    }

    /**
     * returns if a field exists in this form
     *
     * @param string $offset
     * @return bool
     */
    public function __isset($offset) {
        return $this->hasField($offset);
    }

    /**
     * removes a field from this form
     */
    public function __unset($offset)
    {
        $this->remove($offset);
    }


    /**
     * returns if a field exists and wasn't rendered in this form
     *
     * @param string $name
     * @return bool
     */
    public function isFieldToRender($name) {
        return ((isset($this->fields[strtolower($name)])) && !isset($this->renderedFields[strtolower($name)])) &&
        (!$this->parent || $this->parent->isFieldToRender($name));
    }

    /**
     * registers a field in this form
     *
     * @param string $name
     * @param AbstractFormComponent $field
     */
    public function registerField($name, $field) {
        if($this->parent) {
            $this->parent->registerField($name, $field);
        }

        $this->fields[strtolower($name)] = $field;
    }

    /**
     * unregisters the field from this form
     * this means that the field will not be rendered
     *
     * @param string $name
     */
    public function unRegister($name) {
        if($this->parent) {
            $this->parent->unregisterField($name);
        }

        unset($this->fields[strtolower($name)]);
    }

    /**
     * registers the field as rendered
     *
     * @param string $name
     */
    public function registerRendered($name) {
        $this->renderedFields[strtolower($name)] = true;
    }

    /**
     * removes the registration as rendered
     *
     * @param string $name
     */
    public function unregisterRendered($name) {
        unset($this->renderedFields[strtolower($name)]);
    }

    /**
     * unregisters a field.
     *
     * @param string $name
     * @return void
     */
    public function unregisterField($name) {
        if(isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }

        if($this->parent) {
            $this->parent->unregisterField($name);
        }
    }

    /**
     * @param null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null)
    {
        $this->url = str_replace('"', '', ROOT_PATH . BASE_SCRIPT . $this->getRequest()->url . URLEND);

        $data = parent::exportBasicInfo($fieldErrors);

        /** @var AbstractFormComponent $field */
        foreach($this->fieldList as $field) {
            if($this->isFieldToRender($field->getName())) {
                $data->addChild($field->exportBasicInfo($fieldErrors));
            }
        }

        return $data;
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        parent::addRenderData($info, false);

        /** @var FormFieldRenderData $child */
        if($info->getChildren()) {
            $data = array();
            foreach ($info->getChildren() as $child) {
                if ($this->isFieldToRender($child->getName())) {
                    try {
                        $child->getField()->addRenderData($child);
                    } catch(Exception $e) {
                        if($child->getRenderedField() == null) {
                            $child->setRenderedField(new HTMLNode("div", array("class" => "form_field")));
                        }
                        $child->getRenderedField()->append('<div class="error">' . $e->getMessage() . '</div>');
                    }

                    $data[] = $child->ToRestArray(true, false);
                }
            }

            $info->getRenderedField()->append(
                $this->templateView
                    ->customise($info->ToRestArray(false, false))
                    ->customise(array("fields" => new DataSet($data)))
                    ->renderWith($this->template)
            );
        }

        if($notifyField) {
            $this->callExtending("afterRenderFormResponse", $info);
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return ViewAccessableData
     */
    public function getTemplateView()
    {
        return $this->templateView;
    }

    /**
     * @param ViewAccessableData $templateView
     * @return $this
     */
    public function setTemplateView($templateView)
    {
        $this->templateView = $templateView;
        return $this;
    }

    /**
     * @return AbstractFormComponentWithChildren
     */
    public function form() {
        return $this->parent ? $this->parent->form() : $this;
    }

    /**
     * @return string
     */
    public function externalURL()
    {
        if($this->parent) {
            return $this->parent->form()->externalURL() . "/" . $this->name;
        } else if($this->namespace) {
            return $this->namespace;
        } else {
            throw new InvalidArgumentException("AbstractFormComponentWithChildren requires either parent or controller namespace.");
        }
    }

    /**
     * @param array $result
     */
    public function argumentResult(&$result)
    {
        /** @var AbstractFormComponent $field */
        foreach($this->fieldList as $field) {
            $field->argumentResult($result);
        }
    }
}
