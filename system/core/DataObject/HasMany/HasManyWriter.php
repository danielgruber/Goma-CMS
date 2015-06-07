<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Has-Many-Relationships of Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class HasManyWriter extends Extension {

    /**
     * writes has-many-relationships.
     *
     * @param array $data
     */
    protected function onBeforeWriteData($data) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();
        $data = $owner->getData();

        // has-many
        if ($has_many = $owner->getModel()->hasMany()) {
            foreach ($has_many as $name => $class) {
                if (isset($data[$name]) && is_object($data[$name]) && is_a($data[$name], "HasMany_DataObjectSet")) {
                    /** @var HasMany_DataObjectSet $hasManyObject */
                    $hasManyObject = $data[$name];

                    $key = $this->searchForBelongingHasOneRelationship($name, $class);

                    $hasManyObject->setRelationENV($name, $key . "id", $owner->getModel()->id);
                    $hasManyObject->writeToDB(false, true, $owner->getWriteType());
                } else {
                    if (isset($data[$name]) && !isset($data[$name . "ids"])) {
                        $data[$name . "ids"] = $data[$name];
                    }

                    if (isset($data[$name . "ids"]) && is_array($data[$name . "ids"])) {
                        // find field
                        $key = $this->searchForBelongingHasOneRelationship($name, $class);

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

        $owner->setData($data);
    }

    /**
     * searches for belonging has-one-relationship.
     *
     * @param String $relationShipName
     * @param String $hasManyTarget
     * @return mixed
     */
    protected function searchForBelongingHasOneRelationship($relationShipName, $hasManyTarget) {

        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        $key = array_search($owner->getModel()->classname, ClassInfo::$class_info[$hasManyTarget]["has_one"]);
        if ($key === false) {
            $currentClass = $owner->getModel()->classname;
            while ($currentClass = strtolower(get_parent_class($currentClass))) {
                if ($key = array_search($currentClass, ClassInfo::$class_info[$hasManyTarget]["has_one"])) {
                    break;
                }
            }
        }

        if ($key === false) {
            throw new LogicException("Could not find relation for " . $relationShipName . "ids.");
        }

        return $key;
    }
}
Object::extend("ModelWriter", "HasManyWriter");