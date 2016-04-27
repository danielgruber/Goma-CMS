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
 * - getRange
 *
 * all other functions work in-place.
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
	public function __construct($items = array()) {
		parent::__construct();

		$this->items = (array) $items;
	}
	
	/**
	 * returns data-class of the first item
	*/
	public function DataClass() {
		if(count($this->items) > 0)
			return get_class($this->items[0]);

		return null;
	}
	
	/**
	 * Return the number of items in this list
	 *
	 * @return int
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
	 * pushes a new object or array to the end of the list.
	 *
	  *@param 	array|gObject $item item
	*/
	public function push($item) {
		$this->items[] = $item;
	}
	
	/**
	 * removes a item from the end of the list.
	 *
	 * @return 	array|gObject item
	*/
	public function pop() {
		return array_pop($this->items);
	}
	
	/**
	 * unshifts a new object or array to the beginning of the list.
	 *
	 * @param 	array|gObject $item item
	*/
	public function unshift($item) {
		array_unshift($this->items, $item);
	}
	
	/**
	 * shifts a item from the beginning of the list.
	 *
	 * @return 	array|gObject the removed item
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
	 * adds a item to the list making no guerantee where it will appear.
	 *
	 *@name add
	*/
	public function add($item) {
		$this->push($item);
	}

	/**
	 * inserts a item after a specefied item. if specified item does not exist it inserts it to the end of the list.
	 *
	 * @param gObject $item
	 * @param gObject $after
	 */
	public function insertAfter($item, $after) {
		$new = array();
		$insert = false;
		foreach($this->items as $key => $data) {
			$new[] = $data;
			if(!$insert && $data == $after) {
				$new[] = $item;
				$insert = true;
			}
		}
		
		if(!$insert)
			$new[] = $item;
		
		$this->items = $new;
	}

	/**
	 * inserts a item before a specified item. if specified item does not exist it inserts it to the end of the list.
	 * @param gObject $item
	 * @param gObject $before
	 */
	public function insertBefore($item, $before) {
		$new = array();
		$insert = false;
		foreach($this->items as $key => $data) {
			if(!$insert && $data == $before) {
				$new[] = $item;
				$insert = true;
			}
			$new[] = $data;
		}
		
		if(!$insert)	
			$new[] = $item;
		
		$this->items = $new;
	}

	/**
	 * replaces a item.
	 *
	 * @param    gObject|array $item item
	 * @param    gObject|array $with new item
	 */
	public function replace($item, $with) {
		foreach($this->items as $key => $record) {
			if($record == $item) {
				$this->items[$key] = $with;
				return;
			}
		}
		
		throw new InvalidArgumentException("Item to replace was not found in the list.");
	}

	/**
	 * removes a specific item or item-index.
	 *
	 * @param mixed item
	 * @return bool
	 */
	public function remove($item) {
		foreach($this->items as $key => $record)
			if($item == $record)
				unset($this->items[$key]);
		
		$this->items = array_values($this->items);
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
			$propValue = self::getItemProp($record, $field);
			if(in_array($propValue, $data)) {
				unset($this->items[$key]);
			} else {
				array_push($data, $propValue);
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
	 * returns the current position of a given item.
	 *
	 * 
	 * @param    array|gObject $item item
	 * @return int integer for position of item, boolean false if not found
	 * @throws ItemNotFoundException
	 */
	public function getItemIndex($item) {
		foreach($this->items as $key => $record) {
			if($record == $item)
				return $key;
		}
		
		throw new ItemNotFoundException("Item not found.");
	}

	/**
	 * @param mixed $item
	 * @return bool
	 */
	public function itemExists($item) {
		foreach($this->items as $key => $record) {
			if($record == $item)
				return true;
		}

		return false;
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
	 * @param    gObject|array $item item
	 * @param    array $filter
	 * @return bool
	 */
	static function itemMatchesFilter($item, $filter) {
		foreach($filter as $column => $value) {
			$columnProp = self::getItemProp($item, $column);
			if(!is_array($value)) {
				if($columnProp != $value) {
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
						
						if(!preg_match("/" . $value[1] . "/i", $columnProp))
							return false;
					break;
					case "<":
						if(strcmp($value[1], $columnProp) == 0)
							return false;
							
					case "<=":
						if(strcmp($value[1], $columnProp) == -1)
							return false;
					break;
					case ">":
						if(strcmp($value[1], $columnProp) == 0)
							return false;
					case ">=":
						if(strcmp($value[1], $columnProp) == 1)
							return false;
					break;
					case "<>":
					case "!=":
						if($value[1] == $columnProp)
							return false;
					break;
				}
			} else {
				if(isset($value[0])) {
					$found = false;
					foreach($value as $data) {
						if($columnProp == $data) {
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

		$columnsToSort = array();
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
				$values[$column][] = self::getItemProp($item, $column);
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
	 * moves specific item to another position.
	 * this is working in-place.
	 *
	 * @param    array|gObject $item item
	 * @param    int $to destination position
	 * @param    boolean $insertIfNotExisting if set to true it is not relevant if item exists, it it does not the list inserts item at given position.
	 * @return bool
	 */
	public function move($item, $to, $insertIfNotExisting = false) {
		$inserted = false;
		if(isset($this->items[$to]) || $insertIfNotExisting) {
			$new = array();
			foreach ($this->items as $key => $val) {
				if ($key == $to) {
					$new[] = $item;
					$inserted = true;
				}

				if ($val != $item) {
					$new[] = $val;
				}
			}

			if(!$inserted) {
				$new[] = $item;
			}

			$this->items = $new;
		}

		return $inserted;
	}

	/**
	 * moves specific item before another item.
	 *
	 * @param    array|gObject $item item
	 * @param    array|gObject $before destination object
	 * @param bool $insertIfNotExists
	 * @return bool
	 * @throws ItemNotFoundException
	 */
	public function moveBefore($item, $before, $insertIfNotExists = false) {
		$index = $this->getItemIndex($before);
		
		return $this->move($item, $index, $insertIfNotExists);
	}

	/**
	 * moves specific item behind another item.
	 *
	 * @param    array|gObject $item item
	 * @param    array|gObject $behind destination object
	 * @param bool $insertIfNotExists
	 * @return bool
	 * @throws ItemNotFoundException
	 */
	public function moveBehind($item, $behind, $insertIfNotExists = false) {
		$index = $this->getItemIndex($behind);
		
		$index++;
		return $this->move($item, $index, $insertIfNotExists);
	}
	
	/**
	 * returns if we can sort the ArrayList by a given column.
	*/
	public function canSortBy($column) {
		return true;
	}
	
	/**
	 * returns if we can filter the ArrayList by a given column.
	*/
	public function canFilterBy($column) {
		return true;
	}

	/**
	 * Returns the first item in the list where the key field is equal to the
	 * value.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @param 	bool $caseInsensitive
	 * @return mixed
	 */
	public function find($key, $value, $caseInsensitive = false) {
		foreach($this->items as $record) {
			if($caseInsensitive && strtolower(self::getItemProp($record, $key)) == strtolower($value)) {
				return $record;
			} else if(self::getItemProp($record, $key) == $value) {
				return $record;
			}
		}
		
		return null;
	}
	
	/**
	 * Merges with another array or list by pushing all the items in it onto the
	 * end of this list.
	 *
	 * @param array|gObject $with
	 */
	public function merge($with) {
		foreach ($with as $item) $this->push($item);
	}
	
	/**
	 * Attribute-getter-API. it gets an element of the list at a specified position.
	 *
	 * @param 	int $offset offset
	 * @return 	array|gObject
	*/
	public function __get($offset) {
		if(isset($this->items[$offset]))
			return $this->items[$offset];
		
		return parent::__get($offset);
	}

	/**
	 * Attribute-setter-API. it replaces element at specified position.
	 *
	 * @param  int $offset offset
	 * @param  mixed $value
	 * @return bool|void
	 */
	public function __set($offset, $value) {
		if(isset($this->items[$offset])) {
			$this->items[$offset] = $value;
		}
		
		parent::__set($offset, $value);
	}
	
	/**
	 * unsets an item with given offset.
	 *
	 * @param 	int $offset
	 */
	public function offsetUnset($offset) {
		if(isset($this->items[$offset]))
			unset($this->items[$offset]);

		return parent::offsetUnset($offset);
	}
	
	/**
	 * returns whether an item is set.
	 *
	 * @param 	int $offset
	 * @return	boolean
	 */
	public function offsetExists($offset) {
		return isset($this->items[$offset]) || parent::offsetExists($offset);
	}

	/**
	 * returns an array of all the items of a specific column.
	 *
	 * @param  string $column default: id
	 * @return array
	 */
	public function column($column = "id") {
		$data = array();
		foreach($this as $record) {
			$data[] = self::getItemProp($record, $column);
		}
		
		return $data;
	}
	
	/**
	 * iterator
	 * this extends this dataobject to use foreach on it
	 * @link http://php.net/manual/en/class.iterator.php
	 */
	protected $position = 0;
	
	/**
	 * rewind $position to 0.
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * check if data exists and position is valid.
	 */
	public function valid() {
		return ($this->position < count($this->items));
	}

	/**
	 * gets the key of curren titem.
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * moves pointer to the next item.
	 */
	public function next() {

		$this->position++;
	}

	/**
	 * gets the value of the current item.
	 */
	public function current() {
		return $this->items[$this->position];
	}
}

class ItemNotFoundException extends GomaException {
	protected $standardCode = ExceptionManager::ITEM_NOT_FOUND;
}
