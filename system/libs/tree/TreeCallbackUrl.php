<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */
class TreeCallbackUrl extends RequestHandler {
	/**
	 * url-handler.
	*/
	public $url_handlers = array(
		"model/\$model!/\$parent!"	=> "handleByModel",
		"key/\$key!"				=> "handleByKey"
	);
	
	/**
	 * allow all actions. Permissions are implemented within the methods.
	*/
	public $allowed_actions = array(
		"handleByModel",
		"handleByKey"
	);
	
	/**
	 * generates a tree-callback-URL for a given TreeNode.
	 *
	 * @param	TreeNode $node the treenode for generating the URL
	*/
	static function generate_tree_url(TreeNode $treenode) {
		if(isset($treenode->model) && Object::method_exists($treenode->model->dataclass, "build_tree")) {
			return "treecallback/model/" . $treenode->model->dataclass . "/" . $treenode->model->recordid . URLEND;
		} else if(ClassInfo::exists($treenode->treeclass) && isset($treenode->RecordID) && Object::method_exists($treenode->treeclass, "build_tree")) {
			return "treecallback/model/" . $treenode->treeclass . "/" . $treenode->RecordID . URLEND;
		} else if($treenode->getChildCallback() != null) {
			$key = md5(serialize($treenode));
			session_store("tree_node_" . $key, $treenode);
			return "treecallback/key/" . $key . URLEND;
		} else {
			throw new LogicException("Could not generate URL from TreeNode. You are required to set the child-callback through TreeNode::setChildCallback(\$callback)");
		}
	}
	
	/**
	 * handles data by model.
	*/
	public function handleByModel() {
		$model = $this->getParam("model");
		$parent = $this->getParam("parent");
		
		if(ClassInfo::exists($model) && Object::method_exists($model, "build_tree")) {
			$record = DataObject::get_by_id($model, $parent);
			$tree = call_user_func_array(array($model, "build_tree"), array($record));
			
			if(isset($_GET["renderer"]) && ClassInfo::exists($_GET["renderer"]) && is_subclass_of($_GET["renderer"], "TreeRenderer")) {
				$renderClass = $_GET["renderer"];
			} else {
				$renderClass = "TreeRenderer";
			}
			
			$renderer = new $renderClass($tree);
			return $renderer->render();
		}
		
		return false;
	}
	
	/**
	 * handles data by key.
	*/
	public function handleByKey() {
		$key = $this->getParam("key");
		
		if(session_store_exists("tree_node_" . $key)) {
			$tree = session_restore("tree_node_" . $key)->forceChildren();
			
			if(isset($_GET["renderer"]) && ClassInfo::exists($_GET["renderer"]) && is_subclass_of($_GET["renderer"], "TreeRenderer")) {
				$renderClass = $_GET["renderer"];
			} else {
				$renderClass = "TreeRenderer";
			}
			
			$renderer = new $renderClass($tree);
			return $renderer->render();
		}
		
		return false;
	}
}