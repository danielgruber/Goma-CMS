<?php defined("IN_GOMA") OR die();
/**
  * SettingsController handles a simple local cache that has all settings in it.
  *
  *	@package 	goma cms
  *	@link 		http://goma-cms.org
  *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.2.9
*/

Core::addToHook("loadedClassRegisterExtension", array("settingsController", "setRegisterVars"));

class SettingsController {
	/**
	 * this is a cache of the dataobject of settings
	 *
	 * @name 	settingsCache
	 * @access 	public
	*/
	public static $settingsCache;

	/**
	 * gets the cache
	 *
	 * @name 	preInit
	 * @access 	public
	*/
	public static function PreInit() {
		$cacher = new Cacher("settings");
		if($cacher->checkValid()) {
			self::$settingsCache = new newSettings($cacher->getData());
		} else {
			self::$settingsCache = DataObject::get("newsettings", array("id" => 1))->first();
			$cacher->write(self::$settingsCache->toArray(), 3600);
		}
	}

	/**
	 * gets a value for settings-key.
	 *
	 * @name 	get
	 * @access 	public
	 * @param 	string - name
	*/
	public static function get($name)
	{	
		return isset(self::$settingsCache[$name]) ? self::$settingsCache[$name] : null;
	}
	
	/**
	 * sets register-vars only when RegisterClass is loaded.
	*/
	public static function setRegisterVars() {
		RegisterExtension::$enabled = settingsController::get("register_enabled");
		RegisterExtension::$validateMail = settingsController::get("register_email");
		RegisterExtension::$registerCode = settingsController::get("register");
	}
}

Core::addCMSVarCallback(array("settingsController", "get"));