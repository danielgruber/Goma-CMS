<?php defined("IN_GOMA") OR die();

/**
 * abstract class to extend DataObjects.
 *
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
abstract class DataObjectExtension extends Extension
{

    /**
     * @return array
     */
    public static function DBFields() {
        return isset(Static::$db) ? Static::$db : array();
    }

    /**
     * @return array
     */
    public static function has_one() {
        return isset(Static::$has_one) ? Static::$has_one : array();
    }

    /**
     * @return array
     */
    public static function has_many() {
        return isset(Static::$has_many) ? Static::$has_many : array();
    }

    /**
     * @return array
     */
    public static function many_many() {
        return isset(Static::$many_many) ? Static::$many_many : array();
    }

    /**
     * @return array
     */
    public static function belongs_many_many() {
        return isset(Static::$belongs_many_many) ? Static::$belongs_many_many : array();
    }

    /**
     * @return array
     */
    public static function defaults() {
        return isset(Static::$default) ? Static::$default : array();
    }

    /**
     * @return array
     */
    public static function index() {
        return isset(Static::$index) ? Static::$index : array();
    }

    /**
     * @return array
     */
    public static function many_many_extra_fields() {
        return isset(Static::$many_many_extra_fields) ? Static::$many_many_extra_fields : array();
    }

    /**
     * @return array
     */
    public static function search_fields() {
        return isset(Static::$search_fields) ? Static::$search_fields : array();
    }

    /**
     * it does check if owner is a kind of DataObject.
     *
     * @param object
     * @return $this
     */
    public function setOwner($object)
    {
        if (!is_a($object, 'DataObject'))
        {
            $className = get_class($object);
            throw new InvalidArgumentException("Object must be subclass of DataObject, but is {$className}.");
        }

        parent::setOwner($object);
        return $this;
    }
}