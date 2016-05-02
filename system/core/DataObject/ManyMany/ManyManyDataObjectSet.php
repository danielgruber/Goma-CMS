<?php defined("IN_GOMA") OR die();

/**
 * for many-many-relation
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
 */
class ManyMany_DataObjectSet extends RemoveStagingDataObjectSet {

    const MANIPULATION_DELETE_SPECIFIC = "many_many_deleterecords";
    const MANIPULATION_DELETE_EXISTING = "many_many_deleteexisting";
    const MANIPULATION_INSERT_NEW = "many_many_insertnew";

    /**
     * value of $ownField
     *
     * @var DataObject
     */
    protected $ownRecord;

    /**
     * relationship for this DataSet.
     *
     * @var ModelManyManyRelationShipInfo
     */
    protected $relationShip;

    /**
     * current active data-set.
     * used to give possibility to override table.
     */
    protected $manyManyData;

    /**
     * indicates which version of data-source should be used.
     *
     * @var string
     */
    protected $dataSourceVersion;

    /**
     * update extra fields stage.
     */
    protected $updateExtraFieldsStage;

    /**
     * ManyMany_DataObjectSet constructor.
     * @param array|IDataObjectSetDataSource|IDataObjectSetModelSource|null|string $class
     * @param array|null|string $filter
     * @param array|null|string $sort
     * @param array|int|null $limit
     * @param array|null $join
     * @param array|null|string $search
     * @param null|string $version
     */
    public function __construct($class = null, $filter = null, $sort = null, $limit = null, $join = null, $search = null, $version = null)
    {
        parent::__construct($class, $filter, $sort, $limit, $join, $search, $version);

        $this->updateExtraFieldsStage = new ArrayList();
    }

    /**
     * sets the relation-props
     *
     * @param ModelManyManyRelationShipInfo $relationShip
     * @param DataObject $ownRecord
     */
    public function setRelationENV($relationShip, $ownRecord) {
        if(!is_a($relationShip, "ModelManyManyRelationShipInfo")) {
            throw new InvalidArgumentException("Relationship-Info must be type of ModelManyManyRelationShipInfo");
        }

        $this->relationShip = $relationShip;
        $this->ownRecord = $ownRecord;
        $this->dataSourceVersion = $relationShip->getSourceVersion();
    }

    /**
     * sets source data.
     * @param array $data
     */
    public function setSourceData($data) {
        if(!is_array($data))
            throw new InvalidArgumentException("Source-Data of ManyManySet must be type of array.");

        $this->manyManyData = array();
        foreach($data as $possibleId => $recordData) {
            if(is_array($recordData)) {
                $this->manyManyData[$possibleId] = $recordData;
            } else {
                $this->manyManyData[$recordData] = array();
            }
        }

        $this->fetchMode = self::FETCH_MODE_EDIT;

        $this->clearCache();
    }

    /**
     * set source database.
     */
    public function setSourceDB() {
        $this->manyManyData = null;
        $this->fetchMode = self::FETCH_MODE_EDIT;

        $this->clearCache();
    }

    /**
     * @param string $mode
     */
    public function setVersionMode($mode) {
        if($mode === null || $mode == DataObject::VERSION_MODE_CURRENT_VERSION || $mode == DataObject::VERSION_MODE_LATEST_VERSION) {
            $this->dataSourceVersion = $mode;

            $this->clearCache();
        } else {
            throw new InvalidArgumentException("Invalid version mode.");
        }
    }

    /**
     * @return DataObject
     */
    public function getOwnRecord()
    {
        return $this->ownRecord;
    }

    /**
     * attention this is not used to give you access to current dataset, but source of this set.
     * this can be null.
     * @return null|array
     */
    public function getManyManySourceData()
    {
        return $this->manyManyData;
    }

    /**
     * @return string
     */
    public function getDataSourceVersion()
    {
        return $this->dataSourceVersion;
    }

    /**
     * get the relation-props
     *
     * @return ModelManyManyRelationShipInfo
     */
    public function getRelationShip() {
        return $this->relationShip;
    }

