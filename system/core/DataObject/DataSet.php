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
class DataSet extends ArrayList implements CountAble, Iterator {
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
     * @var ArrayList
     */
    protected $dataSource;

    /**
     * @var ArrayList
     */
    protected $filteredDataSource;

    /**
     * protected customised data
     *
     * @var array
     */
    protected $protected_customised = array();

    /**
     * @var array|string
     */
    protected $filter;

    /**
     * construction
     * @param array $set
     */
    public function __construct($set = array()) {
        parent::__construct($set);

        /* --- */

        $this->dataSource = new ArrayList($set);
        $this->filteredDataSource  = new ArrayList($set);
    }

    /**
     * groups dataset
     * @param string $field
     * @return array
     */
    public function groupBy($field) {
        $set = array();
        foreach($this->items as $record) {
            $key = $this->getItemProp($record, $field);
            if($key !== null) {
                if(!isset($set[$key]))
                    $set[$key] = new DataSet();

                $set[$key]->push($record);
            }
        }

        return $set;
    }

    /**
     * getGroupedSet
     * @param string $field
     * @return DataSet
     */
    public function getGroupedSet($field) {
        return new DataSet($this->groupBy($field));
    }

    /**
     * @return $this
     */
    public function sort()
    {
        $this->dataSource = call_user_func_array(array($this->dataSource, "sort"), func_get_args());

        $this->updateSet($this->filter, $this->page, $this->perPage);

        return $this;
    }

    /**
     * @return $this
     */
    public function filter()
    {
        $this->updateSet(array(func_get_args()), $this->page, $this->perPage);
        $this->filter = array(func_get_args());

        return $this;
    }

    /**
     * @param array $filter
     * @param int|null $page
     * @param int $perPage
     * @return ArrayList|mixed
     */
    protected function updateSet($filter, $page, $perPage) {
        /** @var ArrayList $source */
        $source = $this->dataSource;

        if(isset($filter)) {
            foreach($filter as $part) {
                $source = call_user_func_array(array($source, "filter"), $part);
            }
        }

        $this->filteredDataSource = $source;

        $this->updatePagination($page, $perPage);
    }

    /**
     * @param int|null $page
     * @param int $perPage
     */
    protected function updatePagination($page, $perPage) {
        $source = $this->filteredDataSource;

        if(isset($page)) {
            $pages = max(ceil($this->filteredDataSource->Count() / $this->perPage), 1);
            if($page > $pages) {
                $page = $pages;
            }
            $start = $page * $perPage - $perPage;

            $source = $source->getRange($start, $perPage);
        }

        $this->items = $source->items;
    }

    /**
     * checks if we can sort by a specefied field
     * @param string $field
     * @return bool
     */
    public function canSortBy($field) {
        return true;
    }

    /**
     * checks if we can sort by a specefied field
     * @param string $field
     * @return bool
     */
    public function canFilterBy($field) {
        return false;
    }

    /**
     * generates an array, where the value is a given field
     *
     * @param string $field
     * @return array
     */
    public function fieldToArray($field) {
        $arr = array();
        foreach((array)$this->items as $record) {
            $arr[] = $this->getItemProp($record, $field);
        }
        return $arr;
    }

    /**
     * adds a item to this set
     * @param array|gObject $item
     */
    public function push($item) {
        $this->dataSource->push($item);

        $this->updateSet($this->filter, $this->page, $this->perPage);
    }

    /**
     * alias for push
     * @param array|gObject $item
     */
    public function add($item) {
        $this->push($item);
    }

    /**
     * removes the last item of the set and returns it
     * @return mixed
     */
    public function pop() {
        $return = $this->dataSource->pop();

        $this->updateSet($this->filter, $this->page, $this->perPage);

        return $return;
    }

    /**
     * removes the first item of the set and returns it
     */
    public function shift() {
        $return = $this->dataSource->shift();

        $this->updateSet($this->filter, $this->page, $this->perPage);

        return $return;
    }

    /**
     * @param array|gObject $item
     */
    public function unshift($item)
    {
        $this->dataSource->unshift($item);

        $this->updateSet($this->filter, $this->page, $this->perPage);
    }

    /**
     * this returns whether
     */
    public function last()
    {
        if(count($this->items) > 0) {
            return $this->getConverted($this->items[count($this->items) - 1]);
        }

        return null;
    }


    /**
     * returns the first item
     */
    public function first()
    {
        return isset($this->items[0]) ? $this->getConverted($this->items[0]) : null;
    }

    /**
     * returns current position
     */
    public function position() {
        return $this->position;
    }

    /**
     *
     */
    public function can() {
        $args = func_get_args();
        return call_user_func_array(array($this->first(), "can"), $args);
    }

    /**
     * @return mixed|ViewAccessableData
     */
    public function current() {
        return $this->getConverted(parent::current());
    }

