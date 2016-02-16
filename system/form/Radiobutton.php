<?php
defined("IN_GOMA") OR die();

/**
 * Radio-buttons.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0.5
 */
class RadioButton extends FormField
{
    /**
     * options for this set
     *
     * @name options
     * @access public
     */
    public $options;

    /**
     * which radio-buttons are disabled
     *
     * @name disabledNodes
     * @access public
     */
    public $disabledNodes = array();

    /**
     * defines if we hide disabled nodes
     *
     * @name hideDisabled
     * @access public
     */
    public $hideDisabled = false;

    /**
     * @name __construct
     * @param string - name
     * @param string - title
     * @param array - options
     * @param string - select
     * @param object - form
     * @access public
     */
    public function __construct($name, $title = null, $options = array(), $selected = null, $form = null)
    {
        $this->options = $options;
        parent::__construct($name, $title, $selected, $form);

    }

    /**
     * generates the options
     *
     * @name options
     * @access public
     * @return array
     */
    public function options()
    {
        $this->callExtending("onBeforeOptions");
        return $this->options;
    }

    /**
     * renders a option-record
     *
     * @param $name
     * @param $value
     * @param $title
     * @param null|bool $checked
     * @param null|bool $disabled
     * @return HTMLNode
     */
    public function renderOption($name, $value, $title, $checked = null, $disabled = null)
    {
        if (!isset($checked))
            $checked = false;

        if (!isset($disabled))
            $disabled = false;

        $id = "radio_" . md5($this->ID() . $name . $value);

        $node = new HTMLNode("div", array("class" => "option"), array(
            $input = new HTMLNode('input', array(
                "type" => "radio",
                "name" => $name,
                "value" => $value,
                "id" => $id
            )),
            $_title = new HTMLNode('label', array(
                "for" => $id
            ), $title)
        ));

        if ($checked)
            $input->checked = "checked";

        if ($disabled)
            $input->disabled = "disabled";

        if (isset($disabled) && $disabled && $this->hideDisabled)
            $node->css("display", "none");

        $this->callExtending("renderOption", $node, $input, $_title);

        return $node;
    }

    /**
     * renders the field
     *
     * @name field
     * @access public
     * @return HTMLNode
     */
    public function field()
    {
        $this->callExtending("beforeField");

        $this->container->append(new HTMLNode(
            "label",
            array(),
            $this->title
        ));

        $node = new HTMLNode("div", array("class" => "inputHolder"));

        foreach ($this->options as $value => $title) {
            $node->append($this->renderOption(
                $this->PostName(),
                $value,
                $title,
                $value == $this->value,
                $this->disabled || isset($this->disabledNodes[$value])
            ));
        }

        $this->container->append($node);

        $this->callExtending("afterField");

        return $this->container;
    }

    /**
     * this function generates some JSON for using client side stuff.
     *
     * @name exportJSON
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportFieldInfo($fieldErrors = null) {
        $info = $this->exportBasicInfo($fieldErrors)
            ->setRenderedField($this->field())
            ->setJs($this->js());

        $this->callExtending("afterRenderFormResponse", $info);

        return $info;
    }

    /**
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null)
    {
        $data = parent::exportBasicInfo($fieldErrors);

        $nodes = array();
        foreach ($this->options as $value => $title) {
            $nodes[] = array(
                "value"     => $value,
                "title"     => $title,
                "disabled"  => $this->disabled || isset($this->disabledNodes[$value]),
                "checked"   => $value == $this->value
            );
        }

        $data->setExtra("radioButtons", $nodes);

        return $data;
    }

    /**
     * adds an option
     *
     * @name addOption
     * @access public
     * @param string - key
     * @param mixed - val
     * @param bool - if to prepend instead of append
     */
    public function addOption($key, $val, $prepend = false)
    {
        if (!$prepend)
            $this->options[$key] = $val;
        else
            $this->options = array_merge(array($key => $val), $this->options);
    }

    /**
     * removes an option
     *
     * @name removeOption
     * @access public
     * @param string - key
     */
    public function removeOption($key)
    {
        unset($this->options[$key]);
    }

    /**
     * disables a specific radio-button
     *
     * @name disableNode
     * @access public
     */
    public function disableOption($id)
    {
        $this->disabledNodes[$id] = true;
    }

    /**
     * enables a specific radio-button
     *
     * @name enableNode
     * @access public
     */
    public function enableOption($id)
    {
        unset($this->disabledNodes[$id]);
    }

    /**
     * validation for security reason
     *
     * @name validate
     * @return bool
     */
    public function validate($value)
    {
        if (!isset($this->options[$value])) {
            return false;
        }

        return true;
    }
}