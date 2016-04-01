<?php
defined("IN_GOMA") OR die();

/**
 * Tree-Server.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class TreeServer extends RequestHandler {

	/**
	 * @var array
	 */
	public $url_handlers = array(
		"setCollapsed/\$name!/\$id!" 	=> "setCollapsed",
		"setExpanded/\$name!/\$id!" 		=> "setExpanded",
		"getSubTree/\$name!/\$id!"		=> "getSubTree"
	);

	/**
	 * @var array
	 */
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
		if($this->getRequest()->is_ajax()) {
			return ROOT_PATH . "treeserver/setExpanded/".$name."/".$id."/?redirect=".urlencode(Core::activeURL());
		} else {
			return GomaResponse::redirect($this->getRequest()->get_params["redirect"]);
		}
	}
	
	/**
	 * sets the session-saved-state of a tree-node to expanded
	*/
	public function setExpanded() {
		$id = $this->getParam("id");
		$name = $this->getParam("name");

		GlobalSessionManager::globalSession()->set("treestatus_" . $name . "_" . $id, true);
		if($this->getRequest()->is_ajax()) {
			return ROOT_PATH . "treeserver/setCollapsed/".$name."/".$id."/?redirect=".urlencode(Core::activeURL());;
		} else {
			return GomaResponse::redirect($this->getRequest()->get_params["redirect"]);
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