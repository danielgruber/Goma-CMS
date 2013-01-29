<?php
/**
  * every class having a tree-structure should use this as extension for better performance and good implementation of trees in PHP
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 29.01.2013
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
	
	static $c = 0;
	
	/**
	 * has-one-extension
	*/
	public function has_one() {
		if(strtolower(get_parent_class($this->getOwner()->class)) != "dataobject")
			return array();
		
		return array(
			"parent" => $this->getOwner()->class
		);
	}
	
	/**
	 * has-many-extension
	*/
	public function has_many() {
		if(strtolower(get_parent_class($this->getOwner()->class)) != "dataobject")
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
		return true;
		if(strtolower(get_parent_class($this->getOwner()->class)) != "dataobject")
			return true;
		
		if(!SQL::getFieldsOfTable($this->baseTable . "_tree")) {
			// create and migrate
			$migrate = true;
		}
		
		$log .= SQL::requireTable(	$this->getOwner()->baseTable . "_tree", 
										array(	"id" 		=> "int(10)", 
												"parentid" 	=> "int(10)"
											), 
										array(), 
										array(), 
										$prefix
									);
		if(isset($migrate)) {
			$sql = "SELECT recordid, parentid FROM " . $prefix . $this->getOwner()->baseTable;
			$directParents = array();
			
			if($result = SQL::query($sql)) {
				while($row = SQL::fetch_object($result)) {
					$directParents[$row->recordid] = $row->parentid;
				}
			} else {
				throwErrorbyID(3);
			}
			
			if(count($directParents) > 0) {
				$insert = "INSERT INTO " . $prefix . $this->getOwner()->baseTable . "_tree (id, parentid) VALUES ";
				
				$a = 0;
				foreach($directParents as $id => $parent) {
					if($a == 0)
						$a++;
					else
						$insert .= ", ";
					
					$insert .= "(".$id.", ".(int) $tid.")";
					$tid = $parent;
					while(isset($directParents[$tid])) {
						$tid = $directParents[$tid];
						$insert .= ",(".$id.", ".(int) $tid.")";
					}
				}
				
				if(SQL::Query($insert)) {
					return true;
				} else {
					throwErrorByID(3);
				}
			}
		}
	}
}