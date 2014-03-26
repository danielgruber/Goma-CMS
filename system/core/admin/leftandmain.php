<?php defined("IN_GOMA") OR die();

/**
 * A simple two column admin-panel.
 *
 * @package     Goma\Admin\LeftAndMain
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.2.6
 */
 
class LeftAndMain extends AdminItem {
	
	/**
	 * the base template of the view
	 *
	 *@name baseTemplate
	 *@access public
	*/
	public $baseTemplate = "admin/leftandmain.html";
	
	/**
	 * defines the url-handlers
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		"updateTree/\$search"			=> "updateTree",
		"edit/\$id!/\$model"			=> "cms_edit",
		"del/\$id!/\$model"				=> "cms_del",
		"add/\$model"					=> "cms_add",
		"versions"						=> "versions"
	);
	
	/**
	 * defines the allowed actions
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array(
		"cms_edit", "cms_add", "cms_del", "updateTree", "savesort", "versions"
	);
	
	/**
	 * this var defines the tree-class
	 *
	 *@name tree_class
	 *@access public
	*/
	public $tree_class = "";
	
	/**
	 * marked node
	 *
	 *@name marked
	 *@access public
	*/
	public $marked = 0;
	
	/**
	 * just one time in the request resume should be called
	 *
	 *@name resumeNum
	*/
	static public $resumeNum = 0;
	
	/**
	 * sort-field
	 *
	 *@name sort_field
	 *@access protected
	*/
	protected $sort_field;
	
	/**
	 * render-class.
	*/
	static $render_class = "LeftAndMain_TreeRenderer";
	
	/**
	 * gets the title of the root node
	 *
	 *@name getRootNode
	 *@access public
	*/
	public function getRootNode() {
		return parse_lang($this->root_node);
	}
	
	/**
	 * removes Resume-Cache
	 *
	 *@name removeResume
	 *@access public
	*/
	public function removeResume() {
		unset($_SESSION["goma_resume_".$this->classname.""]);
	}
	
	/**
	 * generates the options for the create-select-field
	 *
	 *@name CreateOptions
	 *@access public
	*/
	public function createOptions() {
		$options = array();
		foreach($this->models as $model) {
			$options[$model] = ClassInfo::getClassTitle($model);
		}
		return $options;
	}
	
	/**
	 * inserts the data in the leftandmain-template
	 *
	 *@name serve
	 *@access public
	*/
	public function serve($content) {
		if(Core::is_ajax()) {
			HTTPResponse::setBody($content);
			HTTPResponse::output();
			exit;
		}
		
		// add resources
		Resources::add("system/core/admin/leftandmain.js", "js", "tpl");
		
		if(isset($this->sort_field)) {
			Resources::addData("var LaMsort = true;");
		} else {
			Resources::addData("var LaMsort = false;");
		}
		
		$search = isset($_GET["searchtree"]) ? text::protect($_GET["searchtree"]) : "";
		

		Resources::addData("var adminURI = '".$this->adminURI()."'; var marked_node = '".$this->marked."';");
		
		$data = $this->ModelInst();
		
		if(defined("LAM_CMS_ADD"))
			$this->ModelInst()->addmode = 1;
		
		$output = $data->customise(array("CONTENT"	=> $content, "activeAdd" => $this->getParam("model"), "SITETREE" => $this->createTree($search), "searchtree" => $search, "ROOT_NODE" => $this->getRootNode()))->renderWith($this->baseTemplate);
		
		$_SESSION[$this->classname . "_LaM_marked"] = $this->marked;
		
		// parent-serve
		return parent::serve($output);
	}
	
	
	/**
	 * generates the tree-links.
	*/
	public function generateTreeLink($child, $bubbles) {
		return new HTMLNode("a", array("href" => $this->originalNamespace . "/record/" . $child->recordid . "/edit" . URLEND, "class" => "node-area"), array(
			new HTMLNode("span", array("class" => "img-holder"), new HTMLNode("img", array("src" => $child->icon))),
			new HTMLNode("span", array("class" => "text-holder"), $child->title),
			$bubbles
		));
	}
	
