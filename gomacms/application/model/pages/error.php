<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 15.12.2012
*/ 
class errorPage extends Page
{
		/**
		 * the icon for this page
		*/
		static public $icon = "images/icons/fatcow16/page_error.png";
		
		/**
		 * the title of this page shown in the select
		*/ 
		public static $cname = '{$_lang_errorpage}';
		
		/**
		 * only allowed in site-root
		 *
		 *@name allow_parent
		*/
		public static $allow_parent = array("pages", "!page");
		
		/**
		 * we need an error-code, for example 500 or 404
		*/
		static $db = array('code' => 'varchar(50)');
		
		/**
		 * generates the extended form for this page
		*/
		public function getForm(&$form)
		{
				parent::getForm($form);
				
				$form->remove("pagecomments");
				$form->remove("rating");
				
				
				$form->add(new select('code',$GLOBALS['lang']['errorcode'],array('404' => '404 - Not Found')),0, "content");
				$form->add(new HTMLEditor('data', $GLOBALS['lang']['url']),0, "content");
		}
		/**
		 * set the correct response
		*/
		public function getContent()
		{
				HTTPResponse::unsetCacheable();
				if(!defined("IS_BACKEND"))
						HTTPresponse::setResHeader($this->code);

				return $this->fieldGet("data");
		}
}
class errorPageController extends PageController
{	
}