    /**
     * returns value of field for this relationship.
     *
     * @return int
     */
    public function getRelationOwnValue() {
        return $this->ownRecord->versionid;
    }

    /**
     * @return mixed
     */
    public function getUpdateExtraFieldsStage()
    {
        return $this->updateExtraFieldsStage;
    }

    /**
     * returns current relationship ids.
     */
    public function getRelationshipIDs() {
        if(isset($this->manyManyData)) {
            return array_keys($this->manyManyData);
        }

        $query = $this->getManyManyQuery(array($this->relationShip->getTargetField()));
        $query->execute();

        $ids = array();
        while($row = $query->fetch_assoc()) {
            $ids[] = $row[$this->relationShip->getTargetField()];
        }

        /** @var DataObject $record */
        foreach($this->staging as $record) {
            $ids[] = $record->versionid;
        }

        return $ids;
    }

    /**
     * @param null $oldId
     * @return array
     * @throws SQLException
     */
    protected function getRelationshipDataFromDB($oldId = null) {
        if(isset($this->manyManyData)) {
            return $this->manyManyData;
        }

        $query = $this->getManyManyQuery(array("*", "recordid"), $oldId);
        $query->execute();

        $arr = array();
        while($row = $query->fetch_assoc()) {
            $id = $row[$this->relationShip->getTargetField()];
            $arr[$id] = array(
                "versionid"                             => $id,
                "relationShipId"                        => $row["relationid"],
                $this->relationShip->getOwnerField()    => $row[$this->relationShip->getOwnerField()]
            );

            $arr[$id][$this->relationShip->getOwnerSortField()] = $row[$this->relationShip->getOwnerSortField()];
            $arr[$id][$this->relationShip->getTargetSortField()] = $row[$this->relationShip->getTargetSortField()];

            if($updateObject = $this->updateExtraFieldsStage->find("id", $row["recordid"])) {
                $updateRecord = $updateObject->toArray();
            }

            foreach ($this->relationShip->getExtraFields() as $field => $pattern) {
                if(isset($updateRecord)) {
                    $arr[$id][$field] = isset($updateRecord[$field]) ? $updateRecord[$field] : $row[$field];
                } else {
                    $arr[$id][$field] = $row[$field];
                }
            }
        }

        return $arr;
    }

    /**
     * returns current relationship data.
     */
    public function getRelationshipData() {
        $arr = $this->getRelationshipDataFromDB();

        /** @var DataObject $record */
        foreach($this->staging as $record) {
            $id = $record->versionid;
            $arr[$id] = array(
                "versionid"                             => $id,
                "relationShipId"                        => 0,
                $this->relationShip->getOwnerField()    => $this->ownRecord->versionid
            );

            $arr[$id][$this->relationShip->getOwnerSortField()] = count($arr);
            $arr[$id][$this->relationShip->getTargetSortField()] = count($arr);

            foreach ($this->relationShip->getExtraFields() as $field => $pattern) {
                $arr[$id][$field] = $record->{$field};
            }
        }

        return $arr;
    }

    /**
     * @param array $fields
     * @param null $oldId
     * @return SelectQuery
     * @throws SQLException
     */
    protected function getManyManyQuery($fields, $oldId = null) {
        if(!$this->relationShip->getTargetBaseTableName()) {
            throw new LogicException("Target-Relationship needs at least basetable.");
        }

        $recordIdQuerySQL = $this->getRecordIdQuery($oldId)->build("distinct recordid");

        $versionId = $this->dataSourceVersion != DataObject::VERSION_MODE_CURRENT_VERSION ?
            ($this->queryVersion() == DataObject::VERSION_STATE ? $this->ownRecord->stateid : $this->ownRecord->publishedid) :
            ($oldId != null ? $oldId : $this->ownRecord->versionid);

        $baseTable = $this->relationShip->getTargetBaseTableName();

        $query = new SelectQuery($baseTable, $fields, "recordid IN (".$recordIdQuerySQL.")");
        $query->db_fields["relationid"] = array($this->relationShip->getTableName(), "id");

        // filter for not existing records
        $query->from[$this->relationShip->getTableName()] = ' LEFT JOIN ' . DB_PREFIX . $this->relationShip->getTableName() . ' AS '. $this->relationShip->getTableName() .
            ' ON ' . $baseTable . '.id = '. $this->relationShip->getTableName() .'.' . $this->relationShip->getTargetField() .
            ' AND ' . $this->relationShip->getTableName().'.' . $this->relationShip->getOwnerField() . ' = \'' . $versionId . '\'';

        $query->from[] = " INNER JOIN " . DB_PREFIX . $baseTable . "_state AS {$baseTable}_state ON {$baseTable}_state.publishedid = {$baseTable}.id";

        $query->sort($this->getManyManySort());

        return $query;
    }