	/**
	 * generates the context-menu.
	*/
	public function generateContextMenu($child) {
		$data = array();
		if($child->treeclass) {
			
			$data = array(
				array(
					"icon"		=> "images/16x16/edit.png",
					"label" 	=> lang("edit"),
					"onclick"	=> "LoadTreeItem(".$child->recordid.");"
				),
				array(
					"icon"		=> "images/16x16/del.png",
					"label" 	=> lang("delete"),
					"ajaxhref"	=> $this->originalNamespace . "/record/" . $child->recordid . "/delete" . URLEND
				)
			);
		}
		
		$this->callExtending("generateContextMenu", $data);
		
		return $data;
	}
	
	/**
	 * creates the Tree
	 *
	 *@name createTree
	 *@access public
	*/
	public function createTree($search = "", $marked = null) {
		$tree_class = $this->tree_class;
		if($tree_class == "") {
			throw new LogicException("Failed to load Tree-Class. Please define \$tree_class in ".$this->classname);
		}
		
		if(!Object::method_exists($tree_class, "build_tree")) {
			throw new LogicException("Tree-Class does not have a method build_tree. Maybe you have to update your version of goma?");
		}
		
		$tree = call_user_func_array(array($tree_class, "build_tree"), array(0, array("version" => "state", "search" => $search)));
		$treeRenderer = new self::$render_class($tree, null, null, $this->originalNamespace, $this);
		$treeRenderer->setLinkCallback(array($this, "generateTreeLink"));
		$treeRenderer->setActionCallback(array($this, "generateContextMenu"));
		$treeRenderer->mark($this->getParam("id"));
		
		// check for logical opened tree-items.
		if(isset($_GET["edit_id"])) {
			// here we check for Ajax-Opening. It is given to the leftandmain-js-api.
			if($current = DataObject::get_versioned("pages", "state", array("id" => $_GET["edit_id"]))->first()) {
				$treeRenderer->setExpanded($current->id);
				while($current->parent) {
					$current = $current->parent;
					$treeRenderer->setExpanded($current->id);
				}
			}
		} else
		
		// here we check for complete generated pages.
		if($this->getParam("id")) {
			if($current = DataObject::get_versioned("pages", "state", array("id" => $this->getParam("id")))->first()) {
				$treeRenderer->setExpanded($current->id);
				while($current->parent) {
					$current = $current->parent;
					$treeRenderer->setExpanded($current->id);
				}
			}
		}
		
		return $treeRenderer->render(true);
	}
	/**
	 * gets updated data of tree for searching or normal things
	 *
	 *@name updateTree
	 *@access public
	*/
	public function updateTree() {
		
		$this->marked = $this->getParam("marked");
		$search = $this->getParam("search");
		HTTPResponse::setBody($this->createTree($search), isset($_SESSION[$this->classname . "_LaM_marked"]) ? $_SESSION[$this->classname . "_LaM_marked"] : 0);
		HTTPResponse::output();
		exit;
	}
	
	/**
	 * Actions of editing 
	*/
	
	/**
	 * saves data for editing a site via ajax
	 *
	 *@name ajaxSave
	 *@access public
	 *@param array - data
	 *@param object - response
	*/
	public function ajaxSave($data, $response) {
		if($model = $this->save($data)) {
			// notify the user
			Notification::notify($model->classname, lang("SUCCESSFUL_SAVED", "The data was successfully written!"), lang("SAVED"));
			
			$response->exec("var href = '".BASE_URI . $this->adminURI()."record/".$model->id."/edit".URLEND."'; if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) { if(location.href == href) location.reload(); else location.href = href; } else { reloadTree(function(){ goma.ui.ajax(undefined, {url: href, pushToHistory: true}); }, ".var_export($model["id"], true)."); }");
			return $response;
		} else {
			$dialog = new Dialog(lang("LESS_RIGHTS"), lang("ERROR"));
			$response->exec($dialog);
			return $response;
		}
	}
	
	
	/**
	 * saves sort
	 *
	 *@name savesort
	 *@access public
	*/
	public function savesort() {
		$field = $this->sort_field;
		foreach($_POST["treenode"] as $key => $value) {
			DataObject::update($this->tree_class, array($field => $key), array("recordid" => $value), "", true);
		}
		$this->marked = $this->getParam("id");
		HTTPResponse::setBody($this->createTree());
		HTTPResponse::output();
		exit;
	}
	
	/**
	 * hides the deleted object
	 *
	 *@name hideDeletedObject
	 *@access public
	*/
	public function hideDeletedObject($response, $data) {
		$response->exec("reloadTree(function(){ goma.ui.ajax(undefined, {url: '".$this->originalNamespace."'}); });");
		return $response;
	}
	
