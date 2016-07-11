<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.04.2013
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
	
	static $icon = "images/icons/fatcow-icons/16x16/file.png";
	
	/**
	 * don't use from parent-class
	 * there would be much tables, which we don't need
	*/
	static $db = array();
	/**
	 * don't use from parent-class
	 * there would be much tables, which we don't need
	*/
	static $has_one = array();
	/**
	 * don't use from parent-class
	 * there would be much tables, which we don't need
	*/
	static $many_many = array();
	/**
	 * belongs-many-many
	*/
	static $belongs_many_many = array();
	/**
	 * we need no indexes, indexes are in parent class
	*/
	static $index = array(
		
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

		if($this->classname == "page" || $this->classname == "wrapperpage")
			$form->add(new HTMLeditor('data','', null, "400px"), null, "content");
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
	*/
	public static function hidden($class = null) {
		if($class == "childpage")
			return true;
			
		return false;
	}
}

class pageController extends contentController
{
	/**
	 * generates a button edit this page
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