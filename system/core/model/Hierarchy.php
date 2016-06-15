<?php
/**
 * every class having a tree-structure should use this as extension for better performance and good implementation of trees in PHP
 *
 * @package		Goma\Model
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version     1.1
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Hierarchy extends DataObjectExtension implements TreeModel {
	/**
	 * extra-methods
	 *
	 *@name extra_methods
	*/
	static $extra_methods = array(
		"AllChildren", "getallChildVersionIDs", "getAllChildIDs", "searchChildren", "searchAllChildren", "getAllParentIDs", "getAllParents", "build_tree"
	);
	
	/**
	 * has-one-extension
	*/
	public static function has_one($class) {
		if(strtolower(get_parent_class($class)) != "dataobject")
			return array();
		
		return array(
			"parent" => $class
		);
	}
	
	/**
	 * has-many-extension
	*/
	public static function has_many($class) {
		if(strtolower(get_parent_class($class)) != "dataobject")
			return array();
			
		return array(
			"Children" => $class
		);
	}

    /**
     * gets all children and subchildren to a record
     *
     * @param array|string|null $filter
     * @param array|string|null $sort
     * @param int|array|null $limit
     * @return DataObjectSet
     * @internal param $AllChildren
     */
	public function AllChildren($filter = null, $sort = null, $limit = null) {
		return DataObject::get($this->getOwner()->classname, array_merge((array) $filter, array($this->getOwner()->baseTable . "_tree.parentid" => $this->getOwner()->id)), $sort, $limit, array(
			array(
				DataObject::JOIN_TYPE => "INNER",
				DataObject::JOIN_TABLE => $this->getOwner()->baseTable . "_tree",
				DataObject::JOIN_STATEMENT => $this->getOwner()->baseTable . "_tree.id = " . $this->getOwner()->baseTable . ".id",
				DataObject::JOIN_INCLUDEDATA => false
			)
		));
	}

    /**
     * searches through all direct children of a record
     *
     * @param string|array $search
     * @param array|string|null $filter
     * @param array|string|null $sort
     * @param int|array|null $limit
     * @return DataObjectSet
     * @internal param $SearchChildren
     */
	public function SearchChildren($search, $filter = null, $sort = null, $limit = null) {
		return DataObject::search_object($this->getOwner()->classname, $search, array_merge((array) $filter, array("parentid" => $this->getOwner()->id)), $sort, $limit);
	}

    /**
     * searches through all children and subchildren to of record
     *
     * @name SearchAllChildren
     * @return DataObjectSet|DataSet
     */
	public function SearchAllChildren($search, $filter = null, $sort = null, $limit = null) {
		return DataObject::search_object($this->getOwner()->classname, $search, array_merge((array) $filter, array($this->getOwner()->baseTable . "_tree.parentid" => $this->getOwner()->id)), $sort, $limit, array(
			array(
				DataObject::JOIN_TYPE => "INNER",
				DataObject::JOIN_TABLE => $this->getOwner()->baseTable . "_tree",
				DataObject::JOIN_STATEMENT => $this->getOwner()->baseTable . "_tree.id = " . $this->getOwner()->baseTable . ".id",
				DataObject::JOIN_INCLUDEDATA => false
			)
		));
	}

    /**
     * returns a list of all parentids to the top
     *
     * @name getAllParentIDs
     * @return array
     */
	public function getAllParentIDs() {

        $parentid = $this->getOwner()->parentid;

		$query = new SelectQuery($this->getOwner()->baseTable . "_tree", array("parentid"), array("id" => $this->getOwner()->versionid));

        $ids = $this->getArrayFromDB($query, "$parentid");

        return array_filter($ids, function($v){
           return $v != 0;
        });
	}

    /**
     * returns a dataset of all parents
     *
     * @name getAllParents
     * @return DataObjectSet
     */
	public function getAllParents($filter = null, $sort = null, $limit = null) {
		if(!isset($sort)) {
			$sort = array("field" => $this->getOwner()->baseTable . "_tree.height", "type" => "DESC");
		}
		return DataObject::get($this->getOwner()->classname, array_merge((array) $filter, array($this->getOwner()->baseTable . "_tree.id" => $this->getOwner()->versionid)), $sort, $limit, array(
			array(
				DataObject::JOIN_TYPE => "INNER",
				DataObject::JOIN_TABLE => $this->getOwner()->baseTable . "_tree",
				DataObject::JOIN_STATEMENT => $this->getOwner()->baseTable . "_tree.parentid = " . $this->getOwner()->baseTable . ".id",
				DataObject::JOIN_INCLUDEDATA => false
			)
		));
	}

    /**
     * gets all versionids of the children
     *
     * @name getAllChildVersionIDs
     * @access public
     * @return array
     */
	public function getAllChildVersionIDs() {

		$query = new SelectQuery($this->getOwner()->baseTable . "_tree", array("id"), array("parentid" => $this->getOwner()->id));
        return $this->getArrayFromDB($query, "id");
	}

    /**
     * gets all ids of the children
     *
     * @name getAllChildVersionIDs
     * @access public
     * @return array
     */
	public function getAllChildIDs() {
		$query = new SelectQuery($this->getOwner()->baseTable . "_tree", array("recordid"), array("parentid" => $this->getOwner()->id));
		$query->innerJOIN($this->getOwner()->baseTable, $this->getOwner()->baseTable . ".id = " . $this->getOwner()->baseTable . "_tree.id");
		return $this->getArrayFromDB($query, "recordid");
	}

    /**
     * builds an array of all fields with $fieldname.
     *
     * @param SelectQuery $query
     * @param string $field
     * @return array
     * @throws SQLException
     */
    protected function getArrayFromDB($query, $field) {
        $ids = array();
        if($query->execute()) {
            while($row = $query->fetch_assoc()) {
                $ids[] = $row[$field];
            }
            return $ids;
        } else {
            throw new SQLException();
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
			$manipulation["tree_table"] = array(
				"command" 		=> "insert",
				"table_name"	=> $this->getOwner()->baseTable . "_tree",
				"fields" 		=> array()
			);
			
			$height = 0;
			$p = $this->getOwner();
			
			$ids = array();
			
			while($p->parent && $height < 100) {
				$p = $p->parent();
				
				if(!in_array($p->id, $ids)) {
					$manipulation["tree_table"]["fields"][] = array("id" => $this->getOwner()->versionid, "parentid" => $p->id);
					$ids[] = $p->id;
					$height++;
				} else {
					log_error("Endless-Hierarchy-Error: " . $id . " is a endless-loop.");
					break;
				}
			}
			
			if($height == 100) {
                throw new LogicException('Hierarchy only supports height up to 100. This object seems to have more than hundred parent nodes. <pre>'.print_r($this->getOwner(), true).'</pre>');
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
		if(!DataObject::versioned($this->getOwner()->classname) && isset(ClassInfo::$database[$this->getOwner()->baseTable . "_tree"])) {
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
	 *@name generateClassInfo$parentid
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
     * @param $prefix
     * @param $log
     * @return bool
     * @throws SQLException
     */
	public function buildDB($prefix, &$log) {
		
		if(strtolower(get_parent_class($this->getOwner()->classname)) != "dataobject")
			return true;

        $migrate = !SQL::getFieldsOfTable($this->getOwner()->baseTable . "_tree");

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
		
		if($migrate !== false) {
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
				throw new SQLException();
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
					throw new SQLException();
				}
			}
		}
	}
	
	/**
	 * generates a tree.
	 *
	 * @param 	gObject|null $parent parent
	 * @return 	array|gObject TreeNodes
	*/
	static function build_tree($parent = null, $dataParams = array()) {
	
	}
}