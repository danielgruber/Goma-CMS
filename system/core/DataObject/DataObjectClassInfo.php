<?php defined('IN_GOMA') OR die();

/**
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version     5.0
 */
class DataObjectClassInfo extends Extension
{

    /**
     * cache for relationships.
     */
    private static $relationShips = array();

    /**
     * generates extra class-info for given model
     *
     * @param string $class
     */
    public function generate($class)
    {
        if (PROFILE) Profiler::mark("DataObjectClassInfo::generate");

        self::generateModelClassInfo($class);

        if (class_exists($class) && class_exists("DataObject") && is_subclass_of($class, "DataObject")) {
            $classInstance = Object::instance($class);

            $has_one = ModelInfoGenerator::generateHas_one($class);
            $has_many = ModelInfoGenerator::generateHas_many($class);

            // generate table_name
            if (StaticsManager::hasStatic($class, "table")) {
                $table_name = StaticsManager::getStatic($class, "table");
            } else {
                $table_name = $classInstance->prefix . str_replace("\\", "_", $class);
            }


            $many_many = ModelInfoGenerator::generateMany_many($class);
            $db_fields = ModelInfoGenerator::generateDBFields($class);
            $belongs_many_many = ModelInfoGenerator::generateBelongs_many_many($class);

            $searchable_fields = ModelInfoGenerator::generate_search_fields($class);

            $indexes = ModelInfoGenerator::generateIndexes($class);

            /* --- */

            foreach ($indexes as $key => $value) {
                if (is_array($value)) {
                    $fields = $value["fields"];
                    $indexes[$key]["fields"] = array();
                    if (!is_array($fields))
                        $fields = explode(",", $fields);

                    if(strtolower($value["type"]) == "unique") {
                        if(!in_array("recordid", $fields)) {
                            $fields[] = "recordid";
                        }
                    }

                    $maxlength = $length = floor(333 / count($fields));
                    $fields_ordered = array();

                    foreach ($fields as $field) {
                        if (isset($db_fields[$field])) {
                            if (preg_match('/\(\s*([0-9]+)\s*\)/Us', $db_fields[$field], $matches)) {
                                $fields_ordered[$field] = $matches[1] - 1;
                            } else {
                                $fields_ordered[$field] = $maxlength;
                            }
                        } else {
                            unset($indexes[$key]);
                            unset($fields_ordered);
                            break;
                        }
                    }
                    if (isset($fields_ordered)) {
                        $indexlength = 333;

                        $i = 0;
                        foreach ($fields_ordered as $field => $length) {
                            if ($length < $maxlength) {

                                $maxlength = floor($indexlength / (count($fields) - $i));
                                $indexlength -= $length;
                                $indexes[$key]["fields"][] = $field;
                            } else if (preg_match('/enum/i', $db_fields[$field])) {
                                $indexes[$key]["fields"][] = $field;
                            } else {
                                $length = $maxlength;
                                // support for ASC/DESC
                                if (preg_match("/(ASC|DESC)/i", $field, $matches)) {
                                    $field = preg_replace("/(ASC|DESC)/i", "", $field);
                                    $indexes[$key]["fields"][] = $field . " (" . $length . ") " . $matches[1] . "";
                                } else {
                                    $indexes[$key]["fields"][] = $field . " (" . $length . ")";
                                }
                                unset($matches);
                            }


                            $i++;
                        }
                    }
                } else if (isset($db_fields[$key])) {
                    $indexes[$key] = $value;

                    if(strtolower($value) == "unique") {
                        $indexes[$key] = array(
                            "name" => $key,
                            "fields" => array(
                                $key,
                                "recordid"
                            ),
                            "type"  => "unique"
                        );
                    }
                } else if (!$value) {
                    unset($db_fields[$key]);
                }
                unset($key, $value, $fields, $maxlength, $fields_ordered, $i);
            }

            if (count($has_one) > 0) ClassInfo::$class_info[$class]["has_one"] = $has_one;
            if (count($has_many) > 0) ClassInfo::$class_info[$class]["has_many"] = $has_many;
            if (count($db_fields) > 0) ClassInfo::$class_info[$class]["db"] = $db_fields;
            if (count($many_many) > 0) ClassInfo::$class_info[$class]["many_many"] = $many_many;
            if (count($belongs_many_many) > 0) ClassInfo::$class_info[$class]["belongs_many_many"] = $belongs_many_many;

            if (count($searchable_fields) > 0) ClassInfo::$class_info[$class]["search"] = $searchable_fields;
            if (count($indexes) > 0) ClassInfo::$class_info[$class]["index"] = $indexes;


            /* --- */

            $relationShips = ModelManyManyRelationShipInfo::generateFromClass($class);

            if(!empty($relationShips)) {
                if(!isset(ClassInfo::$class_info[$class]["many_many_relations"])) {
                    ClassInfo::$class_info[$class]["many_many_relations"] = array();
                }

                foreach($relationShips as $relationShip) {

                    /** @var ModelManyManyRelationShipInfo $relationShip */

                    if (defined("SQL_LOADUP") && $fields = SQL::getFieldsOfTable($relationShip->getTableName())) {
                        ClassInfo::$database[$relationShip->getTableName()] = $fields;
                        unset($fields, $data);
                    }

                    ClassInfo::$class_info[$class]["many_many_relations"][$relationShip->getRelationShipName()] =
                        $relationShip->toClassInfo();

                    if($relationShip->getBelongingName() == null) {

                        ClassInfo::$class_info[$relationShip->getTarget()]["many_many_relations_extra"][] = array(
                            $class, $relationShip->getRelationShipName()
                        );
                    }
                }
            }

            unset($key, $data, $fields);

            /*
             * check if we need a sql-table
            */

            if (count($db_fields) == 0) {
                ClassInfo::$class_info[$class]["table"] = false;
                ClassInfo::$class_info[$class]["table_exists"] = false;
            } else {
                ClassInfo::$class_info[$class]["table"] = $table_name;
                ClassInfo::addTable($table_name, $class);
                if (defined("SQL_LOADUP") && $fields = SQL::getFieldsOfTable($table_name)) {
                    ClassInfo::$database[$table_name] = $fields;
                    ClassInfo::$class_info[$class]["table_exists"] = true;
                } else {
                    ClassInfo::$class_info[$class]["table_exists"] = false;
                }
            }

            unset($db_fields, $many_many, $has_one, $has_many, $searchable_fields, $belongs_many_many);

            // get data classes

            $parent = strtolower(get_parent_class($class));

            if ($parent == "dataobject" || $parent == "array_dataobject") {
                ClassInfo::$class_info[$class]["baseclass"] = $class;
            }

            if ($parent != "dataobject" && $parent != "array_dataobject") {
                ClassInfo::$class_info[$class]["dataclasses"][] = $class;
            }

            $_c = $parent;
            while ($_c != "dataobject" && $_c != "array_dataobject") {
                if (ClassInfo::$class_info[$class]["table"] !== false) {
                    ClassInfo::$class_info[$_c]["dataclasses"][] = $class;
                }
                if (strtolower(get_parent_class($_c)) == "dataobject") {
                    ClassInfo::$class_info[$class]["baseclass"] = $_c;
                } else {
                    ClassInfo::$class_info[$class]["dataclasses"][] = $_c;
                }

                $_c = strtolower(get_parent_class($_c));
            }
            unset($_c, $parent, $classInstance);
        }
        if (PROFILE) Profiler::unmark("DataObjectClassInfo::generate");
    }

