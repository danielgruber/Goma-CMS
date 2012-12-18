<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 16.04.2012
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Page extends pages
{
	/**
	 * allowed children
	 *
	 *@name allow_children
	*/
	public static $allow_children = array(
		"Page", "ChildPage", "WrapperPage"
	);
	
	/**
	 * name
	*/
	public static $cname = '{$_lang_just_content_page}';
	
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
	 * gets the FORM
	*/
	public function getForm(&$form)
	{
		parent::getForm($form);
		// HACK HACK HACK!
		if($this->class == "page" || $this->class == "wrapperpage")
				$form->add(new HTMLeditor('data','', null, "400px"), 0, "content");
	}				
}

class WrapperPage extends Page {
	/**
	 * name
	*/
	public static $cname = '{$_lang_wrapper_page}';
	
	/**
	 * icon
	*/
	public static $icon = "images/icons/fatcow16/column_four.png";
	
	/**
	 * allowed children
	 *
	 *@name allow_children
	*/
	public static $allow_children = array(
		"Page", "ChildPage", "WrapperPage"
	);
}

class ChildPage extends Page {
	/**
	 * limit parents
	*/
	public static $allow_parent = array(
		"WrapperPage"
	);
	
	/**
	 * children
	 *
	 *@name allow_children
	*/
	public static $allow_children = array();
	
	/**
	 * hide this type of page
	 *
	 *@name hidden
	*/
	public static function hidden() {
		return true;
	}
}

class pageController extends contentController
{
	/**
	 * generates a button edit this page
	 *
	 *@name frontedBar
	 *@access public
	*/
	public function frontedBar() {
		if(!$this->modelInst()->id)
			return array();
		
		return array(
			array(
				"url" 			=> BASE_SCRIPT . "admin/content/record/" . $this->modelInst()->id . "/edit",
				"title"			=> lang("edit_this_page", "edit this page"),
				"attr_title"	=> $this->modelInst()->title
			)
		);
	}
}

class WrapperPageController extends pageController
{
	/**
	 * other template for this
	 *
	 *@name template
	*/
	public $template = "pages/wrapperPage.html";
}

class ChildPageController extends pageController {
	
}