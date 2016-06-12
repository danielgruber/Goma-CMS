<?php defined("IN_GOMA") OR die();

/**
 * stores the state of the form.
 * is serialzable.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.2
 */
class FormState extends gObject {
    protected $data;

    /**
     * FormState constructor.
     * @param array $data
     */
    function __construct($data = array()) {
        if(!is_array($data))
            throw new InvalidArgumentException("FormState Constructor requires first argument to be array.");
        $this->data = $data;
    }

    function __get($name) {
        if(!isset($this->data[$name]))
            $this->data[$name] = new FormState();
        if(is_array($this->data[$name]))
            $this->data[$name] = new FormState($this->data[$name]);
        return $this->data[$name];
    }

    function __set($name, $value) {
        $this->data[$name] = $value;
    }

    function __isset($name) {
        return isset($this->data[$name]);
    }

    function __toString() {
        if(!$this->data)
            return "";
        else
            return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    function toArray() {
        $output = array();
        foreach($this->data as $k => $v) {
            $output[$k] = (is_object($v) && method_exists($v, 'toArray')) ? $v->toArray() : $v;
        }
        return $output;
    }
}
