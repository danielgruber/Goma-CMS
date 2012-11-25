<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 25.11.2012
  * $Version 1.0.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class groupAdmin extends adminItem {
	/**
	 * text
	*/
	public $text = '{$_lang_groups}';
	
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
		"group"
	);
	
	/**
	 * history-url
	 *
	 *@name historyURL
	 *@access public
	*/
	public function historyURL() {
		return "admin/history/group";
	}
	
	
	/**
	 * logic
	*/
	public function index() {
		$config = TableFieldConfig_Editable::create();
		$config->getComponentByType("TableFieldDataColumns")->setDisplayFields(array(
			"name"		=> lang("name")
		));
		$config->removeComponent($config->getComponentByType("TableFieldToolbarHeader"));
		
		$form = new Form($this, "form", array(
			new TableField("groupTable", lang("groups"), $this->modelInst(), $config)
		));
		
		return $form->render();
	}
}