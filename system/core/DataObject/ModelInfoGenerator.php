<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ModelInfoGenerator {

    /**
     * combines data from given class-attribute + given extension method
     * @param string|object $class
     * @param string $staticProp static property on class
     * @param string $extensionMethod static method on extension
     * @param bool $useParents
     * @return array
     */
    protected static function generate_combined_array($class, $staticProp, $extensionMethod, $useParents = false) {
        $class = ClassManifest::resolveClassName($class);

        $fields = array();
        if (StaticsManager::hasStatic($class, $staticProp)) {
            $fields = (array)StaticsManager::getStatic($class, $staticProp);
        }

        // fields of extensions
        foreach(Object::getExtensionsForClass($class, false) as $extension) {
            if(Object::method_exists($extension, $extensionMethod)) {
                if($extensionFields = call_user_func_array(array($extension, $extensionMethod), array())) {
                    $fields = array_merge($fields, (array) $extensionFields);
                }
            }
        }

        // if parents, include parents.
        $parent = get_parent_class($class);
        if ($useParents == true && $parent != "DataObject") {
            $fields = array_merge(self::generate_combined_array($parent, $staticProp, $extensionMethod, true), $fields);
        }

        $fields = ArrayLib::map_key($fields, "strtolower");

        return $fields;
    }

    /**
     * gets all dbfields
     *
     * @param string|object $class
     * @param bool $parents
     * @return array
     */
    public function generateDBFields($class, $parents = false) {

        $fields = self::generate_combined_array($class, "db", "DBFields", $parents);

        foreach(self::generate_has_one($class, false) as $key => $value) {
            if (!isset($fields[$key . "id"])) { // check if field already is existing.
                $fields[$key . "id"] = "int(10)";
            }

            unset($key, $value);
        }

        if ($fields) {
            $fields = array_merge(call_user_func_array(array($class, "DefaultSQLFields"), array($class)), $fields);
        }

        return $fields;
    }

    public function validateDBFields($fields) {
        foreach($fields as $name => $type) {
            // hack to not break current Goma-CMS Build
            if(in_array($name, ViewAccessableData::$notViewableMethods) && (ClassInfo::$appENV["app"]["name"] != "gomacms" || goma_version_compare(ClassInfo::appVersion(), "2.0RC2-074", ">="))) {
                throw new DBFieldNotValidException($this->classname . "." . $name);
            }
        }
    }

    /**
     * gets has_one
     *
     * @access public
     * @param string|object $class
     * @param bool $parents
     * @return array
     */
    public static function generateHas_one($class, $parents = true) {

        $has_one = self::generate_combined_array($class, "has_one", "has_one", $parents);

        if (ClassInfo::get_parent_class($class) == "dataobject") {
            $has_one["autor"] = "user";
            $has_one["editor"] = "user";
        }

        $has_one = array_map("strtolower", $has_one);

        return $has_one;
    }

    /**
     * gets has_many
     *
     * @access public
     * @param string|object $class
     * @param bool $parents
     * @return array
     */
    public static function generateHas_many($class, $parents = true) {

        $has_many = self::generate_combined_array($class, "has_many", "has_many", $parents);

        $has_many = array_map("strtolower", $has_many);
        return $has_many;
    }

    /**
     * gets many_many
     *
     * @param string|object $class
     * @param bool $parents
     * @return array
     */
    public static function generateMany_many($class, $parents = true) {
        $many_many = self::generate_combined_array($class, "many_many", "belongs_many_many", $parents);

        // put everything in lowercase
        foreach($many_many as $k => $v) {
            if(is_string($v)) {
                $many_many[$k] = strtolower($v);
            } else {
                $many_many[$k]["class"] = strtolower($v["class"]);
            }
        }

        return $many_many;
    }

    /**
     * gets belongs_many_many
     *
     * @param string|object $class
     * @param bool $parents
     * @return array
     */
    public static function generateBelongs_many_many($class, $parents = true) {
        $belongs_many_many = self::generate_combined_array($class, "belongs_many_many", "belongs_many_many", $parents);

        // put values to lowercase
        foreach($belongs_many_many as $k => $v) {
            if(is_string($v)) {
                $belongs_many_many[$k] = strtolower($v);
            } else {
                $belongs_many_many[$k]["class"] = strtolower($v["class"]);
            }
        }

        return $belongs_many_many;
    }

    /**
     * generates many-many-data for given key and value pair.
     * it also have to know if it is belonging or not.
     *
     * @param string $class
     * @param $key
     * @param $value
     * @param bool $belonging
     */
    protected static function generate_many_many_tableinfo($class, $key, $value, $belonging = false) {
        $key = trim(strtolower($key));
        $extraFields = self::get_many_many_extraFields($class, $key);

        $table = "many_many_".strtolower(get_class($this))."_".  $key . '_' . $value;
        if (!SQL::getFieldsOfTable($table)) {
            $table = "many_".strtolower(get_class($this))."_".  $key;
        }
    }

    /**
     * returns basic information about a given relationship.
     *
     * this returns an dictionary with the following keys:
     * - holder: Holder-Class
     * - belonging: Belonging-class
     * - relation: relationship-name from holder-class
     * - belongingRelation: relationShip-name from belonging class
     *
     * @param string $class
     * @param string $key
     * @param string $value
     * @param bool $belonging
     * @return array
     */
    protected static function getRelationShipInfo($class, $key, $value, $belonging = false) {
        $relationInfo = self::getRelationInfoWithInverse($value);
        if($belonging) {

        } else {
            return array(
                "holder" => $class,
                "belonging" => $relationInfo[0],
                "relation" => $key,
                "belongingRelation" => self::findInverseManyManyRelationship($key, $class, $value, $belonging));
        }
    }

    /**
     * searches for inverse relationships on other class.
     *
     * @param string $key of this relationship
     * @param string $class name of class which holds relationship
     * @param string $value value of relationship
     * @param bool $belonging if class stores it in belongs or normal many-many
     * @return string
     */
    protected static function findInverseManyManyRelationship($key, $class, $value, $belonging = false) {
        $info = self::getRelationInfoWithInverse($value);

        $relationships = ($belonging) ? self::generateMany_many($info[0]) : self::generateBelongs_many_many($info[0]);

        if(isset($info[1]) && isset($relationships[$info[1]])) {
            $relationInfo = $relationships[$info[1]];
            if((!isset($relationInfo[1]) || $relationInfo[1] == $key) && ($relationInfo[0] == $class || is_subclass_of($class, $relationInfo[0]))) {
                return $info[1];
            }
        } else {
            // find relationship
            foreach($relationships as $k => $v) {
                $relationInfo = self::getRelationInfoWithInverse($v);

                if((!isset($relationInfo[1]) || $relationInfo[1] == $key) && ($relationInfo[0] == $class || is_subclass_of($class, $relationInfo[0]))) {
                    return $k;
                }
            }
        }

        if($belonging) {
            throw new LogicException("No Inverse relation found for Relationship $key on $class.", ExceptionManager::RELATIONSHIP_INVERSE_REQUIRED);
        } else {
            return null;
        }

    }

    /**
     * gets extra-fields for given class and key.
     *
     * @param string|object $class
     * @param key of many-many-relationship
     * @return array
     */
    protected static function get_many_many_extraFields($class, $key) {

        $fields = array();
        if(StaticsManager::hasStatic($class, "many_many_extra_fields")) {
            $extraFields = ArrayLib::map_key("strtolower", (array)StaticsManager::getStatic($this->classname, "many_many_extra_fields"));
            if (isset($extraFields[$key])) {
                $fields = $extraFields[$key];
            }
        }

        foreach(Object::getExtensionsForClass($class, false) as $extension) {
            if(Object::method_exists($extension, "many_many_extra_fields")) {
                if($extensionFields = call_user_func_array(array($extension, "many_many_extra_fields"), array())) {
                    if(isset($extensionFields[$key])) {
                        $fields = array_merge($fields, $extensionFields[$key]);
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * generates many-many tables
     *
     *@name generateManyManyTables
     *@access public
     */
    public static function generateManyManyTables($class) {
        $class = ClassManifest::resolveClassName($class);

        $tables = array();



        // many-many
        foreach($this->generateMany_many(false) as $key => $value) {
            // generate extra-fields
            if (isset($this->many_many_extra_fields[$key])) {
                $extraFields = $this->many_many_extra_fields[$key];
            } else if (self::isStatic($this->classname, "many_many_extra_fields")) {
                $extraFields = ArrayLib::map_key("strtolower", (array)StaticsManager::getStatic($this->classname, "many_many_extra_fields"));
                if (isset($extraFields[$key]))
                    $extraFields = $extraFields[$key];
                else
                    $extraFields = array();
            } else {
                $extraFields = array();
            }

            $extendExtraFields = $this->localCallExtending("many_many_extra_fields");
            if (isset($extendExtraFields[$key])) {
                $extraFields = array_merge($extraFields, $extendExtraFields[$key]);
            }

            $value = ClassManifest::resolveClassName($value);

            $table = "many_many_".strtolower(get_class($this))."_".  $key . '_' . $value;
            if (!SQL::getFieldsOfTable($table)) {
                $table = "many_".strtolower(get_class($this))."_".  $key;
            }

            $object = $value;
            if($value === strtolower(get_class($this))) {
                $value = $value . "_" . $value;
            }

            $tables[$key] = array(
                "table"			=> $table,
                "field"			=> strtolower(get_class($this)) . "id",
                "extfield"		=> $value . "id",
                "object"		=> $object
            );
            if ($extraFields) {
                $tables[$key]["extraFields"] = $extraFields;
            }
            unset($key, $value);
        }

        //# belongs-many-many
        foreach($this->generateBelongs_Many_many(false) as $key => $value) {
            $info = $this->getRelationInfoWithInverse($value);
            $value = $info[0];
            $relation = $info[1];

            if (is_subclass_of($value, "DataObject")) {
                $inst = Object::instance($value);
                $relations = ArrayLib::map_key("strtolower", $inst->generateMany_Many());

                $field = strtolower(get_class($this));

                if (is_array($relations)) {
                    if (isset($relation)) {
                        $relation = strtolower($relation);

                        if (isset($relations[$relation]) && ($relations[$relation] == $this->classname) || is_subclass_of($this->classname, $relations[$relation]) || $this->classname == $relations[$relation] || is_subclass_of($relations[$relation], $this->classname)) {
                            // everything okay
                        } else {
                            throw new LogicException("Relation ".$relation." does not exist on ".$value.".");
                        }
                    } else {
                        $relation = null;
                        foreach($relations as $r => $d) {
                            if(is_array($d) && $d["class"] == $this->classname) {
                                $relation = $r;
                                break;
                            } else if(is_string($d) && $d == $this->classname) {
                                $relation = $r;
                                break;
                            }
                        }

                        // search for inverse with parent class-names.
                        if(!isset($relation)) {
                            $c = $this->classname;
                            while($c = ClassInfo::getParentClass($c))
                            {
                                foreach($relations as $r => $d) {
                                    if(is_array($d) && $d["class"] == $c) {
                                        $relation = $r;
                                        $field = $c;
                                        break 2;
                                    } else if(is_string($d) && $d == $c) {
                                        $relation = $r;
                                        $field = $c;
                                        break 2;
                                    }
                                }

                            }
                        }

                        if(!isset($relation)) {
                            throw new Exception("No inverse on ".$value." found.");
                        }
                    }
                } else {
                    throw new LogicException("Relation ".$relation." does not exist on ".$value.".");
                }

                // generate extra-fields
                if (isset($inst->many_many_extra_fields[$relation])) {
                    $extraFields = $this->many_many_extra_fields[$key];
                } else if (self::isStatic($inst->classname, "many_many_extra_fields")) {
                    $extraFields = ArrayLib::map_key("strtolower", (array)StaticsManager::getStatic($inst->classname, "many_many_extra_fields"));
                    if (isset($extraFields[$relation]))
                        $extraFields = $extraFields[$relation];
                    else
                        $extraFields = array();
                } else {
                    $extraFields = array();
                }

                $extendExtraFields = $inst->localCallExtending("many_many_extra_fields");
                if (isset($extendExtraFields[$relation])) {
                    $extraFields = array_merge($extraFields, $extendExtraFields);
                }


            } else {

                throw new LogicException($value . " must be subclass of DataObject to be a handler for a many-many-relation.");
            }

            if ($relation) {
                $table = "many_many_".$value."_".  $relation . '_' . strtolower(get_class($this));
                if (!SQL::getFieldsOfTable($table))
                    $table = "many_" . $value . "_" . $relation;

                if($value === $field) {
                    $field = $field . "_" . $field;
                }

                $tables[$key] = array(
                    "table"			=> $table,
                    "field"			=> $field . "id",
                    "extfield"		=> $value . "id",
                    "object"    	=> $value
                );
                if ($extraFields) {
                    $tables[$key]["extraFields"] = $extraFields;
                }
                unset($key, $value);
            }
        }

        $parent = get_parent_class($this);
        if ($parent != "DataObject") {
            $tables = array_merge(Object::instance($parent)->generateManyManyTables(), $tables);
        }

        return $tables;
    }

    /**
     * tries to extract inverse from relationship-info.
     *
     * @param string|array $value value of many-many-relatipnship.
     * @return array first value if class and second inverse
     */
    public static function getRelationInfoWithInverse($value) {
        $relation = null;
        if (is_array($value)) {
            if (isset($value["relation"]) && isset($value["class"])) {
                $relation = $value["relation"];
                $value = $value["class"];
            } else if (isset($value["inverse"]) && isset($value["class"])) {
                $relation = $value["inverse"];
                $value = $value["class"];
            } else {
                $value = array_values($value);
                $relation = @$value[1];
                $value = $value[0];
            }
        }

        return array(ClassInfo::find_class_name($value), strtolower($relation));
    }

    /**
     * indexes
     *
     * @param string|object $class
     * @return array
     */
    public static function generateIndexes($class) {
        $indexes = self::generate_combined_array($class, "index", "index", false);

        foreach(self::generateHas_one($class, false) as $key => $value) {
            if (!isset($indexes[$key . "id"])) {
                $indexes[$key . "id"] = "INDEX";
                unset($key, $value);
            }
        }

        $searchable_fields = StaticsManager::getStatic($class, "search_fields");
        if ($searchable_fields) {
            // we add an index for fast searching
            $indexes["searchable_fields"] = array("type" => "INDEX", "fields" => implode(",", $searchable_fields), "name" => "searchable_fields");
        }

        // validate
        foreach($indexes as $name => $type) {
            if (is_array($type)) {
                if (!isset($type["type"]) || !isset($type["fields"])) {
                    throw new LogicException("Index $name in DataObject $class is invalid. Type and Fields are required.", ExceptionManager::INDEX_INVALID);
                }
            }
        }

        $db = self::generateDBFields($class, false);
        if (isset($db["last_modified"])) {
            $indexes["last_modified"] = "INDEX";
        }

        return $indexes;

    }

    /**
     * generates casting
     *
     * @param string|object $class
     * @param bool $parents
     * @return array
     */
    public function generateCasting($class, $parents = true) {

        return self::generate_combined_array($class, "casting", "casting", $parents);
    }
}