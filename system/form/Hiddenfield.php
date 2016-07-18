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
     */
    public $POST = false;

    static $i = 0;

    /**
     * we don't need a title in this field
     * @param string $name
     * @param string $value
     * @param AbstractFormComponent $form
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
     */
    public function setValue()
    {
        if(($value = $this->getModel()) !== null && is_string($value) || is_int($value)) {
            $this->input->val($value);
        } else {
            $this->input->val(1);
        }
    }

    /**
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null)
    {
        $info = parent::exportBasicInfo($fieldErrors);

        $info->setIsHidden(true);

        return $info;
    }

    /**
     * renders the field
     *
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->callExtending("beforeField");

        $this->setValue();

        $this->input->removeAttr("disabled");
        $this->container->append($this->input);
        $this->container->addClass("hidden");
        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormField::field");

        return $this->container;
    }
}
