<?php defined("IN_GOMA") OR die();

/**
 * a basic class for managing data in an array and manipualte the group of records.
 *
 * Note that the following methods create new instances of this Object:
 *
 * - reverse
 * - filter
 * - sort
 * - exclude
 * - limit
 * - getReange
 *
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 	1.0
 */
class ArrayList extends ViewAccessableData implements Countable {
	/**
	 * items in this list.
	*/
	protected $items = array();
	
	/**
	 * constructor. 
	 *
	 * @param 	array $items items to start
	*/
	public function __construct(array $items = array()) {
		$this->items = $items;
		
		parent::__construct();
	}
	
	/**
	 * returns data-class of the first item
	*/
	public function DataClass() {
		if(count($this->items) > 0) return get_class($this->items[0]);
	}
	
	/**
	 * Return the number of items in this list
	 *
	 *@return int
	 */
	public function count() {
		return count($this->items);
	}
	
	/**
	 * Returns true if this list has items
	 * 
	 * @return bool
	 */
	public function bool() {
		return (bool) count($this);
	}
	
	/**
	 * returns an array of this
	*/
	public function ToArray() {
		return $this->items;
	}
	
	/**
	 * returns a nested array of this and subobjects
	*/
	public function ToNestedArray() {
		$arr = array();
		foreach($this->items as $item) {
			if(is_object($item)) {
				if(Object::method_exists($item, "toNestedArray")) {
					$arr[] = $item->ToNestedArray();
				} else if(Object::method_exists($item, "toMap")) {
					$arr[] = $item->ToMap();
				} else if(Object::method_exists($item, "toArray")) {
					$arr[] = $item->ToArray();
				} else {
					$arr[] = (array) $item;
				}
			} else {
				$arr[] = $item;
			}
		}
	}
	
	/**
	 * pushes a new object or array to the end of the list
	 *
	 *@name push
	 *@param array|object item
	*/
	public function push($item) {
		$this->items[] = $item;
	}
	
	/**
	 * removes a item from the end of the list
	 *
	 *@name pop
	 *@return array|object item
	*/
	public function pop() {
		return array_pop($this->items);
	}
	
	/**
	 * unshifts a new object or array to the beginning of the list
	 *
	 *@name unshift
	 *@param array|object item
	*/
	public function unshift($item) {
		array_unshift($this->items, $item);
	}
	
	/**
	 * shifts a item from the beginning of the list
	 *
	 *@name shift
	 *@return array|object item
	*/
	public function shift() {
		return array_shift($this->items);
	}
	
	/**
	 * revereses an ArrayList and gives the new back.
	*/ 
	public function reverse() {
		$list = new ArrayList();
		foreach($this as $record) {
			$list->unshift($record);
		}
		return $list;
	}
	
	/**
	 * adds a item to the end
	 *
	 *@name add
	*/
	public function add($item) {
		$this->push($item);
	}
	
	/**
	 * inserts a item after a specefied item
	 *
	 *@name insertAfter
	*/
	public function insertAfter($item, $after) {
		$new = array();
		foreach($this->items as $key => $data) {
			$new[] = $data;
			if($data == $after) {
				$new[] = $item;
			}
		}
		
		$this->items = $new;
	}
	
	/**
	 * inserts a item before a specefied item
	 *
	 *@name insertBefore
	*/
	public function insertBefore($item, $before) {
		$new = array();
		foreach($this->items as $key => $data) {
			if($data == $before) {
				$new[] = $item;
			}
			$new[] = $data;
		}
		
		$this->items = $new;
	}
	
	/**
	 * removes a specific item or item-index.
	 * 
	 *@param object|array item
	*/
	public function remove($item) {
		foreach($this->items as $key => $record)
			if($item == $record)
				unset($this->items[$key]);
		
		$this->items = array_values($this->items);
		
		return true;
	}
	
	/**
	 * removes all dupilicated from the list by given field. it modifies this list directly.
	 *
	 * @param 	string $field field for checking duplicated
	 * @return 	void 
	*/
	public function removeDuplicates($field = "id") {
		$data = array();
		foreach($this->items as $key => $record) {
			if(in_array($record[$field], $data)) {
				unset($this->items[$key]);
			} else {
				array_push($data, $record[$field]);
			}
		}
		
		$this->items = array_values($this->items);
	}
	