    /**
     * returns recorid-query.
     * @param null $oldId
     * @return SelectQuery
     */
    protected function getRecordIdQuery($oldId = null) {
        if(!$this->relationShip->getTargetBaseTableName()) {
            throw new LogicException("Target-Relationship needs at least basetable.");
        }

        $versionId = $this->dataSourceVersion != DataObject::VERSION_MODE_CURRENT_VERSION ?
            ($this->queryVersion() == DataObject::VERSION_STATE ? $this->ownRecord->stateid : $this->ownRecord->publishedid) :
            ($oldId != null ? $oldId : $this->ownRecord->versionid);

        $recordIdQuery = new SelectQuery($this->relationShip->getTargetBaseTableName(), array());
        $recordIdQuery->innerJoin($this->relationShip->getTableName(), " {$this->relationShip->getTableName()}.{$this->relationShip->getTargetField()} =" .
            "{$this->relationShip->getTargetBaseTableName()}.id AND {$this->relationShip->getOwnerField()} = '{$versionId}'");

        if (ClassManifest::isSameClass($this->relationShip->getTargetClass(), $this->ownRecord->DataClass()) ||
            is_subclass_of($this->relationShip->getTargetClass(), $this->ownRecord->DataClass()) ||
            is_subclass_of($this->ownRecord->DataClass(), $this->relationShip->getTargetClass())
        ) {
            $recordIdQuery->addFilter("recordid != '".$this->ownRecord->id."'");
        }

        if($excludedRecords = array_merge($this->staging->fieldToArray("id"), $this->removeStaging->fieldToArray("id"))) {
            $recordIdQuery->addFilter(" recordid NOT IN ('" . implode("','", $excludedRecords) . "') ");
        }

        return $recordIdQuery;
    }

    /**
     * returns many-many-sort.
     * @param array|null $sort
     * @return string
     */
    protected function getManyManySort($sort = null) {
        if(!isset($sort) || !$sort) {
            $name = $this->relationShip->getRelationShipName();
            $sorts = ArrayLib::map_key("strtolower", StaticsManager::getStatic($this->getOwnRecord()->DataClass(), "many_many_sort"));
            if(isset($sorts[$name]) && $sorts[$name]) {
                return $sorts[$name];
            } else {
                return $this->relationShip->getTableName() . ".".$this->relationShip->getOwnerSortField()." ASC , " .
                $this->relationShip->getTableName() . ".id ASC";
            }
        }

        return $sort;
    }

    /**
     * converts the item to the right format
     *
     * @param DataObject $item
     * @return DataObject
     */
    public function getConverted($item) {
        /** @var DataObject $item */
        $item = parent::getConverted($item);

        if(isset($this->relationShip)) {
            $item->extendedCasting = array_merge($item->extendedCasting, $this->relationShip->getExtraFields());
        }

        if(isset($this->manyManyData) && isset($this->manyManyData[$item->versionid])) {
            foreach($this->manyManyData[$item->versionid] as $key => $data) {
                $item->setField($key, $data);
            }
        }

        return $item;
    }

    /**
     * updates extra fields for record.
     *
     * @param DataObject $record
     */
    public function updateExtraFields($record) {
        if($toRemove = $this->updateExtraFieldsStage->find("versionid", $record->versionid)) {
            $this->updateExtraFieldsStage->remove($toRemove);
        }

        $this->updateExtraFieldsStage->add($record);
    }

