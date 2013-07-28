<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */

class TreeRenderer extends Object {
	/**
	 * the current tree.
	*/
	public $tree;
	
	/**
	 * a list of childs which should be expanded.
	*/
	public $expandedIDs = array();
	
	/**
	 * generates the treerenderer with a given tree.
	 *
	 * @param 	array|TreeNode $tree tree
	*/
	public function __construct($tree) {
		$this->tree = $tree;
		
		parent::__construct();
	}
	
	/**
	 * sets a given node expanded.
	 *
	 * @param	int $recordID nodeid
	*/
	public function setExpanded($recordid) {
		$this->expandedIDs[$recordid] = true;
	}
	
	/**
	 * sets a given node collapsed.
	 *
	 * @param	int $recordID nodeid
	*/
	public function setCollapsed($recordid) {
		$this->expandedIDs[$recordid] = false;
	}
}