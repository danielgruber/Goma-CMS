<?php defined("IN_GOMA") OR die();

/**
 * Basic validator class. Accepts a callback for validation.
 *
 * @package		Goma\Form\Validation
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class FormValidator extends gObject
{
    /**
     * @name array
     * @var mixed - the data
     */
    protected $data;

    /**
     * @var Form
     */
    protected $form;

    /**
     * additional args for the function
     */
    protected $args = array();

    /**
     * @param callback $data
     * @param array $args
     */
    public function __construct($data = null, $args = null)
    {
        parent::__construct();

        if ($this->classname == "formvalidator" && !is_callable($data)) {
            throw new InvalidArgumentException("FormValidator requires a valid callback to be given.");
        }

        $this->args = isset($args) && is_array($args) ? $args : array();
        $this->data = $data;
    }

    /**
     * sets the form
     *
     * @name setForm
     * @param object
     */
    public function setForm(&$form)
    {
        $this->form = $form;
    }

    /**
     * validates the data
     *
     * @throws Exception
     */
    public function validate()
    {
        $data = call_user_func_array($this->data, array_merge(array($this), $this->args));
        if(is_string($data)) {
            throw new Exception($data);
        }
    }

    /**
     * generates some javascript for validating
     *
     * @name js
     * @access public
     * @return string
     */
    public function JS()
    {
        return "";
    }

    /**
     * validator.
     */
    public function exportFieldInfo($fields, $action) {
        $data = array(
            "js" => $this->JS()
        );

        if($data["js"]) {
            return $data;
        }

        return array();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }
}
