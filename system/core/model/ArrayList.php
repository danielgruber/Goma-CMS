<?php
/**
  * a basic class for managing multiple arrays or objects as a list of data
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 23.04.2013
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ArrayList extends ViewAccessableData implements Countable {
	/**
	 * items in this list
	 *
	 *@name items
	*/
	protected $items = array();
	
	/**
	 * constructor
	 *
	 *@name __construct
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
	 * removes a specific item
	 *
	 *@name remove
	*/
	public function remove($item) {
		foreach($this->items as $key => $record)
			if($item == $record)
				unset($this->items[$key]);
	}
}