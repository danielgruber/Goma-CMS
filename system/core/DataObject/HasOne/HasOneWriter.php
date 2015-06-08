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

                    // check for write
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
                    $data[$key . "id"] = $record->id;
                    unset($data[$key]);
                }
            }
        }

        $owner->setData($data);
    }
}
Object::extend("ModelWriter", "HasOneWriter");