	/**
	 * returns a specific range of this set of data.
	 *
	 * @param 	int $start element to start
	 * @param 	int $length length
	 * 
	 * @return ArrayList
	*/
	public function getRange($start, $length) {
		$list = new ArrayList();
		for($i = $start; $i < count($this->items) && $i < $start + $length; $i++) {
			if(isset($i))
				$list->push($this->items[$i]);
		}
		
		return $list;
	}
	
	
	/**
	 * returns a specific range of this set of data.
	 *
	 * @param 	int $start element to start
	 * @param 	int $length length default: 1
	 * 
	 * @return ArrayList
	*/
	public function limit($start, $length = 1) {
		return $this->getRange($start, $length);
	}
	
	/**
	 * returns the first element of the list.
	*/
	public function first() {
		return isset($this->items[0]) ? $this->items[0] : null;
	}
	
	/**
	 * returns the last element of the list.
	*/
	public function last() {
		if(count($this) > 0)
			return $this->items[count($this) - 1];
		
		return null;
	}
	
	/**
	 * Filter the list to include items with these charactaristics.
	 * 
	 * @return ArrayList
	 * @example $list->filter('Name', 'bob'); // only bob in the list
	 * @example $list->filter('Name', array('aziz', 'bob'); // aziz and bob in list
	 * @example $list->filter(array('Name'=>'bob, 'Age'=>21)); // bob with the Age 21 in list
	 * @example $list->filter(array('Name'=>'bob, 'Age'=>array(21, 43))); // bob with the Age 21 or 43
	 * @example $list->filter(array('Name'=>array('aziz','bob'), 'Age'=>array(21, 43))); 
	 * @example $list->filter(array('Name'=>array('LIKE','bob'))) // all records with name bob, case-insensitive and comparable to the SQL-LIKE
	 * @example $list->filter(array('Age' => array("<", 40))) // everybody with age lower 40
	 *          // aziz with the age 21 or 43 and bob with the Age 21 or 43
	 */
	public function filter() {
		if(count(func_get_args())>2){
			throw new InvalidArgumentException('filter takes one array or two arguments');
		}

		if(count(func_get_args()) == 1 && !is_array(func_get_arg(0))){
			throw new InvalidArgumentException('filter takes one array or two arguments');
		}

		$keep = array();
		if(count(func_get_args())==2){
			$keep[func_get_arg(0)] = func_get_arg(1);
		}

		if(count(func_get_args())==1 && is_array(func_get_arg(0))){
			$keep = func_get_arg(0);
		}

		$newItems = new ArrayList();
		foreach($this->items as $item){
			if(self::itemMatchesFilter($item, $keep)) {
				$newItems->push($item);
			}
		}

		return $newItems;
	}
	
