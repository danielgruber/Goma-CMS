<?php defined("IN_GOMA") OR die();

/**
 * A simple two column admin-panel.
 *
 * @package     Goma\Admin\LeftAndMain
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.2.8
 */
class LeftAndMain extends AdminItem {

	/**
	 * the base template of the view
	*/
	public $baseTemplate = "admin/leftandmain.html";

	/**
	 * defines the url-handlers
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
	*/
	public $allowed_actions = array(
		"cms_edit", "cms_add", "cms_del", "updateTree", "savesort", "versions"
	);

	/**
	 * this var defines the tree-class
	*/
	public $tree_class = "";

	/**
	 * marked node
	*/
	public $marked = 0;

	/**
	 * sort-field
	*/
	protected $sort_field;

	/**
	 * render-class.
	*/
	static $render_class = "LeftAndMain_TreeRenderer";

	/**
	 * gets the title of the root node
	 *
	 * @return string
	 */
	protected function getRootNode() {
		return "";
	}

	/**
	 * generates the options for the create-select-field
	 *
	 * @return array
	 */
	public function createOptions() {
		$options = array();
		foreach($this->models as $model) {
			if($title = ClassInfo::getClassTitle($model)) {
				$options[$model] = $title;
			}
		}
		return $options;
	}

	/**
	 * inserts the data in the leftandmain-template
	 *
	 * @param string $content
	 * @return mixed|string
	 */
	public function serve($content) {
		if($this->request->is_ajax()) {
			return $content;
		}

		// add resources
		Resources::add("system/core/admin/leftandmain.js", "js", "tpl");

		if(isset($this->sort_field)) {
			Resources::addData("var LaMsort = true;");
		} else {
			Resources::addData("var LaMsort = false;");
		}

		Resources::addData("var adminURI = '".$this->adminURI()."'; var marked_node = '".$this->marked."';");

		$data = $this->ModelInst();

		$output = $data->customise(
			array(
				"CONTENT"	=> $content,
				"activeAdd" => $this->getParam("model"),
				"SITETREE" => $this->createTree($this->getParam("searchtree")),
				"searchtree" => $this->getParam("searchtree"),
				"ROOT_NODE" => $this->getRootNode(),
				"TREEOPTIONS" => $this->generateTreeOptions()
			)
		)->renderWith($this->baseTemplate);

		// parent-serve
		return parent::serve($output);
	}

