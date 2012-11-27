<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 27.11.2012
  * $Version 2.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

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
		"updateTree/\$marked!/\$search"	=> "updateTree",
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
	 * title of root-node
	 *
	 *@name root_node
	 *@access public
	*/
	public $root_node = "";
	
	/**
	 * colors of the nodes
	 * key is class, value is a array with color and name
	 * for example:
	 * array("withmainbar" => array("color" => "#cfcfcf", "name" => "with mainbar"))
	 *
	 *@name colors
	 *@access public
	 *@var array
	*/
	public $colors = array();
	
	/**
	 * icons
	 *
	 *@name icons
	 *@access public
	*/
	public $icons = array("root" => "images/icons/fatcow16/world.png");
	
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
	 * gets the icons from all classes and the $icons-var
	 *
	 *@name getIcons
	 *@access public
	*/
	public function getIcons() {
		$icons = $this->icons;
		$icons["searchresult"] = "images/icons/fatcow16/magnifier.png";
		foreach($this->models as $model) {
			if(ClassInfo::hasStatic($model, "icon")) {
				$icons[$model] = ClassInfo::findFile(ClassInfo::getStatic($model, "icon"), $model);
			}
			foreach(ClassInfo::getChildren($model) as $child) {
				if(ClassInfo::hasStatic($child, "icon")) {
					$icons[$child] = ClassInfo::findFile(ClassInfo::getStatic($child, "icon"), $child);
				}
			}
		}
		return $icons;
	}
	
	
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
		unset($_SESSION["goma_resume_".$this->class.""]);
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
		$filename = $this->getCSS();
		Resources::add(CACHE_DIRECTORY . "/" . $filename, "css");
		Resources::add("system/core/admin/leftandmain.js", "js", "tpl");
		
		if(isset($this->sort_field)) {
			Resources::addData("var LaMsort = true;");
		} else {
			Resources::addData("var LaMsort = false;");
		}
		
		$search = isset($_GET["searchtree"]) ? text::protect($_GET["searchtree"]) : "";
		

		Resources::addData("var adminURI = '".$this->adminURI()."'; var marked_node = '".$this->marked."';");
		
		$data = $this->ModelInst();
		
		$this->ModelInst()->customise(array("legend" => $this->legend()));
		
		if(defined("LAM_CMS_ADD"))
			$this->ModelInst()->addmode = 1;
		
		$output = $data->customise(array("CONTENT"	=> $content, "activeAdd" => $this->getParam("model"), "SITETREE" => $this->createTree(), "searchtree" => $search, "ROOT_NODE" => $this->getRootNode()))->renderWith($this->baseTemplate);
		
		$_SESSION[$this->class . "_LaM_marked"] = $this->marked;
		
		// parent-serve
		return parent::serve($output);
	}
	
	/**
	 * gets css-code from colors and icons
	 *
	 *@name getCSS
	 *@access public
	*/
	public function getCSS() {
		$filename = "left_and_main_on_the_fly_".$this->class.".css";
		if(!file_exists(ROOT . CACHE_DIRECTORY . "/" . $filename)) {
			$css = "";
			$retinaCSS = "";
			foreach($this->getIcons() as $class => $icon) {
				$retinaPath = substr($icon, 0, strrpos($icon, ".")) . "@2x" . substr($icon, strrpos($icon, "."));
				if(file_exists($retinaPath)) {
					$retinaCSS .= '.'.$class.' span { background-image: url(../../'.$retinaPath.') !important; background-size: 16px 16px }';
				}
				$css .= '.'.$class.' span { background-image: url(../../'.$icon.') !important }';
			}
			
			foreach($this->colors as $class => $data) {
				$css .= '.'.$class.' { color: '.$data["color"].' !important; }';
			}
			
			if(Object::method_exists($this->tree_class, "provideTreeParams")) {
				$params = Object::instance($this->tree_class)->provideTreeParams();
				foreach($params as $class => $data) {
					$css .= '.'.$class.' {';
					foreach($data["css"] as $key => $value) {
						$css .= $key . ': ' . $value . ";\n";
					}
					$css .= "}";
				}
			}
			
			$wholecss = $css . "\n\n/* here goes some retina-stuff */
@media
only screen and (-webkit-min-device-pixel-ratio: 2),
only screen and (   min--moz-device-pixel-ratio: 2),
only screen and (     -o-min-device-pixel-ratio: 2/1) { 
  ".$retinaCSS."
}";
			
			FileSystem::write(ROOT . CACHE_DIRECTORY . "/" . $filename, $wholecss);
			unset($file, $css);
		}
		return $filename;	
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
			throwError(6, 'PHP-Error', "Failed to load \$tree_class! Please define \$tree_class in ".$this->class."");
		}
		
		if(isset($_GET["searchtree"])) {
			$search = $_GET["searchtree"];
		} else if(isset($_POST["searchtree"])) {
			$search = $_POST["searchtree"];
		}
		
		$object = Object::instance($tree_class);
		
		if(empty($search)) {
			$search_parentid = 0;
		} else {
			$search_parentid = array($search);
		}
		
		if(isset($_GET["tree_params"]) && is_array($_GET["tree_params"])) {
			if(!isset($_SESSION[$this->class . "_tree_params"])) $_SESSION[$this->class . "_tree_params"] = array();
			$params = array_merge($_SESSION[$this->class . "_tree_params"], $_GET["tree_params"]);
			$_SESSION[$this->class . "_tree_params"] = $params;
			
		} else if(isset($_SESSION[$this->class . "_tree_params"]) && is_array($_SESSION[$this->class . "_tree_params"])) {
			$params = $_SESSION[$this->class . "_tree_params"];
		} else {
			$params = array();
		}
		
		if(!isset($marked))
			$marked = $this->marked;
		
		if(count($this->models) > 1) {
			$default_tree = $object->renderTree($this->adminURI() . "model/\$class_name/\$id/edit" . URLEND, $marked, $search_parentid, $params, false);
		} else {
			$default_tree = $object->renderTree($this->adminURI() . "record/\$id/edit" . URLEND, $marked, $search_parentid, $params, false);
		}
		
		if($marked == "0") {
			$marked = "marked";
		} else {
			$marked = "";
		}
		
		if(!empty($search)) {
			return '<ul class="tree">
							<li class="expanded last" id="tree_'.$this->class.'">
								<span class="a  '.$marked.'">
									<span class="b">
										<a nodeid="0" class="treelink searchresult" href="'.$this->adminURI() .'"><span>'.lang("result", "result").'</span></a>
									</span>
								</span>
								<ul class="rootnode">
									'.$default_tree.'
								</ul>
							</li>
							
						</ul>';
		} else {
			return '<ul class="tree">
							<li class="expanded last" id="tree_'.$this->class.'">
								<span class="a '.$marked.'">
									<span class="b">
										<a nodeid="0" class="treelink root" href="'.$this->adminURI() .'"><span>'.$this->getRootNode().'</span></a>
									</span>
								</span>
								<ul class="rootnode">
									'.$default_tree.'
								</ul>
							</li>
							
						</ul>';
		}
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
		HTTPResponse::setBody($this->createTree($search), isset($_SESSION[$this->class . "_LaM_marked"]) ? $_SESSION[$this->class . "_LaM_marked"] : 0);
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
			$dialog = new Dialog(lang("successful_saved", "The data was successfully written!"), lang("okay", "Okay"));
			$dialog->close(3);
			$response->exec($dialog);
			$response->exec("if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) { var href = '".BASE_URI . $this->adminURI()."/record/".$model->id."/edit".URLEND."'; if(location.href == href) location.reload(); else location.href = href; } else { reloadTree(function(){ LoadTreeItem('".$model["class_name"] . "_" . $model["id"]."'); }); }");
			return $response;
		} else {
			$dialog = new Dialog(lang("less_rights"), lang("error"));
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
			DataObject::update($this->tree_class, array($field => $key), array("recordid" => $value));
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
		$response->exec("reloadTree(function(){ LoadTreeItem(0);});");
		return $response;
	}
	
	/**
	 * gets the options for add
	 *
	 *@name legend
	 *@access public
	*/
	public function legend() {
		$data = $this->colors;
		$arr = array();
		foreach($data as $class => $data) {
			$arr[] = array("class"	=> $class, "title"	=> parse_lang($data["name"]));
		}
		if(Object::method_exists($this->tree_class, "provideTreeParams")) {
				$params = Object::instance($this->tree_class)->provideTreeParams();
				foreach($params as $class => $data) {
					if(isset($_SESSION[$this->class . "_tree_params"][$class])) 
						$data["default"] = $_SESSION[$this->class . "_tree_params"][$class];
					
					$arr[] = array("class" => $class, "title" => parse_lang($data["title"]), "checkbox" => true, "checked" => $data["default"]);
				}
			}
		return new ViewAccessAbleData($arr);
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
			$dialog = new Dialog(lang("successful_published", "The data was successfully published!"), lang("okay", "Okay"));
			$dialog->close(3);
			$response->exec($dialog);
			$response->exec("if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) { var href = '".BASE_URI . $this->adminURI()."/record/".$model->id."/edit".URLEND."'; if(location.href == href) location.reload(); else location.href = href; } else {reloadTree(function(){ LoadTreeItem('".$model["class_name"] . "_" . $model["id"]."'); });}");
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
		$add["legend"] = $this->Legend();
		
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
		foreach($data as $option => $title) {
			$arr->push(array("value" => $option, "title" => $title));
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
		if($this->ModelInst() && $this->ModelInst()->versioned) {
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
			
			$model = new ViewAccessableData();
			return $model->customise(array("adminuri" => $this->adminURI(), "types" => $this->types()))->renderWith("admin/leftandmain_add.html");
		}
		
		if($model->versioned && $model->canWrite($model)) {
			$model->queryVersion = "state";
		}
		
		return $this->selectModel($model)->form();
	}
	
}
