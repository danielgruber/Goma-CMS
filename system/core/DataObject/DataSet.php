<?php defined("IN_GOMA") OR die();

/**
 * Basic class for all Sets of ViewAccessableData-Objects. Maybe in Future this will be replaced by @ArrayList.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5.9
 */
class DataSet extends ViewAccessAbleData implements CountAble, Iterator {
    /**
     * if to use pages in this dataset
     * @var bool
     */
    protected $pagination = false;

    /**
     * how many items per page
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * the current page of this dataset
     *
     * @var int|null
     */
    protected $page = null;

    /**
     * data cache, we will store all information here, too
     *
     * @var ArrayList
     */
    protected $dataCache = array();

    /**
     * protected customised data
     *
     * @var array
     */
    protected $protected_customised = array();

    /**
     * construction
     *
     *@name __construct
     *@access public
     */
    public function __construct($set = array()) {
        parent::__construct();

        /* --- */

        if(isset($set)) {
            $this->dataCache = array_values((array)$set);
            $this->reRenderSet();
        }
    }

    /**
     * groups dataset
     *
     *@name groupby
     *@access public
     */
    public function groupBy($field) {
        $set = array();
        foreach($this->data as $dataobject) {
            $key = $dataobject[$field];
            if($key !== null) {
                if(!isset($set[$key]))
                    $set[$key] = new DataSet();

                $set[$key]->push($dataobject);
            }
        }
        return $set;
    }
    /**
     * getGroupedSet
     *
     *@name getGroupedSet
     *@access public
     */
    public function getGroupedSet($field) {
        return new DataSet($this->groupBy($field));
    }

    /**
     * returns the number of records in this set
     *
     *@name Count
     *@access public
     */
    public function Count() {
        return count($this->dataCache);
    }

    /**
     * resorts the data
     *
     *@name sort
     *@access public
     *@param string - column
     *@param string - optional - type
     */
    public function sort($column, $type = "ASC") {
        if(!isset($column))
            return $this;

        if(!$this->canSortBy($column))
            return $this;

        switch($type) {
            case "DESC":
                $type = "DESC";
                break;
            default:
                $type = "ASC";
                break;
        }
        $this->sortField = $column;
        if($type == "DESC")
            uasort($this->dataCache, array($this, "sortDESCHelper"));
        else
            uasort($this->dataCache, array($this, "sortASCHelper"));

        $this->dataCache = array_values($this->dataCache);
        $this->reRenderSet();

        return $this;
    }

    /**
     * checks if we can sort by a specefied field
     *
     *@name canSortBy
     */
    public function canSortBy($field) {
        return true; //! TODO: find a method to get this information
    }

    /**
     * checks if we can sort by a specefied field
     *
     *@name canSortBy
     */
    public function canFilterBy($field) {
        return false; //! TODO: Implement Filter in DataSet
    }

    /**
     * helper for Desc-sort
     *
     *@name sortDESCHelper
     *@access public - I think it need to be public
     */
    public function sortDESCHelper($a, $b) {
        if(isset($b[$this->sortField], $a[$this->sortField]))
            return strcmp($b[$this->sortField], $a[$this->sortField]);

        return 0;
    }
    /**
     * helper for ASC-sort
     *
     *@name sortASCHelper
     *@access public - I think it need to be public
     */
    public function sortASCHelper($a, $b) {
        if(isset($b[$this->sortField], $a[$this->sortField]))
            return strcmp($a[$this->sortField], $b[$this->sortField]);

        return 0;
    }


    /**
     * generates an array, where the value is a given field
     *
     *@name fieldToArray
     *@access public
     *@param string - field
     */
    public function fieldToArray($field) {

        $arr = array();
        foreach((array)$this->data as $record) {
            $arr[] = $record[$field];
        }
        unset($record);
        return $arr;
    }

    /**
     * adds a item to this set
     */
    public function push($item) {
        if(is_array($this->dataCache))
            array_push($this->dataCache, $item);
        else
            $this->dataCache = array($item);


    }

    /**
     * alias for push
     */
    public function add($item) {
        $this->push($item);
    }

    /**
     * removes the last item of the set and returns it
     * @return mixed
     */
    public function pop() {
        $data = array_pop($this->dataCache);
        $this->reRenderSet();
        return $data;
    }

    /**
     * removes the first item of the set and returns it
     *
     *@name shift
     *@access public
     */
    public function shift() {
        $data = array_shift($this->dataCache);
        $this->reRenderSet();
        return $data;
    }

    /**
     * this returns whether this rentry is the last or not
     *@name last
     *@access public
     */
    public function last()
    {
        $position = $this->getPosition();
        $content = $this->setPosition($this->Count() - 1);
        $this->position = $position;
        return $content;
    }



