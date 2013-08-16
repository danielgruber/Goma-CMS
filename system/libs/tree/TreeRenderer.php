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
	 * the callback which is called when the link should be generated.
	*/
	protected $linkCallback;
	
	/**
	 * this is the callback which should generate sub-actions.
	*/
	protected $actionCallback;
	
	/**
	 * marked nodes.
	*/
	public $marked = array();
	
	/**
	 * generates the treerenderer with a given tree.
	 *
	 * @param 	array|TreeNode $tree tree
	 * @param	callback $linkCallback a function which should generate text or a HTMLNode for the link.
	 * @param	callback $actionCallback a function which should generate the subaction of a tree-item accessable via right-click or by holding the option-key.
	*/
	public function __construct($tree, $linkCallback = null, $actionCallback = null) {
		$this->tree = $tree;
		$this->actionCallback = $actionCallback;
		$this->linkCallback = $linkCallback;
		
		parent::__construct();
	}
	
	/**
	 * sets a given node expanded.
	 *
	 * @param	int $nodeID nodeid
	*/
	public function setExpanded($nodeID) {
		if(is_array($nodeID))
			foreach($nodeID as $id)
				$this->expandedIDs[$id] = true;
		else
			$this->expandedIDs[$nodeID] = true;
		
		return $this;
	}
	
	/**
	 * sets a given node collapsed.
	 *
	 * @param	int $nodeID nodeid
	*/
	public function setCollapsed($nodeID) {
		if(is_array($nodeID))
			foreach($nodeID as $id)
				$this->expandedIDs[$id] = false;
		else
			$this->expandedIDs[$nodeID] = false;
		
		return $this;
	}
	
	/**
	 * sets the actionCallback, which is called to generate actions for right-click.
	*/
	public function setActionCallback($callback) {
		$this->actionCallback = $callback;
		return $this;
	}
	
	/**
	 * returns the current actionCallback.
	*/
	public function ActionCallback() {
		return $this->actionCallback;
	}
	
	/**
	 * sets the linkCallback, which should generate the link of the tree-item.
	*/
	public function setLinkCallback($callback) {
		$this->linkCallback = $callback;
		return $this;
	}
	
	/**
	 * returns the current linkCallback.
	*/
	public function LinkCallback() {
		return $this->linkCallback;
	}
	
	/**
	 * renders the tree.
	 *
	 * @return 	String
	*/
	public function render($includeUL = false, $parentID = 0) {
		
		Resources::add("tree.css", "css", "tpl");
		Resources::add("system/libs/tree/gTree.js", "js", "tpl");
		
		if(is_array($this->tree)) {
			$html = "\n";
			$html .= $this->renderSubChildren($this->tree, $parentID);
			
			if($includeUL)
				return '<ul class="goma-tree '.$this->class.'">'.$html.'</ul>';
				
			return $html;
		} else {
			return $this->renderChild($this->tree);
		}
	}
	
	/**
	 * method to render all subchildren of given array of children.
	 *
	 * @name	renderSubChildren
	 * @access	protected
	*/
	protected function renderSubChildren($nodes, $parentID = 0) {
		$html = "";
		foreach($nodes as $node) {
			$html .= $this->renderChild($node);
		}
		return $html;
	}
	
	/**
	 * renders a given node.
	 *
	 * @param	TreeNode $child
	 * @return	String
	*/
	protected function renderChild(TreeNode $child) {
		
		$node = new HTMLNode("li", array("id" => "treenode_" . $this->class . "_" . $child->nodeid, "class" => "tree-node"));
		$node->append($wrapper = new HTMLNode("span", array("class" => "tree-wrapper ")));
		
		$node->attr("data-nodeid", $child->nodeid);
		$node->attr("data-recordid", $child->recordid);
		
		if(isset($this->marked[$child->nodeid]) || isset($this->marked[$child->recordid])) {
			$node->addClass("marked");
		}
		
		// now generate link through link-callback.
		if($child->linkCallback()) {
			$link = call_user_func_array($child->linkCallback(), array($child, $this->renderBubbles($child->bubbles())));
			$wrapper->html($link);
		} else
		if($this->linkCallback) {
			$link = call_user_func_array($this->linkCallback, array($child, $this->renderBubbles($child->bubbles())));
			$wrapper->html($link);
		} else {
			$text = $child->title;
			$wrapper->html(array(
				new HTMLNode("img", array("src" => $child->icon)),
				$text,
				$this->renderBubbles($child->bubbles())
			));
			$wrapper->addClass("node-area");
		}
		
		foreach($child->getClasses() as $class)
			$node->addClass($class);
		
		$wrapper->attr("title", convert::raw2text($child->title));
		
		// render children
		if((isset($this->expandedIDs[$child->nodeid]) && $this->expandedIDs[$child->nodeid]) || $child->isExpanded() || (isset($_COOKIE["tree_" . $child->treeclass . "_" . $child->recordid]) && $_COOKIE["tree_" . $child->treeclass . "_" . $child->recordid] == 1)) {
			// children should be shown
			$node->addClass("expanded");
			$node->prepend(new HTMLNode("span", array("class" => "hitarea expanded", "data-cookie" => "tree_" . $child->treeclass . "_" . $child->recordid), new HTMLNode("a", array("href" => TreeCallbackURL::generate_tree_url($child, $this)), new HTMLNode("span"))));
			$node->append($ul = new HTMLNode("ul", array("class" => "expanded")));
			$ul->html($this->renderSubChildren($child->forceChildren(), $child->recordid));
			
			
		} else if(is_callable($child->getChildCallback())) {
			// children via ajax
			$node->prepend(new HTMLNode("span", array("class" => "hitarea collapsed", "data-cookie" => "tree_" . $child->treeclass . "_" . $child->recordid), new HTMLNode("a", array("href" => TreeCallbackURL::generate_tree_url($child, $this)), new HTMLNode("span"))));
			$node->addClass("collapsed");
			
			
		} else if($child->children()) {
			// children available
			$node->addClass("collapsed");
			$node->prepend(new HTMLNode("span", array("class" => "hitarea collapsed", "data-cookie" => "tree_" . $child->treeclass . "_" . $child->recordid), new HTMLNode("a", array("href" => TreeCallbackURL::generate_tree_url($child, $this)), new HTMLNode("span"))));
			$node->append($ul = new HTMLNode("ul", array("class" => "collapsed")));
			$ul->html($this->renderSubChildren($child->Children(), $child->recordid));
			
		} else {
			// no children
		}
		
		return $node->render();
	}
	
	/**
	 * renders the bubbles.
	*/
	public function renderBubbles($bubbles) {
		$node = new HTMLNode("span", array("class" => "tree-bubbles"));
		foreach($bubbles as $bubble) {
			$node->append(new HTMLNode("span", array("class" => "tree-bubble ".$bubble["color"]), $bubble["text"]));
		}
		return $node;
	}
	
	/**
	 * marks a node.
	*/
	public function mark($node) {
		if(is_object($node))
			$this->marked[$node->nodeid] = true;
		else
			$this->marked[$node] = true;
	}
	
	/**
	 * unmarks a node.
	*/
	public function unmark($node) {
		if(is_object($node))
			unset($this->marked[$node->nodeid]);
		else
			unset($this->marked[$node]);
	}
}