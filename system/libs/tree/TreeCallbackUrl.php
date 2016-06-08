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
		"model/\$model!/\$parent!/\$renderer!"	=> "handleByModel",
		"key/\$key!/\$renderer!"					=> "handleByKey"
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
	 * @param TreeNode $treenode the treenode for generating the URL
	 * @param TreeRenderer $renderer
	 * @return string
	 */
	static function generate_tree_url(TreeNode $treenode, TreeRenderer $renderer) {
		
		$renderer->tree = null;
		$renderKey = md5(serialize($renderer));
		GlobalSessionManager::globalSession()->set("tree_renderer_" . $renderKey, $renderer);
		
		if($treenode->getChildCallback() == null && isset($treenode->model) && gObject::method_exists($treenode->model->dataclass, "build_tree")) {
			return "treecallback/model/" . $treenode->model->dataclass . "/" . $treenode->model->recordid . "/" . $renderKey . URLEND  . "?redirect=" . urlencode(getRedirect());
		} else if(ClassInfo::exists($treenode->treeclass) && isset($treenode->RecordID) && gObject::method_exists($treenode->treeclass, "build_tree")) {
			return "treecallback/model/" . $treenode->treeclass . "/" . $treenode->RecordID . "/" . $renderKey . URLEND  . "?redirect=" . urlencode(getRedirect());
		} else if($treenode->getChildCallback() != null) {
			$key = md5(serialize($treenode));
			GlobalSessionManager::globalSession()->set("tree_node_" . $key, $treenode);
			return "treecallback/key/" . $key . "/" . $renderKey . URLEND . "?redirect=" . urlencode(getRedirect());
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
		$renderer = GlobalSessionManager::globalSession()->get("tree_renderer_" . $this->getParam("renderer"));
			
		if(Core::is_ajax()) {
			
			if(ClassInfo::exists($model) && gObject::method_exists($model, "build_tree")) {
				$record = DataObject::get_by_id($model, $parent);
				$tree = call_user_func_array(array($model, "build_tree"), array($record));
				
				$renderer->tree = $tree;
				return $renderer->render(false, $parent);
			}
			
			return false;
		} else {
			if($_COOKIE["tree_" .  $model . "_" . $parent] == 1)
				setcookie("tree_" . $model . "_" . $parent, 0);
			else
				setcookie("tree_" . $model . "_" . $parent, 1);

			return GomaResponse::redirect(getRedirect());
		}
	}
	
	/**
	 * handles data by key.
	*/
	public function handleByKey() {
		$key = $this->getParam("key");
		$renderer = GlobalSessionManager::globalSession()->get("tree_renderer_" . $this->getParam("renderer"));

		if(GlobalSessionManager::globalSession()->hasKey("tree_node_" . $key)) {
			/** @var TreeNode $node */
			$node = GlobalSessionManager::globalSession()->get("tree_node_" . $key);
			$tree = $node->forceChildren();

			if($this->request->is_ajax()) {
				$renderer->tree = $tree;
				return $renderer->render(false, $node->recordid);
			} else {
				if($_COOKIE["tree_" .  $node->treeclass . "_" . $node->recordid] == 1)
					setcookie("tree_" .  $node->treeclass . "_" . $node->recordid, 0, 0, '/');
				else
					setcookie("tree_" .  $node->treeclass . "_" . $node->recordid, 1, 0, "/");

				return GomaResponse::redirect(getRedirect());
			}
		}
		
		return false;
	}
}