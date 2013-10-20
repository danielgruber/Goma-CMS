<?php
defined("IN_GOMA") OR die();

/**
 * the basic class for each goma-controller, which handles models.
 *
 * @author    	Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Controller
 * @version		2.2.3
 */
class Controller extends RequestHandler
{		
		/**
		 * showform if no edit right
		 *
		 *@name showWithoutRight
		 *@access public
		 *@var bool
		 *@default false
		*/
		public static $showWithoutRight = false;
		
		/**
		 * activates the live-counter on this controller
		 *
		 *@name live_counter
		 *@access public
		*/
		public static $live_counter = false;
		
		/**
		 * how much data is on one page?
		 *
		 *@name perPage
		 *@access public
		*/
		public $perPage = null;
		
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
			"record",
			"version"
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
		 * inits the controller:
		 * - determining and loading model
		 * - checking template
		 *
		 *
		 *@name init
		 *@access public
		*/
		public function Init($request = null)
		{
				parent::Init($request);
				
				if($this->template == "")
				{
						$this->template = $this->model() . ".html";
				}
				
				if(ClassInfo::getStatic($this->classname, "live_counter")) {
					// run the livecounter (statistics), just if it is activated or the visitor wasn't tracked already
					if(settingsController::get("livecounter") == 1 || !isset($_SESSION["user_counted"])  || member::login()) {
					// livecounter
						if(PROFILE) Profiler::mark("livecounter");			
						livecounterController::run();				
						if(PROFILE) Profiler::unmark("livecounter");
						$_SESSION["user_counted"] = TIME; 
					}
				}
				
				if($title = $this->PageTitle()) {
					Core::setTitle($title);
					Core::addBreadCrumb($title, $this->namespace . URLEND);
				}
		}
		
		/**
		 * if this method returns a title automatic title and breadcrumb will be set
		 *
		 *@name title
		 *@access public
		*/
		public function PageTitle() {
			return null;
		}
		
		/**
		 * returns an array of the wiki-article and youtube-video for this controller
		 *
		 *@name helpArticle
		 *@access public
		*/
		public function helpArticle() {
			return array();
		}
		
