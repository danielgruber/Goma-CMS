<?php
defined("IN_GOMA") OR die();

/**
 * A single HasMany-Relationship.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 */
class ModelHasOneRelationshipInfo extends ModelRelationShipInfo {
    /**
     * @var string
     */
    protected static $modelInfoGeneratorFunction = "generateHas_one";

    /**
     * constructor.
     *
     * @param string $ownerClass
     * @param string $name
     * @param array|string $options
     */
    public function __construct($ownerClass, $name, $options)
    {
        $this->owner = $ownerClass;
        $this->relationShipName = $name;

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

            $this->targetClass = strtolower($this->targetClass);

            if(isset($options[DataObject::RELATION_INVERSE])) {
                $this->inverse = strtolower($options[DataObject::RELATION_INVERSE]);
            }

            if(isset($options[DataObject::CASCADE_TYPE])) {
                $this->cascade = $options[DataObject::CASCADE_TYPE];
            }

            if(isset($options[DataObject::FETCH_TYPE])) {
                $this->fetchType = $options[DataObject::FETCH_TYPE];
            }
        }

        if(!isset($options["validatedInverse"])) {
            $this->validateInverse();
        }
    }

    /**
     * forces inverse.
     */
    protected function validateInverse() {
        if(isset($this->inverse)) {
            $relationShips = ModelInfoGenerator::generateHas_many($this->targetClass);
            if(!isset($relationShips[$this->inverse])) {
                throw new InvalidArgumentException("Inverse {$this->inverse} not found on class {$this->targetClass}.");
            }
        }

        if(DataObject::Versioned($this->owner) && !DataObject::Versioned($this->targetClass)) {
            if( $this->cascade == DataObject::CASCADE_TYPE_ALL ||
                $this->cascade == DataObject::CASCADE_TYPE_REMOVE ||
                $this->cascade == DataObject::CASCADE_TYPE_UNIQUE) {
                throw new InvalidArgumentException("When using Remove-Cascade Versioning must be equal on both objects.");
            }
        }
    }

    /**
     * generates information for ClassInfo.
     *
     * @return array
     */
    public function toClassInfo() {
        return array(
            DataObject::RELATION_TARGET => $this->targetClass,
            DataObject::RELATION_INVERSE => $this->inverse,
            DataObject::FETCH_TYPE => $this->fetchType,
            DataObject::CASCADE_TYPE => $this->cascade,
            "validatedInverse"  => true
        );
    }
}
