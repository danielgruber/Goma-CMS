<?php
defined("IN_GOMA") OR die();

/**
 * Gives some methods to get information about tables regarding classes.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class DBTableManager
{
    /**
     * returns a list of database-tables that can be referred to the DataObject.
     *
     * @name 	Tables
     * @param 	string $class
     * @return 	array
     */
    public static function Tables($class) {
        $class = ClassManifest::resolveClassName($class);

        if (!isset(ClassInfo::$class_info[$class]["baseclass"]))
            return array();

        if (ClassInfo::$class_info[$class]["baseclass"] == $class) {
            return self::TablesOfBaseClass($class);
        } else {
            return self::TablesOfBaseClass(ClassInfo::$class_info[$class]["baseclass"]);
        }
    }

    /**
     * gets all referred database-tables for a given baseclass.
     * this method does not check for Base-Class.
     *
     * @param 	string $baseClass
     * @return 	array
     */
    protected static function TablesOfBaseClass($baseClass) {
        if (!isset(ClassInfo::$class_info[$baseClass]["table"]) || empty(ClassInfo::$class_info[$baseClass]["table"])) {
            return array();
        }

        $tables = array();

        $tables[$baseClass . "_state"] = $baseClass . "_state";

        $tables = self::fillTableArray($baseClass, $tables);

        foreach (ClassInfo::getChildren($baseClass) as $subclass) {
            $tables = self::fillTableArray($subclass, $tables);
        }

        return $tables;
    }

    /**
     * fills an array with key and value the same for tables for given class.
     *
     * @param 	string $class
     * @param 	array $tables
     * @return 	array
     */
    protected static function fillTableArray($class, $tables) {
        if (isset(ClassInfo::$class_info[$class]["table"])) {
            $table = ClassInfo::$class_info[$class]["table"];

            if ($table) {
                $tables[$table] = $table;
            }
        }

        if (isset(ClassInfo::$class_info[$class]["many_many_tables"]) && ClassInfo::$class_info[$class]["many_many_tables"]) {
            foreach (ClassInfo::$class_info[$class]["many_many_tables"] as $data) {
                $tables[$data["table"]] = $data["table"];
            }
        }

        return $tables;
    }
}
