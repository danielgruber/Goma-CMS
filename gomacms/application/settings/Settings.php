<?php defined("IN_GOMA") OR die();
/**
  * Settings DataObject.
  *
  *	@package 	goma cms
  *	@link 		http://goma-cms.org
  *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.2.9
*/
class Newsettings extends DataObject implements HistoryData {
	/**
	 * name of this dataobject
	 *
	 * @name 	name
	 * @access 	public
	*/
	public static $cname = '{$_lang_settings}';
	
	/**
	 * fields for general tab
	 *
	 * @name db
	*/
	static $db = array(
		"titel"				=> "varchar(50)",
		"register"			=> "varchar(100)",
		"register_enabled"	=> "Switch",
		"register_email"	=> "Switch",
		"gzip"				=> "Switch"
	);
	
	/**
	 * defaults of these fields
	 *
	 * @name default
	*/
	static $default = array(
		"titel"				=> "Goma - Open Source CMS / Framework",
		"gzip"				=> "0",
		"register_email"	=> "1",
		"register_enabled"	=> "0",
		"status"			=> "1",
		"stpl"				=> "default"
	);
	
	/**
	 * information above each textfield about a specific field
	 *
	 * @name fieldInfo
	*/
	public $fieldInfo = array(
		"register_enabled"	=> "{\$_lang_register_enabled_info}",
		"register"			=> "{\$_lang_registercode_info}",
		"gzip"				=> "{\$_lang_gzip_info}",
		"register_email"	=> "{\$_lang_register_require_email_info}"
	);

	/**
	 * returns the titles for the fields for automatic form generation
	 *
	 *@name getFieldTitles
	*/
	public function getFieldTitles() {
		return  array(
			"register"			=> lang("registercode"),
			"register_enabled"	=> lang("register_enabled", "Enable Registration"),
			"register_email"	=> lang("register_require_email", "Send Registration Mail"),
			"titel"				=> lang("title"),
			"gzip"				=> lang("gzip", "G-Zip")
		);
	}

	/**
	 * we discard the cache before writing.
	 * @param ModelWriter $modelWriter
	 */
	public function onBeforeWrite($modelWriter) {
		parent::onBeforeWrite($modelWriter);
		$cacher = new Cacher("settings");
		$cacher->delete();
	}
	
	/**
	 * generates the Form
	 *
	 *@name getForm
	*/
	public function getForm(&$form) {
		$tabs = new TabSet("tabs", array(), $form);

		$tabs->add($general = new Tab("general", array(), lang("settings_normal", "General")));
		$this->getFormFromDB($general);
		$general->add(new langselect('lang',lang("lang"),PROJECT_LANG));
		$general->add(new select("timezone",lang("timezone"), ArrayLib::key_value(i18n::$timezones), PROJECT_TIMEZONE));
		$general->add($date_format = new Select("date_format_date", lang("date_format"), $this->generateDate(), DATE_FORMAT_DATE));
		$general->add($date_format = new Select("date_format_time", lang("time_format"), $this->generateTIME(), DATE_FORMAT_TIME));

		$general->add($status = new select('status',lang("site_status"),array(STATUS_ACTIVE => lang("SITE_ACTIVE"), STATUS_MAINTANANCE => lang("SITE_MAINTENANCE")), SITE_MODE));
		
		if(STATUS_DISABLED) {
			$status->disable();
		}

		$tabs->add(new Tab("security", array(
			$safe_mode = new Checkbox("safe_mode", lang("safe_mode"), FileSystem::$safe_mode)
		),lang("security")));

		$safe_mode->info = lang("safe_mode_info");
			
		$status->info = lang("sitestatus_info");

		$this->generateSubClassForm($form);
		
		$form->addAction(new CancelButton('cancel',lang("cancel")));
		$form->addAction(new FormAction("submit", lang("save"), null, array("green")));
	}
	
	/**
	 * generates form for subClasses.
	 *
	 * @name 	generateSubClassForm
	*/
	public function generateSubClassForm(&$form) {
		$tabs = $form->tabs;
		foreach(ClassInfo::getChildren("newsettings") as $child) {
			$tabs->add($currenttab = new Tab($child, array(),parse_lang(gObject::instance($child)->tab)));
			$inst = new $child($this->data);
			// sync data
			$inst->getFormFromDB($currenttab);
		}
	}

	/**
	 * generates date-formats
	 *
	 *@name generateDate
	 *@access public
	*/
	public function generateDate() {
		$formats = array();
		foreach(i18n::$date_formats as $format) {
			$formats[$format] = goma_date($format);
		}
		return $formats;
	}
	
	/**
	 * generates time-formats
	 *
	 *@name generateTime
	 *@access public
	*/
	public function generateTime() {
		$formats = array();
		foreach(i18n::$time_formats as $format) {
			$formats[$format] = goma_date($format);
		}
		return $formats;
	}
	
	/**
	 * provides permissions
	*/
	public function providePerms() {
		return array(
			"SETTINGS_ADMIN" 	=> array(
				"title" 		=> '{$_lang_edit_settings}',
				"default"		=> array(
					"type"	=> "admins",
				),
				"forceGroups"	=> true,
				"inherit"		=> "ADMIN"
			)
		);
	}

	/**
	 * returns text what to show about the event
	 *
	 * @name    generateHistoryData
	 * @access    public
	 * @return array
	 */
	public static function generateHistoryData($record) {
		
		$lang = lang("h_settings", '$user updated the <a href="$url">settings</a>.');
		$icon = "images/icons/fatcow16/setting_tools.png";
		$lang = str_replace('$url', "admin/settings" . URLEND, $lang);
		
		return array("icon" => $icon, "text" => $lang, "relevant" => true);
		
	}
}
