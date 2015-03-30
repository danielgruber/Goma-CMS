<?php defined("IN_GOMA") OR die();
/**
  * Push-Settings DataObject.
  *
  *	@package 	goma cms
  *	@link 		http://goma-cms.org
  *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.2.9
*/
class PushSettings extends NewSettings {
	/**
	 * database-fields
	 *
	 *@name db
	*/
	static $db = array(
		"p_app_key"		=> "varchar(64)",
		"p_app_secret"	=> "varchar(64)",
		"p_app_id"		=> "varchar(64)"
	);
	
	public $tab = "{\$_lang_push}";
	
	public $fieldInfo = array(
		"p_app_id"			=> "{\$_lang_push_info}"
	);
	public function getFieldTitles() {
		return array(
			"p_app_secret"		=> lang("p_app_secret", "App-Secret"),
			"p_app_key"			=> lang("p_app_key", "App-Key"),
			"p_app_id"			=> lang("p_app_id", "App-ID")
		);
	}
}
