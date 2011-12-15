<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 31.08.2011
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


/**
 * new AdminItem
*/

class adminItem extends AdminController {
	/**
	 * rights of this item
	 *@name rights
	 *@access public
	*/
	public $rights = 7;
	/**
	 * sort
	 *@name sort
	 *@access public
	*/
	public $sort = 0;
	/**
	 * text of the link
	 *@name text
	 *@var lang
	*/
	public $text = "";
	/**
	 * picture of the link relational to ROOT . "images/"
	 *@name pic
	 *@access public
	*/
	public $pic = "";
	/**
	 * if is visible
	 *@name visible
	 *@return bool
	*/
	public function visible()
	{
			return true;
	}
	/**
	 * url_handlers
	*/
	public $url_handlers = array(
		"model/\$model!/\$id!" => "handlerecordformodel"
	);
	/**
	 * allowed_actions
	 *@access public
	*/
	public $allowed_actions = array
	(
		"cms_edit", "cms_add", "cms_del", "handlerecordformodel"
	);
	/**
	 * this property contains all models, this model uses
	 *@name models
	 *@access public
	 *@var array
	*/
	public $models = array();
	/**
	 * instances of the models
	 *
	 *@name modelInstances
	 *@access public
	*/
	public $modelInstances = array();
	/**
	 * controller inst of the model if set
	 *
	 *@name controllerInst
	 *@access public
	*/ 
	public $controllerInst;
	/**
	 * the template 
	 *@name template
	 *@access public
	 *@var string
	*/
	public $template = "";
	/**
	  * where
	  *@name where
	  *@access public
	*/
	public $where = array();
	/**
	 * gives back the url of this admin-item
	 *
	 *@name url
	 *@access public
	*/
	public function url() {
		return BASE_SCRIPT . "admin/".substr($this->class, 0, -5)."/";
	}
	public function adminURI() {
		return BASE_SCRIPT . "admin/".substr($this->class, 0, -5)."/";
	}
	/**
	 * gives back the title of this module
	*/
	public function AdminTitle() {
		return parse_lang($this->text);	
	}
	/**
	 * returns the current model
	 *
	 *@name model
	 *@access public
	*/
	public function model() {
		if(!is_object($this->model_inst))
			$this->modelInst();
			
		return parent::model();
	}
	/**
	 * creates model-inst
	 *
	 *@name createModelInst
	 *@access public
	*/
	public function modelInst() {
		
		if(count($this->models) == 1)
		{
			$m = arraylib::first($this->models);
			if(!is_object($this->model_inst))
				$this->model_inst = $this->decorateModel(DataObject::get($m, $this->where), array(), $this);
			
			$this->modelInstances = array($m => $this->model_inst);
			
			return $this->model_inst;
		} else if(count($this->models) > 1) {
			$models = array();
			foreach($this->models as $model) {
				$models[$model] = $this->decorateModel(DataObject::get($model, $this->where));
			}
			
			foreach($models as $model) {
				$model->customise($models);
			}
			
			$this->modelInstances = $models;
			// select model
			$this->autoSelectModel();
			
			return $this->model_inst;
				
		} else {
			throwError(6, 'PHP-Error', "No Model for Admin-Module ".$this->class."");
		}
	}
	/**
	 * gives back a instance if this controller with the given model
	 *
	 *@name selectModel
	 *@access public
	 *@param string - name
	 *@param bool - if instead writing on this object
	*/
	public function selectModel($name, $onThis = false) {
		
		if(!is_object($name)) {
			if(!isset($this->modelInstances[$name])) {
				return $this;
			}
		}
		
		if($onThis) {
		 	$this->model_inst = (is_object($name)) ? $name : $this->modelInstances[$name];
			$this->model_inst->controller = $this;
			$this->model = null;
			$this->controllerInst = null;
			return $this;
		} else {
			$controller = clone $this;
			$controller->model_inst = (is_object($name)) ? $name : $this->modelInstances[$name];
			$controller->model_inst->controller = $controller;
			$controller->model = null;
			$controller->controllerInst = null;
			
			return $controller;
		}
	} 
	/**
	 * auto selects the model
	 *
	 *@name auotSelectModel
	 *@access public
	*/
	public function autoSelectModel($onThis = false) {
		
		
		// get
		if(isset($_GET["model"]))
			if(isset($this->modelInstances[$_GET["model"]])) {
				$this->selectModel($_GET["model"], true);
				return true;
			}
			
		
		// preselect first model
		$this->selectModel(ArrayLib::firstkey($this->modelInstances), true);
			
	}
	/**
	 * decorates the given model with some needed vars
	 *
	 *@name decorateModel
	 *@access public
	 *@param object
	*/
	public function decorateModel($model, $additional = array(), $controller = null) {
		
		$model->customise(array_merge(array(
			"admintitle"	=> $this->adminTitle(),
			"url"			=> $this->url(),
			"adminURI"		=> $this->adminURI()
		), $additional));
		if($controller === null) $controller = clone $this;
		
		if($this->pages) {
			$model->pages = true;
			$model->perPage = $this->perPage;
		}
		$controller->model_inst = $model;
		$controller->model = null;
		$model->controller = $controller;
		return $model;
	}
	/**
	 * we provide all methods from the controllerInst too
	 *
	 *@name __call
	 *@access public
	*/
	public function __call($name, $args) {
		
		if(Object::method_exists($this->getControllerInst(), $name)) {
			$this->getControllerInst()->request = $this->request;
			return call_user_func_array(array($this->getControllerInst(), $name), $args);
		}
		return parent::__call($name, $args);
	}
	/**
	 * we provide all methods from the controllerInst too
	 * method_exists-overloading-api
	 *
	 *@name __cancall
	 *@access public
	*/
	public function __cancall($name) {
		return Object::method_exists($this->getControllerInst(), $name);
	}
	
