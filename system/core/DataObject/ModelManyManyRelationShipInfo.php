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
     * returns field which belongs to the owner.
     *
     * @return string
     */
    public function getOwnerField() {
        return $this->owner . "id";
    }

    /**
     * returns field which belongs to the target.
     *
     * @return string
     */
    public function getTargetField() {
        return $this->target . "id";
    }

    /**
     * returns field which belongs to the sorting of the owner.
     *
     * @return string
     */
    public function getOwnerSortField() {
        return $this->owner . "_sort";
    }

    /**
     * returns field which belongs to the sorting of the target.
     *
     * @return string
     */
    public function getTargetSortField() {
        return $this->target . "_sort";
    }

    /**
     * @return string
     */
    public function getTargetTableName()
    {
        return isset(ClassInfo::$class_info[$this->target]["table"]) ? ClassInfo::$class_info[$this->target]["table"] : null;
    }

    /**
     * generates table-name.
     *
     * @return string
     */
    protected function generateTableName() {


        if(SQL::getFieldsOfTable($this->getOldTableName())) {
            return $this->getOldTableName();
        }

        return $this->getNewTableName();

    }

    /**
     * generates new table-name style.
     *
     * @return string
     */
    protected function getNewTableName() {
        if($this->controlling) {
            return "many_" . $this->owner . "_" . $this->relationShipName;
        } else {
            return "many_" . $this->target . "_" . $this->belongingName;
        }
    }

    /**
     * generates old table-name style.
     *
     * @return string
     */
    protected function getOldTableName() {
        if($this->controlling) {
            return "many_many_" . $this->owner . "_" . $this->relationShipName . "_" . $this->target;
        } else {
            return "many_many_" . $this->target . "_" . $this->belongingName . "_" . $this->owner;
        }
    }

    /**
     * generates information for ClassInfo.
     *
     * @return array
     */
    public function toClassInfo() {
        return array(
            "table"         => $this->tableName,
            "ef"            => $this->extraFields,
            "target"        => $this->target,
            "belonging"     => $this->belongingName,
            "isMain"        => $this->controlling
        );
    }

    /**
     * returns current table-layout for this relationship.
     *
     * @return array
     */
    public function getCurrentTableLayout() {
        if(isset(ClassInfo::$database[$this->tableName])) {
            return ClassInfo::$database[$this->tableName];
        }

        return null;
    }

    /**
     * returns planned table layout for this relationship.
     *
     * @return array
     */
    public function getPlannedTableLayout() {
        $fields = array(
            "id"                        => "int(10) PRIMARY KEY auto_increment",
            $this->getTargetField()     => "int(10)",
            $this->getOwnerField()      => "int(10)",

            // sort
            $this->getOwnerSortField()  => "int(10)",
            $this->getTargetSortField() => "int(10)"
        );

        return array_merge($fields, $this->extraFields);
    }

    /**
     * returns indexes for this table-layout.
     *
     * @return array
     */
    public function getIndexes() {
        return array(
            "dataindex_reverse"	=> array(
                "name" 		=> "dataindex_reverse",
                "fields"	=> array($this->getOwnerField(), $this->getTargetField()),
                "type"		=> "UNIQUE"
            ),

            "dataindexunique"	=> array(
                "name"		=> "dataindexunique",
                "type"		=> "UNIQUE",
                "fields"	=> array($this->getTargetField(), $this->getOwnerField())
            )
        );
    }


    /**
     * gets key for ClassInfo-Array.
     *
     * @return string
     */
    protected function toClassInfoKey() {
        return $this->relationShipName;
    }

    /**
     * returns inverted relation.
     *
     * @return ModelManyManyRelationShipInfo
     */
    public function getInverted() {
        $inverted = clone $this;
        $inverted->owner = $this->target;
        $inverted->target = $this->owner;
        $inverted->controlling = !$this->controlling;
        $inverted->relationShipName = $this->belongingName;
        $inverted->belongingName = $this->relationShipName;

        return $inverted;
    }

    /**
     * generates objects of type ManyManyRelationShipInfo from ClassInfo.
     *
     * @param string $class
     * @param array $info
     * @return array<ModelManyManyRelationShipInfo>
     */
    public static function generateFromClassInfo($class, $info) {
        $relationShips = array();

        $class = ClassManifest::resolveClassName($class);

        foreach($info as $name => $record) {
            $relationShip = new ModelManyManyRelationShipInfo();
            $relationShip->owner = $class;

            $relationShip->relationShipName = $name;
            $relationShip->belongingName = $record["belonging"];
            $relationShip->controlling = $record["isMain"];
            $relationShip->extraFields = $record["ef"];
            $relationShip->tableName = $record["table"];
            $relationShip->target = $record["target"];

            $relationShips[$name] = $relationShip;
        }

        return $relationShips;
    }

    /**
     * generates relationships from class.
     *
     * @param string $class
     * @param bool $parents
     * @return array <ModelManyManyRelationShipInfo>
     */
    public static function generateFromClass($class, $parents = false) {

        $class = ClassManifest::resolveClassName($class);

        $relationShips = array();

        foreach(ModelInfoGenerator::generateMany_many($class, false) as $name => $value) {
            $relationShips[$name] = self::generateRelationShipInfo($class, $name, $value, false);
        }

        foreach(ModelInfoGenerator::generateBelongs_many_many($class, false) as $name => $value) {
            $relationShips[$name] = self::generateRelationShipInfo($class, $name, $value, true);
        }

        if($parents !== false) {
            $parentClass = ClassInfo::get_parent_class($class);
            while($parentClass != null && !ClassInfo::isAbstract($parentClass)) {
                $relationShips = array_merge(self::generateFromClass($parentClass, true), $relationShips);
                $parentClass = ClassInfo::get_parent_class($parentClass);
            }
        }

        return $relationShips;
    }

    /**
     * generates relationship from info in array.
     *
     * @param string $class
     * @param string $name
     * @param mixed $value
     * @param bool $belonging
     * @return ModelManyManyRelationShipInfo
     */
    protected static function generateRelationShipInfo($class, $name, $value, $belonging) {
        $relationShip = new ModelManyManyRelationShipInfo();
        $relationShip->relationShipName = $name;
        $relationShip->owner = $class;

        $info = self::getRelationInfoWithInverse($value);

        $relationShip->target = $info[0];
        $relationShip->belongingName = self::findInverseManyManyRelationship($name, $class, $info, $belonging);

        $relationShip->extraFields = ModelInfoGenerator::get_many_many_extraFields($class, $name);

        $relationShip->controlling = !$belonging;

        $relationShip->tableName = $relationShip->generateTableName();

        return $relationShip;
    }

    /**
     * searches for inverse relationships on other class.
     *
     * @param string $relationName of this relationship
     * @param string $class name of class which holds relationship
     * @param array $info from getRelationInfoWithInverse
     * @param bool $belonging if class stores it in belongs or normal many-many
     * @return string
     */
    public static function findInverseManyManyRelationship($relationName, $class, $info, $belonging = false)
    {
        $relationships = ($belonging) ? ModelInfoGenerator::generateMany_many($info[0]) : ModelInfoGenerator::generateBelongs_many_many($info[0]);
        $info[0] = strtolower($info[0]);

        // if inverse is set in value of relationship, just validate inverse
        if (isset($info[1])) {
            if(isset($relationships[$info[1]])) {
                $relationInfo = self::getRelationInfoWithInverse($relationships[$info[1]]);
                if (self::isInverseValid($relationInfo, $relationName, $class)) {
                    return $info[1];
                }
            } else {
                throw new LogicException("Defined Inverse-Relationship {$info[1]} not found on class {$info[0]} defined in class $class relationship $relationName");
            }
        } else {
            // find relationship on other class
            foreach ($relationships as $name => $relationValue) {
                $relationInfo = self::getRelationInfoWithInverse($relationValue);

                // validate relation
                if (self::isInverseValid($relationInfo, $relationName, $class)) {
                    return $name;
                }
            }
        }

        if ($belonging) {
            throw new LogicException("No Inverse relation found for Relationship for $relationName in class $class. We search in class " . $info[0], ExceptionManager::RELATIONSHIP_INVERSE_REQUIRED);
        } else {
            return null;
        }

    }

    /**
     * returns true when inverse for relationship is valid.
     *
     * @param array $relationInfo
     * @param string $relationName
     * @param string $class
     * @return bool
     */
    protected static function isInverseValid($relationInfo, $relationName, $class) {
        return  (!isset($relationInfo[1]) || $relationInfo[1] == strtolower($relationName)) &&
        (ClassManifest::isSameClass($relationInfo[0], $class) || is_subclass_of($class, $relationInfo[0]));
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
                $relation = isset($value[1]) ? $value[1] : null;
                $value = $value[0];
            }
        }

        if(is_string($relation)) {
            $relation = strtolower($relation);
        }

        return array(ClassInfo::find_class_name($value), $relation);
    }

}