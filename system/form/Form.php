<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 30.10.2011
  * $Version - 004
 */
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang('form');

require_once(FRAMEWORK_ROOT . "form/FormField.php");
require_once(FRAMEWORK_ROOT . "libs/html/HTMLNode.php");
require_once(FRAMEWORK_ROOT . "form/FormAction.php");
require_once(FRAMEWORK_ROOT . "form/Hiddenfield.php");

class Form extends object
{
		/**
		 * you can use data-handlers, to edit data before it is given to the submission-method
		 *
		 *@name dataHandlers
		 *@access public
		*/
		public $dataHandlers = array();
		/**
		 * all available fields in this form
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
		 *@var array
		*/
		public $showFields = array();
		/**
		 * to sort fields
		 *@name fieldSort
		 *@access public
		 *@var array
		*/
		public $fieldSort = array();
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
		 * the div for the actions
		 *@name actionDiv
		 *@access public
		*/
		public $actionDiv;
		/**
		 * the div for the fields
		 *@name fieldsDiv
		 *@access public
		*/
		public $fieldsDiv;
		/**
		 * default submission
		 *@name submission
		 *@access public
		 *@var string
		*/
		private $submission;
		/**
		 * controller of this form
		 *@name controller
		 *@access public
		 *@var object
		*/
		public $controller;
		/**
		 * form-secret-key
		 *@name secretKey
		 *@access public
		*/
		public $secretKey;
		/**
		 * name of the form
		 *@name name
		 *@access public
		 *@var string
		*/
		public $name;
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
		 * url
		 *
		 *@name action
		*/
		public $action = "";
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
		 *@name __construct
		 *@access public
		 *@param object - controller
		 *@param string - name
		 *@param array - fields
		 *@param array - actions
		 *@param array - validators
		*/
		public function __construct($controller, $name, $fields = array(), $actions = array(), $validators = array())
		{
				
				parent::__construct();
				
				/* --- */
				
				Profiler::mark("form::__construct");
				
				gloader::load("modernizr");
				
				if(!is_object($controller))
				{
						throwError(5, 'PHP-Error', 'Controller '.$controller.' is no object in '.__FILE__.' on line '.__LINE__.'');
				}
				
				$this->controller = $controller;
				$this->name = $name;
				$this->secretKey = randomString(30);
				$this->url = $_SERVER["REQUEST_URI"];
				$this->post = $_POST;
				
				
				
				if(isset($_SESSION["form_restore_" . $this->name]) && session_store_exists("form_" . strtolower($this->name))) {
					$data = session_restore("form_" . strtolower($this->name));
					$this->result = $data->result;
					$this->post = $data->post;
					$this->restorer = $data;
					unset($_SESSION["form_restore_" . $this->name]);
				}
				
				foreach($fields as $sort => $field)
				{
						$this->showFields[$field->name] = $field;
						$this->fieldSort[$field->name] = $sort;
						$field->setForm($this);
						$sort++;
				}
				
				foreach($actions as $submit => $action)
				{
						$action->setForm($this);
				}
				
				if(!is_array($validators))
					$validators = array($validators);
				
				$this->validators = array_merge($this->validators, $validators);
				
				// create form tag
				$this->form = $this->createFormTag();
				$this->actionDiv = $this->createActionDiv();
				$this->fieldsDiv = $this->createFieldsDiv();
				
				Profiler::unmark("form::__construct");
		}
		/**
		 * creates the Form-Tag
		*/
		public function createFormTag()
		{	
				return new HTMLNode('form', array(
					'method'	=> 'post',
					'name'		=> $this->name(),
					'id'		=> $this->ID(),
					"class"		=> "form " . $this->name
				));
		}
		/**
		 * creates the Action-Div
		*/
		public function createActionDiv()
		{	
				return new HTMLNode('div', array(
					'class'	=> "actions"
				));
		}
		/**
		 * creates the Action-Div
		*/
		public function createFieldsDiv()
		{	
				return new HTMLNode('div', array(
					'class'	=> "fields"
				));
		}
		/**
		 * activates restore for next generate
		 *
		 *@name activateRestore
		 *@access public
		*/
		public function activateRestore() {
			$_SESSION["form_restore_" . $this->name] = true;
		}
		/**
		 * disables restore for next generate
		 *
		 *@name disableRestore
		 *@access public
		*/
		public function disableRestore() {
			unset($_SESSION["form_restore_" . $this->name]);
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
		public function defaultFields()
		{
							
				if($this->secret)
				{
						$this->add(new HiddenField("secret_".$this->ID()."", $this->secretKey));
						$_SESSION["form_secrets"][$this->name()] = $this->secretKey; 
				}
				
				$this->add(new HiddenField("form_submit_" . $this->name(), "1"));
				// add that this is a submit-function
				$this->add(new JavaScriptField("leave_check", '$(function(){
					$("#'.$this->ID().'").bind("formsubmit",function(){
						self.leave_check = true;
					});
					
					$(function(){
						$("#'.$this->ID().'").submit(function(){
							var eventb = jQuery.Event("beforesubmit");
							$("#'.$this->id().'").trigger(eventb);
							if ( eventb.result === false ) {
								return false;
							}
							
							var event = jQuery.Event("formsubmit");
							$("#'.$this->id().'").trigger(event);
							if ( event.result === false ) {
								return false;
							}
						});
					});
					
					$("#'.$this->ID().'").find("select, input[type=text], input[type=hidden], input[type=radio], input[type=checkbox], input[type=password], textarea").change(function(){
						self.leave_check = false;
					});
					
					$("#'.$this->ID().' > .default_submit").click(function(){
						$("#'.$this->ID().' > .actions  input[type=submit]").each(function(){
							if($(this).attr("name") != "cancel" && !$(this).hasClass("cancel")) {
								$(this).click();
								return false;
							}
						});
						return false;
					});
				});'));
								
				
				
				if(!isset($this->fields["redirect"]))
					$this->add(new HiddenField("redirect", getredirect()));
		}
		/**
		 * renders the form
		 *@name render
		 *@access public
		*/
		public function render()
		{
				Resources::add("form.css", "css");
				if(isset($_POST["form_submit_" . $this->name()]))
				{
						// check secret
						if($this->secret && isset($_SESSION["form_secrets"][$this->name()]) && $_POST["secret_" . $this->ID()] == $_SESSION["form_secrets"][$this->name()])
						{
								$this->defaultFields();
								return $this->submit();
						} else if(!$this->secret)
						{
								$this->defaultFields();
								return $this->submit();
						} else {
								$this->form->append(new HTMLNode("div", array(
										"class"=> "notice",
									), lang("form_not_saved_yet", "The Data hasn't saved yet.")));	
						}
				}
				
				unset($_SESSION["form_secrets"][$this->name()]);
				$this->defaultFields();
				return $this->renderForm();
		}
		/**
		 * renders the form
		 *@name renderForm
		 *@access public
		*/
		public function renderForm()
		{
				$this->renderedFields = array();
				Profiler::mark("Form::renderForm");
				$this->callExtending("beforeRender");
				
				// check get
				foreach($_GET as $key => $value) {
					if(_ereg("^field_action_([a-zA-Z0-9_]+)_([a-zA-Z0-9_]+)$", $key, $matches)) {
						
						if(isset($this->fields[$matches[1]]) && $this->fields[$matches[1]]->hasAction($matches[2])) {
							$this->activateRestore();
							if(session_store_exists("form_" . strtolower($this->name))) {
								$data = session_restore("form_" . strtolower($this->name));
								$this->result = $data->result;
								$this->post = $data->post;
								$this->restorer = $data;
							}
							return $this->fields[$matches[1]]->handleAction($matches[2]);
						}
					}
				}
				
				
				$this->saveToSession();
				
				$this->form->action = ($this->action != "") ? $this->action : $this->url;
				
				$this->form->append('<input type="submit" name="default_submit" value="" class="default_submit" style="position: absolute;bottom: 0px;right: 0px;height: 0px !important;width:0px !important;background: transparent;color: transparent;border: 0;" />');
				
				// first we have to sort the fields
				usort($this->showFields, array($this, "sort"));			
				$i = 0;	
				foreach($this->showFields as $field)
				{
						
						$name = $field->name;
						if(isset($this->fields[$name]) && !isset($this->renderedFields[$name]))
						{
								$this->renderedFields[$name] = true;
								$div = $field->field();
								if(is_object($div) && !$div->hasClass("hidden")) {
									if($i == 0) {
										$i++;
										$div->addClass("one");
									} else {
										$i = 0;
										$div->addClass("two");
									}
									
								}
								$this->fieldsDiv->append($div);
						}
				}
				
				unset($field);
				
				
				$i = 0;
				foreach($this->actions as $action)
				{
						$field = $action["field"];
						$container = $field->field();
						if($i == 0) {
							$i++;
							$container->addClass("action_one");
						} else {
							$i = 0;
							$container->addClass("action_two");
						}
						$this->actionDiv->append($container);
				}
				
				unset($div, $i, $container);
				
				
				// javascript
				$js = '$(function(){ 
							$("#form_'.$this->form->name.' .err").remove(); 
							$("#form_'.$this->form->name.'").bind("formsubmit", function() {
								$("#form_'.$this->form->name.' .err").remove();
							});
						 });';
				
