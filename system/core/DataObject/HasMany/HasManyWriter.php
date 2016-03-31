<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Has-Many-Relationships of Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0.1
 *
 * @method ModelWriter getOwner()
 */
class HasManyWriter extends Extension {
    /**
     * writes has-many-relationships.
     */
    protected function onBeforeWriteData() {
        $owner = $this->getOwner();
        $data = $owner->getData();

        // has-many
        /** @var HasManyGetter $hasManyExtension */
        if($hasManyExtension = $owner->getModel()->getInstance(HasManyGetter::ID)) {
            if ($has_many = $hasManyExtension->hasMany()) {
                foreach ($has_many as $name => $class) {
                    if (isset($data[$name]) && is_object($data[$name]) && is_a($data[$name], "HasMany_DataObjectSet")) {
                        /** @var HasMany_DataObjectSet $hasManyObject */
                        $hasManyObject = $data[$name];

                        if($this->shouldUpdateData($has_many[$name])) {
                            $hasManyObject->setRelationENV($name, $has_many[$name]->getInverse() . "id", $owner->getModel()->id);
                            $hasManyObject->writeToDB(false, true, $owner->getWriteType());

                            if($hasManyObject->fieldToArray("id")) {
                                $this->removeFromRelationShip($class->getTargetClass(), $has_many[$name]->getInverse() . "id", $owner->getModel()->id, $hasManyObject->fieldToArray("id"), $this->shouldRemoveData($has_many[$name]));
                            }
                        } else {
                            $data[$name] = $hasManyObject->fieldToArray("id");
                        }
                    }

                    if (isset($data[$name]) && !isset($data[$name . "ids"]) && is_array($data[$name])) {
                        $data[$name . "ids"] = $data[$name];
                    }

                    if (isset($data[$name . "ids"]) && $this->validateIDsData($data[$name . "ids"])) {
                        if(in_array(0, $data[$name . "ids"])) {
                            throw new InvalidArgumentException("HasMany-Relationship must contain only already written records.");
                        }

                        $this->removeFromRelationShip($class->getTargetClass(), $has_many[$name]->getInverse() . "id", $owner->getModel()->id, $data[$name . "ids"], $this->shouldRemoveData($has_many[$name]));
                        $this->updateRelationship($data[$name . "ids"], $has_many[$name]);
                    }
                }
            }
        }

        $owner->setData($data);
    }

    /**
     * @param ModelHasManyRelationShipInfo $info
     * @return bool
     */
    protected function shouldRemoveData($info) {
        return (substr($info->getCascade(), 0, 1) == 1);
    }

    /**
     * @param ModelHasManyRelationShipInfo $info
     * @return bool
     */
    protected function shouldUpdateData($info) {
        return (substr($info->getCascade(), 1, 1) == 1);
    }

    /**
     * set field to 0 for all elements which have at the moment the given id on that field, but
     * the recordid is not in the given array.
     *
     * @param string $class
     * @param string $field
     * @param int $key
     * @param int[] $excludeRecordIds
     * @param bool $removeFromDatabase
     */
    protected function removeFromRelationShip($class, $field, $key, $excludeRecordIds, $removeFromDatabase) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        foreach(DataObject::get($class,
            "$field = '".$key."' AND recordid NOT IN ('".implode("','", $excludeRecordIds)."')"
        ) as $notExistingElement) {
            if($removeFromDatabase) {
                $notExistingElement->remove();
            } else {
                $notExistingElement->$field = 0;
                $writer = $owner->getRepository()->buildWriter(
                    $notExistingElement,
                    -1,
                    $owner->getSilent(),
                    $owner->getUpdateCreated(),
                    $owner->getWriteType(),
                    $owner->getDatabaseWriter());
                $writer->write();
            }
        }
    }

    /**
     * @param array $ids
     * @param ModelHasManyRelationShipInfo $relationShip
     */
    protected function updateRelationship($ids, $relationShip) {
        $owner = $this->getOwner();

        /** @var DataObject $record */
        foreach(DataObject::get($relationShip->getTargetClass(), array("id" => $ids)) as $record) {
            $record->{$relationShip->getInverse() . "id"} = $this->getOwner()->getModel()->id;
            $writer = $owner->getRepository()->buildWriter(
                $record,
                -1,
                $owner->getSilent(),
                $owner->getUpdateCreated(),
                $owner->getWriteType(),
                $owner->getDatabaseWriter());
            $writer->write();
        }
    }

    /**
     * validates if input is correct.
     *
     * @param $data
     * @return bool
     */
    private function validateIDsData($data)
    {
        if(!is_array($data)) {
            return false;
        }

        foreach($data as $record) {
            if(!is_string($record) && !is_int($record)) {
                return false;
            }
        }

        return true;
    }

    /**
     * extends hasChanged-Method.
     */
    public function extendHasChanged(&$changed) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();
        /** @var HasManyGetter $extensionInstance */
        if($extensionInstance = $owner->getModel()->getInstance(HasManyGetter::ID)) {
            // has-many
            if ($has_many = $extensionInstance->hasMany()) {
                if ($owner->checkForChangeInRelationship(array_keys($has_many), true, true)) {
                    $changed = true;

                    return;
                }
            }
        }
    }
}
gObject::extend("ModelWriter", "HasManyWriter");
