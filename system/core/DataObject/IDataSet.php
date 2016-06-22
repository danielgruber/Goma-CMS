<?php
defined("IN_GOMA") OR die();

/**
 * Describes what ViewAccessableData Sets are required to have.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */
interface IDataSet extends Countable {
    /**
     * @param string $name
     * @param string $value
     * @param bool $caseInsensitive
     * @return mixed
     */
    public function find($name, $value, $caseInsensitive = false);

    /**
     * Filter the list to include items with these charactaristics.
     *
     * @param array|string ...
     * @param string ...
     * @return $this
     * @example $list->filter('Name', 'bob'); // only bob in the list
     * @example $list->filter('Name', array('aziz', 'bob'); // aziz and bob in list
     * @example $list->filter(array('Name'=>'bob, 'Age'=>21)); // bob with the Age 21 in list
     * @example $list->filter(array('Name'=>'bob, 'Age'=>array(21, 43))); // bob with the Age 21 or 43
     * @example $list->filter(array('Name'=>array('aziz','bob'), 'Age'=>array(21, 43)));
     * @example $list->filter(array('Name'=>array('LIKE','bob'))) // all records with name bob, case-insensitive and comparable to the SQL-LIKE
     * @example $list->filter(array('Age' => array("<", 40))) // everybody with age lower 40
     * @example $list->filter(array(array('Age' => array("<", 40)))) // everybody with age lower 40
     *          // aziz with the age 21 or 43 and bob with the Age 21 or 43
     */
    public function filter();

    /**
     * same parameters as filter, but we add it to current set with and.
     *
     * @param array|string ...
     * @param string ...
     * @return $this
     */
    public function addFilter();

    /**
     * @example $list->sort('Name'); // default ASC sorting
     * @example $list->sort('Name DESC'); // DESC sorting
     * @example $list->sort('Name', 'ASC');
     * @example $list->sort(array('Name'=>'ASC,'Age'=>'DESC'));
     *
     * @param array|string ...
     * @param string ...
     * @return $this
     */
    public function sort();

    /**
     * get DataSet of groups.
     *
     * @param string $field
     * @return array
     */
    public function getGroupedSet($field);

    /**
     * get array of groups.
     *
     * @param string $field
     * @return array
     */
    public function groupBy($field);

    /**
     * @param int|null $page
     * @param int|null $perPage
     * @return $this
     */
    public function activatePagination($page = null, $perPage = null);

    /**
     * @return int|null
     */
    public function getPage();

    /**
     * @return int
     */
    public function getPerPage();

    /**
     * @return boolean
     */
    public function isPagination();

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function offsetGet($offset);

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset);

    /**
     * returns the first item
     *
     * @return DataObject|null
     */
    public function first();

    /**
     * returns last item.
     *
     * @return DataObject|null
     */
    public function last();

    /**
     * gets a Range of items in a DataSet of this DataSet
     * pagination is always ignored
     *
     * @param int $start
     * @param int $length
     * @return ArrayList
     */
    public function getRange($start, $length);

    /**
     * @return int
     */
    public function countWholeSet();

    /**
     * @return int
     */
    public function count();

    /**
     * @return int
     */
    public function getPageCount();

    /**
     * @return void
     */
    public function disablePagination();

    /**
     * @param string $field
     * @return bool
     */
    public function canSortBy($field);

    /**
     * @param string $field
     * @return bool
     */
    public function canFilterBy($field);
}
