<?php
/**
  *@todo comment it
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 16.07.2011
*/  

defined("IN_GOMA") OR die("<!-- Restricted Access -->");

loadlang("article");

/**
 * categories
*/
class ArticleCategory extends Page {
	/**
	 * category-icon
	*/ 
	static public $icon = "images/icons/fatcow-icons/16x16/page_white_stack.png";
	/**
	 * title of the site-type
	 *
	 *@name name
	 *@access public
	*/
	public $name = '{$_lang_ar_page_category}';
	/**
	 * allowed parent pages
	 *
	 *@name can_parent
	 *@access public
	*/
	public $can_parent = array(
		"pages",
		"ArticleCategory"
	);
	/**
	 * relations
	*/
	public $has_many = array(
		"categories"	=> "ArticleCategory",
		"articles"		=> "Article"
	);
	/**
	 * form
	*/
	public function getForm(&$form) {
		parent::getForm($form);
		
		/* --- */
		
		$form->addToField("content",new HTMLEditor("data", lang("description")));
	}
}

class ArticleCategoryController extends PageController {
	public $template = "articlesystem/category.html";
}

class Article extends Page {
	/**
	 * show comments by default
	*/
	public $defaults = array(
		"showcomments" => 1
	);
	/**
	 * article-icon
	*/
	public static $icon = "images/icons/fatcow-icons/16x16/article.png";
	/**
	 * database-fields
	 *
	 *@name db_fields
	 *@access public
	*/
	public $db_fields = array(
		"description"		=> "text"
	);
	/**
	 * title of the site-type
	 *
	 *@name name
	 *@access public
	*/
	public $name = '{$_lang_ar_page_article}';
	/**
	 * an article needs a category
	 *
	 *@name can_parent
	 *@access public
	*/
	public $can_parent = array('articlecategory');
	/**
	 * generate specific form
	 *
	 *@name getForm
	*/
	public function getForm(&$form)
	{
			parent::getForm($form);
			
			/* --- */
			
			$form->addToField("content",new HTMLEditor('description', lang("description"), null, "100px"));
			$form->addToField("content",new HTMLEditor('data', lang("content"),null, "300px"));
	}
}

class ArticleController extends PageController {
	public $template = "articlesystem/article.html";
}

// fix for old versions
class Articles extends ArticleCategory {
	public function hidden() {
		return true;
	}
}
class ArticlesController extends ArticleCategoryController {}