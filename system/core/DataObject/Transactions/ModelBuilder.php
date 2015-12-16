<?php defined("IN_GOMA") OR die();

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
class ModelBuilder {
    /**
     * checks for database-table.
     *
     * @param DataObject $model
     */
    public static function checkForTableExisting($model, $create = false) {
        // check if table in db and if not, create it
        if ($model->baseTable != "" && !isset(ClassInfo::$database[$model->baseTable])) {
            if($create || (isset($_GET["create"]) && $_GET["create"] == $model->classname)) {
                foreach (array_merge(ClassInfo::getChildren($model->classname), array($model->classname)) as $child) {
                    gObject::instance($child)->buildDB();
                }
                ClassInfo::write();
            } else {
                throw new LogicException("DataBase-Table for {$model->classname} does not exist. Append ?create={$model->classname} to url to rebuild.");
            }
        }
    }
}