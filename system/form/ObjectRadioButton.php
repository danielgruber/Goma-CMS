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
class ObjectRadioButton extends RadioButton  {
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
     * template.
     */
    protected $setTemplate = "form/FieldSet.html";

    /**
     * @var ViewAccessableData
     */
    protected $templateView;

    public function __construct($name = null, $title = null, $options = array(), $selected = null, $form = null)
    {
        parent::__construct($name, $title, $options, $selected, $form);

        $this->templateView = new ViewAccessableData();
    }

    /**
     * renders a option-record
     *
     * @param string $postname
     * @param mixed $value
     * @param string $title
     * @param bool|null $checked
     * @param bool|null $disabled
     * @param FormFieldRenderData $field
     * @param FormFieldRenderData $info
     * @return HTMLNode
     * @internal param $renderOption
     * @access public
     */
    public function renderOption($postname, $value, $title, $checked = null, $disabled = null, $field = null, $info = null)
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
            ), $this->templateView->customise($info->ToRestArray())->customise(array(
                "fields" => new DataSet(array(
                    $field->ToRestArray(true, true)
                ))
            ))->renderWith($this->setTemplate)));
        }

        return $node;
    }

    /**
     * renders the field
     *
     * @param FormFieldRenderData|null $info
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
            $childToRender = null;
            if (is_array($title) && isset($title[1])) {
                $field = $this->form()->getField($title[1]);
                $title = $title[0];

                /** @var FormFieldRenderData $child */
                foreach($info->getChildren() as $child) {
                    if($child->getName() == $field->name) {
                        if($this->form()->isFieldToRender($child->getName())) {
                            $child->getField()->addRenderData($child);
                            $childToRender = $child;
                            break;
                        }
                    }
                }
            }

            $node->append(
                $this->renderOption(
                    $this->PostName(),
                    $value,
                    $title,
                    $value == $this->value,
                    $this->disabled || isset($this->disabledNodes[$value]),
                    $childToRender,
                    $info
                )
            );
        }

        $this->container->append($node);

        $this->callExtending("afterField");

        return $this->container;
    }

    /**
     * adds render data.
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        parent::addRenderData($info, false);

        $info->addJSFile("system/form/ObjectRadioButton.js");

        $this->callExtending("afterRenderFormResponse", $info);
    }

    /**
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null)
    {
        $data = parent::exportBasicInfo($fieldErrors);

        $nodes = $data->getExtra("radioButtons");
        foreach($nodes as $node) {
            if (is_array($node["title"]) && isset($node["title"][1])) {
                if($field = $this->form()->getField($node["title"][1])) {
                    $node["title"] = $node["title"][0];

                    $data->addChild($field->exportBasicInfo($fieldErrors));
                } else {
                    throw new LogicException("Could not find Field " . $node["title"][1]);
                }
            }
        }

        $data->setExtra("radioButtons", $nodes);

        return $data;
    }

    /**
     * generates the javascript for this field
     *
     * @return string
     */
    public function JS()
    {
        $js = 'initObjectRadioButtons(field, field.divId, ' . json_encode($this->javaScriptNeeded) . ');';
        return parent::js() . $js;
    }
}
