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
     *
     * @var string
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
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * constructor.
     *
     * @param string $ownerClass
     * @param string $name
     * @param array|string $options
     */
    public function __construct($ownerClass, $name, $options)
    {
        $this->owner = strtolower(trim($ownerClass));
        $this->relationShipName = strtolower(trim($name));

        if(is_string($options)) {
            $this->targetClass = strtolower($options);
        } else {
            if(isset($options[DataObject::RELATION_TARGET])) {
                $this->targetClass = $options[DataObject::RELATION_TARGET];
            } else if(isset($options["class"])) {
                $this->targetClass = $options["class"];
            } else {
                throw new InvalidArgumentException("No Target class defined.");
            }

            $this->targetClass = strtolower(trim($this->targetClass));

            if(isset($options[DataObject::RELATION_INVERSE])) {
                $this->inverse = strtolower(trim($options[DataObject::RELATION_INVERSE]));
            }

            if(isset($options[DataObject::CASCADE_TYPE])) {
                $this->cascade = $options[DataObject::CASCADE_TYPE];
            }

            if(isset($options[DataObject::FETCH_TYPE])) {
                $this->fetchType = $options[DataObject::FETCH_TYPE];
            }
        }

        if(!isset($options["validatedInverse"])) {
            $this->validateAndForceInverse();
        }
    }

    /**
     * @return mixed
     */
    abstract protected function validateAndForceInverse();

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
     * @return bool
     */
    public function shouldRemoveData() {
        return (substr($this->getCascade(), 0, 1) == 1);
    }

    /**
     * @return bool
     */
    public function shouldUpdateData() {
        return (substr($this->getCascade(), 1, 1) == 1);
    }

    /**
     * ToClassInfo.
     *
     * @return array
     */
    abstract public function toClassInfo();
}
