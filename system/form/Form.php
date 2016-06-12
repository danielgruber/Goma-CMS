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
 * @method enableActions
 * @method disableActions
 */
class Form extends AbstractFormComponentWithChildren {

	/**
	 * session-prefix for form.
	 */
	const SESSION_PREFIX = "form";

	/**
	 * you can use data-handlers, to edit data before it is given to the
	 * submission-method
	 */
	protected $dataHandlers = array();

	/**
	 * actions
	 * @var FormAction[]
	 */
	public $actions = array();

	/**
	 * the form-tag
	 *
	 * @var HTMLNode
	 */
	public $form;

	/**
	 * default submission
	 * @var string
	 */
	protected $submission;

	/**
	 * controller of this form
	 *
	 * @var RequestHandler
	 */
	public $controller;

	/**
	 * form-secret-key
	 *
	 * @var string
	 */
	protected $secretKey;

	/**
	 * validators of the form
	 * @var FormValidator[]
	 */
	public $validators = array();

	/**
	 * result of the form
	 *
	 * @var array
	 */
	public $result = array();

	/**
	 * restore-class
	 */
	public $restorer;

	/**
	 * current state of this form
	 *
	 * @var FormState
	 */
	public $state;

	/**
	 * leave-check.
	 * @var bool
	 */
	protected $leaveCheck = true;

	/**
	 * @param RequestHandler $controller
	 * @param string $name
	 * @param array $fields
	 * @param array $actions
	 * @param array $validators
	 * @param Request|null $request
	 * @param ViewAccessableData|null $model
	 */
	public function __construct($controller = null, $name = null, $fields = array(), $actions = array(), $validators = array(), $request = null, $model = null) {

		parent::__construct($name, $fields, $model);

		if(!isset($controller))
			return;

		if(PROFILE)
			Profiler::mark("form::__construct");

		$this->initWithRequest($controller, $request);

		$this->addFields(array(), $actions, $validators);

		$this->checkForRestore();

		// create form tag
		$this->form = $this->createFormTag();

		if(PROFILE)
			Profiler::unmark("form::__construct");
	}

