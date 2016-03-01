<?php
defined("IN_GOMA") OR die();

/**
 * A field set.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.2
 */
class FieldSet extends FormField
{
    /**
     * items of this fieldset
     *
     * @name items
     * @access public
     */
    protected $items = array();

    /**
     * sort of the items
     *
     * @name sort
     * @access public
     */
    public $sort = array();

    /**
     * fields of this FieldSet
     *
     * @name fields
     * @access public
     * @var array
     */
    public $fields = array();

    /**
     * template.
     */
    protected $template = "form/FieldSet.html";

    /**
     * template-view.
     */
    protected $templateView;

    /**
     * creates field.
     * @param string $name
     * @param array $fields
     * @param string $label
     * @param null $parent
     * @return static
     */
    public static function create($name, $fields, $label = null, $parent = null) {
        return new static($name, $fields, $label, $parent);
    }

    /**
     * @name __construct
     * @param string - name
     * @param string - title
     * @param mixed - value
     * @param object - form
     */
    public function __construct($name = null, $fields = array(), $label = null, &$parent = null)
    {
        parent::__construct($name, $label, null, $parent);

        /* --- */

        $this->container->setTag("fieldset");

        $this->templateView = new ViewAccessableData();

        if (is_array($fields)) {
            $this->fields = $fields;

            foreach($fields as $field) {
                if(!is_object($field)) {
                    throw new InvalidArgumentException("Every Field must be an instance of FormField.");
                }
            }
        } else {
            $this->fields = array();
        }
    }

    /**
     * sets the form for all subfields, too
     *
     * @param Form $form
     * @param bool $renderAfterSetForm
     */
    public function setForm(&$form, $renderAfterSetForm = true)
    {
        parent::setForm($form, false);

        /** @var FormField $field */
        foreach ($this->fields as $sort => $field) {
            $this->items[$field->name] = $field;
            $this->sort[$field->name] = 1 + $sort;
            $field->setForm($this);
        }

        if($renderAfterSetForm) $this->renderAfterSetForm();
    }

    /**
     * creates the legend-element if needed
     *
     * @return HTMLNode
     */
    public function createNode()
    {
        return new HTMLNode("legend", array(), $this->title);
    }

    /**
     * renders the field
     *
     * @return HTMLNode
     */
    public function field()
    {
        if (PROFILE) Profiler::mark("FieldSet::field");

        $this->callExtending("beforeField");

        $this->container->append($this->input);

        $this->callExtending("afterField");

        $this->container->addClass("hidden");
        if (PROFILE) Profiler::unmark("FieldSet::field");

        return $this->container;
    }

    /**
     *
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

        if($notifyField) {
            $this->callExtending("afterRenderFormResponse", $info);
        }
    }

    /**
     * exports basic field info.
     *
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null) {
        $data = parent::exportBasicInfo($fieldErrors);

        // get content
        uasort($this->items, array($this, "sort"));

        /** @var FormField $item */
        foreach($this->items as $item) {
            // if a FieldSet is disabled all subfields should disabled, too
            if ($this->disabled) {
                $item->disable();
            }

            $name = strtolower($item->name);

            if ($this->form()->isFieldToRender($name)) {
                $data->addChild($item->exportBasicInfo($fieldErrors));
            }
        }

        return $data;
    }

    /**
     * adds an field
     *
     * @name add
     * @access public
     */
    public function add($field, $sort = null)
    {
        if ($this->parent) {
            if (!isset($sort)) {
                $sort = 1 + count($this->items);
            }

            $this->sort[$field->name] = $sort;
            $this->items[$field->name] = $field;
            $field->setForm($this);
        } else {
            if (!isset($sort)) {
                $sort = 1 + count($this->fields);
                while (isset($this->fields[$sort]))
                    $sort++;
            }

            $this->fields[$sort] = $field;
        }
    }

    /**
     * removes a field or this field
     *
     * @param FormField|String|null $field
     */
    public function remove($field = null)
    {
        if($field === null && $this->parent) {
            /** @var FormField $subField */
            foreach($this->items as $subField) {
                $subField->remove();
            }

            /** @var FormField $subField */
            foreach($this->fields as $subField) {
                $subField->remove();
            }

            parent::remove();
        } else {
            $fieldName = is_object($field) ? $field->name : $field;
            $this->removeSpecific($fieldName);
        }
    }

    /**
     * removes a specific field from parent and subfields.
     *
     * @param $fieldName
     */
    protected function removeSpecific($fieldName) {
        if ($this->parent) {
            $this->form()->unregisterField($fieldName);

            unset($this->items[$fieldName]);

            foreach ($this->items as $subField) {
                /** @var FieldSet $subField */
                if (is_subclass_of($subField, "FieldSet")) {
                    $subField->remove($fieldName);
                }
            }
        } else {
            foreach ($this->fields as $key => $subField) {
                if (is_subclass_of($subField, "FieldSet")) {
                    /** @var FieldSet $subField */
                    $subField->remove($fieldName);
                } else if ($subField->name == $fieldName) {
                    unset($this->fields[$key]);
                }
            }
        }
    }


    /**
     * sorts the items
     *
     * @name sort
     * @access public
     * @return int
     */
    public function sort($a, $b)
    {
        if ($this->sort[$a->name] == $this->sort[$b->name]) {
            return 0;
        }

        return ($this->sort[$a->name] > $this->sort[$b->name]) ? 1 : -1;
    }
}
