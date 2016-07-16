<?php defined('IN_GOMA') OR die();

/**
 * Basic FormAction class which represents a basic "button".
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
 */
class FormAction extends FormField implements FormActionHandler
{
    /**
     * the submission-method on the controller for this form-action
     */
    protected $submit;

    /**
     * defines that these fields doesn't have a value
     */
    public $hasNoValue = true;

    /**
     * use html.
     */
    public $useHtml = false;

    /**
     * submit without data.
     */
    protected $submitWithoutData = false;

    /**
     * @param string - name
     * @param string - title
     * @param string - optional submission
     * @param object - form
     */
    public function __construct($name = null, $value = null, $submit = null, $classes = null, &$form = null)
    {
        parent::__construct($name, $value);
        if ($submit === null)
            $submit = "@default";

        $this->submit = $submit;
        if ($form != null) {
            $this->parent = $form;
            $this->setForm($form);
        }

        if (isset($classes))
            if (is_array($classes))
                foreach ($classes as $class)
                    $this->addClass($class);
            else
                $this->addClass($classes);
    }

    /**
     * generates the node
     *
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = new HTMLNode("button", array(
            "class" => "input",
            "name"  => $this->PostName(),
            "type"  => "submit"
        ), $this->title);
        $node->addClass("button");
        $node->addClass("formaction");
        $node->removeClass("input");

        return $node;
    }

    /**
     * renders the field
     *
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        if (PROFILE) Profiler::mark("FormAction::field");

        $this->callExtending("beforeField");
        if($this->useHtml) {
            $this->input->html($this->title);
        } else {
            $this->input->val($this->title);
        }

        $this->container->append($this->input);

        $this->container->setTag("span");
        $this->container->addClass("formaction");

        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormAction::field");

        return $this->container;
    }

    /**
     * returns if submit or not.
     * It should check if validation is required.
     *
     * @param array $data
     * @return bool
     */
    public function canSubmit($data)
    {
        return !!$data || $this->submitWithoutData;
    }

    /**
     * sets the submit-method
     * @param string|array $submit
     * @return $this
     */
    public function setSubmit($submit)
    {
        $this->submit = $submit;
        return $this;
    }

    /**
     * returns the submit-method
     *
     * @return null|string
     */
    public function getSubmit()
    {
        return $this->submit;
    }

    /**
     * @return null|string
     */
    public function __getSubmit() {
        return $this->submit;
    }

    /**
     * here you can add classes or remove some
     */

    /**
     * adds a class to the input
     * @param string $class
     * @return $this
     */
    public function addClass($class)
    {
        $this->input->addClass($class);
        return $this;
    }

    /**
     * removes a class from the input
     * @param string $class
     * @return $this
     */
    public function removeClass($class)
    {
        $this->input->removeClass($class);
        return $this;
    }

    /**
     * @return array
     */
    public function getClasses() {
        return explode(" ", $this->input->attr("classes"));
    }

    /**
     * @return mixed
     */
    public function getSubmitWithoutData()
    {
        return $this->submitWithoutData;
    }

    /**
     * @param mixed $submitWithoutData
     */
    public function setSubmitWithoutData($submitWithoutData)
    {
        $this->submitWithoutData = $submitWithoutData;
    }
}
