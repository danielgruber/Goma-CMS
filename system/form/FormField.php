<?php defined("IN_GOMA") OR die();

/**
 * a basic class for every form-field in a form.
 *
 * @package        Goma\Form-Framework
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version    2.3.4
 */
class FormField extends AbstractFormComponent {
    /**
     * this var contains the node-object of the input-element
     *
     * @var HTMLNode
     */
    public $input;

    /**
     * this var contains the container
     *
     * @var HTMLNode
     */
    public $container;

    /**
     * @var array
     */
    public $url_handlers = array(
        '$Action//$id/$otherid' => '$Action'
    );

    /**
     * value
     *
     * @var mixed
     */
    public $value;

    /**
     * title of the field.
     *
     * @var string
     */
    protected $title;

    /**
     * defines if this field should use the full width or not
     * this is good, for example for textarea or something else to get correct position of info and label-area
     *
     * @var bool
     */
    protected $fullSizedField = false;

    /**
     * max-length.
     *
     * @var int
     */
    protected $maxLength = -1;

    /**
     * regexp for matching field value.
     *
     * @var string
     */
    protected $regexp = null;

    /**
     * regexp-error.
     */
    protected $regexpError = "form_not_matching";

    /**
     * errors.
     */
    protected $errors = array();

    /**
     * placeholder.
     *
     * @var string
     */
    protected $placeholder;

    /**
     * creates field.
     * @param string $name
     * @param string $title
     * @param mixed $value
     * @param Form|null $parent
     * @return static
     */
    public static function create($name, $title, $value = null, $parent = null) {
        return new static($name, $title, $value, $parent);
    }

    /**
     * creates field with maxlength.
     * @param string $name
     * @param string $title
     * @param int $maxLength
     * @param mixed $value
     * @param Form|null $parent
     * @return static
     */
    public static function createWithMaxLength($name, $title, $maxLength, $value = null, $parent = null) {
        $field = new static($name, $title, $value, $parent);
        $field->maxLength = $maxLength;
        return $field;
    }

    /**
     * @param FormField $field
     * @param string $placeholder
     * @return FormField
     */
    public static function addPlaceholder($field, $placeholder) {
        $field->setPlaceholder($placeholder);
        return $field;
    }

    /**
     * created field.
     *
     * @param string $name
     * @param string $title
     * @param mixed $value
     * @param Form|null $parent
     */
    public function __construct($name = null, $title = null, $value = null, &$parent = null)
    {
        parent::__construct($name, $value, $parent);

        $this->title = $title;
        $this->placeholder = $title;

        $this->input = $this->createNode();

        $this->container = new HTMLNode("div", array(
            "class" => "form_field " . $this->classname . " form_field_" . $name . ""
        ));

        if ($this->fullSizedField)
            $this->container->addClass("fullSize");
    }

    /**
     * creates the Node
     *
     * @return HTMLNode
     */
    public function createNode()
    {
        $input = new HTMLNode("input", array(
            'name' => $this->PostName(),
            "class" => "input",
            "type" => "text",
            "title" => $this->title
        ));

        return $input;
    }

    /**
     * sets the value
     */
    public function setValue() {
        $model = $this->getModel();
        if ($this->input && ($this->input->getTag() == "input" || $this->input->getTag() == "textarea") &&
            (is_string($model) || (is_object($model) && gObject::method_exists($model->classname, "__toString"))))
            $this->input->val($model);
    }

    /**
     * renders the field
     *
     * @param FormFieldRenderData $info
     * @return HTMLNode
     * @internal
     */
    public function field($info)
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->callExtending("beforeField");

        $this->setValue();

        $this->container->append(new HTMLNode(
            "label",
            array("for" => $this->ID()),
            $this->title
        ));

        if($this->maxLength > 0) {
            $this->input->attr("maxlength", $this->maxLength);
        }

        $this->input->placeholder = $this->placeholder;

        $this->container->append($this->input);

        if($this->errors) {
            $this->container->addClass("form-field-has-error");
        }

        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormField::field");

        return $this->container;
    }

    /**
     * this function generates some JavaScript for this formfield
     *
     * @return string
     */
    public function js()
    {
        return null;
    }

    /**
     * exports basic field info.
     *
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportBasicInfo($fieldErrors = null) {
        if(isset($fieldErrors[strtolower($this->name)])) {
            $this->errors = $fieldErrors[strtolower($this->name)];
        }

        return parent::exportBasicInfo($fieldErrors)
            -> setMaxLength($this->maxLength)
            -> setRegexp($this->regexp)
            -> setTitle($this->title);
    }

    /**
     * this is the validation for this field if it is required
     *
     * @return bool
     */
    public function validate($value) {
        if($this->maxLength > 0 && is_string($value) && strlen($value) > $this->maxLength) {
            return lang("form_too_long") . $this->title;
        }

        if($this->regexp) {
            if(!preg_match($this->regexp, $value)) {
                return lang($this->regexpError) . " '" . $this->title . "'";
            }
        }

        return true;
    }

    /**
     * sets the parent form-object
     * @param AbstractFormComponentWithChildren $form
     * @param bool $renderAfterSetForm
     * @return $this
     */
    public function setForm(&$form, $renderAfterSetForm = true)
    {
        parent::setForm($form);
        if (is_object($this->input)) {
            $this->input->name = $this->PostName();
        }


        $this->getValue();
        if($renderAfterSetForm) $this->renderAfterSetForm();

        return $this;
    }

    /**
     * gets value if is in result or post-data
     *
     * @internal
     */
    public function getValue()
    {
        $this->value = $this->getModel();
    }

    /**
     * renders some field contents after setForm
     *
     * @internal
     */
    public function renderAfterSetForm()
    {
        if (is_object($this->input)) $this->input->id = $this->ID();
        if (is_object($this->container)) $this->container->id = $this->divID();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength
     * @return $this
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegexp()
    {
        return $this->regexp;
    }

    /**
     * @param string $regexp
     * @return $this
     */
    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
        return $this;
    }

    /**
     * adds an extra-class to the field
     * @param string $class
     * @return $this
     */
    public function addExtraClass($class)
    {
        $this->container->addClass($class);
        return $this;
    }

    /**
     * removes an extra-class from the field
     * @param string $class
     * @return $this
     */
    public function removeExtraClass($class)
    {
        $this->container->removeClass($class);
        return $this;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }
}
