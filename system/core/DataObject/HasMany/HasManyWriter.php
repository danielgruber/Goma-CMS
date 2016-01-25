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
 */
class HasManyWriter extends Extension {

    /**
     * writes has-many-relationships.
     */
    protected function onBeforeWriteData() {
        /** @var ModelWriter $owner */
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

                        $key = self::searchForBelongingHasOneRelationship($owner->getModel(), $name, $class);

                        if($hasManyObject->fieldToArray("id")) {
                            $this->removeFromRelationShip($class, $key . "id", $owner->getModel()->id, $hasManyObject->fieldToArray("id"));
                        }

                        $hasManyObject->setRelationENV($name, $key . "id", $owner->getModel()->id);
                        $hasManyObject->writeToDB(false, true, $owner->getWriteType());
                    } else {
                        if (isset($data[$name]) && !isset($data[$name . "ids"])) {
                            $data[$name . "ids"] = $data[$name];
                        }

                        if (isset($data[$name . "ids"]) && $this->validateIDsData($data[$name . "ids"])) {
                            // find field
                            $key = self::searchForBelongingHasOneRelationship($owner->getModel(), $name, $class);

                            if($data[$name . "ids"]) {
                                $this->removeFromRelationShip($class, $key . "id", $owner->getModel()->id, $data[$name . "ids"]);
                            }

                            foreach ($data[$name . "ids"] as $id) {
                                /** @var DataObject $belongingObject */
                                $belongingObject = DataObject::get_one($class, array("id" => $id));
                                $belongingObject[$key . "id"] = $owner->getModel()->id;

                                $writer = $owner->getRepository()->buildWriter(
                                    $belongingObject,
                                    -1,
                                    $owner->getSilent(),
                                    $owner->getUpdateCreated(),
                                    $owner->getWriteType(),
                                    $owner->getDatabaseWriter());
                                $writer->write();
                            }
                        }
                    }
                }
            }
        }

        $owner->setData($data);
    }

    /**
     * set field to 0 for all elements which have at the moment the given id on that field, but
     * the recordid is not in the given array.
     *
     * @param string $class
     * @param string $field
     * @param int $key
     * @param array $excludeRecordIds
     */
    protected function removeFromRelationShip($class, $field, $key, $excludeRecordIds) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        foreach(DataObject::get($class,
            "$field = '".$key."' AND recordid NOT IN ('".implode("','", $excludeRecordIds)."'')"
        ) as $notExistingElement) {
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

    /**
     * searches for belonging has-one-relationship.
     *
     * @param DataObject $model
     * @param String $relationShipName
     * @param String $hasManyTarget
     * @return mixed
     */
    public static function searchForBelongingHasOneRelationship($model, $relationShipName, $hasManyTarget) {
        $key = array_search($model->classname, ClassInfo::$class_info[$hasManyTarget]["has_one"]);
        if ($key === false) {
            $currentClass = $model->classname;
            while ($currentClass = strtolower(get_parent_class($currentClass))) {
                if ($key = array_search($currentClass, ClassInfo::$class_info[$hasManyTarget]["has_one"])) {
                    break;
                }
            }
        }

        if ($key === false) {
            throw new LogicException("Could not find inverse-relation for " . $relationShipName . " on class $hasManyTarget.");
        }

        return $key;
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
