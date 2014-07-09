<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.04.2014
  * $Version 1.4.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class RequestForm extends Object {
	/**
	 * title of the form
	*/
	protected $title;
	
	/**
	 * form
	*/
	protected $realform;
	
	/**
	 * fields
	*/
	public $fields = array();
	
	/**
	 * results
	*/
	public $results = array();
	
	/**
	 * dialog
	*/
	public $dialog;
	
	/**
	 * return value
	*/
	public $arr;
	
	/**
	 * key for unique assignment
	*/
	public $key;
	
	/**
	 * validators
	*/
	public $validators;
	
	/**
	 * user-set-redirect
	 *
	 *@name redirect
	 *@access public
	 *@var null - string
	*/
	public $redirect;
	
	/**
	 * cause we are acting like a controller, we need also the current request.
	*/
	public $request;
	
	/**
	 * constructing the form
	 *
	 *@name __construct
	 *@access public
	 *@param array - fields
	 *@param string - title
	 *@param string - key
	 *@param array - validators
	 *@param string - title of the okay-button
	*/
	public function __construct($fields, $title, $key = "", $validators = array(), $btnokay = null, $redirect = null) {
		parent::__construct();
		
		
		$this->title = $title;
		$this->dialog = new Dialog("", $title);
		$this->validators = $validators;
		$this->fields = $fields;
		
		if(isset($_POST["requestform_key"])) {
			$this->key .= $_POST["requestform_key"];
		} else {
			$random = randomString(10);
			$this->key .= $random;
			$this->fields[] = new HiddenField("requestform_key", $random);
		}
		
		if($btnokay !== null) {
			$this->btnokay = $btnokay;
		} else {
			$this->btnokay = lang("okay", "OK");
		}
		
		$this->redirect = $redirect;
	}
	
	/**
	 * returns the data if submitted and if it wasn't, it will send the data to the browser
	 *
	 *@name get
	 *@access public
	*/
	public function get() {
		
		if(isset($_POST[md5($this->title . $this->key)]) && isset($_SESSION["requestform"][md5($this->title . $this->key)]))
		{
			$data = $_SESSION["requestform"][md5($this->title . $this->key)];
			return $data;
		}
		
		// GENERATE FORM
		$fields = $this->fields;
		// get all field-names, which are in the form already
		$names = array();
		foreach($fields as $field) 
			$names[] = $field->name;
		
		// now add all post-vars for the next request to emulate we have the same request
		foreach($_POST as $key => $value) {
			if(!in_array($key, $names))
				$fields[] = new HiddenField($key, $value);
		}
		
		$redirect = isset($this->redirect) ? $this->redirect : getredirect();
		
		// get the submit-button
		if(request::isJSResponse() || isset($_GET["dropdownDialog"])) {
			$cancel = new CancelButton("cancel", lang("cancel", "Cancel"), $redirect, $this->dialog->getcloseJS() . "return false;");
			if(isset($_GET["dropdownDialog"]))
				$submit = new AjaxSubmitButton("submit", $this->btnokay, "ajaxDialog", "submit", array("green"));
			else
				$submit = new AjaxSubmitButton("submit", $this->btnokay, "ajax", "submit", array("green"));
			
		} else {
			$cancel = new CancelButton("cancel", lang("cancel", "Cancel"), getredirect(true));
			$submit = new FormAction("okay", $this->btnokay, null, array("green"));
		}
			
		// add field to identify current submit
		$fields[] = new HiddenField(md5($this->title . $this->key), true);
		$this->realform = new Form($this,  "request",$fields, array(
			$cancel,
			$submit
			
		), $this->validators);
		$this->realform->setSubmission("submit");
		
		$data = $this->realform->render();
		
		if(is_array($data)) {
			
			return $data;
		}
		
		$this->dialog->closeButton = false;
		
		if(request::isJSResponse() || isset($_GET["dropdownDialog"])) {
				$this->dialog->content = $data;
				$response = new AjaxResponse();
				$response->exec($this->dialog);
				HTTPResponse::setBody($response->render());
				HTTPResponse::output();
				exit;
		} else {
				$view = new ViewAccessableData();
				return showSite($view->customise(array("content" => $data, "title" => $this->title))->renderWith("framework/dialog.html"), null);

		}
	}
	
	public function submit($data) {
		
		$arr = array();
		foreach($this->fields as $field) {
			if(isset($data[$field->name])) {
				$arr[$field->name] = $data[$field->name];
			}
		}
		$this->arr = $arr;
		$_SESSION["requestform"][md5($this->title . $this->key)] = $arr;
		return $arr;
	}
	/**
	 * ajax-action of this form
	 *
	 *@name ajax
	 *@access public
	 *@param array - data
	 *@param object - ajaxresponse
	 *@param object - form
	*/
	public function ajax($data, $response, $form) {
		$arr = array();
		foreach($this->fields as $field) {
			if(isset($data[$field->name])) {
				$arr[$field->name] = $data[$field->name];
			}
		}
		$this->arr = $arr;
		
		$_SESSION["requestform"][md5($this->title . $this->key)] = $arr;
		$response->exec('var bluebox_id = $("#'.$form->ID().'").parents(".bluebox").attr("id").replace("bluebox_", ""); getblueboxbyid(bluebox_id).close();
		runPreRequest(1, {type: "POST", data: {requestform_key: '.var_export($this->key, true).', "'.md5($this->title . $this->key).'": true}});');
		HTTPResponse::setBody($response->render());
		HTTPResponse::output();
		exit;
	}
	
	/**
	 * ajax-action of this form
	 *
	 *@name ajax
	 *@access public
	 *@param array - data
	 *@param object - ajaxresponse
	 *@param object - form
	*/
	public function ajaxDialog($data, $response, $form) {
		$arr = array();
		foreach($this->fields as $field) {
			if(isset($data[$field->name])) {
				$arr[$field->name] = $data[$field->name];
			}
		}
		$this->arr = $arr;
		
		$_SESSION["requestform"][md5($this->title . $this->key)] = $arr;
		$response->exec('var dropdown_id = $(this).parents(".dropdownDialog").attr("id"); dropdownDialog.get(dropdown_id).hide();
		runPreRequest(1, {type: "POST", data: {requestform_key: '.var_export($this->key, true).', "'.md5($this->title . $this->key).'": true}});');
		HTTPResponse::setBody($response->render());
		HTTPResponse::output();
		exit;
	}
	
	/**
	 * adds a field
	 *
	 *@name add
	 *@access public
	*/
	public function add($field) {
		array_push($this->fields, $field);
	}
	
}