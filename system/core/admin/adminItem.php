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
// TODO: Fix multiple model support
class adminItem extends AdminController implements PermProvider {
	/**
	 * rights of this item
	*/
	public $rights = 7;
	
	/**
	 * sort
	*/
	public $sort = 0;
	
	/**
	 * text of the link
	*/
	public $text;

	/**
	 * allowed_actions
	*/
	public $allowed_actions = array
	(
		"cms_edit", "cms_add", "cms_del"
	);
	
	/**
	 * this property contains all models, this model uses
	 * @var array
	 * @deprecated
	*/
	public $models = array();
	
	/**
	 * instances of the models
	*/
	public $modelInstances = array();

	/**
	 * controller inst of the model if set
	*/
	public $controllerInst;

	/**
	 * the template
	 * @var string
	*/
	public $template = "";

	/**
	 * adminItem constructor.
	 * @param null $keyChain
	 */
	public function __construct($keyChain = null)
	{
		parent::__construct($keyChain);

		if(!$this->model) {
			if (isset($this->models)) {
				if (count($this->models) == 1) {
					$this->model = $this->models[0];
				} else if (count($this->models) > 1) {
					throw new InvalidArgumentException("adminItem does not support more than 1 model.");
				}
			}
		}
	}

	/**
	 * if is visible
	*/
	public function visible()
	{
		return true;
	}

	/**
	 * gives back the url of this admin-item
	 *
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
	*/
	public function Title() {
		return parse_lang($this->text);	
	}

	/**
	 * returns the current model
	 * @param ViewAccessableData|null $model
	 * @return null|string
	 */
	public function model($model = null) {
		if(!is_object($this->model_inst))
			$this->modelInst($model);
			
		return parent::model($model);
	}

	/**
	 * gives back a instance if this controller with the given model
	 *
	 * @param ViewAccessableData|string $name
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
			$this->setModelInst((is_object($name)) ? $name : $this->modelInstances[$name]);
			$this->model = null;
			$this->controllerInst = null;
			return $this;
		} else {
			$controller = clone $this;
			$controller->setModelInst((is_object($name)) ? $name : $this->modelInstances[$name]);
			$controller->model = null;
			$controller->controllerInst = null;
			
			return $controller;
		}
	}

	/**
	 * auto selects the model
	 *
	 * @param bool $onThis
	 * @param ViewAccessableData|null $model
	 * @return adminItem
	 */
	public function autoSelectModel($onThis = false, $model = null) {
		if($onThis) {
			$this->modelInst($model);
			return $this;
		}

		$controller = clone $this;
		$controller->modelInst($model);
		return $controller;
	}

	/**
	 * @param string $model
	 * @return bool
	 */
	public function createDefaultSetFromModel($model) {
		if(parent::createDefaultSetFromModel($model)) {
			$this->decorateModel($this->model_inst);
			return true;
		}
		return false;
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
	 * @return mixed|string
	 */
	public function add() {
		return $this->cms_add();	
	}

	/**
	 *  provides no perms
	 *
	 * @return array
	 */
	public function providePerms()
	{
		return array();
	}

	/**
	 * generates the normal controller for the model inst
	 *
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
	 * handles a request with a given record in it's controller
	 *
	 * @return string
	 */
	public function record() {
		if (is_a($this->modelInst(), "IDataSet")) {
			$data = clone $this->modelInst();
			$data->addFilter(array("id" => $this->getParam("id")));
			$this->callExtending("decorateRecord", $model);
			$this->decorateRecord($data);
			$this->decorateModel($data);
			if ($data->first() != null) {
				return $this->getWithModel($data->first())->handleRequest($this->request);
			} else {
				if(is_a($this->modelInst(), "DataObjectSet")) {
					$clonedData = clone $data;
					$clonedData->setVersion("group");
					$this->decorateRecord($clonedData);
					$this->decorateModel($clonedData);

					if($clonedData->Count() > 0) {
						return $this->selectModel($clonedData->first())->handleRequest($this->request);
					}
				}

				return $this->index();
			}
		} else {
			return $this->index();
		}
	}
}