    /**
     * generate class-info for Models. it gets casting and defaults.
     *
     * @param string $class
     */
    protected static function generateModelClassInfo($class) {
        if (!ClassInfo::isAbstract($class)) {
            $casting = ModelInfoGenerator::generateCasting($class);
            if (count($casting) > 0) {
                ClassInfo::$class_info[$class]["casting"] = $casting;
            }

            $defaults = ModelInfoGenerator::generateDefaults($class);
            if (count($defaults) > 0) {
                ClassInfo::$class_info[$class]["defaults"] = $defaults;
            }
        }
    }

    /**
     * returns array of ModelManyManyRelationShipInfo
     *
     * @param string|object $class
     * @return array
     */
    public static function getManyManyRelationships($class) {
        $class = ClassManifest::resolveClassName($class);

        if(!isset(self::$relationShips[$class]) || defined("GENERATE_CLASS_INFO")) {

            $currentClass = $class;
            self::$relationShips[$class] = array();

            do {
                $info = isset(ClassInfo::$class_info[$currentClass]["many_many_relations"]) ? ClassInfo::$class_info[$currentClass]["many_many_relations"] : array();

                if(!empty($info)) {
                    $relationShips = ModelManyManyRelationShipInfo::generateFromClassInfo($currentClass, $info);
                } else {
                    $relationShips = array();
                }

                self::$relationShips[$class] = array_merge($relationShips, self::$relationShips[$class]);

                $currentClass = ClassInfo::get_parent_class($currentClass);

            } while($currentClass != null && !ClassInfo::isAbstract($currentClass));
        }

        return self::$relationShips[$class];
    }

}

Object::extend("ClassInfo", "DataObjectClassInfo");
