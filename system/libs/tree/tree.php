<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 -  2012 Goma-Team
  * last modified: 19.12.12
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

interface TreeServer {
	/**
	 * generates a normal tree from a given parentnode on
	 *
	 *@name generateTree
	 *@access public
	 *@param parentID
	 *@param params - custom params for the tree
	*/
	public function generateTree($parentID = null, $params = array());
	
	/**
	 * generates a tree which was generates because of search
	 *
	 *@name generateSearchTree
	 *@access public
	 *@param string - search-expression
	 *@param parentID
	 *@param params - custom params for the tree
	*/
	public function generateSearchTree($search, $parentID = null, $params = array());
	
	/**
	 * this returns a list of methods which are supported as params
	 *
	 *@name getParams
	*/
	public function getParams();
}

class TreeRenderer extends Object {
	/**
	 * constructor needs an array of TreeNode-Objects or a TreeNode-Object
	 *
	 *@name __construct
	 *@access public
	 *@param array|treenode
	*/
	public function __construct($tree) {
		
	}
}

class TreeNode extends Object {
	/**
	 * id of the node
	 *
	 *@name nodeid
	*/
	public $nodeid;
	
	/**
	 * record-id
	 *
	 *@name recordID
	 *@access public
	*/
	public $recordID;
	
	/**
	 * title of the node
	 *
	 *@name title
	*/
	public $title;
	
	/**
	 * url when we click on the treenode
	 *
	 *@name url
	*/
	public $url;
	
	/**
	 * class-name of the tree-node
	 *
	 *@name treeclass
	 *@access public
	*/
	public $treeclass;
	
	/**
	 * icon of this treenode
	 *
	 *@name icon
	*/
	public $icon;
	
	/**
	 * bubbles with different colors
	 * for example: Modified, Submitted
	 *
	 *@name bubbles
	 *@name protected
	*/
	protected $bubbles;
	
	/**
	 * children
	 *
	 *@name children
	*/
	protected $children = array();
	
	/**
	 * force on state for children:
	 * - open or closed
	 * null for cookie-based
	 *
	 *@name childState
	*/
	public $childState;
	
	/**
	 * generates a new treenode
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($nodeid, $recordid, $title, $url, $class_name, $icon = null) {
		$this->nodeid = $nodeid;
		$this->recordid = $recordid;
		$this->title = $title;
		$this->url = $url;
		$this->treeclass = $class_name;
		if(isset($icon) && $icon && $icon = ClassInfo::findFile($icon)) {
			$this->icon = $icon;
		} else {
			if(ClassInfo::hasStatic($class_name, "icon") && $icon = ClassInfo::findFile(ClassInfo::getStatic($class_name, "icon")))
				$this->icon = $icon;
		}
	}
	
	/**
	 * returns the icon
	 *
	 *@name getIcon
	 *@access public
	*/
	public function getIcon() {
		return $this->icon;
	}
	
	/**
	 * sets an icon
	 *
	 *@name setIcon
	 *@access public
	*/
	public function setIcon($icon) {
		if($icon && $icon = ClassInfo::findFile($icon)) {
			$this->icon = $icon;
			return true;
		}
		
		return false;
	}
	
	/**
	 * adds a bubble
	 *
	 *@name addBubble
	 *@access public
	 *@param text
	 *@param color: green, yellow, red, blue, grey, orange
	*/
	public function addBubble($text, $color = "blue") {
		switch($color) {
			case "green":
				$bg = "#9dffa2";
				$color = "#098e00";
			break;
			case "red":
				$bg = "#ff7f74";
				$color = "#8e0812";
			break;
			default:
				$bg = "#d4f1ff";
				$color = "#005888";
			break;
		}
		
		$this->bubbles[md5($text)] = array("text" => $text, "bg" => $bg, "color" => $color);
	}
	
	/**
	 * removes a bubble
	 *
	 *@name removeBubble
	 *@access public
	 *@param text
	*/
	public function removeBubble($text) {
		unset($this->bubbles[md5($text)]);
	}
	
	/**
	 * sets children loading via Ajax
	 *
	 *@name setChildrenAjax
	 *@access public
	*/
	public function setChildrenAjax($bool = true) {
		if($bool && $this->children == array())
			$this->children = "ajax";
		else if(!$bool && $this->children == "ajax")
			$this->children = array();
	}
	
	/**
	 * adds a child
	 *
	 *@name addChild
	 *@access public
	*/
	public function addChild(TreeNode $child) {
		if(is_array($this->children)) {
			$this->children[$child->nodeid] = $child;
		} else {
			throwError(6, "PHP-Error", "Could not add Child to node, because seems not as childable, maybe set to ajax?");
		}
	}
	
	/**
	 * removes a child
	 *
	 *@name removeChild
	 *@access public
	*/
	public function removeChild(TreeNode $child) {
		if(is_array($this->children)) {
			unset($this->children[$child->nodeid]);
		} else {
			throwError(6, "PHP-Error", "Could not remove Child from node, because seems not as childable, maybe set to ajax?");
		}
	}
	
	/**
	 * gets all children
	 *
	 *@name getChildren
	*/
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * sets children collapsed
	 *
	 *@name setCollapsed
	 *@access public
	*/
	public function setCollapsed() {
		$this->childState = "collapsed";
	}
	
	/**
	 * sets children expanded
	 *
	 *@name setCollapsed
	 *@access public
	*/
	public function setExpanded() {
		$this->childState = "expanded";
	}
	
	/**
	 * sets children to cookie-based
	 *
	 *@name setCookieBased
	 *@access public
	*/
	public function setCookieBased() {
		$this->childState = null;
	}
	
	/**
	 * returns the record
	 *
	 *@name record
	*/
	public function record() {
		return DataObject::Get_by_id($this->treeclass, $this->recordid);
	}
}