<?php
defined("IN_GOMA") OR die();

/**
 * A single HasMany-Relationship.
 *
 * @package Goma
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 */
class ModelHasManyRelationShipInfo extends ModelRelationShipInfo {
    /**
     * @var string
     */
    protected static $modelInfoGeneratorFunction = "generateHas_many";

    public function __construct($ownerClass, $name, $options)
    {
        parent::__construct($ownerClass, $name, $options);

        if(isset($options["validatedInverse"]) && !isset($this->inverse)) {
            $this->validateAndForceInverse();
        }
    }

    /**
     * forces inverse.
     */
    protected function validateAndForceInverse() {
        $relationShips = ModelInfoGenerator::generateHas_one($this->targetClass);
        if(isset($this->inverse)) {
            if(!isset($relationShips[$this->inverse])) {
                throw new InvalidArgumentException("Inverse {$this->inverse} not found on class {$this->targetClass}.");
            }
        } else {
            $possibleKeys = array();
            foreach($relationShips as $key => $value) {
                if(is_array($value)) {
                    if(isset($value[DataObject::RELATION_TARGET])) {
                        $value = $value[DataObject::RELATION_TARGET];
                    } else if(isset($value["class"])) {
                        $value = $value["class"];
                    }
                }

                if(is_string($value)) {
                    if(ClassManifest::isSameClass($value, $this->owner) || is_subclass_of($this->owner, $value)) {
                        $possibleKeys[] = $key;
                    }
                }
            }

            if(count($possibleKeys) == 1) {
                $this->inverse = $possibleKeys[0];
            } else if(count($possibleKeys) == 0) {
                throw new InvalidArgumentException("Inverse for {$this->relationShipName} on class {$this->targetClass} not found. Create one has-one-relationship on target-class.");
            } else {
                throw new InvalidArgumentException("Multiple matching Has-One-Relationships found on class {$this->targetClass}. Please define inverse for relationship {$this->relationShipName} on {$this->owner}. " . print_r($possibleKeys, true));
            }
        }

        if($this->cascade == DataObject::CASCADE_TYPE_UNIQUE) {
            throw new InvalidArgumentException("HasMany does not support UNIQUE Cascade Type.");
        }

        if(DataObject::Versioned($this->owner) && !DataObject::Versioned($this->targetClass)) {
            if($this->cascade == DataObject::CASCADE_TYPE_ALL || $this->cascade == DataObject::CASCADE_TYPE_REMOVE) {
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
