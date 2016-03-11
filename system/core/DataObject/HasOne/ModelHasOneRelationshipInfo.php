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
     * forces inverse.
     */
    protected function validateAndForceInverse() {
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
