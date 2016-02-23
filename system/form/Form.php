<?php
defined("IN_GOMA") OR die();

loadlang('form');

require_once (FRAMEWORK_ROOT . "form/FormField.php");
require_once (FRAMEWORK_ROOT . "libs/html/HTMLNode.php");
require_once (FRAMEWORK_ROOT . "form/FormAction.php");
require_once (FRAMEWORK_ROOT . "form/Hiddenfield.php");

/**
 * The basic class for every Form in the Goma-Framework. It can have FormFields
 * in it.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 2.4.2
 *
 * @method disable
 * @method reenable
 * @method enableActions
 * @method disableActions
 */
class Form extends gObject {

	/**
	 * session-prefix for form.
	 */
	const SESSION_PREFIX = "form";

	/**
	 * name of the form
	 *@name name
	 *@access protected
	 *@var string
	 */
	protected $name;

	/**
	 * you can use data-handlers, to edit data before it is given to the
	 * submission-method
	 *
	 *@name dataHandlers
	 *@access public
	 */
	protected $dataHandlers = array();

	/**
	 * all available fields in this form
	 *
	 *@name fields
	 *@access public
	 *@var array
	 */
	public $fields = array();

	/**
	 * already rendered fields
	 *
	 *@name renderedFields
	 *@access public
	 */
	public $renderedFields = array();

	/**
	 * all fields the form has to generate from this object
	 *@name showFields
	 *@access public
	 *@var arrayList
	 */
	public $showFields;

	public $fieldSort;

	/**
	 * @var ArrayList<FormField>
	 */
	public $fieldList;

	/**
	 * actions
	 *@name actions
	 *@access public
	 *@var array
	 */
	public $actions = array();

	/**
	 * the form-tag
	 *@name form
	 *@access public
	 */
	public $form;

	/**
	 * default submission
	 *@name submission
	 *@access protected
	 *@var string
	 */
	protected $submission;

	/**
	 * controller of this form
	 *
	 * @var RequestHandler
	 */
	public $controller;

	/**
	 * the model, which belongs to this form
	 *
	 *@name model
	 *@access public
	 */
	public $model;

	/**
	 * form-secret-key
	 *@name secretKey
	 *@access public
	 */
	protected $secretKey;

	/**
	 * validators of the form
	 *@name validators
	 *@access public
	 *@var array
	 */
	public $validators = array();

	/**
	 * result of the form
	 *@name result
	 *@access public
	 *@var array
	 */
	public $result = array();

	/**
	 * if we add secret key to the form
	 *@name secret
	 *@access public
	 *@var bool
	 */
	protected $secret = true;

	/**
	 * url of this form
	 *
	 *@name url
	 *@access public
	 */
	public $url;

	/**
	 * post-data
	 *
	 *@name post
	 *@access public
	 */
	public $post;

	/**
	 * restore-class
	 *
	 *@name restorer
	 *@access public
	 */
	public $restorer;

	/**
	 * defines if we should use state-data in sub-queries of this Form
	 *
	 *@name useStateData
	 *@access public
	 */
	public $useStateData = false;

	/**
	 * current state of this form
	 *
	 * @var FormState
	 */
	public $state;

	/**
	 * request
	 *
	 * @var Request
	 */
	public $request;

	/**
	 * @var bool
	 */
	public $disabled = false;

	/**
	 * @param RequestHandler $controller
	 * @param string $name
	 * @param array $fields
	 * @param array $actions
	 * @param array $validators
	 * @param Request|null $request
	 * @param ViewAccessableData|null $model
     */
	public function __construct($controller, $name, $fields = array(), $actions = array(), $validators = array(), $request = null, $model = null) {

		parent::__construct();

		if(PROFILE)
			Profiler::mark("form::__construct");

		$this->name = strtolower($name);

		$this->initWithRequest($controller, $request);

		$this->initModel($controller, $model);

		$this->checkForRestore();

		$this->fieldList = new ArrayList();

		$this->addFields($fields, $actions, $validators);

		// create form tag
		$this->form = $this->createFormTag();

		if(PROFILE)
			Profiler::unmark("form::__construct");
	}

