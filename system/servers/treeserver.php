<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 25.12.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TreeServer extends RequestHandler {
	public $url_handlers = array(
		"setCollapsed/\$name!/\$id!" 	=> "setCollapsed",
		"setExpanded/\$name!/\$id!" 		=> "setExpanded",
		"getSubTree/\$name!/\$id!"		=> "getSubTree"
	);
	
	public $allowed_actions = array(
		"setCollapsed",
		"setExpanded",
		"getSubTree"
	);
	
	/**
	 * sets the session-saved-state of a tree-node to collapsed
	*/
	public function setCollapsed() {
		$id = $this->getParam("id");
		$name = $this->getParam("name");

		GlobalSessionManager::globalSession()->set("treestatus_" . $name . "_" . $id, false);
		if(Core::is_ajax()) {
			return ROOT_PATH . "treeserver/setExpanded/".$name."/".$id."/?redirect=".urlencode(Core::activeURL());
		} else {
			HTTPResponse::redirect($_GET["redirect"]);
		}
		
	}
	
	/**
	 * sets the session-saved-state of a tree-node to expanded
	*/
	public function setExpanded() {
		$id = $this->getParam("id");
		$name = $this->getParam("name");

		GlobalSessionManager::globalSession()->set("treestatus_" . $name . "_" . $id, true);
		if(Core::is_ajax()) {
			return ROOT_PATH . "treeserver/setCollapsed/".$name."/".$id."/?redirect=".urlencode(Core::activeURL());;
		} else {
			HTTPResponse::redirect($_GET["redirect"]);
		}
		
	}
	
	/**
	 * gets a subtree
	 *@name getSubtree
	 *@access public
	*/
	public function getSubtree() {
		$id = $this->getParam("id");
		$name = $this->getParam("name");
		$href = $this->getParam("href");
		$getinactive = $this->getParam("getinactive");
		if(Core::is_ajax()) {
			$this->setExpanded();
			$data = gObject::instance($name)->renderTree($href, 0,$id, $getinactive);
			return $data;
		} else {
			$this->setExpanded();
			HTTPResponse::redirect($_GET["redirect"]);
		}
	}
	
}