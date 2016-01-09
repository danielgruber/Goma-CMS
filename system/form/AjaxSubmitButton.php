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
		$response = new FormAjaxResponse($this->form(), $this);

		$submission = $this->ajaxsubmit;
		$this->form()->post = $this->request->post_params;
		$allowed_result = array();
		$this->form()->result = array();
		// reset result
		// get data

		foreach($this->form()->fields as $field) {
			// patch for correct behaviour on non-ajax and ajax-side
			$field->getValue();

			// now get results
			$result = $field->result();
			if($result !== null) {
				$this->form()->result[$field->dbname] = $result;
				$allowed_result[$field->dbname] = true;
			}
		}

		// validation
		if(!$this->validateForm($response)) {
			return $response;
		}

		if($this->form()->getsecret()) {
			GlobalSessionManager::globalSession()->set("form_secrets." . $this->form()->name(), randomString(30));
			$response->exec('$("#' . $this->form()->fields["secret_" . $this->form()->id()]->id() . '").val("' . convert::raw2js($this->form()->secretKey) . '");');
		}

		$result = $this->form()->result;
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
			$result = call_user_func_array($callback, array($result, $this->form()));
		}
		
		if(is_callable($submission)) {
			return call_user_func_array($submission, array(
				$result,
				$response,
				$this->form(),
				$this->form()->controller
			));
		} else {

			return call_user_func_array(array(
				$this->form()->controller,
				$submission
			), array(
				$result,
				$response,
				$this->form(),
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
	 */
	public function getSubmit() {
		return $this->submit;
	}

	/**
	 * returns the ajax-submit-method
	 */
	public function getAjaxSubmit() {
		return $this->ajaxsubmit;
	}

	/**
	 * @param FormAjaxResponse $response
	 * @return bool
	 */
	private function validateForm($response)
	{
		foreach($this->form()->validators as $validator) {
			$validator->setForm($this->form());
			$v = $validator->validate();
			if($v !== true) {
				$response->addError($v);
			}
		}

		return count($response->getErrors()) == 0;
	}

}

Core::addRules(array('forms/ajax//$form!/$handler!' => 'AjaxSubmitButton'), 100);
