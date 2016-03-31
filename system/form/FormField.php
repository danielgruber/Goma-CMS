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
class FormField extends RequestHandler {
    /**
     * this var defines if the value of $this->form()->post[$name] should be set as value if it is set
     *
     * @var boolean
     */
    protected $POST = true;

    /**
     * the parent field of this field, e.g. a form or a fieldset
     *
     * @var Form|FieldSet
     * @name parent
     */
    protected $parent;

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
     * name of this field
     *
     * @var string
     */
    public $name;

    /**
     * name of the data-relation
     *
     * @var string
     */
    public $dbname;

    /**
     * overrides the post-name
     *
     * @name overridePostName
     * @access public
     */
    public $overridePostName;

    /**
     * defines if this field is disabled
     *
     * @var bool
     */
    public $disabled = false;

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
     * @param $name
     * @param $title
     * @param $value
     * @param null $parent
     * @return static
     */
    public static function create($name, $title, $value = null, $parent = null) {
        return new static($name, $title, $value, $parent);
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
        parent::__construct();

        /* --- */

        $this->name = $name;
        $this->dbname = strtolower(trim($name));
        $this->title = $title;
        $this->placeholder = $title;
        $this->value = $value;
        $this->parent =& $parent;
        if ($parent) {
            $this->form()->fields[$name] = $this;
            if (is_a($this->parent, "form")) {
                $this->parent->fieldList->add($this);
            } else {
                $this->parent->items[$name] = $this;
                $this->parent->sort[$name] = count($this->parent->sort);
            }
        }

        $this->input = $this->createNode();

        $this->container = new HTMLNode("div", array(
            "class" => "form_field " . $this->classname . " form_field_" . $name . ""
        ));

        if ($this->fullSizedField)
            $this->container->addClass("fullSize");

        if ($this->parent)
            $this->renderAfterSetForm();
    }

    /**
     * creates the Node
     *
     * @name createNode
     * @access public
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

        if($this->maxLength > 0) {
            $input->attr("maxlength", $this->maxLength);
        }

        return $input;
    }

    /**
     * sets the value
     * @name setValue
     * @access public
     */
    public function setValue() {
        if ($this->input && ($this->input->getTag() == "input" || $this->input->getTag() == "textarea") && (is_string($this->value) || (is_object($this->value) && gObject::method_exists($this->value->classname, "__toString"))))
            $this->input->val($this->value);
    }

    /**
     * renders the field
     *
     * @param FormFieldRenderData|null $info
     * @return HTMLNode
     * @internal
     */
    public function field($info = null)
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->callExtending("beforeField");

        $this->setValue();