    /**
     * removes record from update extra fields stage.
     * @param DataObject $record
     */
    public function removeFromUpdateExtraFields($record) {
        if($toRemove = $this->updateExtraFieldsStage->find("versionid", $record->versionid)) {
            $this->updateExtraFieldsStage->remove($toRemove);
        }
    }

    /**
     * write to DB
     *
     * @param bool $forceInsert to force insert
     * @param bool $forceWrite to force write
     * @param int $snap_priority of the snapshop: autosave 0, save 1, publish 2
     * @param null|IModelRepository $repository
     * @param null $oldId
     * @throws MySQLException
     */
    public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2, $repository = null, $oldId = null) {
        $manipulation = array();
        $sort = 0;
        $addedRecords = array();

        if($this->fetchMode == self::FETCH_MODE_CREATE_NEW) {
            $manipulation[self::MANIPULATION_DELETE_EXISTING] = array(
                "command"		=> "delete",
                "table_name"	=> $this->relationShip->getTableName(),
                "where"			=> array(
                    $this->relationShip->getOwnerField() => $this->ownRecord->versionid
                )
            );
        } else {
            if($this->ownRecord->versionid != $this->ownRecord->publishedid || $oldId || $this->manyManyData || $this->updateExtraFieldsStage->count() > 0) {
                $relationData = $this->getRelationshipDataFromDB($oldId);

                $manipulation[self::MANIPULATION_DELETE_EXISTING] = array(
                    "command"		=> "delete",
                    "table_name"	=> $this->relationShip->getTableName(),
                    "where"			=> array(
                        $this->relationShip->getOwnerField() => $this->ownRecord->versionid
                    )
                );

                if(!empty($relationData)) {
                    $manipulation[self::MANIPULATION_INSERT_NEW] = array(
                        "command"       => "insert",
                        "table_name"	=> $this->relationShip->getTableName(),
                        "fields"        => array()
                    );

                    foreach ($relationData as $id => $record) {
                        $newRecord = array(
                            $this->relationShip->getOwnerField()        => $this->ownRecord->versionid,
                            $this->relationShip->getTargetField()       => $id,
                            $this->relationShip->getTargetSortField()   => isset($record[$this->relationShip->getTargetSortField()]) ?
                                $record[$this->relationShip->getTargetSortField()] : 0,
                            $this->relationShip->getOwnerSortField()    => $sort
                        );

                        foreach($this->relationShip->getExtraFields() as $field => $type) {
                            $newRecord[$field] = isset($record[$field]) ? $record[$field] : "";
                        }

                        $manipulation[self::MANIPULATION_INSERT_NEW]["fields"][] = $newRecord;

                        $addedRecords[$id] = false;
                        $sort++;
                    }
                }
            }
        }

        $copyOfAddStage = $this->staging->ToArray();
        parent::commitStaging($forceInsert, $forceWrite, $snap_priority, $repository);

        /** @var DataObject $record */
        foreach($copyOfAddStage as $record) {
            $newRecord = array(
                $this->relationShip->getOwnerField()        => $this->ownRecord->versionid,
                $this->relationShip->getTargetField()       => $record->versionid,
                $this->relationShip->getTargetSortField()   => isset($record->{$this->relationShip->getTargetSortField()}) ?
                    $record->{$this->relationShip->getTargetSortField()} : 0,
                $this->relationShip->getOwnerSortField()    => $sort
            );

            foreach($this->relationShip->getExtraFields() as $field => $type) {
                $newRecord[$field] = isset($record->{$field}) ? $record->{$field} : "";
            }

            if(!isset($manipulation[self::MANIPULATION_INSERT_NEW])) {
                $manipulation[self::MANIPULATION_INSERT_NEW] = array(
                    "command"       => "insert",
                    "table_name"	=> $this->relationShip->getTableName(),
                    "fields"        => array()
                );
            }

            $manipulation[self::MANIPULATION_INSERT_NEW]["fields"][] = $newRecord;

            $addedRecords[$record->versionid] = true;
            $sort++;
        }

        // update not written records to indicate changes
        $baseClassTarget = ClassInfo::$class_info[$this->relationShip->getTargetClass()]["baseclass"];
        DataObject::update($baseClassTarget, array("last_modified" => NOW),
            array(
                "id" => array_keys(
                    array_filter($addedRecords,
                        function($item){
                            return !$item;
                        }
                    )
                )
            )
        );

        $this->dbDataSource()->onBeforeManipulateManyMany($manipulation, $this, $addedRecords);
        $this->modelSource()->callExtending("onBeforeManipulateManyMany", $manipulation, $this, $addedRecords);
        if(!$this->dbDataSource()->manipulate($manipulation)) {
            throw new LogicException("Could not manipulate Database. Manipulation corrupted. <pre>" . print_r($manipulation, true) . "</pre>");
        }
    }

    /**
     * @return array
     */
    public function getSortForQuery()
    {
        $sort = parent::getSortForQuery();
        if(isset($this->manyManyData)) {
            if($sort) {
                return array_merge((array) $sort, array("versionid", array_keys($this->manyManyData)));
            } else {
                return array(array("versionid", array_keys($this->manyManyData)));
            }
        }

        return $sort;
    }

    /**
     *
     */
    public function getFilterForQuery()
    {
        $filter = (array) parent::getFilterForQuery();

        if(isset($this->manyManyData)) {
            $filter[] = $this->relationShip->getTargetBaseTableName() . ".id IN ('".implode("','", array_keys($this->manyManyData))."') ";
        } else {
            $filter[] = " recordid IN (".$this->getRecordIdQuery()->build("distinct recordid").") ";
        }

        return $filter;
    }

    /**
     * joins stuff.
     * @return array
     */
    public function getJoinForQuery()
    {
        $join = parent::getJoinForQuery();

        $relationTable = $this->relationShip->getTableName();
        // search second join
        foreach((array) $join as $table => $data) {
            if(strpos($data, $relationTable)) {
                unset($join[$table]);
            }
        }

        $versionId = $this->dataSourceVersion != DataObject::VERSION_MODE_CURRENT_VERSION ?
            ($this->queryVersion() == DataObject::VERSION_STATE ? $this->ownRecord->stateid : $this->ownRecord->publishedid) :
            $this->ownRecord->versionid;

        $join[$relationTable] = " INNER JOIN " . DB_PREFIX . $relationTable . " AS " .
            $relationTable . " ON " . $relationTable . "." . $this->relationShip->getTargetField() . " = " . $this->dbDataSource()->table() . ".id AND " .
            $relationTable . "." . $this->relationShip->getOwnerField() . " = '" . $versionId . "'";

        return $join;
    }

    /**
     * @param null|IModelRepository $repository
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param IModelRepository $repository
     * @param bool $asReturn
     * @return mixed
     * @throws SQLException
     */
    public function commitRemoveStaging($repository, $forceWrite = false, $snap_priority = 2, $repository = null, $asReturn = false)
    {
        $versionQuery = new SelectQuery($this->relationShip->getTargetBaseTableName(), array("id"), array(
            "recordid" => $this->removeStaging->fieldToArray("recordid")
        ));

        $manipulation[self::MANIPULATION_DELETE_SPECIFIC] = array(
            "command"		=> "delete",
            "table_name"	=> $this->relationShip->getTableName(),
            "where"			=> " {$this->relationShip->getTargetField()} IN (".$versionQuery->build().") ");

        if($asReturn) {
            return $asReturn;
        } else {
            $insertedRelationships = array();
            $this->dbDataSource()->onBeforeManipulateManyMany($manipulation, $this, $insertedRelationships);
            $this->modelSource()->callExtending("onBeforeManipulateManyMany", $manipulation, $this, $insertedRelationships);
            if(!$this->dbDataSource()->manipulate($manipulation)) {
                throw new LogicException("Could not manipulate Database. Manipulation corrupted. <pre>" . print_r($manipulation, true) . "</pre>");
            }

            $this->dbDataSource()->clearCache();
        }
    }

    /**
     * @param array|string $filter
     * @return array
     */
    protected function argumentFilterForHidingRemovedStageForQuery($filter)
    {

    }
}
