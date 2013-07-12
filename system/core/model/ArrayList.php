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
			foreach(func_get_arg(0) as $column => $value) {
				$keep[$column] = $value;
			}
		}

		$newItems = new ArrayList();
		foreach($this->items as $item){
			$keepItem = true;
			foreach($keep as $column => $value ) {
				if(is_array($value) && !in_array($item[$column], $value)) {
					$keepItem = false;
				} else if(!is_array($value) && $item[$column] != $value) {
					$keepItem = false;
				}
			}
			if($keepItem) {
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
			}
		}
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