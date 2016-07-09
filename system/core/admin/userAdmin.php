<?php defined("IN_GOMA") OR die();

/**
 * User-Admin-Panel
 *
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * @version 1.1
*/
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
	public $model = "user";
	
	static $icon = "system/templates/admin/images/user.png";

	/**
	 * history-url
	 *
	 * @name historyURL
	 * @access public
	 * @return string
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
														'<i class="fa fa-lock"></i>',
														lang("lock"), 
														array($this, "checkForUnlock"),
			array("button button-clear yellow")));
		$config->addComponent(new TableFieldActionLink(	$this->namespace . '/toggleLock/$id' . URLEND . '?redirect=' . urlencode(getredirect()), 
														'<i class="fa fa-unlock"></i>',
														lang("unlock"), 
														array($this, "checkForLock"),
														array("button button-clear yellow")));

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
	 * @return bool|string
	 */
	public function toggleLock() {
		if($this->getParam("id") && Permission::check("USERS_MANAGE") && $this->getParam("id") != member::$id) {
			/** @var User $user */
			if($user = DataObject::get_by_id("user", $this->getParam("id"))) {
				if($user->status == 1) {
					if($this->confirm(lang("user_lock_q"), lang("yes"), null, $user)) {
						$user->status = 2;
						$user->writeToDB();
						return $this->actionComplete("lock_user", $user);
					}
				} else {
					if($this->confirm(lang("user_unlock_q"), lang("yes"), null, $user)) {
						$user->status = 1;
						$user->writeToDB();
						return $this->actionComplete("unlock_user", $user);
					}
				}


			}
		}

		return $this->redirectBack();
	}

	/**
	 * this is the method, which is called when a action was completed successfully or not.
	 *
	 * it is called when actions of this controller are completed and the user should be notified. For example if the
	 * user saves data and it was successfully saved, this method is called with the param save_success. It is also
	 * called if an error occurs.
	 *
	 * @param    string $action the action called
	 * @param    gObject $record optional: record if available
	 * @access    public
	 * @return bool|string
	 */
	public function actionComplete($action, $record = null) {
		if($action == "publish_success") {
			AddContent::addSuccess(lang("successful_saved", "The data was successfully saved."));
			return $this->redirectback();
		}

		if($action == "unlock_user") {
			AddContent::addSuccess(lang("user_unlocked", "The account has been unlocked."));
			return $this->redirectback();
		}

		if($action == "lock_user") {
			AddContent::addSuccess(lang("user_locked", "The user has been locked."));
			return $this->redirectback();
		}
		
		return parent::actionComplete($action, $record);
	}
}
