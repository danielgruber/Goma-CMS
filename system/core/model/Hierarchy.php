<?php
/**
  * every class having a tree-structure should use this as extension for better performance and good implementation of trees in PHP
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 30.01.2013
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
		return DataObject::get($this->getOwner()->class, array_merge((array) $filter, array($this->getOwner()->baseClass . "_tree.parentid" => $this->getOwner()->id), $sort, $limit, array(
			"INNER JOIN " . DB_PREFIX . $this->getOwner()->baseClass . "_tree AS " . $this->getOwner()->baseClass . "_tree ON " . $this->getOwner()->baseClass . "_tree.id = " . $this->getOwner()->baseTable . ".id"
		));
	}
	
	/**
	 * searches through all direct children of a record
	 *
	 *@name SearchChildren
	*/
	public function SearchChildren($search, $filter = null, $sort = null, $limit = null) {
		return DataObject::search_object($this->getOwner()->class, $search, array_merge((array) $filter, array("parentid" => $this->getOwner()->id)));
	}
	
	/**
	 * searches through all children and subchildren to of record
	 *
	 *@name SearchAllChildren
	*/
	public function SearchAllChildren($search, $filter = null, $sort = null, $limit = null) {
		return DataObject::search_object($this->getOwner()->class, $search, array_merge((array) $filter, array($this->getOwner()->baseClass . "_tree.parentid" => $this->getOwner()->id), $sort, $limit, array(
			"INNER JOIN " . DB_PREFIX . $this->getOwner()->baseClass . "_tree AS " . $this->getOwner()->baseClass . "_tree ON " . $this->getOwner()->baseClass . "_tree.id = " . $this->getOwner()->baseTable . ".id"
		));
	}
	
	/**
	 * //!extend APIs
	*/
	
	/**
	 * before inserting data
	 *
	 *@name onBeforeManipulate
	*/
	public function onBeforeManipulate(&$manipulation, $job) {
		if($job == "write" && isset(ClassInfo::$database[$this->getOwner()->baseTable . "_tree"])) {
			$parentid = $this->getOwner()->parentid;
		
			$manipulation["tree_table"] = array(
				"command" 		=> "insert",
				"table_name"	=> $this->getOwner()->baseTable . "_tree",
				"fields" 		=> array(array("id" => $this->getOwner()->versionid, "parentid" => 0))
			);
			
			$p = $this->getOwner();
			while($p->parent) {
				$p = $p->parent();
				
				$manipulation["tree_table"]["fields"][] = array("id" => $this->getOwner()->versionid, "parentid" => $p->id);
			}
		}
	}
	
	/**
	 * before removing data
	 *
	 *@name onBeforeRemove
	*/
	public function onBeforeRemove(&$manipulation) {
		if(!DataObject::versioned($this->getOwner()->class) && isset(ClassInfo::$database[$this->getOwner()->baseTable . "_tree"])) {
			$manipulation["delete_tree"] = array(
				"table" 	=> $this->getOwner()->baseTable . "_tree",
				"command"	=> "delete",
				"where"		=> array(
					"id" => $this->getOwner()->versionid
				)
			);
		}
	}
	
	/**
	 * build a seperate tree-table
	 *
	 *@name buidlDB
	*/
	public function buildDB($prefix, &$log) {
		
		if(strtolower(get_parent_class($this->getOwner()->class)) != "dataobject")
			return true;
		
		if(!SQL::getFieldsOfTable($this->getOwner()->baseTable . "_tree")) {
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
		
		// set Database-Record
		ClassInfo::$database[$this->getOwner()->baseTable . "_tree"] = array(
			"id" => "int(10)", 
			"parentid" => "int(10)"
		);
		
		if(isset($migrate)) {
			$sql = "SELECT recordid, parentid, id FROM " . $prefix . $this->getOwner()->baseTable . " ORDER BY id DESC";
			$directParents = array();
			$versions = array();
			
			$i = 0;
			if($result = SQL::query($sql)) {
				while($row = SQL::fetch_object($result)) {
					if(!isset($directParents[$row->recordid]))
						$directParents[$row->recordid] = $row->parentid;
					
					$versions[$row->id] = $row->parentid;
					
					$i++;
				}
			} else {
				throwErrorbyID(3);
			}
			
			if(count($directParents) > 0) {
				$insert = "INSERT INTO " . $prefix . $this->getOwner()->baseTable . "_tree (id, parentid) VALUES ";
				
				$a = 0;
				foreach($versions as $id => $parent) {
					if($a == 0)
						$a++;
					else
						$insert .= ", ";
					
					$insert .= "(".$id.", ".(int) $parent.")";
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