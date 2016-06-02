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
class ClusterFormField extends FormField {
    /**
     * fields of this cluster
     */
    public $fields = array();

    /**
     * items of this cluster
     */
    public $items = array();

    /**
     * fields already rendered
     */
    public $renderedFields = array();

    /**
     * sort of the items
     */
    public $sort = array();

    /**
     * url of the original form
     */
    public $url;

    /**
     * result will be linked on value
     */
    public $result;

    /**
     * model.
     */
    public $model;

    /**
     * controller
     */
    public $controller;

    /**
     * state
     */
    public $state;

    /**
     * template.
     * @var string
     */
    protected $template = "form/FieldSet.html";

    /**
     * @var ViewAccessableData
     */
    protected $templateView;

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
        if (!isset($value))
            $value = array();

        $this->templateView = new ViewAccessableData();

        parent::__construct($name, $title, $value, $form);

        foreach ((array)$fields as $field) {
            $field->overridePostName = $this->name . "_" . $field->name;
            $this->sort[$field->name] = 1 + count($this->items);
            $this->items[] = $field;
        }
    }

    /**
     * checks if the action is available
     * we implement sub-namespaces for sub-items here
     *
     * @return bool
     */
    public function hasAction($action)
    {
        if (isset($this->fields[$action]))
            return true;

        if (parent::hasAction($action))
            return true;

        return false;
    }

    /**
     * handles the action
     * we implement sub-namespaces for sub-items here
     *
     * @return string|false
     */
    public function handleAction($action)
    {
        if (isset($this->fields[$action])) {
            return $this->fields[$action]->handleRequest($this->request);
        }

        return parent::handleAction($action);
    }

    /**
     * returns the node
     *
     * @return HTMLNode
     */
    public function createNode()
    {
        return new HTMLNode("div");
    }

    /**
     * @param FormFieldRenderData $info
     */
    public function addRenderData($info)
    {
        parent::addRenderData($info);

        /** @var FormFieldRenderData $child */
        $data = array();
        foreach ($info->getChildren() as $child) {
            if ($this->form()->isFieldToRender($child->getName())) {
                $child->getField()->addRenderData($child);

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

    /**
     * exports basic field info.
     *
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null)
    {
        $data = parent::exportBasicInfo($fieldErrors);

        // get content
        uasort($this->items, array($this, "sort"));

        /** @var FormField $item */
        foreach ($this->items as $item) {
            // if a FieldSet is disabled all subfields should disabled, too
            if ($this->disabled) {
                $item->disable();
            }

            $data->addChild($item->exportBasicInfo($fieldErrors));
        }

        return $data;
    }

    /**
     * adds an field
     * @param FormField $field
     * @param int $sort
     */
    public function add($field, $sort = 0)
    {
        $field->overridePostName = $this->name . "_" . $field->name;

        if ($sort == 0) {
            $sort = 1 + count($this->items);
        }

        $this->sort[$field->name] = $sort;
        $this->items[$field->name] = $field;
        if (isset($this->parent))
            /** @var FormField $field */
            $field->setForm($this);
    }


    /**
     * removes a field or this field
     * @param string|null $field
     */
    public function remove($field = null)
    {
        if ($field === null) {
            parent::remove();
        } else {
            if (isset($this->fields[$this->name . "_" . $field])) {
                unset($this->fields[$this->name . "_" . $field]);
            }

            if (isset($this->items[$this->name . "_" . $field])) {
                unset($this->items[$this->name . "_" . $field]);
            }
        }
    }

    /**
     * sorts the items
     *
     * @return int
     */
    public function sort($a, $b)
    {
        if ($this->sort[$a->name] == $this->sort[$b->name]) {
            return 0;
        }

        return ($this->sort[$a->name] > $this->sort[$b->name]) ? 1 : -1;
    }

    /**
     * sets the form
     * @param Form $form
     * @param bool $renderAfterSetForm
     * @return $this
     */
    public function setForm(&$form, $renderAfterSetForm = true)
    {
        parent::setForm($form, false);

        unset($this->fields[$this->name]);
        $this->orgForm()->registerField($this->name, $this);

        while (!isset($form->url) && is_object($form)) {
            $form = $form->form();
        }

        $this->url =& $form->url;
        $this->controller =& $this->orgForm()->controller;
        $this->state = $this->orgForm()->state->{$this->classname . $this->name};

        $this->getValue();

        foreach ($this->items as $field) {
            $field->setForm($this);
        }

        if ($renderAfterSetForm) $this->renderAfterSetForm();

        return $this;
    }

    /**
     * returns the form
     *
     * @return $this|Form
     */
    public function &form()
    {
        return $this;
    }

    /**
     * returns original form
     *
     * @return Form
     */
    public function orgForm()
    {
        return parent::form();
    }

    /**
     * the url for ajax
     *
     * @return string
     */
    public function externalURL()
    {
        return $this->orgForm()->externalURL() . "/" . $this->name;
    }

    /**
     * disables this field and all sub-fields
     */
    public function disable()
    {
        $this->disabled = true;
        /** @var FormField $field */
        foreach ($this->fields as $field)
            $field->disable();
    }

    /**
     * enables this field and all sub-fields
     */
    public function enable()
    {
        $this->disabled = false;
        /** @var FormField $field */
        foreach ($this->fields as $field)
            $field->enable();
    }

    /**
     * generates an id for the field
     *
     * @return string
     */
    public function ID()
    {
        if (Core::is_ajax()) {
            return "form_field_" . $this->classname . "_" . md5($this->orgForm()->getName() . $this->title) . "_" . $this->name . "_ajax";
        } else {
            return "form_field_" . $this->classname . "_" . md5($this->orgForm()->getName() . $this->title) . "_" . $this->name;
        }
    }

    /**
     * result
     *
     * @return array|mixed|null
     */
    public function result()
    {
        $this->result = $this->getModel();
        if(!$this->result)
            $this->result = array();

        /** @var FormField $field */
        foreach ($this->fields as $field) {
            $this->result[$field->dbname] = $field->result();
        }

        return $this->result;
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
     * this function generates some JavaScript for this formfield
     * @return string
     */
    public function js()
    {

    }

    /**
     * registers a field in this form
     *
     * @param string $name
     * @param object $field
     * @return bool
     */
    public function registerField($name, $field)
    {
        if ($name == $this->name) {
            return false;
        }
        $this->fields[strtolower($name)] = $field;
        $field->overridePostName = $this->name . "_" . $name;
    }

    /**
     * just unregisters the field in this form
     * @param string $name
     */
    public function unRegister($name)
    {
        unset($this->fields[strtolower($name)]);
    }

    /**
     * gets the field by the given name
     *
     * @param string $name
     * @return bool
     */
    public function getField($name)
    {
        if (isset($this->fields[strtolower($name)]))
            return $this->fields[strtolower($name)];

        return false;
    }

    /**
     * returns if a field exists in this form
     *
     * @return bool
     */
    public function isField($name)
    {
        return (isset($this->fields[strtolower($name)]));
    }

    /**
     * returns if a field exists and wasn't rendered in this form
     *
     * @return bool
     */
    public function isFieldToRender($name)
    {
        return ((isset($this->fields[strtolower($name)])) && !isset($this->renderedFields[strtolower($name)]));
    }

    /**
     * registers the field as rendered
     *
     * @name registerRendered
     * @access public
     * @param string - name
     */
    public function registerRendered($name)
    {
        $this->renderedFields[strtolower($name)] = true;
    }

    /**
     * removes the registration as rendered
     *
     * @name unregisterRendered
     * @access public
     * @param string - name
     */
    public function unregisterRendered($name)
    {
        unset($this->renderedFields[strtolower($name)]);
    }

    //!Overloading
    /**
     * Overloading
     */

    /**
     * returns a field in this form by name
     * it's not relevant how deep the field is in this form if the field is *not* within a ClusterFormField
     *
     * @return bool|mixed
     */
    public function __get($offset)
    {
        if ($offset == "form") {
            return $this->orgForm()->form;
        }

        return $this->getField($offset);
    }

    /**
     * currently set doesn't do anything
     *
     * @name __set
     * @access public
     */
    public function __set($offset, $value)
    {
        // currently there is no option to overload a form with fields
    }

    /**
     * returns if a field exists in this form
     *
     * @return bool
     */
    public function __isset($offset)
    {
        return $this->isField($offset);
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
     */
    public function setTemplateView($templateView)
    {
        $this->templateView = $templateView;
    }

    public function getName()
    {
        return $this->orgForm()->getName();
    }
}
