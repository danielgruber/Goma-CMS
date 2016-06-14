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
    const ID = "RemoveStagingDataObjectSet";

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
     * @param array|null $join
     * @param array|null|string $search
     * @param null|string $version
     */
    public function __construct($class = null, $filter = null, $sort = null, $join = null, $search = null, $version = null)
    {
        parent::__construct($class, $filter, $sort, $join, $search, $version);

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
     * @param null $repository
     * @param bool $callRemove
     * @throws DataObjectSetCommitException
     */
    public function commitStaging($forceInsert = false, $forceWrite = false, $snap_priority = 2, $repository = null, $callRemove = true)
    {
        $repository = isset($repository) ? $repository : Core::repository();

        parent::commitStaging($forceInsert, $forceWrite, $snap_priority, $repository);

        if($callRemove) $this->commitRemoveStaging($repository, $forceWrite, $snap_priority, $repository);
    }

    /**
     * @return array
     */
    protected function getFilterForQuery()
    {
        return $this->argumentFilterForHidingRemovedStageForQuery(parent::getFilterForQuery());
    }

    /**
     * @param null|IModelRepository $repository
     * @param bool $forceWrite
     * @param int $snap_priority
     * @param IModelRepository $repository
     * @return mixed
     */
    abstract public function commitRemoveStaging($repository, $forceWrite = false, $snap_priority = 2, $repository = null);

    /**
     * @param array|string $filter
     * @return array
     */
    abstract protected function argumentFilterForHidingRemovedStageForQuery($filter);

    /**
     * @return bool
     */
    abstract public function canSortSet();
    //abstract public function sortSet();
}
