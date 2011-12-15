<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 01.07.2010
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Page extends pages
{
		
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		public $db_fields = array();
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		public $has_one = array();
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		public $many_many = array();
		/**
		 * belongs-many-many
		*/
		public $belongs_many_many = array();
		/**
		 * we need no indexes, indexes are in parent class
		*/
		public $indexes = array(
			
		);
		/**
		 * searchable fields
		*/
		public $searchable_fields = array(
		
		);
		
		public $prefix = "Page_";
		/**
		 * orderby
		*/
		public $orderby = array('field' => 'sort', 'type' => 'ASC');
		/**
		 * name
		*/
		public $name = '{$_lang_just_content_page}';
		/**
		 * which parents are allowed
		*/
		public $can_parent = array('page', 'boxpage','modulepage', 'pages');
		/**
		 * gets the FORM
		*/
		public function getForm(&$form)
		{
				parent::getForm($form);
				// HACK HACK HACK!
				if($this->class == "page")
						$form->add(new HTMLeditor('data','', null, "400px"), 0, "content");
						
				

		}				
}

class pageController extends contentController
{
	
}