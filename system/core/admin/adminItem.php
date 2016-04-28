<?php defined("IN_GOMA") OR die();
/**
 * base-class for every "tab" which is visible in the admin-panel.
 *
 * @package 	goma framework
 * @link 		http://goma-cms.org
 * @license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 		Goma-Team
 * @version 	2.4
 *
 * last modified: 27.02.2015
*/
class adminItem extends AdminController implements PermProvider {
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
	public $text;
	
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
	 * if is visible
	 *@name visible
	 *@return bool
	*/
	public function visible()
	{
			return true;
	}

	/**
	 * gives back the url of this admin-item
	 *
	 * @name url
	 * @access public
	 * @return string
	 */
	public function url() {
		return $this->originalNamespace . "/";
	}
	
	public function adminURI() {
		return $this->originalNamespace . "/";
	}
	
	/**
	 * gives back the title of this module
	 *
	 *@name adminItem
	 *@access public
	*/
	public function Title() {
		return parse_lang($this->text);	
	}
	
	/**
	 * returns the current model
	 *
	 *@name model
	 *@access public
	*/
	public function model($model = null) {
		if(!is_object($this->model_inst))
			$this->modelInst($model);
			
		return parent::model($model);
	}

	/**
	 * creates model-inst
	 *
	 * @name createModelInst
	 * @access public
	 * @return DataObject|null|gObject|ViewAccessAbleData
	 */
	public function modelInst($firstModel = null) {
		
		
		if(isset($firstModel) && is_object($firstModel)) {
			$this->autoSelectModel(true, $firstModel);
			return $this->model_inst;
		} else if(is_object($this->model_inst)) {
			return $this->model_inst;
		}
		
		if(count($this->models) == 1)
		{

			$firstModel = ArrayLib::first($this->models);
			if(!is_object($this->model_inst))
				$this->model_inst = $this->decorateModel(DataObject::get($firstModel, $this->where), array(), $this);
			
			$this->modelInstances = array($firstModel => $this->model_inst);
			
			return $this->model_inst;
		} else if(count($this->models) > 1) {
			$models = array();
			foreach($this->models as $model) {
				$models[$model] = $this->decorateModel(DataObject::get($model, $this->where));
			}

			/** @var ViewAccessableData $model */
			foreach($models as $model) {
				$model->customise($models);
			}
			
			$this->modelInstances = $models;
			// select model
			$this->autoSelectModel(true, $firstModel);
			
			return $this->model_inst;
		} else {
			throw new LogicException("No Model for Admin-Module " . $this->classname);
		}
	}

	/**
	 * gives back a instance if this controller with the given model
	 *
	 * @name selectModel
	 * @access public
	 * @param string $name
	 * @param bool $onThis set model for this instance or create new instance.
	 * @return adminItem
	 */
	public function selectModel($name, $onThis = false) {
		
		if(!is_object($name)) {
			if(!isset($this->modelInstances[$name])) {
				return $this;
			}
		}
		
		if($onThis) {
			/** @var ViewAccessableData $name */
			$this->setModelInst((is_object($name)) ? $name : $this->modelInstances[$name]);
			$this->model = null;
			$this->controllerInst = null;
			return $this;
		} else {
			$controller = clone $this;
			/** @var ViewAccessableData $name */
			$controller->setModelInst((is_object($name)) ? $name : $this->modelInstances[$name]);
			$controller->model = null;
			$controller->controllerInst = null;
			
			return $controller;
		}
	}

	/**
	 * auto selects the model
	 *
	 * @return adminItem
	 */
	public function autoSelectModel($onThis = false, $model = null) {
		
		if(isset($model) && is_string($model)) {
			if(isset($this->modelInstances[$model])) {
				return $this->selectModel($model, $onThis);
			}
		}
		
		// get
		if(isset($this->request->get_params["model"]))
			if(isset($this->modelInstances[$this->request->get_params["model"]])) {
				return $this->selectModel($this->request->get_params["model"], $onThis);
			}
			
		
		// preselect first model
		return $this->selectModel(ArrayLib::firstkey($this->modelInstances), $onThis);
			
	}

	/**
	 * decorates the given model with some needed vars
	 *
	 * @param ViewAccessableData|DataObjectSet $model
	 * @param array $additional
	 * @param Controller|null $controller
	 * @return DataObject
	 */
	public function decorateModel($model, $additional = array(), $controller = null) {
		
		$model->customise(array_merge(array(
			"admintitle"	=> $this->adminTitle(),
			"url"			=> $this->url(),
			"adminURI"		=> $this->adminURI()
		), $additional));
		if($controller === null) $controller = clone $this;
		
		// pagination-support
		if(is_object($model) && is_a($model, "DataSet") && !$model->isPagination() && $this->pages) {
			$page = isset($this->request->get_params["pa"]) ? $this->request->get_params["pa"] : null;
			if($this->perPage)
				$model->activatePagination($page, $this->perPage);
			else
				$model->activatePagination($page);
		}
		
		// controller
		$controller->model_inst = $model;
		$controller->model = null;
		$model->controller = $controller;
		return $model;
	}

