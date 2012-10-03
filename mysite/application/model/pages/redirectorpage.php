<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 10.09.2012
  * $Version 1.0
*/ 

defined("IN_GOMA") OR die("");

class redirector extends Page
{
		/**
		 * title of the page
		*/
		public $name = '{$_lang_redirect}';
		/**
		 * which parents are allowed
		*/
		public $can_parent = array('page', 'boxpage', 'mod', 'pages');
		
		/**
		 * icon
		*/
		public static $icon = "images/icons/goma/16x16/link.png";
		
		/**
		 * generates the form
		 *
		 *@name getForm
		 *@access public
		*/
		public function getForm(&$form)
		{
				parent::getForm($form);
				$form->add(new textField('data', $GLOBALS['lang']['url']),0, "content");
				
				$form->addValidator(new requiredFields(array("data")), "requireURL");
		}
		
		/**
		 * returns URL
		*/
		public function getUrl()
		{
				return $this->data["data"];
		}
}

class redirectorController extends PageController
{
	public function index() {
		HTTPResponse::redirect($this->modelInst()->data["data"]);
	}
}