		/**
		 * returns the model-object
		 *
		 *@name modelInst
		 *@access public
		*/
		public function modelInst($model = null) {
			
			if(is_object($model) && is_a($model, "ViewAccessableData")) {
				$this->model_inst = $model;
				$this->model = $model->dataClass;
			} else if(isset($model) && ClassInfo::exists($model)) {
				$this->model = $model;
			}
			
			if(!is_object($this->model_inst) || (isset($model) && ClassInfo::exists($model))) {
				if(isset($this->model)) {
					$this->model_inst = Object::instance($this->model);
				} else {
					if(ClassInfo::exists($model = substr($this->classname, 0, -10))) {
						$this->model = $model;
						$this->model_inst = Object::instance($this->model);
					} else if(ClassInfo::exists($model = substr($this->classname, 0, -11))) {
						$this->model = $model;
						$this->model_inst = Object::instance($this->model);
					}
				}
			} else if(!isset($this->model)) {
				$this->model = $this->model_inst->dataClass;
			}
			
			if(isset($this->model_inst) && is_object($this->model_inst) && is_a($this->model_inst, "DataSet") && !$this->model_inst->isPagination() && $this->pages && $this->perPage) {
				$page = isset($_GET["pa"]) ? $_GET["pa"] : null;
				if($this->perPage)
					$this->model_inst->activatePagination($page, $this->perPage);
				else
					$this->model_inst->activatePagination($page);
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
					if(ClassInfo::exists($model = substr($this->classname, 0, -10))) {
						$this->model = $model;
					} else if(ClassInfo::exists($model = substr($this->classname, 0, -11))) {
						$this->model = $model;
					}
				} else {
					$this->model = $this->model_inst->dataClass;
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
		public function handleRequest($request, $subController = false)
		{
				$this->areaData = array();
				
				$data = $this->__output(parent::handleRequest($request, $subController));
				
				if($this->helpArticle()) {
					Resources::addData("goma.help.initWithParams(".json_encode($this->helpArticle()).");");
				}
				
				if(Core::is_ajax() && is_object($data) && Object::method_exists($data,"render")) {
					HTTPResponse::setBody($data->render());
					HTTPResponse::output();
					exit;
				}
				
				return $data;
		}
		
		/**
		 * output-layer
		 *
		 *@name __output
		 *@access public
		*/
		public function __output($content) {
				
			return $content;
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
				$this->tplVars["namespace"] = $this->namespace;
				if(is_a($this->modelInst(), "DataObject") && $this->modelInst()->controller != $this) {
					$model = DataObject::Get($this->model(), $this->where);
					$model->controller = clone $this;
					return $model->customise($this->tplVars)->renderWith($this->template);
				} else {
					return $this->modelInst()->customise($this->tplVars)->renderWith($this->template);
				}
			} else {
				$trace = @debug_backtrace();
				throwError(6, "Logical Exception", "No Template for Controller ".$this->classname." in ".$trace[0]["file"]." on line ".$trace[0]["line"].".");
			}
		}
		
		/**
		 * renders given view with areas
		 *
		 *@name renderWithAreas
		 *@access public
		*/
		public function renderWithAreas($template, $model = null) {
			$areas = array_keys($this->areaData);
			
			if(!isset($model))
				$model = $this->modelInst();
			
			foreach($this->areaData as $key => $value) {
				$this->tplVars[$key] = $value;
			}
			// get iAreas
			
			return $model->customise($this->tplVars)->renderWith($template);
		}
		
		/**
		 * renders with given view
		 *
		 *@name renderWith
		 *@access public
		*/
		public function renderWith($template, $model = null) {			
			if(!isset($model))
				$model = $this->modelInst();
			
			return $model->customise($this->tplVars)->renderWith($template);
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
					$controller = $data->controller();
					return $controller->handleRequest($this->request);
				} else {
					return $this->index();
				}
			} else {
				return $this->index();
			}
		}
		
		/**
		 * handles a request with a given versionid in it's controller
		 *
		 *@name version
		 *@access public
		*/
		public function version() {
			$id = $this->getParam("id");
			if($model = $this->model()) {
				$data = DataObject::get_one($model, array("versionid" => $id));
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
		public function form($name = null, $model = null, $fields = array(),$edit = false, $submission = "submit_form", $disabled = false)
		{		
			return $this->buildForm($name, $model, $fields, $edit, $submission, $disabled)->render();
		}
		
		/**
		 * builds the form
		 *
		 *@name buildForm
		 *@access public
		*/
		public function buildForm($name = null, $model = null, $fields = array(),$edit = false, $submission = "submit_form", $disabled = false) {
			if(!isset($model) || !$model) {
				$model = clone $this->modelInst();
			}
			
			if(!Object::method_exists($model, "generateForm")) {
				$trace = @debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				throwError(6, "Logical Exception", "No Method generateForm for Model ".get_class($model)." in ".$trace[0]["file"]." on line ".$trace[0]["line"].".");
			}
			
			// add the right controller
			$controller = clone $this;
			$model->controller($controller);
			
			$form = $model->generateForm($name, $edit, $disabled, isset($this->request) ? $this->request : null);
			$form->setSubmission($submission);
			
			// we add where to the form
			foreach($this->where as $key => $value)
			{
				$form->add(new HiddenField($key, $value));
			}
			
			$this->callExtending("afterForm", $form);
			
			return $form;
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
		 *
		 *@name edit
		 *@access public
		*/
		public function edit()
		{
			if($this->countModelRecords() == 1 && (!$this->getParam("id") || !is_a($this->modelInst(), "DataObjectSet"))  && (!$this->getParam("id") || $this->ModelInst()->id == $this->getParam("id"))) {
				if(!$this->modelInst()->can("Write"))
				{
					if(ClassInfo::getStatic($this->classname, "showWithoutRight") || $this->modelInst()->showWithoutRight) {
						$disabled = true;
					} else {
						return $this->actionComplete("less_rights");
					}
				} else {
					$disabled = false;
				}
				
				return $this->form("edit_" . $this->classname . $this->modelInst()->id, $this->modelInst(), array(
					
				), true, "safe", $disabled);
			} else if($this->getParam("id")) {
				if(preg_match('/^[0-9]+$/', $this->getParam("id"))) {
					$model = DataObject::get_one($this->model(), array_merge($this->where, array("id" => $this->getParam("id"))));
					if($model) {
						return $model->controller(clone $this)->edit();
					} else {
						throwError(6, "Data-Error", "No data found for ID ".$this->getParam("id"));
					}
				} else {
					log_error("Warning: Param ID for Action edit is not an integer: " . print_r($this->request, true));
					$this->redirectBack();
				}
			} else {
				throwError(6, "Invalid Argument", "Controller::Edit should be called if you just have one Record or a given ID in URL.");
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
				if(!$this->modelInst()->can("Delete"))
				{
					return $this->actionComplete("less_rights");
				} else {
					$disabled = false;
				}
				
				if(is_a($this->modelInst(), "DataObjectSet"))
					$toDelete = $this->modelInst()->first();
				else
					$toDelete = $this->modelInst();
				
				// generate description for data to delete
				$description = $toDelete->generateRepresentation(false);
				if(isset($description))
					$description = '<a href="'.$this->namespace.'/edit/'.$toDelete->id . URLEND .'" target="_blank">'.$description.'</a>';
				
				if($this->confirm(lang("delete_confirm", "Do you really want to delete this record?"), null, null, $description)) {
					
					$data = clone $toDelete;
					$toDelete->remove();
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
						return $this->actionComplete("delete_success", $data);
					}
				}
			} else {
				if(preg_match('/^[0-9]+$/', $this->getParam("id"))) {
					$model = DataObject::get_one($this->model(), array_merge($this->where, array("id" => $this->getParam("id"))));
					if($model) {
						return $model->controller(clone $this)->delete();
					} else {
						return false;
					}
				} else {
					log_error("Warning: Param ID for Action delete is not an integer: " . print_r($this->request, true));
					$this->redirectBack();	
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
		 * Alias for Controller::submit_form.
		 *
		 * @access 	public
		 * @param 	array $data
		*/
		public function safe($data)
		{
			if($model = $this->save($data) !== false)
			{
				return $this->actionComplete("save_success", $model);
			} else
			{
				$debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				throwError(6, 'Server-Error', 'Could not save data in '.$debug[0]["file"].' on line '.$debug[0]["line"].'.');
			}
		}
		
		/**
		 * saves data to database and marks the record as draft if versions are enabled.
		 *
		 * Saves data to the database. It decides if to create a new record or not whether an id is set or not.
		 * It marks the record as draft if versions are enabled on this model.
		 *
		 * @access 	public
		 * @param 	array $data
		*/
		public function submit_form($data) {
			if($model = $this->save($data) !== false)
			{
				return $this->actionComplete("save_success", $model);
			} else
			{
				$debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				throwError(6, 'Server-Error', 'Could not save data in '.$debug[0]["file"].' on line '.$debug[0]["line"].'.');
			}
		}
		
		/**
		 * global save method for the database.
		 *
		 * it saves data to the database. you can define which priority should be selected and if permissions are relevant.
		 *
		 * @access	public
		 * @param 	array $data data
		 * @param 	integer $priority Defines what type of save it is: 0 = autosave, 1 = save, 2 = publish
		 * @param 	boolean $forceInsert forces the database to insert a new record of this data and neglect permissions
		 * @param 	boolean $forceWrite forces the database to write without involving permissions
		*/
		public function save($data, $priority = 1, $forceInsert = false, $forceWrite = false)
		{
				$this->callExtending("onBeforeSave", $data, $priority);
				
				$model = $this->modelInst()->_clone();
				
				if(is_object($data) && is_subclass_of($data, "ViewaccessableData"))
				{
						$data = $data->ToArray();
				}
				
				foreach($data as $key => $value)
				{
						$model[$key] = $value;
				}
				
				if($model->write($forceInsert, $forceWrite, $priority))
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
		 * saves data to database and marks the record published.
		 *
		 * Saves data to the database. It decides if to create a new record or not whether an id is set or not.
		 * It marks the record as published.
		 *
		 * @access 	public
		 * @param 	array $data
		*/
		public function publish($data)
		{	
			if($model = $this->save($data, 2) !== false)
			{
				return $this->actionComplete("publish_success", $model);
			} else
			{
				$debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				throwError(6, 'Server-Error', 'Could not publish data in '.$debug[0]["file"].' on line '.$debug[0]["line"].'.');
			}
		}
		
		/**
		 * this is the method, which is called when a action was completed successfully or not.
		 *
		 * it is called when actions of this controller are completed and the user should be notified. For example if the user saves data and it was successfully saved, this method is called with the param save_success. It is also called if an error occurs.
		 *
		 * @param 	string $action the action called
		 * @param	object $record optional: record if available
		 * @access 	public
		*/
		public function actionComplete($action, $record = null) {
			switch($action) {
				case "publish_success": 
					AddContent::addSuccess(lang("successful_published", "The entry was successfully published."));
					$this->redirectback();
				break;
				case "save_success":
					AddContent::addSuccess(lang("successful_saved", "The data was successfully saved."));
					$this->redirectback();
				break;
				case "less_rights":
					
					return '<div class="error">' . lang("less_rights", "You are not allowed to visit this page or perform this action.") . '</div>';
				break;
				case "delete_success":
					$this->redirectback();
				break;
			}
		}
		
		/**
		 * redirects back to the page before based on some information by the user.
		 *
		 * it detects redirect-params with GET and POST-Vars. It uses the Referer and as a last instance it redirects to homepage.
		 * you can define params to add to the redirect if you want.
		 *
		 * @access	public
		 * @param 	string $param get-parameter
		 * @param 	string $value value of the get-parameter
		*/
		public function redirectback($param = null, $value = null)
		{
				if(isset($_GET["redirect"]))
				{
						$redirect = $_GET["redirect"];
				} else if(isset($_POST["redirect"]))
				{
						$redirect = $_POST["redirect"];
				} else 
				{
						$redirect = $this->originalNamespace;
				}
				
				if(isset($param) && isset($value))
					$redirect = TPLCaller::addParamToURL($redirect, $param, $value);
					
				HTTPResponse::redirect($redirect);
		}
		
		/**
		 * asks the user if he want's to do sth
		 *
		 *@name confirm
		 *@access public
		 *@param string - question
		 *@param string - title of the okay-button, if you want to set it, default: "yes"
		 *@param string|null - redirect on cancel button
		*/
		public function confirm($title, $btnokay = null, $redirectOnCancel = null, $description = null) {
			
			$form = new RequestForm(array(
				new HTMLField("confirm", '<div class="text">'. $title . '</div>')
			), lang("confirm", "Confirm..."), md5("confirm_" . $title . $this->classname), array(), ($btnokay === null) ? lang("yes") : $btnokay, $redirectOnCancel);
			if(isset($description)) {
				$form->add(new HTMLField("description", '<div class="confirmDescription">'.$description.'</div>'));
			}
			$form->get();
			return true;
			
		}
		
		/**
		 * prompts the user
		 *
		 *@name prompt
		 *@param string - message
		 *@param array - validators
		 *@param string - default value
		 *@param string|null - redirect on cancel button
		*/
		public function prompt($title, $validators = array(), $value = null, $redirectOnCancel = null, $usePwdField = null) {
			
			$field = ($usePwdField) ? new PasswordField("prompt_text", $title, $value) : new TextField("prompt_text", $title, $value);
			$form = new RequestForm(array(
				$field
			), lang("prompt", "Insert Text..."), md5("prompt_" . $title . $this->classname), $validators, null, $redirectOnCancel);
			$data = $form->get();
			return $data["prompt_text"];	
			
		}
		
		/**
		 * keychain
		*/
		
		/**
		 * adds a password to the keychain
		 *
		 *@name keyChainAdd
		 *@access public
		 *@param string - password
		 *@param bool - use cookie
		 *@param int - cookie-livetime
		*/
		public static function keyChainAdd($password, $cookie = null, $cookielt = null) {
			if(!isset($cookie)) {
				$cookie = false;
			}
			
			if(!isset($cookielt)) {
				$cookielt = 14 * 24 * 60 * 60;
			}
			
			if(isset($_SESSION["keychain"])) {
				$_SESSION["keychain"] = array();
			}
			$_SESSION["keychain"][] = $password;
			
			if($cookie) {
				setCookie("keychain_" . md5(md5($password)), md5($password), NOW + $cookielt);
			}
		}
		
		/**
		 * checks if a password is in keychain
		 *
		 *@name keyChainCheck
		 *@access public
		*/
		public static function KeyChainCheck($password) {
			if((isset($_SESSION["keychain"]) && in_array($password, $_SESSION["keychain"])) || (isset($_COOKIE["keychain_" . md5(md5($password))]) && $_COOKIE["keychain_" . md5(md5($password))] == md5($password)) || isset($_GET[getPrivateKey()])) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * removes a password from keychain
		 *
		 *@name keyChainRemove
		 *@access public
		*/
		public static function keyChainRemove($password) {
			if(isset($_SESSION["keychain"])) {
				if($key = array_search($password, $_SESSION["keychain"])) {
					unset($_SESSION["keychain"][$key]);
				}
			}
			
			setCookie("keychain_" . md5(md5($password)), null, -1);
		}
}