	/**
	 * we provide all methods of the model-controller, too
	 *
	 * @param string $methodName
	 * @param array $args
	 * @return mixed
	 */
	public function __call($methodName, $args) {
		
		if(gObject::method_exists($this->getControllerInst(), $methodName)) {
			$this->getControllerInst()->request = $this->request;
			return call_user_func_array(array($this->getControllerInst(), $methodName), $args);
		}

		return parent::__call($methodName, $args);
	}

	/**
	 * we provide all methods of the model-controller, too
	 * method_exists-overloading-api of @see Object
	 *
	 * @name    __cancall
	 * @param    string $methodName
	 * @return bool
	 */
	public function __cancall($methodName) {
		if($c = $this->getControllerInst()) {
			return gObject::method_exists($c, $methodName);
		} else {
			return false;
		}
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


	/**
	 * add-form
	 *
	 * @name cms_add
	 * @access public
	 * @return mixed|string
	 */
	public function cms_add() {	
		
		$model = clone $this->modelInst();
		
		if($this->getParam("model")) {
			if($selectedModel = $this->getModelByName($this->getParam("model"))) {
				$model = $selectedModel;
			}
		}
		
		if(DataObject::versioned($model->dataClass) && $model->canWrite($model)) {
			$model->queryVersion = "state";
		}
		
		$submit = DataObject::Versioned($model->classname) ? "publish" : null;

		return $this->selectModel($model)->form(null, null, array(), false, $submit);
	}

	/**
	 * gets model by given name.
	 *
	 * @param string $name name of object.
	 * @return gObject|null
	 */
	protected function getModelByName($name) {
		if(count($this->models) > 1) {
			foreach($this->models as $currentModel) {
				$currentModel = trim(strtolower($currentModel));
				if(ClassManifest::isOfType($name, $currentModel)) {
					return new $name();
				}
			}
		} else {
			$firstModel = ArrayLib::first($this->models);
			if(ClassManifest::isOfType($name, $firstModel)) {
				return new $name;
			}
		}

		return null;
	}

	/**
	 * alias for cms_add
	 *
	 * @name add
	 * @access public
	 * @return mixed|string
	 */
	public function add() {
		return $this->cms_add();	
	}

	/**
	 *  provides no perms
	 *
	 * @name providePerms
	 * @access public
	 * @return array
	 */
	public function providePerms()
	{
		return array();
	}

	/**
	 * generates the normal controller for the model inst
	 *
	 * @name getControllerInst
	 * @access public
	 * @return bool|Controller|null
	 */
	public function getControllerInst() {
		if(!isset($this->controllerInst)) {
			$this->controllerInst = ControllerResolver::instanceForModel($this->modelInst());
		}
		
		return $this->controllerInst;
		
	}

	/**
	 * action-handler with implemented auto-model-selecting
	 *
	 * @name handleAction
	 * @access public
	 * @param string $actionName
	 * @return false|mixed|null
	 */
	public function handleAction($actionName) {
		if($this->model_inst && $this->getParam("model") !== null) {
			if(isset($this->modelInstances[$this->getParam("model")])) {
				$this->selectModel($this->getParam("model"), true);
			}
		}
		
		return parent::handleAction($actionName);
	}

	/**
	 * gets a controller for a record in a given model
	 *
	 * @name handleRecordForModel
	 * @access public
	 * @return string
	 */
	public function handleRecordForModel() {
		
		$model = $this->getParam("model");
		$id = $this->getParam("id");
		
		if(!in_array($model, $this->models)) {
			return $this->index();
		}
		
		$data = DataObject::get($model, array("id" => $id));
		
		$this->callExtending("handleRecordForModel", $model);
		$this->decorateRecord($data);
		$data = $this->decorateModel($data);
		
		if($data->Count() > 0) {
			return $this->selectModel($data->first())->handleRequest($this->request);
		} else {
			return $this->index();
		}
	}

	/**
	 * handles a request with a given record in it's controller
	 *
	 * @name record
	 * @access public
	 * @return string
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
		if($data->Count() > 0) {
			return $this->selectModel($data->first())->handleRequest($this->request);
		} else {
			// get data
			$data = DataObject::get_versioned($model, "group", array("recordid" => $id));
			$this->callExtending("handleRecord", $model);
			$this->decorateRecord($data);
			$data = $this->decorateModel($data);

			if($data->Count() > 0) {
				return $this->selectModel($data->first())->handleRequest($this->request);
			} else {
				return $this->index();
			}
		}
	}
}
