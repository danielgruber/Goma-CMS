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
     * @name __construct
     * @param string - name
     * @param string - title
     * @param mixed - value
     * @param object - form
     */
    public function __construct($name = null, $fields, $label = null, &$parent = null)
    {
        parent::__construct($name, $label, null, $parent);

        /* --- */

        $this->container->setTag("fieldset");

        if (is_array($fields))
            $this->fields = $fields;
        else
            $this->fields = array();
    }

    /**
     * sets the form for all subfields, too
     *
     * @name setForm
     * @access public
     * @param form
     */
    public function setForm(&$form)
    {
        if (is_object($form)) {
            $this->parent =& $form;
            $this->state = $this->form()->state->{$this->classname . $this->name};
            $this->form()->fields[$this->name] = $this;
            $this->renderAfterSetForm();
        } else {
            throw new InvalidArgumentException('$form is not an object. $form needs to be an object in setForm.');
        }

        /** @var FormField $field */
        foreach ($this->fields as $sort => $field) {
            $this->items[$field->name] = $field;
            $this->sort[$field->name] = 1 + $sort;
            $field->setForm($this);
        }
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

        // get content
        uasort($this->items, array($this, "sort"));

        $i = 0;
        /** @var FormField $item */
        foreach ($this->items as $item) {

            // if a FieldSet is disabled all subfields should disabled, too
            if ($this->disabled) {
                $item->disable();
            }


            $name = strtolower($item->name);
            // if a field is deleted the field does not exist in that array
            if ($this->form()->isFieldToRender($name)) {
                $this->form()->registerRendered($name);
                $div = $item->field();
                if (is_object($div) && !$div->hasClass("hidden")) {
                    if ($i == 0) {
                        $i++;
                        $div->addClass("one");
                    } else {
                        $i = 0;
                        $div->addClass("two");
                    }
                    $div->addClass("visibleField");
                }
                $this->container->append($div);
            }
        }
        unset($i, $div, $item);
        $this->callExtending("afterField");

        $this->container->addClass("hidden");
        if (PROFILE) Profiler::unmark("FieldSet::field");

        return $this->container;
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
        if($field == null && $this->parent) {
            foreach($this->items as $subField) {
                /** @var FormField $subField */
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
            if ($this->form()->getField($fieldName) !== null) {
                $this->form()->unregisterField($fieldName);
            }

            if (isset($this->items[$fieldName])) {
                unset($this->items[$fieldName]);
            }

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
