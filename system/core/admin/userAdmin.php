<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 11.02.2013
  * $Version 1.0.3
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
	public $rights = "USERS_MANAGE";
	
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
	 * history-url
	 *
	 *@name historyURL
	 *@access public
	*/
	public function historyURL() {
		return "admin/history/user";
	}
	
	/**
	 * logic
	*/
	public function index() {
		$config = TableFieldConfig_Editable::create();
		$config->getComponentByType("TableFieldDataColumns")->setDisplayFields(array(
			"id"		=> "ID",
			"image"		=> lang("pic"),
			"nickname" 	=> lang("username"),
			"name"		=> lang("name"),
			"email"		=> lang("email"),
			"groupList"	=> lang("groups")
		))->setFieldFormatting(array(
			"image" => '$image.setSize(50, 50)'
		));
		$config->removeComponent($config->getComponentByType("TableFieldToolbarHeader"));
		
		$form = new Form($this, "form", array(
			new TableField("userTable", lang("users"), $this->modelInst(), $config)
		));
		
		return $form->render();
	}
}