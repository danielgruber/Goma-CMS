<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 04.10.2011
  * $Version 2.0.0 - 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class AjaxSubmitButton extends FormAction
{
		/**
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param string - title
		 *@param string - ajax submission
		 *@param string - optional submission
		 *@param object - form
		*/
		public function __construct($name = "", $value = "", $ajaxsubmit = null, $submit = null, $form = null)
		{
				
				parent::__construct($name, $value, null, null);
				if($submit === null)
						$submit = "@default";
				
				$this->submit = $submit;
				$this->ajaxsubmit = $ajaxsubmit;
				if($form != null)
				{
						$this->parent = $form;
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
				if(isset($_GET["boxid"]))
						$boxid =  '+ "?boxid='.convert::raw2js($_GET["boxid"]).'&redirect='.urlencode(getRedirect()).'"';
				else
						$boxid = ' + "?redirect='.urlencode(getRedirect()) . '"';
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
						self.leave_check = true;
						button.css("display", "none");
						container.append("<img src=\"images/16x16/loading.gif\" alt=\"loading...\" class=\"loading\" />");
						$("body").css("cursor", "wait");
						$.ajax({
							url: url_'.$this->name.''.$boxid.',
							type: "post",
							data: $("#'.$this->form()->id().'").serialize(),
							dataType: "html",
							complete: function()
							{
								$("body").css("cursor", "default");
								$("body").css("cursor", "auto");
								container.find(".loading").remove();
								button.css("display", "inline");
							},
							success: function(script) {
								var method = eval("(function(){" + script + "});");
								return method.call($("#'.$this->form()->id().'").get(0));
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
						$result = $field->result();
						if($result !== null)
						{
								$form->result[$field->name] = $result;
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
				
				return $form->controller->$submission($result, $response, $form);
		}
}

Core::addRules(array(
	'forms/ajax//$form!/$handler!'	=> 'AjaxSubmitButton'
), 100);