<?php
/**
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 06.09.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class VersionsViewController extends Controller {
	/**
	 * url-handlers
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		"getversion/\$id!"	=> "getversion"
	);
	/**
	 * allowed actions
	*/
	public $allowed_actions = array("getversion");
	/**
	 * the model, for which versions should be shown
	 *
	 *@name model_inst
	 *@access public
	*/
	public $model_inst;
	/**
	 * template
	 *
	 *@name template
	*/
	public $template = "admin/versionsview/main.html";
	/**
	 *@name __consturct
	 *@access public
	 *@param object - model: you should give any dataobject, but not versioned, for example: DataObject::_get("id" => 1234); it will decrease performance, if you directly give all versions
	*/
	public function __construct($model = null) {
		parent::__construct();
		
		if($model) {
			$this->model_inst = clone $model;
			$this->model_inst->controller = $this;
			$this->model_inst->stateid = null;
			$this->model_inst->publishedid = null;
		}
	}
	/**
	 * form-rendering under this controller
	 *
	 *@name form
	 *@access public
	*/
	public function form($name = false, $model = false, $fields = array(),$edit = false, $submission = "safe", $disabled = false) {
		if($name === false)
				$name = "form_versioned_" . $this->class;
				
		if(!$model && is_object($this->model_inst)) {
			$model = clone $this->model_inst;
		}
		
		$name .= "_" . $model->versionid;
		
		
		
		// add the right controller
		$controller = clone $this;
		$controller->model_inst = $model;
		
		$form = new Form($controller, $name, $fields);
		if($disabled)
			$form->disable();
		
		// set submission
		$form->setSubmission("saveVersion");
		$form->setSubmission($submission);
		
		// draw form
		$model->drawForm($form, $edit);
		
		// we add where to the form
		foreach($this->where as $key => $value)
		{
				$form->add(new HiddenField($key, $value));
		}
		
		$form->actions = array();
		if(substr($name, 0, 7) == "version")
			$form->addAction(new FormAction("saveVersion", lang("restore", "restore")));
		else
			$form->addAction(new FormAction("saveVersion", lang("done", "done")));
		
		/*foreach($form->actions as $action) {
			$action["field"]->container->css("display", "none");
		}*/
		
		
		$this->callExtending("afterForm", $form);
		
		return $form->render();
	}
	/**
	 * saves the version
	 *
	 *@name saveVersion
	*/
	public function saveVersion($data) {
		if($this->save($data) !== false)
		{
				addcontent::add('<div class="success">'.lang("successful_saved", "The data was successfully written!").'</div>');
				$this->redirectback();
		} else
		{
				throwError(6, 'Server-Error', 'Could not save data.');
		}
	}
	/**
	 * gets the form for this version
	 *
	 *@name getVersion
	 *@access public
	*/
	public function getVersion() {
		$id = $this->getParam("id");
		
		$data = DataObject::_get($this->model_inst, array("versionid" => $id));
		if(Core::is_ajax()) {
			HTTPResponse::addHeader("content-type", "text/x-json");
			return json_encode(array("form" => $data->controller($this)->renderForm("version_".$data->id."_" . $data->versionid), "active" => $data->versionid));
		} else {
			$this->tplVars["versionform"] 	= $data->controller($this)->renderForm("version_".$data->id."_" . $data->versionid);
			$this->tplVars["active"] 		= $data->versionid; 
			return $this->index();
		}
	}
	/**
	 * index
	 *
	 *@name index
	 *@access public
	*/
	public function index() {
		Resources::addData("var version_namespace = '".$this->namespace."';");
		if(!isset($this->tplVars["versionform"])) {
			if($this->model_inst->versions("1,1")->count > 0) {
				$data = $this->model_inst->versions("1,1");
				$this->tplVars["versionform"] = $data->controller($this)->renderForm("version_".$data->id."_" . $data->versionid);
				$this->tplVars["active"]	  = $data->versionid;
			} else {
				$this->tplVars["versionform"] = lang("no_versions", "No version found");
				$this->tplVars["active"]	  = 0;
			}
		}
		if(!Core::is_ajax())
			$this->tplVars["currentform"] = $this->model_inst->controller($this)->renderForm();
			
		if(isset($_GET["redirect"])) $_SESSION["redirect"] = $_GET["redirect"];
		return parent::index();
	}
	/**
	 * redirects back
	 *
	 *@name redirectBack
	 *@access public
	*/
	public function redirectBack() {
		if(isset($_SESSION["redirect"])) {
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . $_SESSION["redirect"]);
		} else {
			parent::redirectBack();
		}
	}
}