    /**
     * sets the position of the array
     * @param int $pos
     * @return mixed|ViewAccessableData|void
     */
    public function setPosition($pos) {
        if($pos < count($this->items) && $pos > -1) {
            $this->position = $pos;
        }
        return $this->current();
    }

    /**
     * gets the position
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * gets a Range of items in a DataSet of this DataSet
     * pagination is always ignored
     *
     * @param int $start
     * @param int $length
     * @return ArrayList
     */
    public function getRange($start, $length) {
        $set = clone $this;
        $set->items = $this->dataSource->getRange($start, $length);
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
            $pages = max(ceil($this->filteredDataSource->Count() / $this->perPage), 1);
            if($pages < $page) {
                $page = $pages;
            }

            $this->page = $page;
        }

        if(!isset($this->page)) {
            $this->page = 1;
        }

        $this->updatePagination($this->page, $this->perPage);

        return $this;
    }

    /**
     * alias for activatePagination
     *
     * @param null|int $page
     * @param null|int $perPage
     * @return DataSet
     */
    public function activatePages($page = null, $perPage = null) {
       return $this->activatePagination($page, $perPage);
    }

    /**
     * disables pagination
     * @return $this
     */
    public function disablePagination() {
        $this->page = null;
        $this->updatePagination($this->page, $this->perPage);
        return $this;
    }

    /**
     * returns starting item-count, ending item-count and page
     */
    public function getPageInfo() {
        if($this->page !== null) {
            $end = $this->page * $this->perPage;
            if($this->dataSource->count() < $end) {
                $end = $this->dataSource->count();
            }
            return array("start" => $this->page * $this->perPage - $this->perPage, "end" => $end, "whole" => $this->filteredAndSortedDataSource->count());
        }

        return false;
    }

    /**
     * sets the Page
     * @param null|int $page
     * @param null|int $perPage
     * @return DataSet
     */
    public function setPage($page = null, $perPage = null) {
       return $this->activatePagination($page, $perPage);
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
        return ceil($this->filteredDataSource->Count() / $this->perPage);
    }

    /**
     * sets pointer to last page
     *
     *@name goToLastPage
     *@access public
     */
    public function goToLastPage() {
        $pages = ceil($this->countWithoutPagination() / $this->perPage);
        $this->setPage($pages);
    }

    /**
     * @return int
     */
    public function countWithoutPagination() {
        return $this->filteredDataSource->count();
    }

    /**
     * returns if it has a page before
     *
     * @name isPageBefore
     * @access public
     * @return bool
     */
    public function isPageBefore() {
        return ($this->page > 1);
    }

    /**
     * checks if there is a next page
     *
     * @name isPageNext
     * @access public
     * @return bool
     */
    public function isNextPage() {
        $pages = ceil($this->countWithoutPagination() / $this->perPage);
        return ($this->page < $pages);
    }

    /**
     * returns the page-number of the next page
     *
     * @return int
     */
    public function nextPage() {
        $pages = ceil($this->countWithoutPagination() / $this->perPage);
        if($this->page < $pages) {
            return $this->page + 1;
        } else {
            return $pages;
        }
    }

    /**
     * returns the page before
     * @return int|null
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
            if(is_object($this->first()) && method_exists($this->first(), "getOffset")) {
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
     * @return gObject
     */
    public function makeObject($offset, $data) {
        if(parent::__cancall($offset)) {
            return parent::makeObject($offset, $data);
        } else {
            if(is_object($this->first()) && method_exists($this->first(), "makeObject")) {
                Core::Deprecate(2.0, "first()->$offset()");
                return $this->first()->makeObject($offset, $data);
            }
        }
    }

    /**
     * @param gObject $item
     * @return bool|void
     */
    public function remove($item)
    {
        $removed = $this->dataSource->remove($item);

        $this->updateSet($this->filter, $this->page, $this->perPage);

        return $removed;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $caseInsensitive
     * @return mixed
     */
    public function find($key, $value, $caseInsensitive = false)
    {
        return $this->filteredDataSource->find($key, $value, $caseInsensitive);
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
     * @param array|gObject $item
     * @param int $to
     * @param bool $insertIfNotExisting
     * @return bool
     */
    public function move($item, $to, $insertIfNotExisting = false)
    {
        $this->dataSource->move($item, $to, $insertIfNotExisting);

        if($this->isPagination()) {
            $this->updateSet($this->filter, $this->page, $this->perPage);
        } else {
            parent::move($item, $to, $insertIfNotExisting);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPagination()
    {
        return $this->page !== null;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return int|null
     */
    public function getPage()
    {
        $pages = max(ceil($this->filteredDataSource->Count() / $this->perPage), 1);
        if($pages < $this->page) {
            return $pages;
        }

        return $this->page;
    }
}
