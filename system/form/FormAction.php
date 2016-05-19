<?php defined('IN_GOMA') OR die();

/**
 * Basic FormAction class which represents a basic "button".
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
class FormAction extends FormField implements FormActionHandler
{
    /**
     * the submission-method on the controller for this form-action
     *
     * @name submit
     * @access protected
     */
    protected $submit;

    /**
     * defines that these fields doesn't have a value
     *
     * @name hasNoValue
     */
    public $hasNoValue = true;

    /**
     * use html.
     */
    public $useHtml = false;

    /**
     * @name __construct
     * @access public
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
     * @name createNode
     * @access public
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
     * @name field
     * @access public
     * @return HTMLNode
     */
    public function field()
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
        return !!$data;
    }

    /**
     * sets the submit-method
     * @param string|array $submit
     */
    public function setSubmit($submit)
    {
        $this->submit = $submit;
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
     * here you can add classes or remove some
     */

    /**
     * adds a class to the input
     *
     * @name addClass
     * @access public
     */
    public function addClass($class)
    {
        $this->input->addClass($class);
    }

    /**
     * removes a class from the input
     *
     * @name removeClass
     * @access public
     */
    public function removeClass($class)
    {
        $this->input->removeClass($class);
    }
}
