<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ModelManyManyRelationShipInfo extends ModelRelationShipInfo {

    /**
     * local relationship name,
     *
     * @var string
     */
    protected $relationShipName;

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
     * source-version.
     * latest or current.
     * @var string
     */
    protected $sourceVersion;

    /**
     * ModelManyManyRelationShipInfo constructor.
     * @param string $ownerClass
     * @param string $name
     * @param array|string $options
     * @param bool $isMain
     */
    public function __construct($ownerClass, $name, $options, $isMain)
    {
        $options = $this->parseOptionsForLegacySupport($options);

        $this->controlling = !!$isMain;

        parent::__construct($ownerClass, $name, $options);

        $this->sourceVersion = isset($options[DataObject::MANY_MANY_VERSION_MODE]) ? $options[DataObject::MANY_MANY_VERSION_MODE] : DataObject::VERSION_MODE_LATEST_VERSION;

        if(isset($options["ef"])) {
            $this->extraFields = $options["ef"];
        } else {
            $this->extraFields = ModelInfoGenerator::get_many_many_extraFields($this->owner, $name);
            $belongingExtra = ModelInfoGenerator::get_many_many_extraFields($this->targetClass, $this->inverse);
            foreach ($belongingExtra as $key => $value) {
                if (isset($this->extraFields[$key]) && $this->extraFields[$key] != $value) {
                    throw new InvalidArgumentException("Multiple definitions of same Extra-Field in relationship-pairs in {$this->relationShipName} on class {$this->owner}.");
                }

                $this->extraFields[$key] = $value;
            }
        }

        if(isset($options["table"])) {
            $this->tableName = $options["table"];
        } else {
            $this->tableName = $this->generateTableName();
        }
    }

    /**
     * parses options for legacy support.
     *
     * @param string|array $options
     * @return array
     */
    protected function parseOptionsForLegacySupport($options) {
        if(is_string($options)) {
            $options = array(
                DataObject::RELATION_TARGET => $options
            );
        }

        if(is_array($options) && count($options) == 2 && !isset($options[DataObject::CASCADE_TYPE]) && !isset($options[DataObject::FETCH_TYPE])) {
            Core::Deprecate("2.0", "Use Constants instead of 2 count array for ManyMany-inverse.");
            $options = array_values($options);
            $options = array(
                DataObject::RELATION_TARGET => $options[0],
                DataObject::RELATION_INVERSE => $options[1]
            );
        }

        if(isset($options["belonging"])) {
            $options["inverse"] = $options["belonging"];
        }

        return $options;
    }

    /**
     * @return mixed
     */
    protected function validateAndForceInverse()
    {
        if(!ClassInfo::exists($this->targetClass)) {
            throw new InvalidArgumentException("Target {$this->targetClass} must exist.");
        }

        $relationships = (!$this->isControlling()) ?
            ModelInfoGenerator::generateMany_many($this->targetClass, true) :
            ModelInfoGenerator::generateBelongs_many_many($this->targetClass, true);

        if(isset($this->inverse)) {
            if(!isset($relationships[$this->inverse])) {
                throw new InvalidArgumentException("Defined Inverse-Relationship {$this->inverse} not found on class {$this->targetClass} defined in class {$this->owner} relationship {$this->relationShipName}");
            }
        } else {
            $this->inverse = $this->findInverseRelationshipsWithoutHint($relationships, $this->relationShipName, $this->owner);

            if(!$this->controlling && !$this->inverse) {
                throw new InvalidArgumentException("No Inverse relationship for Relationship for {$this->relationShipName} found in class {$this->targetClass}. Base-Class " . $this->owner, ExceptionManager::RELATIONSHIP_INVERSE_REQUIRED);
            }
        }
    }

    /**
     * @return string
     */
    public function getSourceVersion()
    {
        return $this->sourceVersion;
    }

    /**
     * @return string
     */
    public function getBelongingName()
    {
        return $this->inverse;
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
        return ClassManifest::classesRelated($this->owner, $this->targetClass);
    }

    /**
     * returns field which belongs to the owner.
     *
     * @return string
     */
    public function getOwnerField() {
        if(!$this->controlling && ClassManifest::classesRelated($this->owner, $this->targetClass)) {
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
        if($this->controlling && ClassManifest::classesRelated($this->owner, $this->targetClass)) {
            return $this->targetClass . "_" . $this->targetClass . "id";
        }

        return $this->targetClass . "id";
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
        return $this->targetClass . "_sort";
    }

    /**
     * @return string
     */
    public function getTargetTableName()
    {
        return isset(ClassInfo::$class_info[$this->targetClass]["table"]) ? ClassInfo::$class_info[$this->targetClass]["table"] : null;
    }

    /**
     * @return string
     */
    public function getTargetBaseTableName()
    {
        if(!isset(ClassInfo::$class_info[$this->getTargetClass()]["baseclass"])) {
            throw new LogicException("Target Relationship seems not to have valid ClassInfo.");
        }

        return isset(ClassInfo::$class_info[ClassInfo::$class_info[$this->getTargetClass()]["baseclass"]]["table"]) ?
            ClassInfo::$class_info[ClassInfo::$class_info[$this->getTargetClass()]["baseclass"]]["table"] : null;
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
            return "many_" . $this->targetClass . "_" . $this->inverse;
        }
    }

    /**
     * generates old table-name style.
     *
     * @return string
     */
    protected function getOldTableName() {
        if($this->controlling) {
            return "many_many_" . $this->owner . "_" . $this->relationShipName . "_" . $this->targetClass;
        } else {
            return "many_many_" . $this->targetClass . "_" . $this->inverse . "_" . $this->owner;
        }
    }

    /**
     * generates information for ClassInfo.
     *
     * @return array
     */
    public function toClassInfo()
    {
        return array(
            "table"                            => $this->tableName,
            "ef"                               => $this->extraFields,
            DataObject::RELATION_TARGET        => $this->targetClass,
            DataObject::RELATION_INVERSE       => $this->inverse,
            DataObject::MANY_MANY_VERSION_MODE => $this->sourceVersion,
            "isMain"                           => $this->controlling,
            "validatedInverse"                 => true
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
        $inverted->owner = $this->targetClass;
        $inverted->targetClass = $this->owner;
        $inverted->controlling = !$this->controlling;
        $inverted->relationShipName = $this->inverse;
        $inverted->inverse = $this->relationShipName;

        return $inverted;
    }

    /**
     * generates objects of type ManyManyRelationShipInfo from ClassInfo.
     *
     * @param string $class
     * @param array $info
     * @return ModelManyManyRelationShipInfo[]
     */
    public static function generateFromClassInfo($class, $info) {
        $relationShips = array();

        $class = ClassManifest::resolveClassName($class);

        foreach($info as $name => $record) {
            $relationShip = new ModelManyManyRelationShipInfo($class, $name, $record, $record["isMain"]);

            $relationShips[$name] = $relationShip;
        }

        return $relationShips;
    }

    /**
     * generates relationships from class.
     *
     * @param string $class
     * @param bool $parents
     * @return ModelManyManyRelationShipInfo[]
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
        $relationShip = new ModelManyManyRelationShipInfo($class, $name, $value, !$belonging);

        return $relationShip;
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
            } else if(isset($value[DataObject::RELATION_INVERSE])) {
                return strtolower($value[DataObject::RELATION_INVERSE]);
            } else if(count($value) == 2 && !isset($value[DataObject::CASCADE_TYPE]) && !isset($value[DataObject::FETCH_TYPE])) {
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
            if(isset($value[DataObject::RELATION_TARGET])) {
                return $value[DataObject::RELATION_TARGET];
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
    public function getPlannedTableLayout()
    {
        $fields = array(
            "id"                          => "int(10) PRIMARY KEY auto_increment",

            // versionid
            $this->getTargetField()       => "int(10)",
            $this->getOwnerField()        => "int(10)",

            // sort
            $this->getOwnerSortField()    => "int(10)",
            $this->getTargetSortField()   => "int(10)"
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
