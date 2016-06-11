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
class FieldSet extends AbstractFormComponentWithChildren
{
    /**
     * this var contains the container
     *
     * @var HTMLNode
     */
    public $container;

    /**
     * this var contains the node-object of the input-element
     *
     * @var HTMLNode
     */
    public $input;

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
        parent::__construct($name, $fields, null, $parent);

        $this->container = new HTMLNode("fieldset", array(
            "class" => "form_field " . $this->classname . " form_field_" . $name . ""
        ));

        $this->input = $this->createNode();
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
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
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
     * @return array|null|string|ViewAccessableData
     */
    public function getModel()
    {
        return isset($this->model) ? $this->model : (isset($this->parent) ? $this->parent->getModel() : null);
    }

    /**
     * @return null
     */
    public function js()
    {
        return null;
    }
}
