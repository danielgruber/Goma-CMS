<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 28.11.2011
  * $Version 007
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Controller extends RequestHandler
{
		/**
		 * how much data is on one page?
		 *
		 *@name perPage
		 *@access public
		*/
		public $perPage = 10;
		/**
		 * defines whether to use pages or not
		 *
		 *@name pages
		 *@access public
		 *@var bool
		*/
		public $pages = false;
		/**
		 * defines which model is used for this controller
		 *
		 *@name model
		 *@access public
		 *@var bool|string
		*/
		public $model = null;
		/**
		 * instance of the model
		 *@name model_inst
		 *@access public
		*/
		public $model_inst = false;
		/**
		 * where for the model_inst
		 *@name where
		 *@access public
		*/
		public $where = array();
		/**
		 * allowed actions
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array(
			"edit",
			"delete",
			"record"
		);
		/**
		 * template for this controller
		 *
		 *@name template
		 *@acceess public
		*/
		public $template = "";
		/**
		 * some vars for the template
		 *@name tplVars
		 *@access public
		*/
		public $tplVars = array();
		/**
		 * url-handlers
		 *@name url_handlers
		*/
		public $url_handlers = array(
			'$Action/$id'	=> '$Action'
		);
		/**
		 * areas 
		 *
		 *@name areas
		 *@access public
		*/
		public $areas = array();
		/**
		 * content of areas
		 *
		 *@name areaData
		 *@access public
		*/
		public $areaData = array();
		/**
		 * if this var is set to true areas are always used
		 *
		 *@name useAreas
		 *@access public
		 *@var bool
		*/
		public $useAreas;
		/**
		 * showform if no edit right
		 *
		 *@name showWithoutRight
		 *@access public
		 *@var bool
		 *@default false
		*/
		public $showWithoutRight = false;
		/**
		 * inits the controller:
		 * - determining and loading model
		 * - checking template
		 *
		 *
		 *@name init
		 *@access public
		*/
		public function init()
		{
				parent::Init();
				
				if($this->template == "")
				{
						$this->template = $this->model() . ".html";
				}
		}
		
		/**
		 * returns the model-object
		 *
		 *@name modelInst
		 *@access public
		*/
		public function modelInst($model = null) {
			if(isset($model) && ClassInfo::exists($model)) {
				$this->model = $model;
			}
			if(!is_object($this->model_inst) || (isset($model) && ClassInfo::exists($model))) {
				if(isset($this->model)) {
					$this->model_inst = Object::instance($this->model);
				} else {
					if(ClassInfo::exists($model = substr($this->class, 0, -10))) {
						$this->model = $model;
						$this->model_inst = Object::instance($this->model);
					} else if(ClassInfo::exists($model = substr($this->class, 0, -11))) {
						$this->model = $model;
						$this->model_inst = Object::instance($this->model);
					}
				}
			} else if(!isset($this->model)) {
				if(is_a($this->model_inst, "DataObjectSet"))
					$this->model = $this->model_inst->dataobject->class;
				else
					$this->model = $this->model_inst->class;
			}
			
			return (is_object($this->model_inst)) ? $this->model_inst : new ViewAccessAbleData();
		}
		
		/**
		 * returns the controller-model
		 *
		 *@name model
		 *@access public
		*/
		public function model($model = null) {
			if(isset($model) && ClassInfo::exists($model)) {
				$this->model = $model;
				return $model;
			}
			
			if(!isset($this->model)) {
				if(!is_object($this->model_inst)) {
					if(ClassInfo::exists($model = substr($this->class, 0, -10))) {
						$this->model = $model;
					} else if(ClassInfo::exists($model = substr($this->class, 0, -11))) {
						$this->model = $model;
					}
				} else {
					if(is_a($this->model_inst, "DataObjectSet"))
						$this->model = $this->model_inst->dataobject->class;
					else
						$this->model = $this->model_inst->class;
				}
			}
			
			return $this->model;
		}
		
		/**
		 * returns the count of records in the model according to this controller
		 *
		 *@name countModelRecords
		 *@access public
		*/
		public function countModelRecords() {
			if(is_a($this->modelInst(), "DataObjectSet"))
				return $this->modelInst()->count();
			else {
				if($this->modelInst()->bool())
					return 1;
			}
			
			return 0;
		}
		
		/**
		 * handles requests
		 *@name handleRequest
		*/
		public function handleRequest(request $request)
		{

				$this->areaData = array();
				$data = parent::handleRequest($request);
				
				if(Core::is_ajax() && is_object($data) && Object::method_exists($data,"render")) {
					HTTPResponse::setBody($data->render());
					HTTPResponse::output();
					exit;
				}
				
				if(Core::is_ajax() && isset($_GET["ajaxcontent"]) && (count($this->areaData) > 0 || $this->useAreas === true)) {
					HTTPResponse::addHeader("content-type", "text/x-json");
					$areas = array_keys($this->areaData);
					if(!empty($data) && !is_bool($data)) {
						$this->areaData["content"] = $data;
					}
					return array("areas" => $this->areaData, "class" => $this->model_inst->class);					
				} else {
					if(count($this->areaData) > 0 || $this->useAreas === true) {
						if(!empty($data) && !is_bool($data)) {
							$this->areaData["content"] = $data;
						}
						
						
						return $this->renderWithAreas($this->template);
					} else {
						return $data;
					}
				}
				
		}
		
		/**
		 * this action will be called if no other action was found
		 *
		 *@name index
		 *@access public
		*/
		public function index()
		{
			if($this->template) {
				if(is_a($this->modelInst(), "DataObject") && $this->modelInst()->controller != $this) {
					$model = DataObject::Get($this->model(), $this->where);
					$model->controller = clone $this;
					return $model->customise($this->tplVars)->renderWith($this->template);
				} else {
					return $this->modelInst()->customise($this->tplVars)->renderWith($this->template);
				}
			} else {
				$trace = @debug_backtrace();
				throwError(6, "PHP-Error", "No Template for Controller ".$this->class." in ".$trace[0]["file"]." on line ".$trace[0]["line"].".");
			}
		}
		/**
		 * renders with areas
		 *
		 *@name renderWithAreas
		 *@access public
		*/
		public function renderWithAreas($template, $model = null) {
			$areas = array_keys($this->areaData);
			
			if(!isset($model))
				$model = $this->modelInst();
			
			foreach($this->areaData as $key => $value) {
				$this->tplVars[$model->class . "_" . $key] = $value;
			}
			// get iAreas
			
			return $model->customise($this->tplVars)->renderWith($template, $areas);
		}
		/**
		 * handles a request with a given record in it's controller
		 *
		 *@name record
		 *@access public
		*/
		public function record() {
			$id = $this->getParam("id");
			if($model = $this->model()) {
				$data = DataObject::get_one($model, array("id" => $id));
				$this->callExtending("decorateRecord", $model);
				$this->decorateRecord($data);
				if($data) {
					return $data->controller()->handleRequest($this->request);
				} else {
					return $this->index();
				}
			} else {
				return $this->index();
			}
		}
		/**
		 * hook in this function to decorate a created record of record()-method
		 *
		 *@name decorateRecord
		 *@access public
		*/
		public function decorateRecord(&$record) {
			
		}
		/**
		 * generates a form
		 *
		 *@name form
		 *@access public
		 *@param string - name
		 *@param object|false - model
		 *@param array - additional fields
		 *@param bool - if calling getEditForm or getForm on model
		 *@param string - submission
		*/
		public function form($name = null, $model = null, $fields = array(),$edit = false, $submission = "safe", $disabled = false)
		{		
		
				if(!isset($model) || !$model) {
					$model = clone $this->modelInst();
				} else {
					if(is_a($model, "DataObjectSet"))
						$model = $model->first();
				}
				
				if(!Object::method_exists($model, "generateForm")) {
					$trace = @debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
					throwError(6, "PHP-Error", "No Method generateForm for Model ".get_class($model)." in ".$trace[0]["file"]." on line ".$trace[0]["line"].".");
				}
				
				// add the right controller
				$controller = clone $this;
				$model->controller($controller);
				
				$form = $model->generateForm($name, $edit, $disabled);
				$form->setSubmission($submission);
				
				// we add where to the form
				foreach($this->where as $key => $value)
				{
						$form->add(new HiddenField($key, $value));
				}
				
				$this->callExtending("afterForm", $form);
				
				return $form->render();
		}
		/**
		 * renders the form for this model
		 *
		 *@name renderForm
		 *@access public
		 *@param string - name
		 *@param array - additional fields
		 *@param string - submission
		*/
		public function renderForm($name = false,$fields = array(),$submission = "safe", $disabled = false, $model = null)
		{
			if(!isset($model))
				$model = $this->modelInst();
			
			return $this->form($name, $model, $fields, true, $submission, $disabled);
		}
		/**
		 * edit-function
		 *@name edit
		 *@access public
		*/
		public function edit()
		{
			if($this->countModelRecords() == 1) {
				if(!$this->modelInst()->canWrite($this->modelInst()))
				{
					if($this->showWithoutRight || $data->showWithoutRight) {
						$disabled = true;
						AddContent::addNotice(lang("less_rights"));
					} else {
						return lang("less_rights");
					}
				} else {
					$disabled = false;
				}
				
				return $this->form("edit_" . $this->class . $this->modelInst()->id, $this->modelInst(), array(
					
				), true, "safe", $disabled);
			} else {
				
				$model = DataObject::get_one($this->model(), array_merge($this->where, array("id" => $this->getParam("id"))));
				
				if($model) {
					return $model->controller(clone $this)->edit();
				} else {
					return false;
				}
			}
		}
		/**
		 * delete-function
		 * this delete-function also implements ajax-functions
		 *
		 *@name delete
		 *@access public
		 *@param object - object for hideDeletedObject Function
		*/
		public function delete($object = null)
		{
			if($this->countModelRecords() == 1) {
				if(!$this->modelInst()->canDelete($this->modelInst()))
				{
					return lang("less_rights", "You don't have permissions to access this page.");
				} else {
					$disabled = false;
				}
				
				if($this->confirm(lang("delete_confirm", "Do you really want to delete this record?"))) {
					$data = clone $this->modelInst();
					$this->modelInst()->remove();
					if(request::isJSResponse() || isset($_GET["dropdownDialog"])) {
						$response = new AjaxResponse();
						if($object !== null)
							$data = $object->hideDeletedObject($response, $data);
						else 
							$data = $this->hideDeletedObject($response, $data);
							
						if(is_object($data))
							$data = $data->render();
						
						HTTPResponse::setBody($data);
						HTTPResponse::output();
						exit;
					} else {
						$this->redirectback();
					}
				}
			} else {
				$model = DataObject::get_one($this->model(), array_merge($this->where, array("id" => $this->getParam("id"))));
				if($model) {
					return $model->controller(clone $this)->delete();
				} else {
					return false;
				}
			}
		}
		/**
		 * hides the deleted object
		 *
		 *@name hideDeletedObject
		 *@access public
		*/
		public function hideDeletedObject($response, $data) {
			$response->exec("location.reload();");
			return $response;
		}
		/**
		 * default save-function for forms
		 *
		 *@name safe
		 *@access public
		*/ 
		public function safe($data)
		{
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
		 * default save-method for forms
		 * it's the new one, the old one was @safe
		 *
		 *@name submit_form
		 *@access public
		*/
		public function submit_form($data) {
			if($this->save($data) !== false)
			{
				addcontent::add('<div class="success">'.lang("successful_saved", "The data was successfully written!").'</div>');
				$this->redirectback();
			} else
			{
				$debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				throwError(6, 'Server-Error', 'Could not save data in '.$debug[0]["file"].' on line '.$debug[0]["line"].'.');
			}
		}
		/**
		 * saves data to database, it does not matter if edit or add
		 *
		 *@name save
		 *@access public
		 *@param array - data
		*/
		public function save($data, $priority = 1)
		{	
				$this->callExtending("onBeforeSave", $data, $priority);
				
				$model = $this->modelInst();
				
				if(is_object($data) && is_subclass_of($data, "ViewaccessableData"))
				{
						$data = $data->ToArray();
				}
				
				foreach($data as $key => $value)
				{
						$model[$key] = $value;
				}
				
				if($model->write(false, false, $priority))
				{
						$this->callExtending("onAfterSave", $model, $priority);
						$this->model_inst = $model;
						$model->controller = clone $this;
						return $model;
				} else
				{
						return false;
				}
		}
		/**
		 * saves data to database, it does not matter if edit or add
		 * it publishes the data
		 *
		 *@name publish
		 *@access public
		 *@param array - data
		*/
		public function publish($data)
		{	
				if($this->save($data, 2) !== false)
				{
						AddContent::add('<div class="success">'.lang("successful_published", "The data was successfully published!").'</div>');
						$this->redirectback();
				} else
				{
						$debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
						throwError(6, 'Server-Error', 'Could not publish data in '.$debug[0]["file"].' on line '.$debug[0]["line"].'.');
				}
		}
		/**
		 * redirects back
		 *@name redirectback
		 *@access public
		*/
		public function redirectback()
		{
				if(isset($_GET["redirect"]))
				{
						HTTPResponse::redirect($_GET["redirect"]);
				} else if(isset($_POST["redirect"]))
				{
						HTTPResponse::redirect($_POST["redirect"]);
				} else if(isset($_SERVER["HTTP_REFERER"]))
				{
						HTTPResponse::redirect($_SERVER["HTTP_REFERER"]);
				} else
				{
						HTTPResponse::redirect(BASE_URI);
				}
		}
		/**
		 * asks the user if he want's to do sth
		 *
		 *@name confirm
		 *@access public
		 *@param string - question
		 *@param string - title of the okay-button, if you want to set it, default: "yes"
		*/
		public function confirm($title, $btnokay = null) {
			
			$form = new RequestForm(array(
				new HTMLField("confirm", '<div class="text">'. $title . '</div>')
			), lang("confirm", "Confirm..."), md5("confirm_" . $title . $this->class), array(), ($btnokay === null) ? lang("yes") : $btnokay);
			$form->get();
			return true;	
			
		}
		/**
		 * prompts the user
		*/
		public function prompt($title, $validators = array(), $value = null) {

			$form = new RequestForm(array(
				new TextField("prompt_text", $title, $value)
			), lang("prompt", "Insert Text..."), md5("prompt_" . $title . $this->class), $validators);
			$data = $form->get();
			return $data["prompt_text"];	
			
		}
	
}
