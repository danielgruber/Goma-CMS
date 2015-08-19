<?php
defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */

class HistoryWriter extends Extension {

    /**
     * indicates whether history is disabled.
     */
    protected static $disabled = false;

    /**
     * disables history until it is reenabled.
     */
    public static function disableHistory() {
        self::$disabled = true;
    }

    /**
     * reenables history.
     */
    public static function enableHistory() {
        self::$disabled = false;
    }


    /**
     * called after write.
     */
    public function onAfterWrite() {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        if (!self::$disabled && StaticsManager::getStatic($owner->getModel()->classname, "history")) {

            $command = $owner->getCommandType();
            if($command != ModelRepository::COMMAND_TYPE_INSERT &&
                $owner->getWriteType() == ModelRepository::WRITE_TYPE_PUBLISH) {
                $command = "publish";
            }

            History::push($owner->getModel()->classname, $owner->getOldId(), $owner->getModel()->versionid, $owner->getModel()->id, $command);
        }
        unset($manipulation);
    }
}
Object::extend("ModelWriter", "HistoryWriter");