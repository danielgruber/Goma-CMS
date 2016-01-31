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
            foreach($has_one as $name => $class) {
                if (!isset($data[$name]) || !is_object($data[$name]) || !is_a($data[$name], "DataObject")) {
                    $oldModel = null;

                    foreach ($data as $k => $v) {
                        if (substr(strtolower($k), 0, strlen($name) + 1) == $name . ".") {
                            if(!isset($oldModel)) {
                                $oldModel = $owner->getModel()->getHasOne($name);
                                if (!isset($oldModel)) {
                                    $oldModel = gObject::instance($class);
                                }
                            }

                            $oldModel->$k = $v;

                            unset($data[$k]);
                        }
                    }

                    if(isset($oldModel)) {
                        if($oldModel->hasChanged()) {
                            $data[$name] = $oldModel;
                        }
                    }
                }

                if (isset($data[$name]) && is_object($data[$name]) && is_a($data[$name], "DataObject")) {
                    /** @var DataObject $record */
                    $record = $data[$name];

                    // check for write
                    // TODO: Check here if we can write this record or we should get another record to avoid duplication in db.
                    if($owner->getCommandType() == ModelRepository::COMMAND_TYPE_INSERT || $record->wasChanged()) {
                        $writer = $owner->getRepository()->buildWriter(
                            $record,
                            -1,
                            $owner->getSilent(),
                            $owner->getUpdateCreated(),
                            $owner->getWriteType(),
                            $owner->getDatabaseWriter());
                        $writer->write();
                    }

                    // get id from object
                    $data[$name . "id"] = $record->id;
                    unset($data[$name]);
                }
            }
        }

        $owner->setData($data);
    }
}

gObject::extend("ModelWriter", "HasOneWriter");
