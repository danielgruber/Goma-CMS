<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.06.2011
  * $Version 2.0.0 - 004
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class RequestForm extends Object {
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
	public function __construct($fields, $title, $key = "", $validators = array(), $btnokay = null) {
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
		
		// get the submit-button
		if(request::isJSResponse() || isset($_GET["dropdownDialog"])) {
			$cancel = new CancelButton("cancel", lang("cancel", "Cancel"), getredirect(), $this->dialog->getcloseJS() . "return false;");
			if(isset($_GET["dropdownDialog"]))
				$submit = new AjaxSubmitButton("submit", $this->btnokay, "ajaxDialog", "submit");
			else
				$submit = new AjaxSubmitButton("submit", $this->btnokay, "ajax", "submit");
			
		} else {
			$cancel = new CancelButton("cancel", lang("cancel", "Cancel"), getredirect(true));
			$submit = new FormAction("submit", $this->btnokay);
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
				$this->dialog->content = $data;
				showsite($this->dialog->renderHTML(), /*$this->title*/ "");
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
		var settings = getPreRequest(1);
		if(settings.data == null || typeof settings.data == "string")
			settings.data = [];
		
		
		
		settings.type = "POST";
		settings.data["'.md5($this->title . $this->key).'"] = true;
		settings.data["requestform_key"] = '.var_export($this->key, true).';
		$.ajax(settings);');
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
		var settings = getPreRequest(1);
		if(settings.data == null || typeof settings.data == "string")
			settings.data = {};
		
		settings.type = "POST";
		settings.data["'.md5($this->title . $this->key).'"] = true;
		settings.data.requestform_key = '.var_export($this->key, true).';

		$.ajax(settings);');
		HTTPResponse::setBody($response->render());
		HTTPResponse::output();
		exit;
	}
	
}