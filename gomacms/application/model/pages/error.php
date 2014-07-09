<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.04.2013
*/ 
class errorPage extends Page
{
		/**
		 * the icon for this page
		*/
		static $icon = "images/icons/fatcow16/page_error.png";
		
		/**
		 * the title of this page shown in the select
		*/ 
		static $cname = '{$_lang_errorpage}';
		
		/**
		 * only allowed in site-root
		 *
		 *@name allow_parent
		*/
		static $allow_parent = array("pages", "!page");
		
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
				
				
				$form->add(new select('code', lang("errorcode"), array('404' => '404 - Not Found')), null, "content");
				$form->add(new HTMLEditor('data', lang("content")), null, "content");
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

