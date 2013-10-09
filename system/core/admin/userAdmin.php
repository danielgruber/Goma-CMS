<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
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
	
	static $icon = "system/templates/admin/images/user.png";
	
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
	
	/**
	 * this is the method, which is called when a action was completed successfully or not.
	 *
	 * it is called when actions of this controller are completed and the user should be notified. For example if the user saves data and it was successfully saved, this method is called with the param save_success. It is also called if an error occurs.
	 *
	 * @param 	string $action the action called
	 * @param	object $record optional: record if available
	 * @access 	public
	*/
	public function actionComplete($action, $record = null) {
		if($action == "publish_success") {
			AddContent::addSuccess(lang("successful_saved", "The data was successfully saved."));
			$this->redirectback();
			return true;
		}
		
		return parent::actionComplete($action, $record);
	}
}