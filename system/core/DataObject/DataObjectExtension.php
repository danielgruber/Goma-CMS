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
        return isset(static::$db) ? static::$db : array();
    }

    /**
     * @return array
     */
    public static function has_one() {
        return isset(static::$has_one) ? static::$has_one : array();
    }

    /**
     * @return array
     */
    public static function has_many() {
        return isset(static::$has_many) ? static::$has_many : array();
    }

    /**
     * @return array
     */
    public static function many_many() {
        return isset(static::$many_many) ? static::$many_many : array();
    }

    /**
     * @return array
     */
    public static function belongs_many_many() {
        return isset(static::$belongs_many_many) ? static::$belongs_many_many : array();
    }

    /**
     * @return array
     */
    public static function defaults() {
        return isset(static::$default) ? static::$default : array();
    }

    /**
     * @return array
     */
    public static function index() {
        return isset(static::$index) ? static::$index : array();
    }

    /**
     * @return array
     */
    public static function many_many_extra_fields() {
        return isset(static::$many_many_extra_fields) ? static::$many_many_extra_fields : array();
    }

    /**
     * @return array
     */
    public static function search_fields() {
        return isset(static::$search_fields) ? static::$search_fields : array();
    }

    /**
     * it does check if owner is a kind of DataObject.
     *
     * @param object
     * @return $this
     */
    public function setOwner($object)
    {
        if (!is_a($object, 'DataObject') && !is_null($object))
        {
            $className = get_class($object);
            throw new InvalidArgumentException("Object must be subclass of DataObject, but is {$className}.");
        }

        parent::setOwner($object);
        return $this;
    }
}