    /**
     * returns the first item
     *@name first
     *@access public
     */
    public function first()
    {
        if(isset($this->data[key($this->data)])) {
            if(!$this->data[key($this->data)]) {
                $pos = key($this->data);
                while(isset($this->data[$pos]) && !$this->data[$pos]) {
                    $pos;
                }

                if(!isset($this->data[$pos])) {
                    return null;
                }

                $d = $this->data[$pos];
            } else {
                $d = $this->data[key($this->data)];
            }
            $data = $this->getConverted($d);
            $this->data[key($this->data)] = $data;
            return $data;
        } else
            return null;
    }
    /**
     * returns current position
     */
    public function position() {
        return $this->position;
    }
    /**
     * returns if this is a highlighted one
     *
     *@name highlight
     *@access public
     */
    public function highlight() {
        $r = ($this->position + 1) % 2;
        return ($r == 0);
    }
    /**
     * returns if this is a white one
     *
     *@name white
     *@access public
     */
    public function white() {
        return (!$this->highlight());
    }
    /**
     * make the functions on top to variables, for example $this.white
     */
    public function getWhite() {
        return $this->white();
    }
    public function getHighlight() {
        return $this->highlight();
    }
    public function getFirst() {
        return $this->first();
    }

    /**
     *
     */
    public function can() {
        $args = func_get_args();
        return call_user_func_array(array($this->first(), "can"), $args);
    }

    /**
     * iterator
     * this extends this dataobject to use foreach on it
     * @link http://php.net/manual/en/class.iterator.php
     */
    /**
     * this var is the current position
     *@name position
     *@access protected
     */
    protected $position = 0;

    /**
     * rewind $position to 0
     *
     *@name rewind
     */
    public function rewind() {
        if(!is_array($this->data) && !is_object($this->data)) {
            return;
        }
        reset($this->data);
        $this->position = key($this->data);


        if($this->pagination) {
            while(isset($this->dataset[$this->position]) && !$this->dataset[$this->position]) {
                $this->position++;
            }
        } else {
            while(isset($this->data[$this->position]) && !$this->data[$this->position]) {
                $this->position++;
            }
        }
    }

    /**
     * check if data exists
     *
     *@name valid
     */
    public function valid()
    {
        if(!is_array($this->data) && !is_object($this->data)) {
            return false;
        }

        return ($this->position >= key($this->data) && $this->position < count($this->data));
    }

    /**
     * gets the key
     *
     *@name key
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * gets the next one
     *
     *@name next
     */
    public function next()
    {
        $this->position++;
        if($this->pagination) {
            while(isset($this->dataset[$this->position]) && !$this->dataset[$this->position]) {
                $this->position++;
            }
        } else {
            while(isset($this->data[$this->position]) && !$this->data[$this->position]) {
                $this->position++;
            }
        }
    }

    /**
     * @return mixed|ViewAccessableData
     */
    public function current() {
        $data = $this->getConverted($this->data[$this->position]);

        if(is_object($data) && is_a($data, "viewaccessabledata"))
            $data->dataSetPosition = $this->position;

        $this->data[$this->position] = $data;
        return $data;
    }

    /**
     * sets the position of the array
     *
     *@name setPosition
     *@access public
     */
    public function setPosition($pos) {
        if($pos < count($this->data) && $pos > -1) {
            $this->position = $pos;
        }
        return $this->current();
    }

    /**
     * gets the position
     *
     *@name getPosition
     *@access public
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * gets a Range of items in a DataSet of this DataSet
     * pagination is always ignored
     *
     *@name getRange
     *@access public
     *@return DataSet
     */
    public function getRange($start, $length) {
        $set = new DataSet();
        for($i = $start; $i < ($start + $length); $i++) {
            if(isset($this->dataCache[$i]))
                $set->push($this->dataCache[$i]);
        }
        return $set;
    }

    /**
     * gets a Range of items as array of this DataSet
     * pagination is always ignored
     *
     *@name getArrayRange
     *@access public
     *@return array
     */
    public function getArrayRange($start, $length) {
        $set = array();
        for($i = $start; $i < ($start + $length); $i++) {
            if(isset($this->dataCache[$i]))
                $set[] =& $this->dataCache[$i];
        }
        return $set;
    }

    /**
     * activates pagination
     *
     * @param int|null $page
     * @param int|null $perPage
     * @access public
     * @return $this
     */
    public function activatePagination($page = null, $perPage = null) {
        if(isset($perPage) && $perPage > 0)
            $this->perPage = $perPage;

        if(isset($page) && RegexpUtil::isNumber($page) && $page > 0) {

            // first validate the data
            $pages = max(ceil($this->Count() / $this->perPage), 1);
            if($pages < $page) {
                $page = $pages;
            }

            $this->page = $page;
        }
        if(!isset($this->page)) {
            $this->page = 1;
        }

        $this->pagination = true;
        $this->reRenderSet();

        return $this;
    }