	/**
	 * generates a set of options as HTML, that can be used to have more than just a search
	 * to customise the tree. For example a multilingual-plugin should add a select-option
	 * to filter by language.
	*/
	public function generateTreeOptions() {
		$tree_class = $this->tree_class;
		if($tree_class == "") {
			throw new LogicException("Failed to load Tree-Class. Please define \$tree_class in ".$this->classname);
		}

		$html = new HTMLNode("div");

		if(gObject::method_exists($tree_class, "generateTreeOptions")) {
			call_user_func_array(array($tree_class, "generateTreeOptions"), array($html, $this));
		}

		/** @var gObject $treeInstance */
		$treeInstance = new $tree_class;
		$treeInstance->callExtending("generateTreeOptions", $html, $this);

		if($html->children()) {
			return $html->render();
		}

		return "";
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
	 *
	 * @param DataObject $child
	 * @return array
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
	 * @param gObject $instance
	 * @param array $options
	 * @return array|mixed
	 */
	protected function callArgumentTree($instance, $options) {
		$newParams = call_user_func_array(array($instance, "argumentTree"), array($this, $options));
		if(is_array($newParams) && isset($newParams["version"]) && isset($newParams["filter"])) {
			$options = $newParams;
		}
		return $options;
	}

	/**
	 * @param TreeRenderer $treeRenderer
	 * @param string $id
     */
	protected function setExpanded($treeRenderer, $id) {
		// here we check for Ajax-Opening. It is given to the leftandmain-js-api.
		/** @var DataObject $current */
		if($current = DataObject::get_versioned("pages", "state", array("id" => $id))->first()) {
			$treeRenderer->setExpanded($current->id);
			while($current->parent) {
				$current = $current->parent;
				$treeRenderer->setExpanded($current->id);
			}
		}
	}

	/**
	 * creates the Tree
	 *
	 * @param string $search
	 * @param bool $marked
	 * @return String
	 */
	public function createTree($search = "", $marked = null) {
		$tree_class = $this->tree_class;
		if($tree_class == "") {
			throw new LogicException("Failed to load Tree-Class. Please define \$tree_class in ".$this->classname);
		}

		if(!gObject::method_exists($tree_class, "build_tree")) {
			throw new LogicException("Tree-Class does not have a method build_tree. Maybe you have to update your version of goma?");
		}

		$options = array("version" => "state", "search" => $search, "filter" => array());

		// give the tree-class the ability to modify the options.
		if(gObject::method_exists($tree_class, "argumentTree")) {
			$options = $this->callArgumentTree($tree_class, $options);
		}

		// iterate through extensions to give them the ability to change the options.
		$treeClassInstance = new $tree_class;
		foreach($treeClassInstance->getextensions() as $ext)
		{
			if (ClassInfo::hasInterface($ext, "TreeArgumenter")) {
				$options = $this->callArgumentTree($treeClassInstance->getinstance($ext), $options);
			}
		}
		unset($treeClassInstance);

		// generate tree
		$tree = call_user_func_array(array($tree_class, "build_tree"), array(0, $options));
		/** @var TreeRenderer $treeRenderer */
		$treeRenderer = new self::$render_class($tree, null, null, $this->originalNamespace, $this);
		$treeRenderer->setLinkCallback(array($this, "generateTreeLink"));
		$treeRenderer->setActionCallback(array($this, "generateContextMenu"));
		$treeRenderer->mark($this->getParam("id"));

		// check for logical opened tree-items.
		if(isset($this->getRequest()->get_params["edit_id"])) {
			$this->setExpanded($treeRenderer, $this->getRequest()->get_params["edit_id"]);
		} else if($this->getParam("id")) {
			$this->setExpanded($treeRenderer, $this->getParam("id"));
		}

		return $treeRenderer->render(true);
	}
	/**
	 * gets updated data of tree for searching or normal things
	*/
	public function updateTree() {
		$this->marked = $this->getParam("marked");
		$search = $this->getParam("search");

		return GomaResponse::create()->setShouldServe(false)->setBody(
			GomaResponseBody::create($this->createTree($search))->setParseHTML(false)
		);
	}

	/**
	 * Actions of editing
	*/

	/**
	 * saves data for editing a site via ajax
	 *
	 * @param array $data
	 * @param FormAjaxResponse $response
	 * @return FormAjaxResponse
	 */
	public function ajaxSave($data, $response, $form = null, $controller = null, $forceInsert = false, $forceWrite = false, $overrideCreated = false) {
		try {
			$model = $this->save($data, 1, $forceInsert, $forceWrite, $overrideCreated);
			// notify the user
			Notification::notify($model->classname, lang("SUCCESSFUL_SAVED", "The data was successfully written!"), lang("SAVED"));

			$response->exec("var href = '" . BASE_URI . $this->adminURI() . "record/" . $model->id . "/edit" . URLEND . "'; if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) { if(location.href == href) location.reload(); else location.href = href; } else { reloadTree(function(){ goma.ui.ajax(undefined, {url: href, showLoading: true, pushToHistory: true}); }, " . var_export($model["id"], true) . "); }");

			return $response;
		} catch(Exception $e) {
			$response->exec('alert('.var_export($e->getMessage(), true).');');
			return $response;
		}
	}


	/**
	 * saves sort
	*/
	public function savesort() {
		if(isset($this->request->post_params["treenode"])) {
			$field = $this->sort_field;
			foreach ($this->request->post_params["treenode"] as $key => $value) {
				DataObject::update($this->tree_class, array($field => $key), array("recordid" => $value), "", true);
			}
			$this->marked = $this->getParam("id");

			return GomaResponse::create()->setShouldServe(false)->setBody(
				GomaResponseBody::create($this->createTree())->setParseHTML(false)
			);
		}

		throw new BadRequestException();
	}

	/**
	 * hides the deleted object
	*/
	public function hideDeletedObject($response, $data) {
		$response->exec("reloadTree(function(){ goma.ui.ajax(undefined, {url: '".$this->originalNamespace."'}); });");
		return $response;
	}

	/**
	 * publishes data for editing a site via ajax
	 * @param array $data
	 * @param AjaxResponse $response
	 * @param null $form
	 * @param null $controller
	 * @param bool $overrideCreated
	 * @return AjaxResponse
	 */
	public function ajaxPublish($data, $response, $form = null, $controller = null, $overrideCreated = false) {
		if($model = $this->save($data, 2, false, false, $overrideCreated)) {
			// notify the user
			Notification::notify($model->classname, lang("successful_published", "The data was successfully published!"), lang("published"));

			$response->exec("var href = '".BASE_URI . $this->adminURI()."record/".$model->id."/edit".URLEND."'; if(getInternetExplorerVersion() <= 9 && getInternetExplorerVersion() != -1) { if(location.href == href) location.reload(); else location.href = href; } else {reloadTree(function(){ goma.ui.ajax(undefined, {url: href, showLoading: true, pushToHistory: true});}, ".$model->id."); }");

			return $response;
		} else {
			$response->exec('alert('.var_export(lang("less_rights"), true).');');
			return $response;
		}
	}

	/**
	 * decorate model
	 *
	 * @name decorateModel
	 * @access public
	 * @param object $model
	 * @param array $add
	 * @param gObject|null $controller
	 * @return DataObject
	 */
	public function decorateModel($model, $add = array(), $controller = null) {
		$add["types"] = $this->Types();

		return parent::decorateModel($model, $add, $controller);
	}

	/**
	 * gets the options for add
	 *
	 * @return DataSet
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
	 * @param DataObjectSet|DataObject $record
	 */
	public function decorateRecord(&$record) {
		if(is_a($record, "DataObjectSet")) {
			if (!$record->getVersion()) $record->setVersion("state");
		}

		$this->marked = $record->class_name . "_" . $record->recordid;
	}

	/**
	 * adds content-class left-and-main to content-div
	 *
	 * @return string
	 */
	public function contentClass() {
		return parent::contentclass() . " left-and-main";
	}

	/**
	 * add-form
	 *
	 * @return string
	 */
	public function cms_add() {
		$model = clone $this->modelInst();

		if($this->getParam("model")) {
			if($selectedModel = $this->getModelByName($this->getParam("model"))) {
				$model = $selectedModel;
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
	 * @return string
	 */
	public function index() {
		Resources::addJS('$(function(){$(".leftbar_toggle, .leftandmaintable tr > .left").addClass("active");$(".leftbar_toggle, .leftandmaintable tr > .left").removeClass("not_active");$(".leftbar_toggle").addClass("index");});');

		if(!$this->template)
			return "";

		return parent::index();
	}
}
