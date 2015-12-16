<?php defined("IN_GOMA") OR die();

/**
 * Basic class for casting field values for Database.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */

class DataBaseFieldManager {
    /**
     * gets field-value-pairs for a given class-table of the current data
     * it returns the data for the table of the given class
     * this is used for seperating data in write to correct tables
     *
     * @param string $class |object class or table-name
     * @param array $data
     * @param bool $useDefault if use defaul values from class or not
     * @param bool $silent default: false
     * @return array
     */
    public static function getFieldValues($class, $data, $useDefault, $silent = false)
    {
        $className = ClassManifest::resolveClassName($class);

        $arr = array();
        if (isset(ClassInfo::$class_info[$className]["db"])) {

            if (isset(ClassInfo::$database[ClassInfo::$class_info[$className]["table"]])) {
                $arr = self::fillFieldArray(ClassInfo::$database[ClassInfo::$class_info[$className]["table"]], $data, $className, $useDefault, !$silent);
            }
        } else if (isset(ClassInfo::$database[$className])) {
            $arr = self::fillFieldArray(ClassInfo::$database[$className], $data, $className, $useDefault, !$silent);
        }

        // casting
        foreach($arr as $field => $val) {
            $casting = gObject::instance($className)->casting();
            if(isset($casting[$field])) {
                $object = DBField::getObjectByCasting($casting[$field], $field, $val);
                if(gObject::method_exists($object, "forDB")) {
                    $arr[$field] = $object->forDB();
                } else {
                    $arr[$field] = $object->raw();
                }
            }
        }

        if(isset($arr["recordid"])) {
            unset($arr["recordid"]);
        }

        return $arr;
    }

    /**
     * parses field-data into an array.
     *
     * @param array $fields
     * @param array $data
     * @param string $className
     * @param bool $useDefaults
     * @param bool $addLastModified
     * @return array
     * @internal param fields $array
     */
    public static function fillFieldArray($fields, $data, $className, $useDefaults = true, $addLastModified = true) {
        $arr = array();

        foreach($fields as $field => $type)
        {
            if (strtolower($field) != "id") {
                $value =  self::parseRawValue($data, $field);
                if(!isset($value)) {
                    $value = self::parseRawValue($data, strtolower($field));
                }

                if(isset($value)) {
                    $arr[$field] = $value;
                } else if($useDefaults) {
                    $defaultValue = gObject::instance($className)->getDefaultValue($field);
                    if($defaultValue != null) {
                        $arr[$field] = $defaultValue;
                    }
                }

                if(isset($arr[$field]) && $arr[$field] === false) {
                    $arr[$field] = 0;
                }
            }
        }

        if (isset($fields["last_modified"]) && $addLastModified) {
            $arr["last_modified"] = time();
        }

        return $arr;
    }

    /**
     * parses value with raw and returns it.
     *
     * @param array $data
     * @param string $field
     * @return string val
     */
    protected static function parseRawValue($data, $field) {
        if (isset($data[$field])) {
            if (is_object($data[$field])) {
                if (gObject::method_exists($data[$field], "raw")) {
                    return $data[$field]->raw();
                }
            }

            return $data[$field];
        }
    }
}