    /**
     * alias for activatePagination
     *
     *@name activatePagination
     *@access public
     */
    public function activatePages($page = null, $perPage = null) {
        $this->activatePagination($page, $perPage);
    }

    /**
     * disables pagination
     *
     *@name disablePagination
     *@access public
     */
    public function disablePagination() {
        $this->pagination = false;
        $this->reRenderSet();
    }

    /**
     * returns starting item-count, ending item-count and page
     *
     *@name getPageInfo
     *@access public
     */
    public function getPageInfo() {
        if($this->pagination) {
            $end = $this->page * $this->perPage;
            if($this->count() < $end) {
                $end = $this->count();
            }
            return array("start" => $this->page * $this->perPage - $this->perPage, "end" => $end, "whole" => $this->count());
        }

        return false;
    }

    /**
     * sets the Page
     *
     *@name setPage
     *@access public
     *@param int - page
     *@param int - per page
     */
    public function setPage($page = null, $perPage = null) {
        if(isset($page)) $this->page = $page;
        if(isset($perPage)) $this->perPage = $perPage;
        $this->reRenderSet();
    }

    /**
     * gets available pages as array to render it in good pagination-style.
     *
     * @return array
     */
    public function getPages() {
        return $this->renderPages($this->getPageCount(), $this->page);
    }

    /**
     * returns page-count.
     *
     * @return int
     */
    public function getPageCount() {
        return ceil($this->Count() / $this->perPage);
    }

    /**
     * sets pointer to last page
     *
     *@name goToLastPage
     *@access public
     */
    public function goToLastPage() {
        $pages = ceil($this->Count() / $this->perPage);
        $this->setPage($pages);
    }

    /**
     * returns if it has a page before
     *
     *@name isPageBefore
     *@access public
     */
    public function isPageBefore() {
        return ($this->page > 1);
    }

    /**
     * checks if there is a next page
     *
     *@name isPageNext
     *@access public
     */
    public function isNextPage() {
        $pages = ceil($this->Count() / $this->perPage);
        return ($this->page < $pages);
    }

    /**
     * returns the page-number of the next page
     *
     * @name nextPage
     * @access public
     * @return int
     */
    public function nextPage() {
        $pages = ceil($this->Count() / $this->perPage);
        if($this->page < $pages) {
            return $this->page + 1;
        } else {
            return $pages;
        }
    }

    /**
     * returns the page before
     *
     *@name pageBefore
     *@access public
     */
    public function pageBefore() {
        if($this->page > 1) {
            return $this->page - 1;
        } else {
            return 1;
        }
    }

    /**
     * get an array of pages by given pagecount
     *
     * @name renderPages
     * @access public
     * @param int $pagecount
     * @param int $currentpage
     * @return array
     */
    protected function renderPages($pagecount, $currentpage = 1) {
        if($pagecount < 2) {
            return array(1 => array(
                "page" 	=> 1,
                "black"	=> true
            ));
        } else {
            $data = array();
            if($pagecount < 8) {
                for($i = 1; $i <= $pagecount; $i++) {
                    $data[$i] = array(
                        "page" 	=> ($i),
                        "black"	=> ($i == $currentpage)
                    );
                }
            } else {

                $lastDots = false;
                for($i = 1; $i <= $pagecount; $i++) {
                    if($i < 3 || ($i > $currentpage - 3 && $i < $currentpage + 3) || $i > $pagecount - 3) {
                        $data[$i] = array(
                            "page" 	=> ($i),
                            "black"	=> ($i == $currentpage)
                        );
                        $lastDots = false;
                    } else if(!$lastDots && (($i > 2 && $i < ($currentpage - 2)) || ($i < ($pagecount - 2) && $i > ($currentpage + 2)))) {
                        $data[$i] = array(
                            "page" 	=> "...",
                            "black" => true
                        );
                        $lastDots = true;
                    }
                }
            }
            return $data;
        }
    }

    /**
     * remakes the variable currentSet for pagination
     *
     *@name reRenderSet
     *@access public
     */
    public function reRenderSet() {
        if($this->pagination) {
            $this->dataCache = (array) $this->dataCache + (array) $this->data;
            $start = $this->page * $this->perPage - $this->perPage;
            $count = $this->perPage;
            if($this->Count() < $start) {
                if($this->Count() < $this->perPage) {
                    $start = 0;
                    $count = $this->perPage;
                } else {
                    $pages = ceil($this->Count() / $this->perPage);
                    if($this->page < $pages) {
                        $this->page = $pages;
                    }
                    $start = $this->page * $this->perPage - $this->perPage;
                }
            }
            if($start + $count > $this->Count()) {
                $count = $this->Count() - $start;
            }
            $this->data = array_values($this->getArrayRange($start, $count));
            reset($this->data);
        } else {
            $this->data =& $this->dataCache;
        }
    }