	/**
	 * helper method to analyze if a item matches to a filter.
	 *
	 * @param 	Object|array $item item
	 * @param 	array $filter
	*/
	static function itemMatchesFilter($item, $filter) {
		foreach($filter as $column => $value) {
			if(!is_array($value)) {
				if($item[$column] != $value) {
					return false;
				}
			} else if(isset($value[0], $value[1]) && count($value) == 2 && ($value[0] == "LIKE" || $value[0] == ">" || $value[0] == "<" || $value[0] == "!=" || $value[0] == "<=" || $value[0] == ">=" || $value[0] == "<>")) {
				switch($value[0]) {
					case "LIKE":
						$value[1] = preg_quote($value[1], "/");
						$value[1] = str_replace('%', '.*', $value[1]);
						$value[1] = str_replace('_', '.', $value[1]);
						$value[1] = str_replace('\\.*', "%", $value[1]);
						$value[1] = str_replace('\\.', "_", $value[1]);
						
						if(!preg_match("/" . $value[1] . "/i", $item[$column]))
							return false;
					break;
					case "<":
						if(strcmp($value[1], $item[$column]) == 0)
							return false;
							
					case "<=":
						if(strcmp($value[1], $item[$column]) == -1)
							return false;
					break;
					case ">":
						if(strcmp($value[1], $item[$column]) == 0)
							return false;
					case ">=":
						if(strcmp($value[1], $item[$column]) == 1)
							return false;
					break;
					case "<>":
					case "!=":
						if($value[1] == $item[$column])
							return false;
					break;
				}
			} else {
				if(isset($value[0])) {
					$found = false;
					foreach($value as $data) {
						if($item[$column] == $data) {
							$found = true;
						}
					}
					
					if(!$found)
						return false;
				} else {
					if(!self::itemMatchesFilter($item, $value)) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Sorts this list by one or more fields. You can either pass in a single
	 * field name and direction, or a map of field names to sort directions.
	 *
	 * @author Silverstripe Team https://github.com/silverstripe/silverstripe-framework/blob/3.1/model/ArrayList.php
	 *
	 * @return ArrayList
	 * @example $list->sort('Name'); // default ASC sorting
	 * @example $list->sort('Name DESC'); // DESC sorting
	 * @example $list->sort('Name', 'ASC');
	 * @example $list->sort(array('Name'=>'ASC,'Age'=>'DESC'));
	 */
	public function sort() {
		$args = func_get_args();

		if(count($args)==0){
			return $this;
		}
		if(count($args)>2){
			throw new InvalidArgumentException('This method takes zero, one or two arguments');
		}

		// One argument and it's a string
		if(count($args)==1 && is_string($args[0])){
			$column = $args[0];
			if(strpos($column, ' ') !== false) {
				throw new InvalidArgumentException("You can't pass SQL fragments to sort()");
			}
			$columnsToSort[$column] = SORT_ASC;

		} else if(count($args)==2){
			$columnsToSort[$args[0]]=(strtolower($args[1])=='desc')?SORT_DESC:SORT_ASC;

		} else if(is_array($args[0])) {
			foreach($args[0] as $column => $sort_order){
				$columnsToSort[$column] = (strtolower($sort_order)=='desc')?SORT_DESC:SORT_ASC;
			}
		} else {
			throw new InvalidArgumentException("Bad arguments passed to sort()");
		}

		// This the main sorting algorithm that supports infinite sorting params
		$multisortArgs = array();
		$values = array();
		foreach($columnsToSort as $column => $direction ) {
			// The reason these are added to columns is of the references, otherwise when the foreach
			// is done, all $values and $direction look the same
			$values[$column] = array();
			$sortDirection[$column] = $direction;
			// We need to subtract every value into a temporary array for sorting
			foreach($this->items as $index => $item) {
				$values[$column][] = $this->extractValue($item, $column);
			}
			// PHP 5.3 requires below arguments to be reference when using array_multisort together 
			// with call_user_func_array
			// First argument is the 'value' array to be sorted
			$multisortArgs[] = &$values[$column];
			// First argument is the direction to be sorted, 
			$multisortArgs[] = &$sortDirection[$column];
		}

		$list = clone $this;
		// As the last argument we pass in a reference to the items that all the sorting will be applied upon
		$multisortArgs[] = &$list->items;
		call_user_func_array('array_multisort', $multisortArgs);
		return $list;
	}
	
	/**
	 * Attribute-getter-API. it gets an element of the list at a specified position.
	 *
	 * @param 	int $offset offset
	 * @return 	array|object
	*/
	public function __get($offset) {
		if(isset($this->items[$offset]))
			return $this->items[$offset];
		
		return null;
	}
	
	/**
	 * Attribute-setter-API. it replaces element at specified position.
	 *
	 * @param 	int $offset offset
	 * @parma 	string $value value
	*/
	public function __set($offset, $value) {
		if(isset($this->items[$offset])) {
			$this->items[$offset] = $value;
			return true;
		}
		
		return false;
	}
	
	/**
	 * unsets an item with given offset.
	 *
	 * @param 	int $offset
	 */
	public function offsetUnset($offset) {
		if(isset($this->items[$offset]))
			unset($this->items[$offset]);
	}
	
	/**
	 * returns whether an item is set.
	 *
	 * @param 	int $offset
	 * @return	boolean
	 */
	public function offsetExists($offset) {
		return isset($this->items[$offset]);
	}
	
	/**
	 * returns an array of all the items of a specific column.
	 *
	 * @param 	string $column default: id
	*/
	public function column($column = "id") {
		$data = array();
		foreach($this as $record) {
			$data[] = $record[$column];
		}
		
		return $data;
	}
}