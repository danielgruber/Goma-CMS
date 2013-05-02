<?php
/**
  * every class having a tree-structure should use this as extension for better performance and good implementation of trees in PHP
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 02.02.2013
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
		"AllChildren", "getallChildVersionIDs", "getAllChildIDs", "searchChildren", "searchAllChildren", "getAllParentIDs", "getAllParents"
	);
	
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
		return DataObject::get($this->getOwner()->class, array_merge((array) $filter, array($this->getOwner()->baseTable . "_tree.parentid" => $this->getOwner()->id)), $sort, $limit, array(
			"INNER JOIN " . DB_PREFIX . $this->getOwner()->baseTable . "_tree AS " . $this->getOwner()->baseTable . "_tree ON " . $this->getOwner()->baseTable . "_tree.id = " . $this->getOwner()->baseTable . ".id"
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
		return DataObject::search_object($this->getOwner()->class, $search, array_merge((array) $filter, array($this->getOwner()->baseTable . "_tree.parentid" => $this->getOwner()->id)), $sort, $limit, array(
			"INNER JOIN " . DB_PREFIX . $this->getOwner()->baseTable . "_tree AS " . $this->getOwner()->baseTable . "_tree ON " . $this->getOwner()->baseTable . "_tree.id = " . $this->getOwner()->baseTable . ".id"
		));
	}
	
	/**
	 * returns a list of all parentids to the top
	 *
	 *@name getAllParentIDs
	*/ 
	public function getAllParentIDs() {
		$query = new SelectQuery($this->getOwner()->baseTable . "_tree", array("parentid"), array("id" => $this->getOwner()->versionid));
		if($query->execute()) {
			$ids = array();
			while($row = $query->fetch_object()) {
				if($row->parentid != 0)
					$ids[] = $row->parentid;
			}
		}
		return $ids;
	}
	
	/**
	 * returns a dataset of all parents
	 *
	 *@name getAllParents
	*/ 
	public function getAllParents($filter = null, $sort = null, $limit = null) {
		if(!isset($sort)) {
			$sort = array("field" => $this->getOwner()->baseTable . "_tree.height", "type" => "DESC");
		}
		return DataObject::get($this->getOwner()->class, array_merge((array) $filter, array($this->getOwner()->baseTable . "_tree.id" => $this->getOwner()->versionid)), $sort, $limit, array(
			"INNER JOIN " . DB_PREFIX . $this->getOwner()->baseTable . "_tree AS " . $this->getOwner()->baseTable . "_tree ON " . $this->getOwner()->baseTable . "_tree.parentid = " . $this->getOwner()->baseTable . ".id"
		));
	}
	
	/**
	 * gets all versionids of the children
	 *
	 *@name getAllChildVersionIDs
	 *@access public
	*/
	public function getAllChildVersionIDs() {
		
		$ids = array();
		$query = new SelectQuery($this->getOwner()->baseTable . "_tree", array("id"), array("parentid" => $this->getOwner()->id));
		if($query->execute()) {
			while($row = $query->fetch_object()) {
				$ids[] = $row->id;
			}
			return $ids;
		} else {
			throwErrorByID(3);
		}
	}
	
	/**
	 * gets all ids of the children
	 *
	 *@name getAllChildVersionIDs
	 *@access public
	*/
	public function getAllChildIDs() {
		$ids = array();
		$query = new SelectQuery($this->getOwner()->baseTable . "_tree", array("recordid"), array("parentid" => $this->getOwner()->id));
		$query->innerJOIN($this->getOwner()->baseTable, $this->getOwner()->baseTable . ".id = " . $this->getOwner()->baseTable . "_tree.id");
		if($query->execute()) {
			while($row = $query->fetch_object()) {
				$ids[] = $row->recordid;
			}
			return $ids;
		} else {
			throwErrorByID(3);
		}
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
				"fields" 		=> array()
			);
			
			$height = 0;
			$p = $this->getOwner();
			while($p->parent) {
				$p = $p->parent();
				
				$manipulation["tree_table"]["fields"][] = array("id" => $this->getOwner()->versionid, "parentid" => $p->id);
				$height++;
			}
			
			$manipulation["tree_table"]["fields"][] = array("id" => $this->getOwner()->versionid, "parentid" => 0, "height" => 0);
			foreach($manipulation["tree_table"]["fields"] as $key => $data) {
				$manipulation["tree_table"]["fields"][$key]["height"] = $height;
				$height--;
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
	 * generates some ClassInfo
	 *
	 *@name generateClassInfo
	 *@access public
	*/
	public function generateClassInfo() {
		if(defined("SQL_LOADUP") && $this->getOwner() && SQL::getFieldsOfTable($this->getOwner()->baseTable . "_tree")) {
			// set Database-Record
			ClassInfo::$database[$this->getOwner()->baseTable . "_tree"] = array(
				"id" 		=> "int(10)", 
				"parentid" 	=> "int(10)",
				"height"	=> "int(10)"
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
												"parentid" 	=> "int(10)",
												"height"	=> "int(10)"
											), 
										array(), 
										array(), 
										$prefix
									);
		
		// set Database-Record
		ClassInfo::$database[$this->getOwner()->baseTable . "_tree"] = array(
			"id" 		=> "int(10)", 
			"parentid" 	=> "int(10)",
			"height"	=> "int(10)"
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
				$insert = "INSERT INTO " . $prefix . $this->getOwner()->baseTable . "_tree (id, parentid, height) VALUES ";
				
				$a = 0;
				foreach($versions as $id => $parent) {
					if($a == 0)
						$a++;
					else
						$insert .= ", ";
					
					// calc height
					$height = 0;
					$tid = $parent;
					while(isset($directParents[$tid])) {
						$tid = $directParents[$tid];
						$height++;
					}
					
					$insert .= "(".$id.", ".(int) $parent.", $height)";
					$tid = $parent;
					while(isset($directParents[$tid])) {
						$tid = $directParents[$tid];
						$height--;
						$insert .= ",(".$id.", ".(int) $tid.", $height)";
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