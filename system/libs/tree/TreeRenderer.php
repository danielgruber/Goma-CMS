<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */

class TreeRenderer extends gObject {
	/**
	 * the current tree.
	 * @var TreeNode|null
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
	 * @var bool
	 */
	static $isFirefox8;
	
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
	 * @param    int $nodeID nodeid
	 *
	 * @return $this
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
	 * @param    int $nodeID nodeid
	 *
	 * @return $this
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
	 * @param bool $includeUL
	 * @param int $parentID
	 * @return String
	 */
	public function render($includeUL = false, $parentID = 0) {
		Resources::add("tree.css", "css", "tpl");
		Resources::add("system/libs/tree/gTree.js", "js", "tpl");
		
		Resources::add("system/libs/thirdparty/jquery-contextmenu/src/jquery.contextMenu.css", "css", "tpl");
		Resources::add("system/libs/thirdparty/jquery-contextmenu/src/jquery.contextMenu.js", "js", "tpl");
		
		if(is_array($this->tree)) {
			$html = "\n";
			$html .= $this->renderSubChildren($this->tree, $parentID);
			
			if($includeUL)
				return '<ul class="goma-tree '.$this->classname.'">'.$html.'</ul>';
				
			return $html;
		} else {
			return $this->renderChild($this->tree);
		}
	}

	/**
	 * method to render all subchildren of given array of children.
	 *
	 * @name    renderSubChildren
	 *
	 * @access    protected
	 * @return string
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
		
		$node = new HTMLNode("li", array("id" => "treenode_" . $this->classname . "_" . $child->nodeid, "class" => "tree-node"));
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
			$link->addClass("clearfix");
		} else {
			$text = $child->title;
			$wrapper->html(array(
				new HTMLNode("span", array("class" => "img-holder"), new HTMLNode("img", array("src" => $child->icon))),
				new HTMLNode("span", array("class" => "text-holder"), $text),
				$this->renderBubbles($child->bubbles())
			));
			$wrapper->addClass("node-area");
			$wrapper->addClass("clearfix");
		}

		if(is_callable($this->actionCallback)) {
			$menu = call_user_func_array($this->actionCallback, array($child));
			if(is_array($menu)) {
				$menuNode = new HTMLNode("menu", array("id" => "menu_" . $child->nodeid, "type" => "context", "style" => "display:none;"));
				
				$this->renderContextMenu($menu, $menuNode);
				
				$wrapper->append($menuNode);
				$wrapper->contextmenu = "menu_" . $child->nodeid;
			}
		}
		
		foreach($child->getClasses() as $class)
			$node->addClass($class);
		
		$wrapper->attr("title", convert::raw2text($child->title));
		
		// render children
		if((isset($this->expandedIDs[$child->nodeid]) && $this->expandedIDs[$child->nodeid]) || (isset($this->expandedIDs[$child->recordid]) && $this->expandedIDs[$child->recordid]) || $child->isExpanded() || (isset($_COOKIE["tree_" . $child->treeclass . "_" . $child->recordid]) && $_COOKIE["tree_" . $child->treeclass . "_" . $child->recordid] == 1)) {
			if(count($child->forceChildren()) > 0) {
				// children should be shown
				$node->addClass("expanded");
				$node->prepend(new HTMLNode("span", array("class" => "hitarea expanded", "data-cookie" => "tree_" . $child->treeclass . "_" . $child->recordid), new HTMLNode("a", array("href" => TreeCallbackURL::generate_tree_url($child, $this)), new HTMLNode("span"))));
				$node->append($ul = new HTMLNode("ul", array("class" => "expanded")));
				$ul->html($this->renderSubChildren($child->forceChildren(), $child->recordid));
			}
			
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
	 * renders a subMenu for the contextMenu.
	 *
	 * @param array $items
	 * @param HTMLNode $menu
	 */
	protected function renderContextMenu($items, &$menu) {
		if(!isset(self::$isFirefox8)) {
			if(preg_match('/firefox\/([0-9]+)/i', $_SERVER["HTTP_USER_AGENT"], $bMatches)) {
				if($bMatches[1] >= 8) {
					self::$isFirefox8 = true;
				} else {
					self::$isFirefox8 = false;
				}
			} else {
				self::$isFirefox8 = false;
			}
		}
		
		$pointTag = (self::$isFirefox8) ? "menuitem" : "command";
		
		foreach($items as $menuAction) {
			if(is_array($menuAction)) {
				if(isset($menuAction["children"])) {
					$menu->append($subMenu = new HTMLNode("menu", array("label" => $menuAction["label"])));
					$this->renderContextMenu($menuAction["children"], $subMenu);
				} else {
					$menu->append($point = new HTMLNode($pointTag, array("label" => $menuAction["label"])));
					
					if(isset($menuAction["onclick"])) {
						$point->onclick = $menuAction["onclick"];
					} else if(isset($menuAction["href"])) {
						$point->onclick = 'location.href = '.var_export($menuAction["href"], true).';';
					} else if(isset($menuAction["ajaxhref"])) {
						$point->onclick = 'goma.ui.ajax(undefined, {pushToHistory: true, url: '.var_export($menuAction["ajaxhref"], true).'});';
					}
					
					if(isset($menuAction["disabled"])) {
						$point->disabled = "disabled";
					}
					
					if(isset($menuAction["type"])) {
						$point->type = $menuAction["type"];
					}
					
					if(isset($menuAction["checked"])) {
						$point->checked = $menuAction["checked"];
					}
					
					if(isset($menuAction["radiogroup"])) {
						$point->radiogroup = $menuAction["radiogroup"];
					}
					
					if(isset($menuAction["icon"])) {
						$point->icon = BASE_URI . $menuAction["icon"];
					}
				}
			} else if($menuAction == "hr") {
				$menu->append(new HTMLNode("hr"));
			} else {
				continue;
			}
		}
	}

	/**
	 * renders the bubbles.
	 * @param array $bubbles
	 * @return HTMLNode
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
	 * @param TreeNode|string $node
	 */
	public function mark($node) {
		if(is_object($node))
			$this->marked[$node->nodeid] = true;
		else
			$this->marked[$node] = true;
	}

	/**
	 * unmarks a node.
	 * @param TreeNode|string $node
	 */
	public function unmark($node) {
		if(is_object($node))
			unset($this->marked[$node->nodeid]);
		else
			unset($this->marked[$node]);
	}
}
