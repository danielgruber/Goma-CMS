<?php
defined("IN_GOMA") OR die();

/**
 * A simple FormAction, which submits data via Ajax and calls the
 * ajax-response-handler given.
 *
 * you should return the given AjaxResponse-Object or Plain JavaScript in
 * Ajax-Response-Handler.
 * a handler could look like this:
 * public function ajaxSave($data, $response) {
 *      $response->exec("alert('Nice!')");
 *      return $response;
 * }
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Form
 * @version	2.1.9
 */
class AjaxSubmitButton extends FormAction {
	/**
	 * the action for ajax-submission
	 *
	 *@name ajaxsubmit
	 *@acccess protected
	 */
	protected $ajaxsubmit;

	/**
	 *@name __construct
	 *@access public
	 *@param string - name
	 *@param string - title
	 *@param string - ajax submission
	 *@param string - optional submission
	 *@param object - form
	 */
	public function __construct($name = "", $value = "", $ajaxsubmit = null, $submit = null, $classes = null, &$form = null) {

		parent::__construct($name, $value, null, $classes);
		if($submit === null)
			$submit = "@default";

		$this->submit = $submit;
		$this->ajaxsubmit = $ajaxsubmit;
		if($form != null) {
			$this->parent = &$form;
			$this->setForm($form);
		}
	}

	/**
	 * generates the js
	 * @name js
	 * @access public
	 * @return string
	 */
	public function js() {
		// appendix to the url
		$append = '?redirect=' . urlencode(getRedirect());
		foreach($_GET as $key => $val) {
			$append .= '&' . urlencode($key) . '=' . urlencode($val);
		}

		Resources::add("system/form/actions/AjaxSubmitButton.js", "js", "tpl");

		return 'initAjaxSubmitbutton('.var_export($this->ID(), true).', '.var_export($this->divID(), true).', '.var_export($this->form()->ID(), true).', '.var_export($this->externalURL() . "/", true).', '.var_export($append, true).');';
	}

	/**
	 * endpoint for ajax request.
	 *
	 * @name handleRequest
	 * @access public
	 * @param object - request
	 * @return false|mixed|null|string|void
	 */
	public function handleRequest(request $request) {
		$this->request = $request;

		$this->init();

		return $this->submit();
	}

	/**
	 * submit-function
	 * @name submit
	 * @access public
	 * @return mixed
	 */
	public function submit() {
		$response = new AjaxResponse;
		$response->exec('$("#' . $this->form()->ID() . '").find(".error").remove();');
		$response->exec('var ajax_button = $("#' . $this->ID() . '");');

		$submission = $this->ajaxsubmit;
		$form = $this->form();
		$form->post = $_POST;
		$allowed_result = array();
		$form->result = array();
		// reset result
		// get data

		foreach($form->fields as $field) {
			// patch for correct behaviour on non-ajax and ajax-side
			$field->getValue();

			// now get results
			$result = $field->result();
			if($result !== null) {
				$form->result[$field->dbname] = $result;
				$allowed_result[$field->dbname] = true;
			}
		}

		// validation
		$valid = true;
		$errors = new HTMLNode('div', array('class' => "error"), array(new HTMLNode('ul', array())));

		foreach($form->validators as $validator) {
			$validator->setForm($form);
			$v = $validator->validate();
			if($v !== true) {
				$valid = false;
				$errors->getNode(0)->append(new HTMLNode('li', array('class' => 'erroritem'), $v));
			}
		}

		if($valid !== true) {
			$response->prepend("#" . $form->ID(), $errors->render());
			return $response->render();
		}

		if($form->getsecret()) {
			GlobalSessionManager::globalSession()->set("form_secrets." . $form->name(), randomString(30));
			$response->exec('$("#' . $form->fields["secret_" . $form->id()]->id() . '").val("' . convert::raw2js($this->form()->secretKey) . '");');
		}

		$result = $form->result;
		if(is_object($result) && is_subclass_of($result, "dataobject")) {
			/** @var DataObject $result */
			$result = $result->ToArray();
		}

		$realresult = array();
		// now check which fields has edited
		foreach($result as $key => $value) {
			if(isset($allowed_result[$key])) {
				$realresult[$key] = $value;
			}
		}

		$result = $realresult;
		unset($realresult, $allowed_result);

		foreach($this->form()->getDataHandlers() as $callback) {
			$result = call_user_func_array($callback, array($result, $form));
		}
		
		if(is_callable($submission)) {
			return call_user_func_array($submission, array(
				$result,
				$response,
				$this,
				$this->form()->controller
			));
		} else {

			return call_user_func_array(array(
				$form->controller,
				$submission
			), array(
				$result,
				$response,
				$form,
				$this->form()->controller
			));
		}
	}

	/**
	 * sets the submit-method and ajax-submit-method
	 *
	 *@name setSubmit
	 *@access public
	 *@param string - submit
	 *@param string - ajaxsubmit
	 */
	public function setSubmit($submit, $ajaxsubmit = null) {
		$this->submit = $submit;
		if(isset($ajaxsubmit))
			$this->ajaxsubmit = $ajaxsubmit;
	}

	/**
	 * returns the submit-method
	 *
	 *@name getSubmit
	 *@access public
	 */
	public function getSubmit() {
		return $this->submit;
	}

	/**
	 * returns the ajax-submit-method
	 *
	 *@name getAjaxSubmit
	 *@access public
	 */
	public function getAjaxSubmit() {
		return $this->ajaxsubmit;
	}

}

Core::addRules(array('forms/ajax//$form!/$handler!' => 'AjaxSubmitButton'), 100);
