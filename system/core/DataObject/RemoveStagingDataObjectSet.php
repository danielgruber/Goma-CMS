<?php
defined("IN_GOMA") OR die();

/**
 * This is a DataObjectSet which supports Staging for Deletion.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
abstract class RemoveStagingDataObjectSet extends DataObjectSet {
    /**
     * remove staging ArrayList.
     *
     * @var ArrayList
     */
    protected $removeStaging;

    /**
     * RemoveStagingDataObjectSet constructor.
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

        $this->removeStaging = new ArrayList();
    }

    /**
     * @return ArrayList
     */
    public function getRemoveStaging()
    {
        return $this->removeStaging;
    }

    /**
     * removes object from set.
     * you can remove a deleted record in stage from staging with removeFromStage.
     *
     * @param DataObject $record
     */
    public function removeFromSet($record) {
        if($record->id == 0) {
            throw new InvalidArgumentException("You can not remove a not written DataObject from Set, please use removeFromStaging instead.");
        }

        if(!$this->removeStaging->find("id", $record->id)) {
            $this->removeStaging->add($record);
        }
    }

    /**
     * @param DataObject $record
     */
    public function removeFromStage($record)
    {
        if($record->id != 0 && $recordToRemove = $this->removeStaging->find("id", $record->id)) {
            $this->removeStaging->remove($recordToRemove);
        } else {
            parent::removeFromStage($record);
        }
    }

    /**
     * @param bool $forceInsert
     * @param bool $forceWrite
     * @param int $snap_priority
     * @throws DataObjectSetCommitException
     */
    public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2)
    {
        parent::commitStaging($forceInsert, $forceWrite, $snap_priority);

        $this->commitRemoveStaging($forceWrite, $snap_priority);
    }

    /**
     * @return array
     */
    protected function getFilterForQuery()
    {
        return $this->argumentFilterForHidingRemovedStageForQuery(parent::getFilterForQuery());
    }

    /**
     * @param bool $forceWrite
     * @param int $snap_priority
     * @return mixed
     */
    abstract public function commitRemoveStaging($forceWrite = false, $snap_priority = 2);

    /**
     * @param $filter
     * @return array
     */
    abstract protected function argumentFilterForHidingRemovedStageForQuery($filter);
}
