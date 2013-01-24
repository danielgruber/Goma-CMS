<?php
/**
  * every class having a tree-structure should use this as extension for better performance and good implementation of trees in PHP
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 22.01.2013
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Hierarchy extends DataObjectExtension {
	/**
	 * extra-methods
	 *
	 *@name extra_methods
	*/
	static $extra_methods = array(
		"AllChildren", "searchChildren", "searchAllChildren"
	);
	/**
	 * has-one-extension
	*/
	public function has_one() {
		if($this->getOwner()->class != $this->getOwner()->baseClass)
			return array();
		
		return array(
			"parent" => $this->getOwner()->class
		);
	}
	
	/**
	 * has-many-extension
	*/
	public function has_many() {
		if($this->getOwner()->class != $this->getOwner()->baseClass)
			return array();
			
		return array(
			"Children" => $this->getOwner()->class
		);
	}
	
	/**
	 * gets all children and subchildren to a record
	 *
	 *@name AllChildren
	*/
	public function AllChildren($filter = null, $sort = null, $limit = null) {
		
	}
	
	/**
	 * searches through all direct children of a record
	 *
	 *@name SearchChildren
	*/
	public function SearchChildren($search, $filter = null, $sort = null, $limit = null) {
		
	}
	
	/**
	 * searches through all children and subchildren to of record
	 *
	 *@name SearchAllChildren
	*/
	public function SearchAllChildren($search, $filter = null, $sort = null, $limit = null) {
		
	}
	
	/**
	 * //!extend APIs
	*/
	
	/**
	 * build a seperate tree-table
	 *
	 *@name buidlDB
	*/
	public function buildDB($prefix, &$log) {
		
	}
}