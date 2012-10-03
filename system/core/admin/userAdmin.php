<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 29.09.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class userAdmin extends adminItem {
	/**
	 * text
	*/
	public $text = '{$_lang_users}';
	
	/**
	 * permissions
	*/
	public $rights = "10";
	
	/**
	 * sort
	*/
	public $sort = "700";
	
	/**
	 * models
	*/
	public $models = array(
		"user"
	);
	
	/**
	 * default-template
	*/
	public $template = "admin/users.html";
	
	/**
	 * logic
	*/
	public function index() {
		$config = TableFieldConfig_Base::create();
		$config->getComponentByType("TableFieldDataColumns")->setDisplayFields(array(
			"nickname" 	=> lang("username"),
			"name"		=> lang("name"),
			"email"		=> lang("email")
		));
		
		$form = new Form($this, "form", array(
			new TableField("userTable", lang("users"), $this->modelInst(), $config)
		));
		
		return $form->render();
	}
}