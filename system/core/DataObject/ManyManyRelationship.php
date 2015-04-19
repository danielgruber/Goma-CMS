<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ModelManyManyRelationShipInfo {
    /**
     * class owning this relationship.
     *
     * @var string
     */
    protected $owner;

    /**
     * class to which this relationship points.
     *
     * @var string
     */
    protected $target;

    /**
     * local relationship name,
     *
     * @var string
     */
    protected $relationShipName;

    /**
     * belonging relationship name.
     *
     * @var string
     */
    protected $belongingName;

    /**
     * table-name.
     *
     * @var string
     */
    protected $tableName;

    /**
     * extra-fields.
     *
     * @var array
     */
    protected $extraFields;

    /**
     * indicates if this is the controlling relationship.
     * if it is the belonging one, this is false.
     *
     * @var bool
     */
    protected $controlling;

    /**
     * constructor.
     */
    protected function __construct() {

    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return string
     */
    public function getRelationShipName()
    {
        return $this->relationShipName;
    }

    /**
     * @return string
     */
    public function getBelongingName()
    {
        return $this->belongingName;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return array
     */
    public function getExtraFields()
    {
        return $this->extraFields;
    }

    /**
     * @return boolean
     */
    public function isControlling()
    {
        return $this->controlling;
    }

    /**
     * generates information for ClassInfo.
     */
    protected function toClassInfo() {
        return array(
            "table"         => $this->tableName,
            "ef"            => $this->extraFields,
            "target"        => $this->target,
            "belonging"     => $this->belongingName,
            "isMain"        => $this->controlling
        );
    }

    /**
     * generates objects of type ManyManyRelationShipInfo from ClassInfo.
     *
     * @param string $class
     * @param array $info
     * @return array<ModelManyManyRelationShipInfo>
     */
    protected static function generateFromClassInfo($class, $info) {
        $relationShips = array();

        foreach($info as $name => $record) {
            $relationShip = new ModelManyManyRelationShipInfo();
            $relationShip->owner = $class;

            $relationShip->relationShipName = $name;
            $relationShip->belongingName = $record["belonging"];
            $relationShip->controlling = $record["isMain"];
            $relationShip->extraFields = $record["ef"];
            $relationShip->tableName = $record["table"];
            $relationShip->target = $record["target"];

            $relationShips[] = $relationShip;
        }

        return $relationShips;
    }

    /**
     * generates relationships from class.
     *
     * @param string $class
     * @return array<ModelManyManyRelationShipInfo>
     */
    protected static function generateFromClass($class) {
        $relationShips = array();

        foreach(ModelInfoGenerator::generateMany_many($class) as $name => $value) {
            $relationShop =
        }

        return $relationShips;
    }

    /**
     * generates relationship from info in array.
     *
     * @param string $name
     * @param mixed $value
     * @return ModelManyManyRelationShipInfo
     */
    protected function generateRelationShipInfo($name, $value) {
        $relationShip = new ModelManyManyRelationShipInfo();
        $relationShip->relationShipName = $name;

        $info = self::getRelationInfoWithInverse($value);
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
    public static function getRelationShipInfo($class, $key, $value, $belonging = false)
    {
        $relationInfo = self::getRelationInfoWithInverse($value);
        if ($belonging) {
            $inverse = self::findInverseManyManyRelationship($key, $class, $value, $belonging));

             return array(
                 "holder" => $inverse,
                 "belonging" => $relationInfo[0],
                 "relation" => $key,
                 "belongingRelation" => self::findInverseManyManyRelationship($key, $class, $value, $belonging));
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
    public static function findInverseManyManyRelationship($key, $class, $value, $belonging = false)
    {
        $info = self::getRelationInfoWithInverse($value);

        $relationships = ($belonging) ? ModelInfoGenerator::generateMany_many($info[0]) : ModelInfoGenerator::generateBelongs_many_many($info[0]);

        if (isset($info[1]) && isset($relationships[$info[1]])) {
            $relationInfo = $relationships[$info[1]];
            if ((!isset($relationInfo[1]) || $relationInfo[1] == $key) && ($relationInfo[0] == $class || is_subclass_of($class, $relationInfo[0]))) {
                return $info[1];
            }
        } else {
            // find relationship
            foreach ($relationships as $k => $v) {
                $relationInfo = self::getRelationInfoWithInverse($v);

                if ((!isset($relationInfo[1]) || $relationInfo[1] == $key) && ($relationInfo[0] == $class || is_subclass_of($class, $relationInfo[0]))) {
                    return $k;
                }
            }
        }

        if ($belonging) {
            throw new LogicException("No Inverse relation found for Relationship $key on $class.", ExceptionManager::RELATIONSHIP_INVERSE_REQUIRED);
        } else {
            return null;
        }

    }

    /**
     * generates many-many tables
     *
     *@name generateManyManyTables
     *@access public
     */
    public static function generateManyManyTables($class)
    {
        $class = ClassManifest::resolveClassName($class);

        $tables = array();


        // many-many
        foreach (ModelInfoGenerator
        ->
        generateMany_many(false) as $key => $value) {
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

        $table = "many_many_" . strtolower(get_class($this)) . "_" . $key . '_' . $value;
        if (!SQL::getFieldsOfTable($table)) {
            $table = "many_" . strtolower(get_class($this)) . "_" . $key;
        }

        $object = $value;
        if ($value === strtolower(get_class($this))) {
            $value = $value . "_" . $value;
        }

        $tables[$key] = array(
            "table" => $table,
            "field" => strtolower(get_class($this)) . "id",
            "extfield" => $value . "id",
            "object" => $object
        );
        if ($extraFields) {
            $tables[$key]["extraFields"] = $extraFields;
        }
        unset($key, $value);
    }

        //# belongs-many-many
        foreach (ModelInfoGenerator->generateBelongs_Many_many(false) as $key => $value) {
        $info = ModelManyManyRelationShipInfo::getRelationInfoWithInverse($value);
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
                        throw new LogicException("Relation " . $relation . " does not exist on " . $value . ".");
                    }
                } else {
                    $relation = null;
                    foreach ($relations as $r => $d) {
                        if (is_array($d) && $d["class"] == $this->classname) {
                            $relation = $r;
                            break;
                        } else if (is_string($d) && $d == $this->classname) {
                            $relation = $r;
                            break;
                        }
                    }

                    // search for inverse with parent class-names.
                    if (!isset($relation)) {
                        $c = $this->classname;
                        while ($c = ClassInfo::getParentClass($c)) {
                            foreach ($relations as $r => $d) {
                                if (is_array($d) && $d["class"] == $c) {
                                    $relation = $r;
                                    $field = $c;
                                    break 2;
                                } else if (is_string($d) && $d == $c) {
                                    $relation = $r;
                                    $field = $c;
                                    break 2;
                                }
                            }

                        }
                    }

                    if (!isset($relation)) {
                        throw new Exception("No inverse on " . $value . " found.");
                    }
                }
            } else {
                throw new LogicException("Relation " . $relation . " does not exist on " . $value . ".");
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
            $table = "many_many_" . $value . "_" . $relation . '_' . strtolower(get_class($this));
            if (!SQL::getFieldsOfTable($table))
                $table = "many_" . $value . "_" . $relation;

            if ($value === $field) {
                $field = $field . "_" . $field;
            }

            $tables[$key] = array(
                "table" => $table,
                "field" => $field . "id",
                "extfield" => $value . "id",
                "object" => $value
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
    public static function getRelationInfoWithInverse($value)
    {
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

        if(is_string($relation)) {
            $relation = strtolower($relation);
        }

        return array(ClassInfo::find_class_name($value), $relation);
    }

}