				foreach($this->fields as $field)
				{
						$js .= $field->JS();
				}
				
				foreach($this->validators as $validator)
				{
					if(is_object($validator)) {
						$validator->setForm($this);
						$js .= $validator->JS();
					}
				}
				
				
				$this->form->append($this->fieldsDiv);
				$this->form->append($this->actionDiv);
			
				$this->callExtending("afterRender");
				
				$this->form->id = $this->ID();
				
				Profiler::mark("Form::renderForm::render");
				$data = $this->form->render("          ");
				Resources::addJS($js);
				Profiler::unmark("Form::renderForm::render");
				
				Profiler::unmark("Form::renderForm");
				
				
				return $data;
				
		}
		/**
		 * submission
		 *@name submit
		 *@access public
		*/
		public function submit()
		{
				$this->callExtending("beforeSubmit");
				
				$this->post = $_POST;
				
				$_SESSION["form_secrets"] = array();
				
				$i = 0;
				
				foreach($this->post as $key => $value) {
					if(_ereg("^field_action_([a-zA-Z0-9_]+)_([a-zA-Z_0-9]+)$", $key, $matches)) {
						if(isset($this->fields[$matches[1]]) && $this->fields[$matches[1]]->hasAction($matches[2])) {
							$this->activateRestore();
							return $this->fields[$matches[1]]->handleAction($matches[2]);
						}
					}
				}
				
				
				
				// find action
				foreach($this->actions as $action)
				{
						$field = $action["field"];
						if(isset($_POST[$field->name]) || (isset($_POST["default_submit"]) && !$field->input->hasClass("cancel") && !$field->input->name != "cancel"))
						{
							$i++;
							if($field->canSubmit($action["submit"])) {
								if($action["submit"] == "@default")
								{
										$submission = $this->submission;
								} else
								{
										$submission = $action["submit"];
								}
								break;
							} else {
								$this->defaultFields();
								return $this->renderForm();
							}
						}
				}
				
				
				
				// no registered action has submitted the form
				if($i == 0) {
					$this->defaultFields();
					return $this->renderForm();
				}
				
				
				// just write it
				$this->saveToSession();
				
				$allowed_result = array();
				$this->result = array(); // reset result
				
				// get data
				foreach($this->fields as $field)
				{
					$result = $field->result();

					if($result !== null)
					{
						$this->result[$field->name] = $result;
						$allowed_result[$field->name] = true;
					}
				}
				
				// validation
				$valid = true;
				$errors = new HTMLNode('div',array(
					'class'	=> "error"
				),array(
					new HTMLNode('ul', array(
						
					))
				));
				
				
				
				foreach($this->validators as $validator)
				{
						$validator->setForm($this);
						$v = $validator->validate();
						if($v !== true)
						{
								$valid = false;
								$errors->getNode(0)->append(new HTMLNode('li', array(
								'class'	=> 'erroritem'
								), $v));
						}
				}
				
				if($valid !== true)
				{
						$_SESSION["form_secrets"][$this->name()] = $this->__get("secret_" . $this->ID())->value;
						$this->form->append($errors);
						return $this->renderForm();
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
				
				foreach($this->dataHandlers as $callback) {
					$result = call_user_func_array($callback, array($result));
				}
				
				$this->callExtending("afterSubmit", $result);
				
				return $this->controller->$submission($result, $this);
		}
		/**
		 * you can use data-handlers, to edit data before it is given to the submission-method
		 * you give a callback and you get a result
		 *
		 *@name addDataHandler
		 *@access public
		 *@param callback
		*/
		public function addDataHandler($callback) {
			$this->dataHandlers[] = $callback;
		}
		/**
		 * sorts the items
		 *@name sort
		 *@access public
		*/
		public function sort($a, $b)
		{
				if($this->fieldSort[$a->name] == $this->fieldSort[$b->name])
				{
						return 0;
				}
				
				return ($this->fieldSort[$a->name] > $this->fieldSort[$b->name]) ? 1 : -1;
		}
		/**
		 * gets the default submission
		 *@name getSubmission
		 *@access public
		*/
		public function getSubmission()
		{
				return $this->submission;
		}
		/**
		 * sets the default submission
		 *@name setSubmission
		 *@access public
		*/
		public function setSubmission($submission)
		{
			if($submission)
				if(Object::method_exists($this->controller, $submission))
				{
						$this->submission = $submission;
				} else
				{
						throwError('6', 'PHP-Error', 'Unknowen function "'.$submission.'" for Controller '.get_class($this->controller).'. Please create function and run dev.');
				}
		}
		/**
		 * removes a field
		 *@name remove
		 *@access public
		*/
		public function remove($field)
		{
				if(isset($this->fields[$field]))
				{
						unset($this->fields[$field]);
				}
				
				if(isset($this->showFields[$field]))
				{
						unset($this->showFields[$field]);
				}
				
				if(isset($this->actions[$field]))
				{
						unset($this->actions[$field]);
				}
				
				foreach($this->showFields as $_field) {
					if(is_subclass_of($_field, "FieldSet")) {
						$_field->remove($field);
					}
				}
		}
		/**
		 * adds an field
		 *@name add
		 *@access public
		*/
		public function add($field,$sort = 0, $to = "this")
		{
				if($to == "this")
				{
						if($sort == 0)
						{
								$sort = count($this->showFields);
						}
						$this->showFields[$field->name] = $field;
						$this->fieldSort[$field->name] = $sort;
						$field->setForm($this);
				} else
				{
						if(isset($this->$to))
						{
								$this->$to->add($field, $sort);
						}
							
				}
		}
		/**
		 * adds a field to a given field set
		 *
		 *@name addToField
		 *@access public
		*/
		public function addToField($fieldname, $field, $sort = 0) {
			return $this->add($field, $sort, $fieldname);
		}
		/**
		 * adds an action
		 *@name addAction
		 *@access public
		*/
		public function addAction($action)
		{
				$action->setForm($this);
		}
		/**
		 * adds a validator
		 *@name addValidator
		 *@access public
		*/
		public function addValidator($validator, $name)
		{		
				$this->validators[$name] = $validator;
				$validator->setForm($this);
		}
		/**
		 * removes an validator
		 *@name removeValidator
		 *@access public
		*/
		public function removeValidator($name)
		{
				unset($this->validators[$name]);
		}
		/**
		 * removes the secret key
		 * DON'T DO THIS IF YOU DON'T KNOW WHAT YOU DO!
		 *@name removeSecret
		 *@acess public
		*/
		public function removeSecret()
		{
				$this->secret = false;
		}
		/**
		 * adds the secret key
		 *@name addSecret
		 *@acess public
		*/
		public function addSecret()
		{
				$this->secret = true;
		}
		/**
		 * gets the secret
		 *@name getsecret
		 *@access public
		*/
		public function getSecret()
		{
				return $this->secret;
		}
		/**
		 * returns the current real form-object
		 *@name form
		 *@access public
		*/
		public function &form()
		{
				return $this;
		}
		/**
		 * genrates an id for this form
		 *@name ID
		 *@access public
		*/
		public function ID()
		{
				return "form_" . md5($this->name);
		}
		/**
		 * generates an name for this form
		 *@name name
		 *@access public
		*/
		public function name()
		{
				return $this->name;
		}
		/**
		 * Overloading
		*/
		
		/**
		 * get
		 *@name __get
		 *@access public
		*/
		public function __get($offset)
		{
				return (isset($this->fields[$offset])) ? $this->fields[$offset] : false;
		}
		/**
		 * set
		 *@name __set
		 *@access public
		*/
		public function __set($offset, $value)
		{
				// currently there is no option to overload a form with fields
		}
		/**
		 * isset
		 *@name __isset
		 *@access public
		*/
		public function __isset($offset)
		{
				return (isset($this->fields[$offset]));
		}
		/**
		 * unset
		 *@name __unset
		 *@access public
		*/
		public function __unset($offset)
		{
				unset($this->fields[$offset]);
		}
		/**
		 * saves current form to session
		*/
		public function saveToSession() {
			session_store("form_" . strtolower($this->name), $this);
		}
		
		
}

class ExternalForm extends RequestHandler
{
		public function handleRequest($request)
		{
				$this->request = $request;
				
				$this->init();
				
				$form = $request->getParam("form");
				$field = $request->getParam("field");
				return $this->FieldExtAction($form, $field);
		}
		/**
		 * a external resource for a form
		 *@name FieldExtAction
		 *@access public
		 *@param name - form
		 *@param name - field
		*/
		public function FieldExtAction($form, $field)
		{
				if(session_store_exists("form_" . strtolower($form)))
				{
						$f = session_restore("form_" . strtolower($form));
						if(isset($f->$field))
						{
								$data = $f->$field->handleRequest($this->request);
								
								session_store("form_" . strtolower($form), $f);
								return $data;
						}
						return false;
						
				}
				return false;
		}
}

Core::addRules(array(
	'system/forms/$form!/$field!' => "ExternalForm"
), 50);