	/**
	 * adds field to the form.
	 */
	public function addFields($fields, $actions, $validators) {
		// register fields
		/** @var FormField $field */
		foreach($fields as $sort => $field) {
			$this->add($field);
		}

		// register actions
		/** @var FormAction $action */
		foreach($actions as $action) {
			$this->addAction($action);
		}

		$this->validators = array_merge($this->validators, (array) $validators);
	}

	/**
	 * inits form with request.
	 *
	 * @param RequestHandler $controller
	 * @param Request $request
	 */
	protected function initWithRequest($controller, $request) {
		if(!is_a($controller, "RequestHandler")) {
			throw new InvalidArgumentException('Controller "' . get_class($controller) . '" is not a request-handler.');
		}

		$this->controller = $controller;

		$this->secretKey = randomString(30);
		$this->url = str_replace('"', '', $_SERVER["REQUEST_URI"]);
		$this->request = isset($request) ? $request : $controller->getRequest();

		if(isset($this->request)) {
			$this->post = $this->request->post_params;
		} else {
			$this->post = $_POST;
		}
	}

	/**
	 * inits model.
	 *
	 * @param RequestHandler $controller
	 * @param ViewAccessableData|null $model
	 */
	protected function initModel($controller, $model) {
		// set model
		if(isset($model)) {
			$this->model = $model;
		} else if(gObject::method_exists($controller, "modelInst")) {
			if($controller->modelInst()) {
				/** @var Controller $controller */
				$this->model = $controller->modelInst();
			}
		}
	}

	/**
	 * checks for form-restore and inits state.
	 */
	protected function checkForRestore() {
		// if we restore form
		if(
			GlobalSessionManager::globalSession()->hasKey("form_restore." . $this->name()) &&
			GlobalSessionManager::globalSession()->hasKey(self::SESSION_PREFIX . "." . strtolower($this->name))
		) {
			$data = GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . strtolower($this->name));
			$this->useStateData = $data->useStateData;
			$this->result = $data->result;
			$this->post = $data->post;
			$this->state = $data->state;
			$this->restorer = $data;

			GlobalSessionManager::globalSession()->remove("form_restore." . $this->name());
		}

