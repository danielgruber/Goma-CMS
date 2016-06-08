<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */

class TreeNode extends ArrayList {

	/**
	 * colors for Tree-Node.
	 */
	const COLOR_GREEN = "green";
	const COLOR_YELLOW = "yellow";
	const COLOR_RED = "red";
	const COLOR_BLUE = "blue";
	const COLOR_GREY = "grey";
	const COLOR_ORANGE = "orange";
	const COLOR_PURPLE = "purple";

	/**
	 * unique id of the node.
	 *
	 * @var string
	*/
	public $nodeid;
	
	/**
	 * record-id of the node. this should be unique, too, but is not absolutily required.
	 *
	 *@var int
	*/
	public $recordid;
	
	/**
	 * text of the node.
	*/
	public $title;
	
	/**
	 * class-name of the tree-node.
	*/
	public $treeclass;
	
	/**
	 * icon of this tree-node.
	*/
	public $icon;
	
	/**
	 * defines the behaviour of children.
	 *
	 * force on state for children:
	 * - open or closed
	 * null for automatic
	*/
	public $childState;
	
	/**
	 * you can put the model-connection here.
	 *
	 * @param 	object
	*/
	public $model;
	
	/**
	 * bubbles with different colors.
	 * for example: Modified, Submitted
	*/
	protected $bubbles = array();
	
	/**
	 * stores the callback, which is called when children are needed.
	*/
	protected $childCallback;

	/**
	 * @var bool
	 */
	protected $childCallbackFetched = false;
	
	/**
	 * child-params.
	*/
	protected $childParams;
	
	/**
	 * html-classes.
	*/
	protected $htmlClasses = array();
	
	/**
	 * the callback over which this node is rendered.
	*/
	protected $linkCallback;

	/**
	 * generates a new treenode.
	 *
	 * @param    string $nodeid id of this node
	 * @param 	 int|null $recordid
	 * @param    int $recordid
	 * @param    string $title text of this node
	 * @param    string $class_name class-name for this node
	 * @param    null|string $icon
	 */
	public function __construct($nodeid = null, $recordid = null, $title = null, $class_name = null, $icon = null) {
		
		parent::__construct(null);
		
		if(!isset($nodeid)) {
			return false;
		}
		
		$this->nodeid = $nodeid;
		$this->recordid = $recordid;
		$this->title = $title;
		$this->treeclass = $class_name;
		if(isset($icon) && $icon && $icon = ClassInfo::findFile($icon, $class_name)) {
			$this->icon = $icon;
		} else if(strtolower($class_name) != "treeholder" && !empty($class_name)) {
			$this->icon = ClassInfo::getClassIcon($class_name);
		}
	}

	/**
	 * returns the icon
	 *
	 * @return string|null
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * sets an icon
	 *
	 * @return bool
	 */
	public function setIcon($icon) {
		if($icon && $icon = ClassInfo::findFile($icon, $this->treeclass)) {
			$this->icon = $icon;
			return true;
		}
		
		return false;
	}

	/**
	 * adds a bubble
	 *
	 * @param string $text
	 * @param string $color : green, yellow, red, blue, grey, orange, purple
	 * @return $this
	 */
	public function addBubble($text, $color = self::COLOR_BLUE) {
		
		$this->bubbles[md5($text)] = array("text" => $text, "color" => $color);
		return $this;
	}

	/**
	 * removes a bubble
	 *
	 * @param string $text
	 * @return $this
	 */
	public function removeBubble($text) {
		unset($this->bubbles[md5($text)]);
		return $this;
	}

	/**
	 * returns all bubbles
	 *
	 * @return array
	 */
	public function bubbles() {
		return $this->bubbles;
	}

	/**
	 * sets children
	 * @param TreeNode[] $children
	 */
	public function setChildren($children) {
		// validate and stack it in
		$this->items = array();
		foreach($children as $child) {
			$this->push($child);
		}
	}

	/**
	 * sets lazy-loading-child-callback.
	 *
	 * @param callback $callback
	 * @param array $params
	 */
	public function setChildCallback($callback, $params = array()) {
		if(is_callable($callback)) {
			$this->childCallback = $callback;
			$this->childParams = $params;
			$this->childCallbackFetched = false;
		} else
			throw new LogicException("TreeNode::setChildCallback: first argument must be a valid callback.");
	}
	
	/**
	 * returns current child-callback.
	*/
	public function getChildCallback() {
		return $this->childCallback;
	}

	/**
	 * adds a child
	 * @param TreeNode $child
	 */
	public function addChild($child) {
		if(!$this->childCallback) {
			$this->push($child);
		} else {
			throw new LogicException("This is a lazy loading TreeNode, you cannot add a child.");
		}
	}

	/**
	 * removes a child
	 * @param TreeNode $child
	 */
	public function removeChild($child) {
		if(!$this->childCallback) {
			$this->remove($child);
		} else {
			throw new LogicException("This is a lazy loading TreeNode, you cannot remove a child.");
		}
	}
	
	/**
	 * gets all children as ArrayList.
	 *
	 * @return TreeNode[]
	*/
	public function Children() {
		return $this->items;
	}
	
	/**
	 * gets all children as Array.
	 * @return TreeNode[]
	*/
	public function getChildren() {
		return $this->children();
	}
	
	/**
	 * forces to get children
	 * it will call callback if not available
	 * @return TreeNode[]
	*/ 
	public function forceChildren() {
		if($this->childCallback) {
			if($this->childCallbackFetched) {
				return $this->children();
			} else {
				$this->items = call_user_func_array($this->childCallback, array($this, (array) $this->childParams));
				if(!is_array($this->items)) {
					throw new InvalidArgumentException("Childcallback is required to give back an array of TreeNodes.");
				}
				$this->childCallbackFetched = true;

				return $this->children();
			}
		} else {
			return $this->Children();
		}
	}
	
	/**
	 * sets children collapsed
	*/
	public function setCollapsed() {
		$this->childState = "collapsed";
		return $this;
	}
	
	/**
	 * returns if is Collapsed
	*/
	public function isCollapsed() {
		return ($this->childState == "collapsed");
	}

	/**
	 * sets children expanded
	 *
	 * @name setCollapsed
	 * @access public
	 * @return $this
	 */
	public function setExpanded() {
		$this->childState = "expanded";
		return $this;
	}
	
	/**
	 * returns if is Expanded
	*/
	public function isExpanded() {
		return ($this->childState == "expanded");
	}
	
	/**
	 * sets children to cookie-based
	*/
	public function setCookieBased() {
		$this->childState = null;
	}
	
	/**
	 * returns the record
	*/
	public function record() {
		if(isset($this->model))
			return $this->model;
		
		$this->model = DataObject::get_by_id($this->treeclass, $this->recordid);
		return $this->model;
	}

	/**
	 * adds a html-class
	 * @param string $class
	 * @return $this
	 */
	public function addClass($class) {
		$this->htmlClasses[$class] = $class;
		return $this;
	}

	/**
	 * rempves a html-class
	 * @param string $class
	 * @return $this
	 */
	public function removeClass($class) {
		unset($this->htmlClasses[$class]);
		return $this;
	}
	
	/**
	 * returns the HTML-Classes
	 */
	public function getClasses() {
		return $this->htmlClasses;
	}

	/**
	 * sets the linkCallback, which should generate the link of the tree-item.
	 * @param $callback
	 * @return $this
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
}
