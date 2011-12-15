<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.07.2011
*/ 
class redirector extends Page
{
		public $name = '{$_lang_redirect}';
		/**
		 * which parents are allowed
		*/
		public $can_parent = array('page', 'boxpage', 'mod', 'pages');
		public function getForm(&$form, $data)
		{
				parent::getForm($form, $data);
				$form->add(new textField('data', $GLOBALS['lang']['url']),0, "content");
				
				$form->addValidator(new requiredFields(array()), "test");
		}
		public function getContent()
		{
				if(!defined("IS_BACKEND"))
						HTTPResponse::redirect($this->data["data"]);
				return $this->data["data"];
		}
		public function getUrl()
		{
				return $this->data["data"];
		}
}
class redirectorController extends PageController
{
		
}