	/**
	 * adds field to the form.
	 * @param FormField[] $fields
	 * @param FormAction[] $actions
	 * @param FormValidator[] $validators
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

		foreach($validators as $key => $value) {
			$this->addValidator($value, $key);
		}
	}

	/**
	 * inits form with request.
	 *
	 * @param RequestHandler $controller
	 * @param Request $request
	 * @return string
	 */
	protected function initWithRequest($controller, $request) {
		if(!is_a($controller, "RequestHandler")) {
			throw new InvalidArgumentException('Controller "' . get_class($controller) . '" is not a request-handler.');
		}

		$this->controller = $controller;
		$this->request = isset($request) ? $request : $controller->getRequest();

		if(!isset($this->request)) {
			$this->request = new Request(isset($_POST) ? "post" : "get", URL, $_GET, $_POST);
		}

		$this->url = str_replace('"', '', ROOT_PATH . BASE_SCRIPT . $this->getRequest()->url . URLEND);

		if(isset($this->controller->originalNamespace) && $this->controller->originalNamespace) {
			$this->namespace = ROOT_PATH . BASE_SCRIPT . $this->controller->originalNamespace . "/forms/form/" . $this->name;
		} else {
			$this->namespace = ROOT_PATH . BASE_SCRIPT . "system/forms/" . $this->name;
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
			$this->state = $data->state;
			$this->restorer = $data;

			if($data->secret) {
				$this->activateSecret($data->secretKey);
			}

			GlobalSessionManager::globalSession()->remove("form_restore." . $this->name());
		} else {
			// get form-state
			if(GlobalSessionManager::globalSession()->hasKey("form_state_" . $this->name)) {
				$this->state = new FormState(GlobalSessionManager::globalSession()->get("form_state_" . $this->name));
				$this->activateSecret($this->state->secret);
			} else {
				$this->state = new FormState();
				$this->activateSecret();
			}
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
	 */
	public function redirectToForm() {
		$this->saveToSession();
		$this->activateRestore();
		HTTPResponse::redirect($this->url);
	}

	/**
	 * generates default fields for this form
	 */
	public function defaultFields() {
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
		if(isset($this->getRequest()->post_params["form_submit_" . $this->name()]) &&
			GlobalSessionManager::globalSession()->hasKey(self::SESSION_PREFIX . "." . strtolower($this->name))) {
			// check secret
			if($this->secretKey && isset($this->getRequest()->post_params["secret_" . $this->ID()]) &&
				$this->getRequest()->post_params["secret_" . $this->ID()] == $this->state->secret) {
				$this->defaultFields();
				return $this->trySubmit();
			} else if(!$this->secretKey) {
				$this->defaultFields();
				return $this->trySubmit();
			} else {
				$this->form->append(new HTMLNode("div", array("class" => "notice form"), lang("form_not_saved_yet", "The Data hasn't saved yet.")));
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
		if(isset($this->request) && isset($this->request->get_params)) {
			foreach ($this->request->get_params as $key => $value) {
				if (preg_match("/^field_action_([a-zA-Z0-9_]+)_([a-zA-Z0-9_]+)$/", $key, $matches)) {
					if (isset($this->fields[$matches[1]]) && $this->fields[$matches[1]]->hasAction($matches[2])) {
						$this->activateRestore();
						if ($data = GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . strtolower($this->name))) {
							$this->result = $data->result;
							$this->post = $data->post;
							$this->restorer = $data;
						}

						return $this->fields[$matches[1]]->handleAction($matches[2]);
					}
				}
			}
		}

		return false;
	}

	/**
	 * renders the form
	 *
	 * @param array $errors
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
		$js = 'var form = new goma.form(' . var_export($this->ID(), true) . ', '.var_export($this->leaveCheck, true).', '.json_encode($jsonData).', '.json_encode($errorSet).');';
		if(count($errors) > 0) {
			$js .= "form.setLeaveCheck(true);";
		}
		Resources::addJS('$(function(){ '.$js.' });');

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
	 * @param array|ViewAccessableData $result
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
		foreach($this->request->post_params as $key => $value) {
			if(preg_match("/^field_action_([a-zA-Z0-9_]+)_([a-zA-Z_0-9]+)$/", $key, $matches)) {
				if(isset($this->fields[$matches[1]]) && $this->fields[$matches[1]]->hasAction($matches[2])) {
					$this->activateRestore();
					return $this->fields[$matches[1]]->handleAction($matches[2]);
				}
			}
		}

		if($this->secretKey) {
			$this->activateSecret();
		}

		/** @var Form $data */
		$data = GlobalSessionManager::globalSession()->get(self::SESSION_PREFIX . "." . strtolower($this->name));
		$data->request = $this->request;

		$this->saveToSession();

		try {
			$content = $data->handleSubmit();

			GlobalSessionManager::globalSession()->set("form_state_" . $this->name, $this->state->ToArray());

			return $content;
		} catch(Exception $e) {
			if(is_a($e, "FormNotValidException")) {
				/** @var FormNotValidException $e */
				$errors = $e->getErrors();
			} else if(!is_a($e, "FormNotSubmittedException")) {
				$errors = array($e);
			} else {
				$errors = array();
			}

			$this->state = $data->state;
			$this->state->secret = $this->secretKey;

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
		$submissionWithoutValidation = self::findSubmission($this, $this->getRequest()->post_params, null);

		$result = $this->gatherResultForSubmit(is_null($submissionWithoutValidation));

		$submission = isset($submissionWithoutValidation) ?
			$submissionWithoutValidation :
			self::findSubmission($this, $this->getRequest()->post_params, $result);

		if(!$submission) {
			throw new FormNotSubmittedException();
		}

		if(is_callable($submission) && !is_string($submission)) {
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
	 * @param bool $validate if to validate result
	 * @return array|mixed
	 * @throws FormNotValidException
	 */
	public function gatherResultForSubmit($validate = true) {
		$this->callExtending("beforeSubmit");

		$this->result = $result = $this->fetchResultWithDataHandlers();

		if($validate) {
			// validation
			$errors = array();

			foreach ($this->validators as $validator) {
				/** @var FormValidator $validator */
				$validator->setForm($this);
				try {
					$validator->validate();
				} catch (Exception $e) {
					$errors[] = $e;
				}
			}

			if (count($errors) > 0) {
				throw new FormNotValidException($errors);
			}
		}

		$this->callExtending("afterSubmit", $result);

		return $result;
	}

	/**
	 * @return array
	 */
	protected function fetchResultWithDataHandlers() {
		$result = array();

		// get data
		/** @var AbstractFormComponent $field */
		foreach($this->fieldList as $field) {
			if($field->name != "secret_" . $this->ID()) {
				$field->argumentResult($result);
			}
		}

		$this->callExtending("getResult", $result);

		foreach($this->getDataHandlers() as $callback) {
			$result = call_user_func_array($callback, array($result, $this));
		}

		return $result;
	}

	/**
	 * finds a submission for the form. returns null when not found.
	 *
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
	 */
	public function getSubmission() {
		return $this->submission;
	}

	/**
	 * @return RequestHandler
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * sets the default submission
	 * @param string|array $submission
	 */
	public function setSubmission($submission) {
		if (is_callable($submission) || gObject::method_exists($this->controller, $submission)) {
			$this->submission = $submission;
		} else {
			throw new LogicException("Unknown Function '$submission'' for Controller {$this->controller}.");
		}
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
	 *
	 * @deprecated
	 * @param FormValidator $validator
	 * @param string $name
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
	 */
	public function removeSecret() {
		$this->secretKey = null;
		$this->remove("secret_" . $this->ID());
		$this->state->secret = null;
	}

	/**
	 * activates the secret key
	 * @param string|null $secret
	 */
	public function activateSecret($secret = null) {
		if($this->secretKey) $this->removeSecret();

		$this->secretKey = is_string($secret) ? $secret : randomString(30);
		$this->add(new HiddenField("secret_" . $this->ID(), $this->secretKey));
		$this->state->secret = $this->secretKey;
	}

	/**
	 * gets the secret
	 *
	 * @return bool
	 */
	public function getSecret() {
		return !!$this->secretKey;
	}


	//!Mostly internal APIs
	/**
	 * saves current form to session
	 */
	public function saveToSession() {
		GlobalSessionManager::globalSession()->set(self::SESSION_PREFIX . "." . strtolower($this->name), $this);
	}

	/**
	 * genrates an id for this form
	 */
	public function ID() {
		return "form_" . md5($this->name);
	}

	/**
	 * generates an name for this form
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function getLeaveCheck()
	{
		return $this->leaveCheck;
	}

	/**
	 * @param bool $leaveCheck
	 * @return $this
	 */
	public function setLeaveCheck($leaveCheck)
	{
		$this->leaveCheck = $leaveCheck;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSecretKey()
	{
		return $this->secretKey;
	}

	public function field($info)
	{
		throw new InvalidArgumentException("Can't add Form to a Form below.");
	}

	public function js()
	{
		// TODO: Implement js() method.
	}
}