	/**
	 * rewrite delete, edit and add
	*/
	public function cms_del() {
		return $this->delete();
	}
	public function cms_edit() {
		return $this->edit();
	}
	public function cms_add() {	
		
		$model = clone $this->modelInst();
		
		if($this->getParam("model")) {
			if(is_subclass_of($this->getParam("model"), $model->class) || $model->class == $this->getParam("model")) {
				$type = $this->getParam("model");
				$model = new $type;
			}
		}
		
		return $this->selectModel($model)->form();
	}
	/**
	 * alias
	*/
	public function add() {
		return $this->cms_add();	
	}
	
	/**
	 *  provides perms
	*/ 
	public function providePermissions()
	{
			return array();
	}
	/**
	 * generates the normal controller for the model inst
	 *
	 *@name getControllerInst
	 *@access public
	*/
	public function getControllerInst() {
		if(!isset($this->controllerInst)) {
			$controller = $this->modelInst()->controller;
			$this->model_inst->controller = Object::instance($this->model())->controller;
			$c = $this->model_inst->controller();
			$c->model_inst = $this->model_inst;
			$c->model = null;
			$this->model_inst->controller = $controller;
			unset($controller);
			$this->controllerInst = $c;
		}
		
		return $this->controllerInst;
		
	}
	/**
	 * action-handler with implemented auto-model-selecting
	 *
	 *@name handleAction
	 *@access public
	 *@param string - name
	*/
	public function handleAction($name) {
		if($this->model_inst && $this->getParam("model") !== null) {
			if(isset($this->modelInstances[$this->getParam("model")])) {
				$this->selectModel($this->getParam("model"), true);
			}
		}
		
		return parent::handleAction($name);
	}
	/**
	 * gets a controller for a record in a given model
	 *
	 *@name handleRecordForModel
	 *@access public
	*/
	public function handleRecordForModel() {
		
		$model = $this->getParam("model");
		$id = $this->getParam("id");
		
		
		if(!isset($this->modelInstances[$model])) {
			return $this->index();
		}
		
		
		$data = DataObject::get($model, array("id" => $id));
		
		
		$this->callExtending("handleRecordForModel", $model);
		$this->decorateRecord($data);
		$data = $this->decorateModel($data);
		if($data->_count() > 0) {
			
			return $this->selectModel($data)->handleRequest($this->request);
		} else {
			return $this->index();
		}
	}
	/**
	 * handles a request with a given record in it's controller
	 *
	 *@name record
	 *@access public
	*/
	public function record() {
		$id = $this->getParam("id");
		
		$model = $this->model();
		
		// get data
		$data = DataObject::get($model, array("id" => $id));
		$this->callExtending("handleRecord", $model);
		$this->decorateRecord($data);
		$data = $this->decorateModel($data);
		
		
		// check for deleted if no data is there
		if($data->_count() > 0) {
			return $this->selectModel($data)->handleRequest($this->request);
		} else {
			// get data
			$data = DataObject::get_Versioned($model, "group", array("recordid" => $id));
			$this->callExtending("handleRecord", $model);
			$this->decorateRecord($data);
			$data = $this->decorateModel($data);
			if($data->_count() > 0) {
				return $this->selectModel($data)->handleRequest($this->request);
			} else {
				return $this->index();
			}
		}
	}
}