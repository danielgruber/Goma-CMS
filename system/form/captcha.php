<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 26.02.2012
  * $Version 1.1.2
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class Captcha extends FormField 
{
		/**
		 * for a captcha we don't need a title
		 *
		 *@name __construct
		 *@param string - name
		 *@param object - parent
		*/
		public function __construct($name, &$parent = null)
		{
				parent::__construct($name, null, null, $parent);
				$this->title = lang("captcha", "Captcha");
		}
		
		/**
		 * sets the validator
		 *@name setForm
		 *@access public
		*/
		public function setForm(&$form)
		{
				parent::setForm($form);			
				$this->form()->addValidator(new FormValidator(array($this, "validate")), "captcha");
		}
		
		/**
		 * generates the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				$this->callExtending("beforeField");
				
				$container = new HTMLNode("div");
				
				$container->append(new HTMLNode('img', array(
					"src"		=> "images/captcha/captcha.php",
					"alt"		=> "captcha",
					"id"		=> $this->ID() . "_captcha",
					"onclick"	=> "$('#".$this->ID()."').focus();"
				)));
				
				$this->container->append(new HTMLNode("label", array(
					"for"	=> $this->ID()
				), lang("captcha", "Captcha")));
				
				$container->append($this->input);
				
				$container->append(new HTMLNode('a', array(
					"href"		=> "javascript:;",
					"onclick"	=> "$('#".$this->ID() ."_captcha').attr('src','images/captcha/captcha.php?'+Math.random()+'');$('#".$this->ID()."').val('');$('#".$this->ID()."').focus();return false;"
				), lang("captcha_reload", "I can't read the captcha")));
				
								
				$this->container->append($container);
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
		
		/**
		 * validates the captcha
		 *@name validate
		*/
		public function validate()
		{
				return (isset($_SESSION['goma_captcha_spam'], $_POST[$this->name]) && $_SESSION['goma_captcha_spam'] == $_POST[$this->name]) ? true : lang("captcha_wrong", "The Code was wrong");
		}
		
		/**
		 * bind events
		 *
		 *@name JS
		 *@access public
		*/
		public function JS() {
			return '$(function(){
				$("#'.$this->form()->ID().'").bind("ajaxresponded", function(){
					$("#'.$this->ID() .'_captcha").attr("src","images/captcha/captcha.php?"+Math.random());
					$("#'.$this->ID().'").val("");
				});
			});';
		}
}