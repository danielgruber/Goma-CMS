<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */

class LeftAndMain_TreeRenderer extends TreeRenderer {

	/**
	 * generates the treerenderer with a given tree.
	 *
	 * @param 	array|TreeNode $tree tree
	 * @param	callback $linkCallback a function which should generate text or a HTMLNode for the link.
	 * @param	callback $actionCallback a function which should generate the subaction of a tree-item accessable via right-click or by holding the option-key.
	 * @param 	string $namespace namespace of the leftandmain-panel
	*/
	public function __construct($tree, $linkCallback = null, $actionCallback = null, $namespace = null) {
		$this->namespace = $namespace;
		parent::__construct($tree, $linkCallback, $actionCallback);
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
		
		// add-button
		$addNode = new TreeNode("page_" . $parentID . "_addButton", 0, lang("PAGE_CREATE"), "");
		$addNode->setChildren(array());
		$addNode->parentid = $parentID;
		$addNode->setLinkCallback(array($this, "createAddLink"));
		$addNode->addClass("hidden");
		$addNode->addClass("action");
		
		$html .= $this->renderChild($addNode);
		
		return $html;
	}
	
	/**
	 * generates the add-link.
	*/
	public function createAddLink($child, $bubbles) {
		return new HTMLNode("a", array("href" => $this->namespace . "/add" . URLEND . "?parentid=" . $child->parentid, "class" => "node-area"), array(
			new HTMLNode("img", array("src" => "images/icons/modernui/dark/24x24/appbar.add.png")),
			$child->title,
			$bubbles
		));
	}
}