    /**
     * returns the offset of the first record or the current model
     *
     * @param string $offset
     * @param array $args
     * @return $this|int|string
     */
    public function getOffset($offset, $args = array()) {
        if(strtolower($offset) == "count") {
            return $this->Count();
        } else if(gObject::method_exists($this->classname, $offset) || parent::__canCall($offset, $args)) {
            return parent::getOffset($offset, $args);
        } else {
            if(is_object($this->first())) {
                Core::Deprecate(2.0, "first()->$offset");
                return $this->first()->getOffset($offset, $args);
            }
        }
    }

    public function this() {
        return $this;
    }

    /**
     * returns if a method exists dynamically.

     * @param string $method
     * @return bool
     */
    public function __cancall($method) {
        if($method == "current")
            return true;

        if(strtolower($method) == "count")
            return true;

        return ((gObject::method_exists($this->classname, $method) || parent::__cancall($method)) || (is_object($this->first()) && gObject::method_exists($this->first(), $method)));
    }

    /**
     * sets an offset
     *
     *@name __set
     *@access public
     *@param string - offset
     *@param mixed - new value
     */
    public function __set($key, $value) {
        if(gObject::method_exists($this->classname, "set" . $key)) {
            return call_user_func_array(array($this, "set" . $key), array($value));
        }

        if(is_object($this->first())) {
            Core::Deprecate(2.0, "first()->$key");
            return $this->first()->__set($key, $value);
        }
        return false;
    }

    /**
     * converts the item to the right format
     *
     * @param Object|array|mixed $item
     * @return ViewAccessableData
     */
    public function getConverted($item) {
        if(is_array($item)) {
            if(isset($item["class_name"]) && ClassInfo::exists($item["class_name"]))
                $object = new $item["class_name"]($item);
            else
                $object = new ViewAccessableData($item);
        } else {
            $object = $item;
        }

        if(isset($object->data)) $object->original = $object->data;

        $object->dataset =& $this;

        if(is_object($object) && method_exists($object, "customise")) {
            $object->customise($this->protected_customised);
            return $object;
        } else {
            return $object;
        }
    }

    /**
     * generates an object from the offset
     *
     * @param string $offset
     * @param mixed $data
     * @param string|null $cachename
     * @return gObject
     */
    public function makeObject($offset, $data, $cachename = null) {
        if(parent::__cancall($offset)) {
            return parent::makeObject($offset, $data, $cachename);
        } else {
            if(is_object($this->first())) {
                Core::Deprecate(2.0, "first()->$offset()");
                return $this->first()->makeObject($offset, $data, $cachename);
            }
        }
    }

    /**
     * removes a specific record from the set
     *
     *@name removeRecord
     *@access public
     *@return record
     */
    public function removeRecord($record) {
        if(is_object($record)) {
            foreach($this->data as $k => $r) {
                if($r == $record) {
                    $this->data[$k] = false;
                }
            }

            foreach($this->dataCache as $k => $r) {
                if($r == $record) {
                    $this->dataCache[$k] = false;
                }
            }

            $this->reRenderSet();

            if(empty($this->data))
                $this->data = array();

            return $record;
        } else {
            $r = null;
            $position = $record;
            if($this->pagination) {
                if(is_array($position)) {
                    foreach($position as $p) {
                        $this->dataCache[$p] = false;
                    }
                } else {
                    $r = $this->dataCache[$position];
                    $this->dataCache[$position] = false;
                }

                // rebuild
                $this->reRenderSet();
            } else {
                if(is_array($position)) {
                    foreach($position as $p) {
                        $this->data[$p] = false;
                        $this->dataCache[$p] = false;
                    }
                } else {
                    $r = $this->dataCache[$position];
                    $this->data[$position] = false;
                    $this->dataCache[$position] = false;
                }
            }

            if(empty($this->data))
                $this->data = array();

            return $r;
        }
    }

    /**
     * on customise make a copy of the data in protected
     *
     * @name customise
     * @access public
     * @return $this
     */
    public function customise($loop = array()) {
        $response = parent::customise($loop);
        // we always want to apply the customised data of the first state to each record
        $this->protected_customised = $this->customised;
        return $response;
    }

    /**
     * @return boolean
     */
    public function isPagination()
    {
        return $this->pagination;
    }


    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }
}
