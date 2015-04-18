<?php defined("IN_GOMA") OR die();
/**
 * bool-type.
 */
class BoolDBField extends DBField {

    /**
     * converts every type of value into bool.
     */
    public function __construct($name, $value, $args = array())
    {
        if(strtolower($value) == strtolower(lang("no")) || strtolower($value) == "no") {
            $value = false;
        }

        $value = (bool) $value;

        parent::__construct($name, $value, $args);
    }

    /**
     * default convert
     */
    public function forTemplate() {
        if($this->value) {
            return lang("yes");
        } else {
            return lang("no");
        }
    }

    /**
     * generatesa a switch.
     *
     * @param string $title
     * @return Checkbox|FormField
     */
    public function formfield($title = null)
    {
        return new Checkbox($this->name, $title, $this->value);
    }
}