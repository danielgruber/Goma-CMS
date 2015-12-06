<?php defined("IN_GOMA") OR die();

/**
 * The basic class for every Form in the Goma-Framework. It can have FormFields
 * in it.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 2.4.2
 */
class ObjectRadioButton extends RadioButton
{

    /**
     * these fields need javascript
     *
     * @name javaScriptNeeded
     * @access protected
     */
    protected $javaScriptNeeded = array();

    /**
     * defines if we hide disabled nodes
     *
     * @name hideDisabled
     * @access public
     */
    public $hideDisabled = true;

    /**
     * renders a option-record
     *
     * @param string $postname
     * @param mixed $value
     * @param string $title
     * @param bool $checked
     * @param bool $disabled
     * @param FormField $field
     * @return HTMLNode
     * @internal param $renderOption
     * @access public
     */
    public function renderOption($postname, $value, $title, $checked = null, $disabled = null, $field = null)
    {
        $node = parent::renderOption($postname, $value, $title, $checked, $disabled);

        $children = $node->children();
        $input = $children[0];

        $id = $input->id;

        $this->javaScriptNeeded[] = $id;

        if (isset($field)) {
            if (!is_object($field)) {
                throw new LogicException("Error in ObjectRadioButton '" . $value . "': Field for Option '" . $value . "' does not exist or is null.");
            }
            $node->append(new HTMLNode('div', array(
                "id" => "displaycontainer_" . $id,
                "class" => "displaycontainer"
            ), $field->field()));
            $this->form()->registerRendered($field->name);
        }

        return $node;
    }

    /**
     * renders the field
     *
     * @name field
     * @access public
     * @return HTMLNode
     */
    public function field($info = null)
    {
        $this->callExtending("beforeField");

        $this->container->append(new HTMLNode(
            "label",
            array(),
            $this->title
        ));

        $node = new HTMLNode("div");

        if ($this->disabled) {
            $this->hideDisabled = false;
        }

        if (!$this->fullSizedField)
            $node->addClass("inputHolder");

        foreach ($this->options as $value => $title) {
            $field = null;
            if (is_array($title) && isset($title[1])) {
                $field = $this->form()->getField($title[1]);
                $title = $title[0];
            }

            if ($value == $this->value) {
                if ($this->disabled || isset($this->disabledNodes[$value])) {
                    $node->append($this->renderOption($this->PostName(), $value, $title, true, true, $field));
                } else {
                    $node->append($this->renderOption($this->PostName(), $value, $title, true, false, $field));
                }
            } else {
                if ($this->disabled || isset($this->disabledNodes[$value])) {
                    $node->append($this->renderOption($this->PostName(), $value, $title, false, true, $field));
                } else {
                    $node->append($this->renderOption($this->PostName(), $value, $title, false, false, $field));
                }
            }
        }

        $this->container->append($node);

        $this->callExtending("afterField");

        return $this->container;
    }

    /**
     * this function generates some JSON for using client side stuff.
     *
     * @name exportJSON
     * @return FormFieldResponse
     */
    public function exportFieldInfo() {
        $info = $this->exportBasicInfo(true);
        $info->setRenderedField($this->field($info))
             ->setJs($this->js());

        $this->callExtending("afterRenderFormResponse", $info);

        return $info;
    }

    /**
     * @param bool $withChildren
     * @return FormFieldResponse
     */
    public function exportBasicInfo($withChildren = false)
    {
        $data = parent::exportBasicInfo();

        $nodes = $data->getExtra("radioButtons");
        foreach($nodes as $node) {
            if (is_array($node["title"]) && isset($node["title"][1])) {
                $field = $this->form()->getField($node["title"][1]);
                $node["title"] = $node["title"][0];

                if($withChildren) {
                    $data->addChild($field->exportBasicInfo()->setJs($field->js()));
                } else {
                    $data->addChild($field->exportBasicInfo());
                }
            }
        }

        $data->setExtra("radioButtons", $nodes);

        return $data;
    }

    /**
     * generates the javascript for this field
     *
     * @name JS
     * @access public
     * @return string
     */
    public function JS()
    {
        Resources::add("system/form/ObjectRadioButton.js", "js", "tpl");
        $js = 'initObjectRadioButtons(field.divId, ' . json_encode($this->javaScriptNeeded) . ');';
        return $js;
    }
}
