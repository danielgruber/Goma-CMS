<?php defined("IN_GOMA") OR die();

/**
 * a basic class for every form-field in a form.
 *
 * @package        Goma\Form-Framework
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version    2.3.3
 */
class FormField extends RequestHandler implements ArrayAccess
{
    /**
     * secret key for this form field
     *
     * @name randomKey
     * @access public
     */
    public $randomKey = "";

    /**
     * this var defines if the value of $this->form()->post[$name] should be set as value if it is set
     *
     * @name POST
     * @access protected
     */
    protected $POST = true;

    /**
     * the parent field of this field, e.g. a form or a fieldset
     *
     * @name parent
     * @access protected
     */
    protected $parent;

    /**
     * this var contains the node-object of the input-element
     *
     * @see HTMLNode
     * @name input
     * @access public
     * @var object
     */
    public $input;

    /**
     * this var contains the container
     * @see HTMLNode
     * @name container
     * @access public
     * @var object
     */
    public $container;

    public $url_handlers = array(
        '$Action//$id/$otherid' => '$Action'
    );

    /**
     * value
     *
     * @name value
     * @access public
     */
    public $value;

    /**
     * name of this field
     *
     * @name name
     * @access public
     */
    public $name;

    /**
     * name of the data-relation
     *
     * @name dbname
     * @access public
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
     * @name disabled
     * @access public
     */
    public $disabled = false;

    /**
     * title of the field.
     *
     * @name title
     */
    public $title;

    /**
     * defines if this field should use the full width or not
     * this is good, for example for textarea or something else to get correct position of info and label-area
     *
     * @name fullSizedField
     * @access public
     */
    protected $fullSizedField = false;

    /**
     * @name __construct
     * @param string - name
     * @param string - title
     * @param mixed - value
     * @param object - form
     */
    public function __construct($name = null, $title = null, $value = null, &$parent = null)
    {
        parent::__construct();

        /* --- */

        $this->randomKey = randomString(3);

        $this->name = $name;
        $this->dbname = strtolower(trim($name));
        $this->title = $title;
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
     * @name createNode
     * @access public
     */
    public function createNode()
    {
        return new HTMLNode("input", array(
            'name' => $this->PostName(),
            "class" => "input",
            "type" => "text",
            "title" => $this->title
        ));
    }

    /**
     * sets the value
     * @name setValue
     * @access public
     */
    public function setValue()
    {
        if ($this->input && ($this->input->getTag() == "input" || $this->input->getTag() == "textarea") && (is_string($this->value) || (is_object($this->value) && Object::method_exists($this->value->classname, "__toString"))))
            $this->input->val($this->value);
    }

    /**
     * renders the field
     * @name field
     * @access public
     */
    public function field()
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->callExtending("beforeField");

        $this->setValue();

        $this->container->append(new HTMLNode(
            "label",
            array("for" => $this->ID()),
            $this->title
        ));

        $this->input->placeholder = $this->title;

        $this->container->append($this->input);

        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormField::field");

        return $this->container;
    }

    /**
     * field function for mobile version
     *
     * @name mobileField
     * @access public
     */
    public function mobileField()
    {
        return $this->field();
    }

    /**
     * this function generates some JavaScript for this formfield
     * @name js
     * @access public
     */
    public function js()
    {
        return "";
    }

    /**
     * this function generates some JavaScript for the validation of the field
     * @name jsValidation
     * @access public
     */
    public function jsValidation()
    {
        return "";
    }

    /**
     * this is the validation for this field if it is required
     *
     * @name validation
     * @access public
     */
    public function validate($value)
    {
        return true;
    }

    /**
     * this function returns the result of this field
     * @name result
     * @access public
     * @return mixed
     */
    public function result()
    {
        if ($this->disabled) {
            return $this->value;
        } else {
            return isset($this->form()->post[$this->PostName()]) ? $this->form()->post[$this->PostName()] : null;
        }
    }

    /**
     * sets the parent form-object
     * @name setForm
     * @access public
     */
    public function setForm(Object &$form)
    {
        $this->parent =& $form;

        $this->form()->registerField($this->name, $this);
        if (is_object($this->input)) {
            $this->input->name = $this->PostName();
        }


        $this->getValue();
        $this->renderAfterSetForm();

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
            if (!$this->disabled && $this->POST && isset($this->form()->post[$this->PostName()])) {
                $this->value = $this->form()->post[$this->PostName()];
            } else if ($this->POST && $this->value == null && is_object($this->form()->result) && is_a($this->form()->result, "ArrayAccess") && isset($this->form()->result[$this->dbname])) {
                $this->value = ($this->form()->result->doObject($this->dbname)) ? $this->form()->result->doObject($this->dbname)->raw() : null;
            } else if ($this->POST && $this->value == null && is_array($this->form()->result) && isset($this->form()->result[$this->dbname])) {
                $this->value = $this->form()->result[$this->dbname];
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
        $this->form()->unregisterField($this->name);
    }

    /**
     * generates an id for the field
     * @name id
     * @access public
     */
    public function ID()
    {
        return "form_field_" . $this->classname . "_" . md5($this->form()->name() . $this->title) . "_" . $this->name;
    }

    /**
     * generates an id for the div
     * @name divID
     * @access public
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
        return isset($this->overridePostName) ? $this->overridePostName : $this->name;
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
     * creates a HTMLNode
     *
     * @name createTag
     * @access public
     */
    public function createTag($tag, $attr, $content)
    {
        $node = new HTMLNode($tag, $attr, $content);
        return $node->render();
    }

    /**
     * getter-method for state
     */
    public function __get($name)
    {
        if (strtolower($name) == "state") {
            return $this->form()->state->{$this->classname . $this->name};
        } else if (isset($this->$name)) {
            return $this->$name;
        } else {
            throw new LogicException("\$" . $name . " is not defined in " . $this->classname . " with name " . $this->name . ".");
        }
    }

    /**
     * unsets an attribute.
     *
     * @param    string $offset
     */
    public function offsetUnset($offset)
    {
        if (isset($this->$offset))
            unset($this->$offset);
    }

    /**
     * returns whether an attribute is set.
     *
     * @param    string $offset
     * @return    boolean
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }


    /**
     * returns the value of an attribute.
     *
     * @param    string $offset
     * @return    boolean
     */
    public function offsetGet($offset)
    {
        return property_exists($this, $offset) ? $this->$offset : null;
    }

    /**
     * sets the value of an attribute.
     *
     * @param    string $offset
     * @param    mixed $value
     * @return    boolean
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
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
}