		// get form-state
		if(GlobalSessionManager::globalSession()->hasKey("form_state_" . $this->name) && isset($this->post)) {
			$this->state = new FormState(GlobalSessionManager::globalSession()->get("form_state_" . $this->name));
		} else {
			$this->state = new FormState();
		}
	}

	/**
	 * creates the Form-Tag
	 */
	protected function createFormTag() {
		return new HTMLNode('form', array(
			'method' => 'post',
			'name' => $this->name(),
			'id' => $this->ID(),
			"class" => "form " . $this->name
		));
	}

	/**
	 * activates restore for next generate
	 *
	 *@name activateRestore
	 *@access public
	 */
	public function activateRestore() {
		GlobalSessionManager::globalSession()->set("form_restore." . $this->name, true);
	}

	/**
	 * disables restore for next generate
	 *
	 *@name disableRestore
	 *@access public
	 */
	public function disableRestore() {
		GlobalSessionManager::globalSession()->remove("form_restore." . $this->name);
	}

	/**
	 * redirects to form
	 *
	 *@name redirectToForm
	 *@access public
	 */
	public function redirectToForm() {

		$this->saveToSession();
		$this->activateRestore();
		HTTPResponse::redirect($this->url);
	}

	/**
	 * generates default fields for this form
	 *@name defaultFields
	 *@access public
	 */
	public function defaultFields() {
		if($this->secret) {
			$this->add(new HiddenField("secret_" . $this->ID(), $this->secretKey));
			$this->state->secret = $this->secretKey;
		}

		$this->add(new HiddenField("form_submit_" . $this->name(), "1"));

		Resources::add("system/form/form.js", "js", "tpl");

		if(!isset($this->fields["redirect"]))
			$this->add(new HiddenField("redirect", getredirect()));
	}

	/**
	 * renders the form
	 *
	 * @return mixed|string
	 */
	public function render() {
		Resources::add("form.less", "css");

		// check for submit or append info for user to resubmit.
		if(isset($this->post["form_submit_" . $this->name()]) && GlobalSessionManager::globalSession()->hasKey(self::SESSION_PREFIX . "." . strtolower($this->name))) {
			// check secret
			if($this->secret && $this->post["secret_" . $this->ID()] == $this->state->secret) {
				$this->defaultFields();
				return $this->trySubmit();
			} else if(!$this->secret) {
				$this->defaultFields();
				return $this->trySubmit();
			} else {
				$this->form->append(new HTMLNode("div", array("class" => "notice", ), lang("form_not_saved_yet", "The Data hasn't saved yet.")));
			}
		}

		if($data = $this->checkForSubfield()) {
			return $data;
		}

		// render form now.
		GlobalSessionManager::globalSession()->remove("form_secrets." . $this->name());

		$this->defaultFields();
		return $this->renderForm();
	}

	/**
	 * checks for rendering of sub-field.
	 */
	protected function checkForSubfield() {
		// check get
		foreach($_GET as $key => $value) {
			if(preg_match("/^field_action_([a-zA-Z0-9_]+)_([a-zA-Z0-9_]+)$/", $key, $matches)) {
				if(isset($this->fields[$matches[1]]) && $this->fields[$matches[1]]->hasAction($matches[2])) {
					$this->activateRestore();
					if($data = GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . strtolower($this->name))) {
						$this->result = $data->result;
						$this->post = $data->post;
						$this->restorer = $data;
					}
					return $this->fields[$matches[1]]->handleAction($matches[2]);
				}
			}
		}

		return false;
	}

	/**
	 * renders the form
	 *
	 * @name renderForm
	 * @access public
	 * @return mixed|string
	 */
	public function renderForm($errors = array()) {
		$this->renderedFields = array();
		if(PROFILE)
			Profiler::mark("Form::renderForm");
		$this->callExtending("beforeRender");

		$this->form->action = $this->url;

		$fieldDataSet = new DataSet();
		$actionDataSet = new DataSet();

		$jsonData = array();

		$errorSet = $this->getErrorDataset($errors, $fieldErrors);
		$fields = $this->getFormFields($fieldErrors);
		$actions = $this->getActionFields();
		$validators = $this->getValidator($fields, $actions);

		/** @var FormFieldRenderData $field */
		foreach($fields as $field) {
			$fieldDataSet->add($field->ToRestArray(true, false));
			$jsonData[] = $field->ToRestArray();
		}

		/** @var FormFieldRenderData $action */
		foreach($actions as $action) {
			$actionDataSet->add($action->ToRestArray(true, false));
			$jsonData[] = $action->ToRestArray();
		}

		foreach($validators as $validator) {
			$jsonData[] = $validator;
		}

		$view = new ViewAccessableData();
		$view->fields = $fieldDataSet;
		$view->actions = $actionDataSet;

		$this->form->append($view->customise(array("errors" => new DataSet($errorSet)))->renderWith("form/form.html"));

		$this->callExtending("afterRender");

		$this->form->id = $this->ID();

		if(PROFILE)
			Profiler::mark("Form::renderForm::render");
		$data = $this->form->render();
		Resources::addJS('$(function(){ console.log(new goma.form(' . var_export($this->ID(), true) . ', '.json_encode($jsonData).', '.json_encode($errorSet).')); });');
		if(PROFILE)
			Profiler::unmark("Form::renderForm::render");

		GlobalSessionManager::globalSession()->set("form_state_" . $this->name, $this->state->ToArray());

		$this->saveToSession();

		if(PROFILE)
			Profiler::unmark("Form::renderForm");

		return $data;
	}

	/**
	 * @param array $errors
	 * @param array $fieldErrors
	 * @return array
	 */
	protected function getErrorDataset($errors, &$fieldErrors) {
		$set = array();
		$fieldErrors = array();

		/** @var Exception $error */
		foreach($errors as $error) {
			if(is_a($error, "FormMultiFieldInvalidDataException")) {
				/** @var FormMultiFieldInvalidDataException $error */
				foreach($error->getFieldsMessages() as $field => $message) {
					$set[] = array(
						"message" 	=> lang($message, $message),
						"field" 	=> $field,
						"type"		=> "FormInvalidDataException"
					);
				}
			} else if(is_a($error, "FormInvalidDataException")) {
				/** @var FormInvalidDataException $error */
				$set[] = array(
					"message" 	=> lang($error->getMessage(), $error->getMessage()),
					"field" 	=> $error->getField(),
					"type"		=> "FormInvalidDataException"
				);
			} else {
				$set[] = array(
					"message" 	=> lang($error->getMessage(), $error->getMessage()),
					"type"		=> get_class($error)
				);
			}
		}

		foreach($set as $error) {
			if(isset($error["field"])) {
				$field = strtolower($error["field"]);
				if(!isset($fieldErrors[$field])) {
					$fieldErrors[$field] = array($error);
				} else {
					$fieldErrors[$field][] = $error;
				}
			}
		}

		return $set;
	}

	protected function getFormFields($fieldErrors) {
		$fields = array();

		/** @var FormField $field */
		foreach($this->fieldList as $field) {
			try {
				if ($this->isFieldToRender($field->name)) {
					$this->registerRendered($field->name);

					$fields[] = $field->exportFieldInfo($fieldErrors);
				}
			} catch(Exception $e) {
				$fields[] = new FormFieldErrorRenderData($field->name, $e);
			}
		}

		return $fields;
	}

	protected function getActionFields() {
		$actions = array();

		/** @var array $action */
		foreach($this->actions as $action) {
			try {
				$actions[] = $action["field"]->exportFieldInfo();
			} catch(Exception $e) {
				$actions[] = new FormFieldErrorRenderData($action["field"]->name, $e);
			}
		}

		return $actions;
	}

	protected function getValidator(&$fields, &$actions) {
		$validators = array();

		/** @var FormValidator $validator */
		foreach($this->validators as $name => $validator) {
			try {
				$data = $validator->exportFieldInfo($fields, $actions);
				if($data) {
					$data["name"] = $name;
					$validators[] = $data;
				}
			} catch(Exception $e) {
				$validators[] = new FormFieldErrorRenderData($name, $e);
			}
		}

		return $validators;
	}

	/**
	 * sets the result
	 *
	 * @name setResult
	 * @access public
	 * @return bool
	 */
	public function setResult($result) {
		if(is_object($result)) {
			if(is_a($result, "viewaccessabledata")) {
				$this->useStateData = ($result->queryVersion == "state");
			}
		}

		if(is_object($result) || is_array($result)) {
			$this->result = $result;
			return true;
		}

		return false;
	}

	/**
	 * tries to submit.
	 */
	public function trySubmit() {
		foreach($this->post as $key => $value) {
			if(preg_match("/^field_action_([a-zA-Z0-9_]+)_([a-zA-Z_0-9]+)$/", $key, $matches)) {
				if(isset($this->fields[$matches[1]]) && $this->fields[$matches[1]]->hasAction($matches[2])) {
					$this->activateRestore();
					return $this->fields[$matches[1]]->handleAction($matches[2]);
				}
			}
		}

		/** @var Form $data */
		$data = GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . strtolower($this->name));
		$data->post = $this->post;

		$this->saveToSession();

		try {
			$content = $data->handleSubmit();

			GlobalSessionManager::globalSession()->set("form_state_" . $this->name, $this->state->ToArray());

			return $content;
		} catch(Exception $e) {
			if(is_a($e, "FormNotValidException")) {
				/** @var FormNotValidException $e */
				$errors = $e->getErrors();
			} else {
				$errors = array($e);
			}

			$this->state = $data->state;

			$this->defaultFields();

			return $this->renderForm($errors);
		}
	}

	/**
	 * gets the result of the form and submits it to
	 * - validators
	 * - data-handlers
	 * - gets submission
	 * - submission
	 * @return mixed|string
	 * @throws FormNotSubmittedException
	 * @throws FormNotValidException
	 */
	protected function handleSubmit() {
		$result = $this->gatherResultForSubmit();

		$submission = self::findSubmission($this, $this->post, $result);

		if(!$submission) {
			throw new FormNotSubmittedException();
		}

		if(is_callable($submission)) {
			return call_user_func_array($submission, array(
				$result,
				$this,
				$this->controller
			));
		} else {
			return call_user_func_array(array(
				$this->controller,
				$submission
			), array(
				$result,
				$this,
				$this->controller
			));
		}
	}

	/**
	 * @return array|mixed
	 * @throws FormNotValidException
	 */
	public function gatherResultForSubmit() {
		$this->callExtending("beforeSubmit");

		$allowed_result = array();
		$this->result = array();
		// reset result

		// get data
		/** @var FormField $field */
		foreach($this->fields as $field) {
			$result = $field->result();

			$this->result[$field->dbname] = $result;
			$allowed_result[$field->dbname] = true;
		}

		// validation
		$errors = array();

		foreach($this->validators as $validator) {
			/** @var FormValidator $validator */
			$validator->setForm($this);
			try {
				$validator->validate();
			} catch(Exception $e) {
				$errors[] = $e;
			}
		}

		$result = $this->result;
		if(is_object($result) && is_subclass_of($result, "dataobject")) {
			$result = $result->to_array();

		}

		// validate result
		$realresult = array();
		// now check which fields has edited
		foreach($result as $key => $value) {
			if(isset($allowed_result[$key])) {
				$realresult[$key] = $value;
			}
		}

		$this->callExtending("getResult", $realresult);

		$result = $realresult;
		unset($realresult, $allowed_result);

		foreach($this->getDataHandlers() as $callback) {
			$result = call_user_func_array($callback, array($result, $this));
		}

		if(count($errors) > 0) {
			throw new FormNotValidException($errors);
		}

		$this->callExtending("afterSubmit", $result);

		return $result;
	}

	/**
	 * finds a submission for the form. returns null when not found.
	 * @param Form $form
	 * @param array $post
	 * @param array $result
	 * @return null|string
	 */
	protected static function findSubmission($form, $post, $result) {
		$submission = null;
		// find actions in fields
		/** @var FormAction $field */
		foreach($form->fields as $field) {
			if(is_a($field, "FormActionHandler")) {
				if(isset($post[$field->postname()]) ||
					(isset($post["default_submit"]) && !$field->input->hasClass("cancel") && !$field->input->name != "cancel")) {
					if($field->canSubmit($result) && $submit = $field->getSubmit($result)) {
						if($submit == "@default") {
							$submission = $form->submission;
						} else {
							$submission = $submit;
						}
						break;
					} else {
						return null;
					}
				}
			}
		}

		return $submission;
	}

	//! Manipulate the form
	/**
	 * you can use data-handlers, to edit data before it is given to the
	 * submission-method
	 * you give a callback and you get a result
	 *
	 *@name addDataHandler
	 *@access public
	 *@param callback
	 */
	public function addDataHandler($callback) {
		if(is_callable($callback)) {
			$this->dataHandlers[] = $callback;
		} else {
			throw new InvalidArgumentException("Argument 1 for Form::addDataHandler should be a valid callback.");
		}
	}

	/**
	 * @return array<Callback>
	 */
	public function getDataHandlers()
	{
		return $this->dataHandlers;
	}

	/**
	 * gets the default submission
	 *@name getSubmission
	 *@access public
	 */
	public function getSubmission() {
		return $this->submission;
	}

    /**
     * returns name.
     */
    public function getName() {
        return $this->name;
    }

	/**
	 * @return RequestHandler
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * sets the default submission
	 *@name setSubmission
	 *@access public
	 */
	public function setSubmission($submission) {
		if (is_callable($submission) || gObject::method_exists($this->controller, $submission)) {
			$this->submission = $submission;
		} else {
			throw new LogicException("Unknown Function '$submission'' for Controller {$this->controller}.");
		}
	}

	/**
	 * removes a field
	 *@name remove
	 *@access public
	 */
	public function remove($field) {
		if(isset($this->fields[$field])) {
			unset($this->fields[$field]);
		}

		if(is_string($field)) {
			$this->fieldList->remove($this->fieldList->find("name", $field, true));
		}

		if(isset($this->actions[$field])) {
			unset($this->actions[$field]);
		}

		foreach($this->fieldList as $_field) {
			if(is_subclass_of($_field, "FieldSet")) {
				$_field->remove($field);
			}
		}
	}

	/**
	 * adds a field.
	 *
	 * @param 	FormField $field
	 * @param 	integer $sort sort, 0 is on top, and count means after which field the
	 * field is rendered, null means default
	 * @param 	String $to where the field is added, for example as a subfield to a
	 * tab
	 */
	public function add($field, $sort = null, $to = null) {
		if($to == "this" || !isset($to)) {

			// if it already exists, we should remove it.
			if($this->fieldList->find("name", $field->name)) {
				$this->fieldList->remove($this->fieldList->find("name", $field->name));
			}

			if(isset($sort))
				$this->fieldList->move($field, $sort, true);
			else
				$this->fieldList->add($field);

			$field->setForm($this);
		} else {
			if(isset($this->$to)) {
				$this->$to->add($field, $sort);
			}

		}
	}

	/**
	 * adds a field. alias to @see Form::add.
	 */
	public function addField($field, $sort = null, $to = null) {
		return $this->add($field, $sort, $to);
	}

	/**
	 * adds a field to a given fieldset.
	 *
	 * @param 	String $fieldname fieldset
	 * @param 	FormField $field the field
	 * @param 	int $sort
	 */
	public function addToField($fieldname, $field, $sort = 0) {
		return $this->add($field, $sort, $fieldname);
	}

	/**
	 * adds an action
	 *@name addAction
	 *@access public
	 */
	public function addAction($action) {
		$action->setForm($this);
		$this->actions[$action->name] = array(
			"field" => $action,
			"submit" => $action->getSubmit()
		);
	}

	/**
	 * removes an action
	 *@name removeAction
	 *@access public
	 */
	public function removeAction($action) {
		if(is_object($action)) {
			$action = $action->name;
		}

		unset($this->actions[$action]);
	}

	/**
	 * adds a validator
	 *@name addValidator
	 *@access public
	 */
	public function addValidator($validator, $name) {
		if(is_string($validator) && is_object($name)) {
			$_name = $validator;
			$validator = $name;
			$name = $_name;
			unset($_name);
		}

		if(is_object($validator) && is_a($validator, "FormValidator") && isset($name)) {
			$this->validators[$name] = $validator;
			$validator->setForm($this);
		} else {
			throw new InvalidArgumentException("Form::addValidator - No Object or name given. First parameter needs to be object and second string.");
		}
	}

	/**
	 * removes an validator
	 *@name removeValidator
	 *@access public
	 */
	public function removeValidator($name) {
		unset($this->validators[$name]);
	}

	/**
	 * removes the secret key
	 * DON'T DO THIS IF YOU DON'T KNOW WHAT YOU DO!
	 *@name removeSecret
	 *@acess public
	 */
	public function removeSecret() {
		$this->secret = false;
	}

	/**
	 * activates the secret key
	 *
	 *@name activateSecret
	 *@acess public
	 */
	public function activateSecret() {
		$this->secret = true;
	}

	/**
	 * gets the secret
	 *
	 * @name getSecret
	 * @access public
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}

	/**
	 * gets the field by the given name or returns null.
	 *
	 * @param string $name
	 * @return FormField|null
	 */
	public function getField($name) {

		return (isset($this->fields[strtolower($name)])) ? $this->fields[strtolower($name)] : null;
	}

	/**
	 * returns if a field exists in this form
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasField($name) {
		return (isset($this->fields[strtolower($name)]));
	}

	/**
	 * returns if a field exists and wasn't rendered in this form
	 *
	 * @name isField
	 * @access public
	 * @return bool
	 */
	public function isFieldToRender($name) {
		return ((isset($this->fields[strtolower($name)])) && !isset($this->renderedFields[strtolower($name)]));
	}

	/**
	 * registers a field in this form
	 *
	 * @param string $name
	 * @param FormField $field
	 */
	public function registerField($name, $field) {
		$this->fields[strtolower($name)] = $field;
	}

	/**
	 * unregisters the field from this form
	 * this means that the field will not be rendered
	 *
	 * @param string $name
	 */
	public function unRegister($name) {
		unset($this->fields[strtolower($name)]);
	}

	/**
	 * registers the field as rendered
	 *
	 * @param string $name
	 */
	public function registerRendered($name) {
		$this->renderedFields[strtolower($name)] = true;
	}

	/**
	 * removes the registration as rendered
	 *
	 *@name unregisterRendered
	 *@access public
	 *@param string - name
	 */
	public function unregisterRendered($name) {
		unset($this->renderedFields[strtolower($name)]);
	}

    /**
     * unregisters a field.
     *
     * @param string name
     * @return void
     */
    public function unregisterField($name) {
        if(isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }
    }

	//!Overloading
	/**
	 * Overloading
	 */

	/**
	 * returns a field in this form by name
	 * it's not relevant how deep the field is in this form if the field is *not*
	 * within a ClusterFormField
	 *
	 *@name __get
	 *@access public
	 */
	public function __get($offset) {
		return $this->getField($offset);
	}

	/**
	 * currently set doesn't do anything
	 *
	 *@name __set
	 *@access public
	 */
	public function __set($offset, $value) {
		// currently there is no option to overload a form with fields
	}

	/**
	 * returns if a field exists in this form
	 *
	 * @param string $offset
	 * @return bool
	 */
	public function __isset($offset) {
		return $this->hasField($offset);
	}

	/**
	 * removes a field from this form
	 *
	 *@name __unset
	 *@access public
	 */
	public function __unset($offset) {
		unset($this->fields[$offset]);
	}

	//!Mostly internal APIs
	/**
	 * saves current form to session
	 */
	public function saveToSession() {
		GlobalSessionManager::globalSession()->set(self::SESSION_PREFIX . "." . strtolower($this->name), $this);
	}

	/**
	 * external url of this form
	 *
	 *@name externalURL
	 *@access public
	 */
	public function externalURL() {
		if(isset($this->controller->originalNamespace) && $this->controller->originalNamespace) {
			return ROOT_PATH . BASE_SCRIPT . $this->controller->originalNamespace . "/forms/form/" . $this->name;
		} else {
			return ROOT_PATH . BASE_SCRIPT . "system/forms/" . $this->name;
		}
	}

	/**
	 * sorts the items
	 *@name sort
	 *@access public
	 */
	public function sort($a, $b) {
		if($this->fieldSort[$a->name] == $this->fieldSort[$b->name]) {
			return 0;
		}

		return ($this->fieldSort[$a->name] > $this->fieldSort[$b->name]) ? 1 : -1;
	}

	/**
	 * returns the current real form-object
	 *@name form
	 *@access public
	 */
	public function & form() {
		return $this;
	}



	/**
	 * genrates an id for this form
	 *@name ID
	 *@access public
	 */
	public function ID() {
		return "form_" . md5($this->name);
	}

	/**
	 * generates an name for this form
	 *@name name
	 *@access public
	 */
	public function name() {
		return $this->name;
	}

	public function __wakeup() {
		parent::__wakeup();
		
		/*foreach($this->fields as $f) {
			if(is_object($f)) {
				$f->__wakeup();
			}
		}
		
		foreach($this->actions as $f) {
			if(is_object($f)) {
				$f->__wakeup();
			}
			
		}
		
		foreach($this->validators as $v) {
			if(is_object($f)) {
				$v->__wakeup();
			}
		}
		
		if($this->controller) {
			if(is_object($this->controller)) {
				$this->controller->__wakeup();
			}
		}*/
	}
}