        $this->container->append(new HTMLNode(
            "label",
            array("for" => $this->ID()),
            $this->title
        ));

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
        return "";
    }

    /**
     * this function generates some JSON for using client side stuff.
     *
     * @param array|null $fieldErrors
     * @return FormFieldRenderData
     */
    public function exportFieldInfo($fieldErrors = null) {
        $info = $this->exportBasicInfo($fieldErrors);

        $this->addRenderData($info);

        return $info;
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true) {
        try {
            $this->form()->registerRendered($info->getName());

            $this->callExtending("beforeRender", $info);

            $fieldData = $this->field($info);

            $info->setRenderedField($fieldData)
                ->setJs($this->js());

            if ($notifyField) {
                $this->callExtending("afterRenderFormResponse", $info);
            }
        } catch(Exception $e) {
            if($info->getRenderedField() == null) {
                $info->setRenderedField(new HTMLNode("div", array("class" => "form_field")));
            }
            $info->getRenderedField()->append('<div class="error">' . $e->getMessage() . '</div>');
        }
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

        return $this->createsRenderDataClass()
            -> setMaxLength($this->maxLength)
            -> setRegexp($this->regexp)
            -> setTitle($this->title)
            -> setIsDisabled($this->disabled)
            -> setField($this)
            -> setHasError(count($this->errors) > 0);
    }

    /**
     * @return FormFieldRenderData
     */
    protected function createsRenderDataClass() {
        return FormFieldRenderData::create($this->name, $this->classname, $this->ID(), $this->divID());
    }

    /**
     * this is the validation for this field if it is required
     *
     * @name validation
     * @access public
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
     * this function returns the result of this field
     *
     * @return mixed
     */
    public function result() {
        if ($this->disabled || $this->form()->disabled || !$this->POST) {
            return $this->value;
        } else {
            return isset($this->form()->post[$this->PostName()]) ? $this->form()->post[$this->PostName()] : null;
        }
    }

    /**
     * sets the parent form-object
     * @param Form $form
     * @param bool $renderAfterSetForm
     */
    public function setForm(&$form, $renderAfterSetForm = true)
    {
        $this->parent =& $form;

        $this->form()->registerField($this->name, $this);
        if (is_object($this->input)) {
            $this->input->name = $this->PostName();
        }


        $this->getValue();
        if($renderAfterSetForm) $this->renderAfterSetForm();
    }

    /**
     * gets value if is in result or post-data
     *
     * @name getValue
     * @access public
     */
    public function getValue()
    {
        if (!isset($this->hasNoValue) || !$this->hasNoValue) {
            if($this->POST) {
                if (!$this->disabled && isset($this->form()->post[$this->PostName()])) {
                    $this->value = $this->form()->post[$this->PostName()];
                } else if ($this->value == null) {
                    if(is_a($this->form()->result, "ArrayAccess") && isset($this->form()->result[$this->dbname])) {
                        $this->value = ($this->form()->result->doObject($this->dbname)) ? $this->form()->result->doObject($this->dbname)->raw() : null;
                    } else if (is_array($this->form()->result) && isset($this->form()->result[$this->dbname])) {
                        $this->value = $this->form()->result[$this->dbname];
                    }
                }
            }
        }
    }

    /**
     * renders some field contents after setForm
     *
     * @name renderAfterSetForm
     * @access public
     */
    public function renderAfterSetForm()
    {
        if (is_object($this->input)) $this->input->id = $this->ID();
        if (is_object($this->container)) $this->container->id = $this->divID();
    }

    /**
     * removes this field
     * @name remove
     * @access public
     */
    public function remove()
    {
        $this->form()->remove($this->name);
    }

    /**
     * generates an id for the field
     * @name id
     * @access public
     * @return string
     */
    public function ID()
    {
        return "form_field_" . $this->classname . "_" . md5($this->form()->name() . $this->title) . "_" . $this->name;
    }

    /**
     * generates an id for the div
     * @name divID
     * @access public
     * @return string
     */
    public function divID()
    {
        return $this->ID() . "_div";
    }

    /**
     * the url for ajax
     *
     * @name externalURL
     * @access public
     * @return string
     */
    public function externalURL()
    {
        return $this->form()->externalURL() . "/" . $this->name;
    }

    /**
     * returns the post-name
     *
     * @return string
     */
    public function PostName()
    {
        return isset($this->overridePostName) ? strtolower($this->overridePostName) : strtolower($this->name);
    }

    /**
     * returns the current real form-object
     *
     * @return Form
     */
    public function &form()
    {
        if (is_object($this->parent)) {
            $data =& $this->parent->form();
            return $data;
        } else {
            throw new LogicException('No Form for Field ' . $this->classname);
        }
    }

    /**
     * disables this field
     *
     * @name disable
     * @access public
     */
    public function disable()
    {
        if (is_object($this->input))
            $this->input->disabled = "disabled";

        $this->disabled = true;
    }

    /**
     * reenables the field
     * @name enable
     * @access public
     */
    public function enable()
    {
        unset($this->input->disabled);
        $this->disabled = false;
    }

    /**
     * getter-method for state
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (strtolower($name) == "state") {
            return $this->form()->state->{$this->classname . $this->name};
        } else if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new LogicException("\$" . $name . " is not defined in " . $this->classname . " with name " . $this->name . ".");
        }
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
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
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
     */
    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
    }

    /**
     * adds an extra-class to the field
     */
    public function addExtraClass($class)
    {
        $this->container->addClass($class);
    }

    /**
     * removes an extra-class from the field
     */
    public function removeExtraClass($class)
    {
        $this->container->removeClass($class);
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
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
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }
}
