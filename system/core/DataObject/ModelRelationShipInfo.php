<?php
defined("IN_GOMA") OR die();

/**
 * Base-Class for Relationship-Infos.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
abstract class ModelRelationShipInfo
{
    /**
     * @var string
     */
    protected $relationShipName;

    /**
     * @var string
     */
    protected $targetClass;

    /**
     * inverse has-one-relationship on $targetClass.
     *
     * @var string
     */
    protected $inverse;

    /**
     * cascade
     *
     * @var string
     */
    protected $cascade = DataObject::CASCADE_TYPE_UPDATE;

    /**
     * fetch-type.
     *
     * @var string
     */
    protected $fetchType = DataObject::FETCH_TYPE_LAZY;

    /**
     * owner-class.
     */
    protected $owner;

    /**
     * @var string
     */
    protected static $modelInfoGeneratorFunction = "";

    /**
     * generates ClassInfo.
     * @param string|object $class
     * @return array
     */
    public static function getClassInfoForClass($class) {
        $class = ClassManifest::resolveClassName($class);

        $hasManyForClass = call_user_func_array(array("ModelInfoGenerator", static::$modelInfoGeneratorFunction), array($class, false));

        $info = array();
        foreach($hasManyForClass as $key => $val) {
            /** @var ModelRelationShipInfo $object */
            $object = new static($class, $key, $val);
            $info[$key] = $object->toClassInfo();
        }

        return $info;
    }

    /**
     * ModelRelationShipInfo constructor.
     * @param string $ownerClass
     * @param string $name
     * @param array $options
     */
    abstract public function __construct($ownerClass, $name, $options);

    /**
     * @return string
     */
    public function getRelationShipName()
    {
        return $this->relationShipName;
    }

    /**
     * @return string
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }

    /**
     * @return string
     */
    public function getInverse()
    {
        return $this->inverse;
    }

    /**
     * @return string
     */
    public function getCascade()
    {
        return $this->cascade;
    }

    /**
     * @return string
     */
    public function getFetchType()
    {
        return $this->fetchType;
    }

    /**
     * ToClassInfo.
     *
     * @return array
     */
    abstract public function toClassInfo();
}
