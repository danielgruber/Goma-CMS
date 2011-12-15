<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 04.08.2010
*/

defined("IN_GOMA") OR die("<!-- restricted access -->"); // silence is golden ;)

class Captcha extends FormField 
{
		/**
		 * for captcha we don't need a title
		 *@name __construct
		 *@param string - name
		 *@param object - parent
		*/
		public function __construct($name, $parent = null)
		{
				parent::__construct($name, null, null, $parent);
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
					"src"	=> "images/captcha/captcha.php",
					"alt"	=> "captcha",
					"id"	=> $this->ID() . "_captcha"
				)));
				
				$container->append(new HTMLNode('a', array(
					"href"		=> "javascript:;",
					"onclick"	=> "$('#".$this->ID() ."_captcha').attr('src','images/captcha/captcha.php?'+Math.random()+'');$('#".$this->ID()."').val('');return false;"
				), lang("captcha_reload", "I can't read the captcha")));
				
				$this->container->append($container);
				
				$this->container->append(new HTMLNode("label", array(
					"for"	=> $this->ID()
				), lang("captcha", "Captcha")));
				
				$this->container->append($this->input);
				
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
}