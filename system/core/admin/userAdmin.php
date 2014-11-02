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
	 * extend actions
	 *
	 *@name allowed_actions
	*/
	public $allowed_actions = array(
		"toggleLock"
	);

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
		$config->addComponent(new TableFieldActionLink(	$this->namespace . '/toggleLock/$id' . URLEND . '?redirect=' . urlencode(getredirect()), 
														'<img src="images/icons/fatcow16/lock.png" data-retina="images/icons/fatcow16/lock@2x.png" alt="'.lang("lock").'" />', 
														lang("lock"), 
														array($this, "checkForUnlock")));
		$config->addComponent(new TableFieldActionLink(	$this->namespace . '/toggleLock/$id' . URLEND . '?redirect=' . urlencode(getredirect()), 
														'<img src="images/icons/fatcow16/lock_break.png" data-retina="images/icons/fatcow16/lock_break@2x.png" alt="'.lang("unlock").'" />', 
														lang("unlock"), 
														array($this, "checkForLock")));

		$form = new Form($this, "form", array(
			new TableField("userTable", lang("users"), $this->modelInst(), $config)
		));
		
		return $form->render();
	}

	/**
	 * helper for tableField.
	*/
	public function checkForLock($tableField, $record) {
		return ($record->status != 1 && Permission::check("USERS_MANAGE") && $record->id != member::$id);
	}

	/**
	 * helper for tableField.
	*/
	public function checkForUnlock($tableField, $record) {
		return ($record->status == 1 && Permission::check("USERS_MANAGE") && $record->id != member::$id);
	}


	/**
	 * switches the lock-state of an user.
	 *
	 * @name toggleLock
	*/
	public function toggleLock() {
		if($this->getParam("id") && Permission::check("USERS_MANAGE") && $this->getParam("id") != member::$id) {
			if($data = DataObject::get_by_id("user", $this->getParam("id"))) {
				if($data->status == 1) {
					if($this->confirm(lang("user_lock_q"), lang("yes"), null, $data)) {
						$data->status = 2;
						$data->write();
						return $this->actionComplete("lock_user", $data);
					}
				} else {
					if($this->confirm(lang("user_unlock_q"), lang("yes"), null, $data)) {
						$data->status = 1;
						$data->write();
						return $this->actionComplete("unlock_user", $data);
					}
				}


			}
		}

		$this->redirectBack();
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

		if($action == "unlock_user") {
			AddContent::addSuccess(lang("user_unlocked", "The account has been unlocked."));
			$this->redirectback();
			return true;
		}

		if($action == "lock_user") {
			AddContent::addSuccess(lang("user_locked", "The user has been locked."));
			$this->redirectback();
			return true;
		}
		
		return parent::actionComplete($action, $record);
	}
}