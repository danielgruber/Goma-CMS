<?php defined("IN_GOMA") OR die();

/**
 * compares a model with an array.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */
class DataObjectCompare {
    /**
     * gets array of changed fields from model.
     *
     * @param DataObject $model
     * @param array $newdata
     * @return array
     */
    public static function getChanges($model, $newdata)
    {
        $changed = array();

        if (is_object($newdata) && gObject::method_exists($newdata, "toArray")) {
            /** @var ViewAccessableData $newdata */
            $newdata = $newdata->ToArray();
        }

        // first calculate change-count
        $data = $model->ToArray();
        foreach ($data as $key => $val) {
            if (isset($newdata[$key])) {
                $comparableTypes = array("boolean", "integer", "string", "double");
                if (gettype($newdata[$key]) != gettype($val) &&
                    !in_array(gettype($newdata[$key]), $comparableTypes) &&
                    !in_array(gettype($val), $comparableTypes)
                ) {
                    $changed[] = strtolower(trim($key));
                } else if ($newdata[$key] != $val) {
                    $changed[] = strtolower(trim($key));
                }
            }
        }

        return $changed;
    }
}