	/**
	 * publishes data for editing a site via ajax
	 *
	 *@name ajaxSave
	 *@access public
	 *@param array - data
	 *@param object - response
	*/
	public function ajaxPublish($data, $response) {
		
		if($model = $this->save($data, 2)) {
			// notify the user
			Notification::notify($model->classname, lang("successful_published", "The data was successfully published!"), lang("published"));
			
			$response->exec("var href = '".BASE_URI . $this->adminURI()."record/".$model->id."/edit".URLEND."'; if(getInternetExplorerVersion() <= 9 && getInternetExplorerVersion() != -1) { if(location.href == href) location.reload(); else location.href = href; } else {reloadTree(function(){ goma.ui.ajax(undefined, {url: href, pushToHistory: true});}, ".$model->id."); }");
			return $response;
		} else {
			$dialog = new Dialog(lang("less_rights"), lang("error"));
			$response->exec($dialog);
			return $response;
		}
	}
	
	/**
	 * decorate model
	 *
	 *@name decorateModel
	 *@access public
	 *@param object - model
	 *@param array additional
	 *@param object|null controller
	*/
	public function decorateModel($model, $add = array(), $controller = null) {
		$add["types"] = $this->Types();

		return parent::decorateModel($model, $add, $controller);
	}
	
	/**
	 * gets the options for add
	 *
	 *@name Types
	 *@access public
	*/
	public function Types() {
		$data = $this->createOptions();
		$arr = new DataSet();
		foreach($data as $class => $title) {
			$arr->push(array("value" => $class, "title" => $title, "icon" => ClassInfo::getClassIcon($class)));
		}
		return $arr;
	}
	
	/**
	 * hook in this function to decorate a created record of record()-method
	 *
	 *@name decorateRecord
	 *@access public
	*/
	public function decorateRecord(&$record) {
		if(!$record->getVersion()) $record->version = "state";
		$this->marked = $record->class_name . "_" . $record->recordid;
	}
	
	/**
	 * view all versions
	 *
	 *@name versions
	 *@access public 
	*/
	public function versions() {
		if($this->ModelInst() && DataObject::Versioned($this->ModelInst()->dataClass)) {
			$controller = new VersionsViewController($this->ModelInst());
			$controller->subController = true;
			return $controller->handleRequest($this->request);
		}
		return false;
	}
	
	/**
	 * adds content-class left-and-main to content-div
	 *
	 *@name contentClass
	 *@access public
	*/
	public function contentClass() {
		return parent::contentclass() . " left-and-main";
	}
	
	/**
	 * add-form
	 *
	 *@name cms_add
	 *@access public
	*/
	public function cms_add() {	
		
		define("LAM_CMS_ADD", 1);
		
		$model = clone $this->modelInst();
		
		if($this->getParam("model")) {
			if(count($this->models) > 1) {
				foreach($this->models as $_model) {
					$_model = trim(strtolower($_model));
					if(is_subclass_of($this->getParam("model"), $_model) || $_model == $this->getParam("model")) {
						$type = $this->getParam("model");
						$model = new $type;
						break;
					}
				}
			} else {
				$models = array_values($this->models);
				$_model = trim(strtolower($models[0]));
				if(is_subclass_of($this->getParam("model"), $_model) || $_model == $this->getParam("model")) {
					$type = $this->getParam("model");
					$model = new $type;
				}
			}
		} else {
			Resources::addJS('$(function(){$(".leftbar_toggle, .leftandmaintable tr > .left").addClass("active");$(".leftbar_toggle, .leftandmaintable tr > .left").removeClass("not_active");$(".leftbar_toggle").addClass("index");});');
		
			$model = new ViewAccessableData();
			return $model->customise(array("adminuri" => $this->adminURI(), "types" => $this->types()))->renderWith("admin/leftandmain_add.html");
		}
		
		if(DataObject::Versioned($model->dataClass) && $model->canWrite($model)) {
			$model->queryVersion = "state";
		}
		
		return $this->selectModel($model)->form();
	}
	
	/**
	 * index-method
	 *
	 *@name index
	*/
	public function index() {
		Resources::addJS('$(function(){$(".leftbar_toggle, .leftandmaintable tr > .left").addClass("active");$(".leftbar_toggle, .leftandmaintable tr > .left").removeClass("not_active");$(".leftbar_toggle").addClass("index");});');
		
		if(!$this->template)
			return "";
		return parent::index();
	}
}
