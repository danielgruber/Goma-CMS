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
     * a relationship is always bidirectional. this indicator means that you are using the same object.
     *
     * @return boolean
     */
    public function isBidirectional()
    {
        return ClassManifest::classesRelated($this->owner, $this->target);
    }

    /**
     * returns field which belongs to the owner.
     *
     * @return string
     */
    public function getOwnerField() {
        if(!$this->controlling && ClassManifest::classesRelated($this->owner, $this->target)) {
            return $this->owner . "_" . $this->owner . "id";
        }

        return $this->owner . "id";
    }

    /**
     * returns field which belongs to the target.
     *
     * @return string
     */
    public function getTargetField() {
        if($this->controlling && ClassManifest::classesRelated($this->owner, $this->target)) {
            return $this->target . "_" . $this->target . "id";
        }

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
     * @param string $owner
     */
    protected function setOwner($owner)
    {
        if($owner) {
            $this->owner = $owner;
        } else if(!$this->owner) {
            throw new LogicException("ModelManyManyRelationShipInfo requires an owner. Owner can't be null.");
        }
    }

    /**
     * @param string $target
     */
    protected function setTarget($target)
    {
        if($target) {
            $this->target = $target;
        } else if(!$this->target) {
            throw new LogicException("ModelManyManyRelationShipInfo requires a target. target can't be null.");
        }
    }

    /**
     * @param string $relationShipName
     */
    protected function setRelationShipName($relationShipName)
    {
        if($relationShipName) {
            $this->relationShipName = $relationShipName;
        } else if(!$this->relationShipName) {
            throw new LogicException("ModelManyManyRelationShipInfo requires a relationship-name. name can't be null.");
        }

    }

    /**
     * @param string $belongingName
     */
    protected function setBelongingName($belongingName)
    {
        $this->belongingName = $belongingName;
    }

    /**
     * @param string $tableName
     */
    protected function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param array $extraFields
     */
    protected function setExtraFields($extraFields)
    {
        if($extraFields) {
            $this->extraFields = (array) $extraFields;
        } else if(!$this->extraFields) {
            $this->extraFields = array();
        }
    }

    /**
     * @param boolean $controlling
     */
    protected function setControlling($controlling)
    {
        $this->controlling = $controlling;
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
            $relationShip->setOwner($class);

            $relationShip->setRelationShipName($name);
            $relationShip->setBelongingName($record["belonging"]);
            $relationShip->setControlling($record["isMain"]);
            $relationShip->setExtraFields($record["ef"]);
            $relationShip->setTableName($record["table"]);
            $relationShip->setTarget($record["target"]);

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
        $belongingExtra = ModelInfoGenerator::get_many_many_extraFields($relationShip->target, $relationShip->belongingName);
        foreach($belongingExtra as $key => $value) {
            if(isset($relationShip->extraFields[$key]) && $relationShip->extraFields[$key] != $value) {
                throw new LogicException("Extra-Fields should not be different on belonging relationship.");
            }

            $relationShip->extraFields[$key] = $value;
        }

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
        $relationships = ($belonging) ? ModelInfoGenerator::generateMany_many($info[0], true) : ModelInfoGenerator::generateBelongs_many_many($info[0], true);

        // if inverse is set in value of relationship, just validate inverse
        if (isset($info[1])) {
            $inverseName = strtolower(trim($info[1]));
            if(isset($relationships[$inverseName])) {
                if (self::isInverseValid($relationships[$inverseName], $relationName, $class)) {
                    return $inverseName;
                }
            } else {
                throw new LogicException("Defined Inverse-Relationship {$inverseName} not found on class {$info[0]} defined in class $class relationship $relationName");
            }
        } else {
            // find relationship on other class
            return self::findInverseRelationshipsWithoutHint($relationships, $relationName, $class);
        }

        if ($belonging) {
            throw new LogicException("No Inverse relationship for Relationship for $relationName found in class $class. Searched in class " . $info[0], ExceptionManager::RELATIONSHIP_INVERSE_REQUIRED);
        } else {
            return null;
        }

    }

    /**
     * checks other classes relationships if there is an inverse or more than one.
     * it throws an exception when there are more than one inverse relationships possible.
     *
     * @param array $relationships
     * @param String $relationName
     * @param String $class
     * @return String
     */
    public static function findInverseRelationshipsWithoutHint($relationships, $relationName, $class) {
        /** @var String $possibleRelationship */

        $possibleRelationship = null;
        foreach ($relationships as $name => $relationValue) {
            // validate relation
            if (self::isInverseValid($relationValue, $relationName, $class)) {
                if(isset($possibleRelationship)) {
                    throw new LogicException("There is more than one possible inverse relationship for $relationName. Please add inverse to definition.");
                }
                $possibleRelationship = $name;
            }
        }

        return $possibleRelationship;
    }

    /**
     * returns true when inverse for relationship is valid.
     *
     * @param array|string $value
     * @param string $relationName
     * @param string $class
     * @return bool
     */
    protected static function isInverseValid($value, $relationName, $class) {

        $relationInfo = self::getRelationInfoWithInverse($value);

        return  (!isset($relationInfo[1]) || $relationInfo[1] == strtolower($relationName)) &&
        (ClassManifest::isSameClass($relationInfo[0], $class));
    }

    /**
     * tries to extract inverse from relationship-info.
     *
     * @param string|array $value value of many-many-relatipnship.
     * @return array first value if class and second inverse
     */
    public static function getRelationInfoWithInverse($value)
    {
        return array(
            ClassInfo::find_class_name(self::getRelationClass($value)),
            self::getRelationInverse($value)
        );
    }

    /**
     * returns inverse relationship name when set.
     *
     * @param array|string $value
     * @return string
     */
    protected static function getRelationInverse($value) {
        if(is_array($value)) {
            if(isset($value["relation"])) {
                return strtolower($value["relation"]);
            } else if(isset($value["inverse"])) {
                return strtolower($value["inverse"]);
            } else if(count($value) == 2) {
                $arr = array_values($value);
                if(isset($arr[1]) && is_string($arr[1])) {
                    return $arr[1];
                }
            }
        }

        return null;
    }

    /**
     * gets info about class from array.
     *
     * @param array|string $value
     * @return string
     */
    protected static function getRelationClass($value) {
        if(is_array($value)) {
            if(isset($value["class"])) {
                return $value["class"];
            }

            $array = array_values($value);
            return $array[0];
        } else {
            return $value;
        }
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

}