<?php defined("IN_GOMA") OR die();

/**
 * Base-Interface for all DB-Fields.
 *
 * @package		Goma\Core\Model
 * @version		1.6
 */
interface IDataBaseField {
    /**
     * constructor
     */
    public function __construct($name, $value, $args = array());

    /**
     * set the value of the field
     *
     *@name setValue
     */
    public function setValue($value);

    /**
     * gets the value of the field
     *
     *@name getValue
     */
    public function getValue();

    /**
     * sets the name of the field
     *
     *@name setName
     */
    public function setName($name);

    /**
     * gets the name of the field
     *
     *@name getName
     */
    public function getName();

    /**
     * gets the raw-data of the field
     * should be give back the same as getValue
     *
     *@name raw
     */
    public function raw();

    /**
     * generates the default form-field for this field
     *
     *@name formfield
     *@access public
     *@param string - title
     */
    public function formfield($title = null);

    /**
     * search-field for searching
     *
     *@name searchfield
     *@access public
     *@param string - title
     */
    public function searchfield($title = null);

    /**
     * this function uses more than one convert-method
     *
     *@name convertMulti
     *@access public
     *@param array - methods
     */
    public function convertMulti($arr);

    /**
     * gets the field-type for the database, for example if you want to have the type varchar instead of the name of this class
     *
     *@name getFieldType
     *@access public
     */
    static public function getFieldType($args = array());

    /**
     * toString-Method
     * should call default-convert
     *
     *@name __toString
     *@access public
     */
    public function __toString();

    /**
     * bool - for IF in template
     * should give back if the value of this field represents a false or true
     *
     *@name toBool
     *@access public
     */
    public function toBool();

    /**
     * to don't give errors for unknowen calls, should always give back raw-data
     *
     *@name __call
     *@access public
     */
    public function __call($name, $args);

    /**
     * bool, like toBool
     */
    public function bool();

    /**
     * returns datatype for view
     */
    public function forTemplate();
}