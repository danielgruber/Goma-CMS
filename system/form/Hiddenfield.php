<?php defined("IN_GOMA") OR die();

/**
 * hidden field. perfect to use as data storage.
 *
 * @package     Goma\Form-Framework
 *
 * @author      Goma-Team
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version     2.3.4
 */
class HiddenField extends FormField
{
    /**
     * it's not allowed to use Posted Data for this field
     *
     * @name POST
     * @access public
     */
    public $POST = false;

    /**
     * we don't need a title in this field
     *
     * @name __construct
     * @access public
     */
    public function __construct($name = null, $value = null, &$form = null)
    {
        parent::__construct($name, null, $value, $form);
    }

    /**
     * creates the node
     * sets the field-type to hidden
     *
     * @name createNode
     * @access public
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = parent::createNode();
        $node->type = "hidden";
        return $node;
    }

    /**
     * sets the value
     *
     * @name setValue
     */
    public function setValue()
    {
        if (is_string($this->value) || is_int($this->value)) {
            $this->input->val($this->value);
        } else {
            $this->input->val(1);
        }
    }

    /**
     * @return FormFieldResponse
     */
    public function exportFieldInfo()
    {
        $info = parent::exportFieldInfo();

        $info->setIsHidden(true);

        return $info;
    }

    /**
     * renders the field
     * @name field
     * @access public
     * @return HTMLNode
     */
    public function field()
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->callExtending("beforeField");

        $this->setValue();

        $this->container->append($this->input);
        $this->container->addClass("hidden");
        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormField::field");

        return $this->container;
    }
}
