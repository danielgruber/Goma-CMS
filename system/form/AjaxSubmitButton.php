<?php
defined("IN_GOMA") OR die();

/**
 * A simple FormAction, which submits data via Ajax and calls the ajax-response-handler given.
 * 
 * you should return the given AjaxResponse-Object or Plain JavaScript in Ajax-Response-Handler.
 * a handler could look like this:
 * public function ajaxSave($data, $response) {
 *      $response->exec("alert('Nice!')");
 *      return $response;
 * }
 *
 * @author    	Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Form
 * @version		2.1.7
 */
class AjaxSubmitButton extends FormAction
{
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
		public function __construct($name = "", $value = "", $ajaxsubmit = null, $submit = null, $classes = null, &$form = null)
		{
				
				parent::__construct($name, $value, null, $classes);
				if($submit === null)
						$submit = "@default";
				
				$this->submit = $submit;
				$this->ajaxsubmit = $ajaxsubmit;
				if($form != null)
				{
						$this->parent =& $form;
						$this->setForm($form);
				}	
		}
		
		/**
		 * generates the js
		 *@name js
		 *@access public
		*/
		public function js()
		{
				// appendix to the url
				$append = ' + "?redirect='.urlencode(getRedirect()) . '"';
				foreach($_GET as $key => $val) {
					$append .= ' + "&'.urlencode($key).'='.urlencode($val).'"';
				}
				
				Resources::addData('var url_'.$this->name.' = "'.$this->externalURL().'/";');
				return '$(function(){
					var button = $("#'.$this->ID().'");
					var container = $("#'.$this->divID().'");
					button.click(function(){
						var eventb = jQuery.Event("beforesubmit");
						$("#'.$this->id().'").trigger(eventb);
						if ( eventb.result === false ) {
							return false;
						}
						var event = jQuery.Event("formsubmit");
						$("#'.$this->form()->id().'").trigger(event);
						if ( event.result === false ) {
							return false;
						}
						
						$("#'.$this->form()->id().'").gForm().setLeaveCheck(false);
						button.css("display", "none");
						container.append("<img src=\"images/16x16/loading.gif\" alt=\"loading...\" class=\"loading\" />");
						$("body").css("cursor", "wait");
						$.ajax({
							url: url_'.$this->name.''.$append.',
							type: "post",
							data: $("#'.$this->form()->id().'").serialize(),
							dataType: "html",
							complete: function()
							{
								$("body").css("cursor", "default");
								$("body").css("cursor", "auto");
								container.find(".loading").remove();
								button.css("display", "inline");
								
								var eventb = jQuery.Event("ajaxresponded");
								$("#'.$this->form()->id().'").trigger(eventb);
							},
							success: function(script, textStatus, jqXHR) {
								
								goma.ui.loadResources(jqXHR).done(function(){;
    								if (window.execScript)
    								 	window.execScript("method = " + "function(){" + script + "};",""); // execScript doesn’t return anything
    								else
    								 	method = eval("(function(){" + script + "});");
    								RunAjaxResources(jqXHR);
    								var r = method.call($("#'.$this->form()->id().'").get(0));
    								
    								$("#'.$this->form()->id().'").gForm().setLeaveCheck(false);
    								
    								return r;
								});
							},
							error: function(jqXHR, textStatus, errorThrown)
							{
								alert("There was an error while submitting your data, please check your Internet Connection or send an E-Mail to the administrator");
							}
						});
						return false;
					});
				});';
		}
		
		/**
		 * handles submits via AJAX
		 *@name handleRequest
		 *@access public
		 *@param object - request
		*/
		public function handleRequest(request $request)
		{
				$this->request = $request;
				
				$this->init();
				
				$form = $this->form();
				
				return $this->submit();
				
				if(isset($_POST["form_submit_" . $form->name()]))
				{
						// check secret
						if($form->getsecret() && $this->form()->secretKey && $_POST["secret_" . $form->ID()] == $this->form()->secretKey)
						{
								return $this->submit();
						} else if(!$form->getsecret())
						{
								return $this->submit();
						}
				}
				
				$response = new AjaxResponse();
				$response->exec(new Dialog("1<br />Your Request wasn't be correct, please try again or refresh your page. Error while checking your RequestID.", "Error"));
				$response->exec("$('#".$form->fields["form_submit_" . $form->name()]->id()."').find('input').val('".$this->form()->secretKey."');");
				return $response->render();
				
		}
		/**
		 * submit-function
		 *@name submit
		 *@access public
		*/
		public function submit()
		{
				$response = new AjaxResponse;
				$response->exec('$("#' . $this->form()->ID() . '").find(".error").remove();');
				$response->exec('var ajax_button = $("#'.$this->ID().'");');
				
				$submission = $this->ajaxsubmit;
				$form = $this->form();
				$form->post = $_POST;
				$allowed_result = array();
				$form->result = array(); // reset result
				// get data
				
				foreach($form->fields as $field)
				{
						// patch for correct behaviour on non-ajax and ajax-side
						$field->getValue();
						
						// now get results
						$result = $field->result();
						if($result !== null)
						{
								$form->result[$field->dbname] = $result;
								$allowed_result[$field->dbname] = true;
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
				
				foreach($form->validators as $validator)
				{
						$validator->setForm($form);
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
						$response->prepend("#" . $form->ID(), $errors->render());
						return $response->render();
				}
				
				if($form->getsecret())
				{
						$_SESSION["form_secrets"][$form->name()] = randomString(30);
						$response->exec('$("#'.$form->fields["secret_" . $form->id()]->id().'").val("'.convert::raw2js($this->form()->secretKey).'");');
				}
				
				$result = $form->result;
				if(is_object($result) && is_subclass_of($result, "dataobject")) {
					$result = $result->to_array();
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
				
				
				foreach($this->form()->dataHandlers as $callback) {
					$result = call_user_func_array($callback, array($result));
				}
				

				return call_user_func_array(array($form->controller, $submission), array($result, $response, $form));
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

Core::addRules(array(
	'forms/ajax//$form!/$handler!'	=> 'AjaxSubmitButton'
), 100);
