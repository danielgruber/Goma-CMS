<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Has-One-Relationships of Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class HasOneWriter extends Extension {
    /**
     * iterates through has-one-relationships and checks if there is something to write.
     */
    public function onBeforeDBWriter() {

        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        $data = $owner->getData();

        if ($has_one = $owner->getModel()->hasOne()) {
            foreach($has_one as $key => $value) {
                if (isset($data[$key]) && is_object($data[$key]) && is_a($data[$key], "DataObject")) {
                    /** @var DataObject $record */
                    $record = $data[$key];

                    if($has_one[$key]->getCascade() == DataObject::CASCADE_TYPE_UNIQUE) {
                        $fields = ArrayLib::map_key("strtolower", StaticsManager::getStatic($has_one[$key]->getTargetClass(), "unique_fields"));
                        $info = array();
                        foreach($fields as $field) {
                            $info[$field] = $record->$field;
                        }

                        // find object
                        $record = DataObject::get_one($has_one[$key]->getTargetClass(), $this->getFilterForUnique($has_one[$key], $info));
                        if(!isset($record)) {
                            $record = $this->getRecordForUnique($has_one[$key], $info);
                            $this->writeObject($record);
                        }

                        $data[$key . "id"] = $record->id;
                        unset($data[$key]);
                    } else {
                        if($this->shouldUpdateData($has_one[$key])) {
                            if($record->wasChanged() || $record->id == 0) {
                                $this->writeObject($record);
                            }
                        }

                        if($record->id == 0) {
                            throw new InvalidArgumentException("You have to Write Has-One-Objects before adding it to a DataObject and writing it.");
                        }
                        // get id from object
                        $data[$key . "id"] = $record->id;
                        unset($data[$key]);
                    }
                }
            }
        }

        $owner->setData($data);
    }

    /**
     * @param ModelHasOneRelationshipInfo $info
     * @param array $data
     * @return array
     */
    protected function getFilterForUnique($info, $data) {
        if($info->isUniqueLike()) {
            foreach($data as $key => $value) {
                $data[$key] = array("LIKE", trim($value));
            }
        }

        return $data;
    }

    /**
     * @param ModelHasOneRelationshipInfo $info
     * @param array $data
     */
    protected function getRecordForUnique($info, $data) {
        $target = $info->getTargetClass();

        if($info->isUniqueLike()) {
            foreach($data as $k => $v) {
                $data[$k] = trim($v);
            }
        }

        return new $target($data);
    }

    /**
     * @param DataObject $record
     */
    protected function writeObject($record) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        $writer = $owner->getRepository()->buildWriter(
            $record,
            -1,
            $owner->getSilent(),
            $owner->getUpdateCreated(),
            $owner->getWriteType(),
            $owner->getDatabaseWriter());
        $writer->write();
    }
    /**
     * @param ModelHasOneRelationshipInfo $info
     * @return bool
     */
    protected function shouldRemoveData($info) {
        return (substr($info->getCascade(), 0, 1) == 1);
    }

    /**
     * @param ModelHasOneRelationshipInfo $info
     * @return bool
     */
    protected function shouldUpdateData($info) {
        return (substr($info->getCascade(), 1, 1) == 1);
    }
}

gObject::extend("ModelWriter", "HasOneWriter");
