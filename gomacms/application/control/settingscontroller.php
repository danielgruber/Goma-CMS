<?php
/**
  * Settings
  *
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 09.01.2013
  * $Version 1.2.5
*/

class SettingsController extends Controller {
	/**
	 * this is a cache of the dataobject of settings
	 *
	 *@name settingsCache
	 *@access public
	*/
	public static $settingsCache;
	/**
	 * gets the cache
	 *
	 *@name preInit
	 *@access public
	*/
	public static function PreInit() {
		self::$settingsCache = DataObject::get("newsettings", array("id" => 1))->first();
	}
	/**
	 * gets one static
	 *@name get
	 *@access public
	 *@param string - name
	*/
	public static function get($name)
	{	
			return isset(self::$settingsCache[$name]) ? self::$settingsCache[$name] : null;
	}

}

class Newsettings extends DataObject implements HistoryData {
	/**
	 * name of this dataobject
	 *
	 *@name name
	 *@access public
	*/
	public static $cname = '{$_lang_settings}';
	
	/**
	 * fields for general tab
	 *
	 *@name db_fields
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
	 *@name defaults
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
	 *@name fieldInfo
	*/
	public $fieldInfo = array(
		"register_enabled"	=> "{\$_lang_register_enabled_info}",
		"register"			=> "{\$_lang_registercode_info}",
		"gzip"				=> "{\$_lang_gzip_info}",
		"register_email"	=> "{\$_lang_register_require_email_info}"
	);
	
	public $controller = "SettingsController";
	
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
	 * generates the Form
	 *
	 *@name getForm
	*/
	public function getForm(&$form) {
		$tabs = new TabSet("tabs", array(), $form);
		
		$tabs->add($general = new Tab("general", array(), lang("settings_normal", "General")));
		$this->getFormFromDB($general);
		$general->add(new langselect('lang',lang("lang"),PROJECT_LANG));
		$general->add(new select("timezone",lang("timezone"), ArrayLib::key_value(i18n::$timezones) ,Core::getCMSVar("TIMEZONE")));
		$general->add($date_format = new Select("date_format", lang("date_format"), $this->generateDate(), DATE_FORMAT));			
						
		$general->add($status = new select('status',lang("site_status"),array(STATUS_ACTIVE => $GLOBALS['lang']['normal'], STATUS_MAINTANANCE => $GLOBALS['lang']['wartung']), SITE_MODE));
		if(STATUS_DISABLED)
			$status->disable();
			
		$status->info = lang("sitestatus_info");
		//$general->add();
		foreach(ClassInfo::getChildren("newsettings") as $child) {
			$tabs->add($currenttab = new Tab($child, array(),parse_lang(Object::instance($child)->tab)));
			$inst = new $child($this->data);
			// sync data
			$inst->getFormFromDB($currenttab);
		}
		
		$form->addAction(new CancelButton('cancel',lang("cancel")));
		$form->addAction(new FormAction("submit", lang("save"), null, array("green")));
	}
	
	/**
	 * generates the date-formats
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
	 *@name generateHistoryData
	 *@access public
	*/
	public static function generateHistoryData($record) {
		
		$lang = lang("h_settings", '$user updated the <a href="$url">settings</a>.');
		$icon = "images/icons/fatcow16/setting_tools.png";
		$lang = str_replace('$url', "admin/settings" . URLEND, $lang);
		
		return array("icon" => $icon, "text" => $lang, "relevant" => true);
		
	}
}

class metaSettings extends Newsettings {
	/**
	 * Database-Fields
	 *
	 *@name db
	*/
	static $db = array(
		"meta_keywords"		=> "varchar(100)",
		"meta_description"	=> "varchar(100)"
	);
	
	public $tab = "{\$_lang_meta}";
	
	public $fieldInfo = array(
		"meta_keywords"		=> "{\$_lang_keywords_info}",
		"meta_description"	=> "{\$_lang_description_info}"
	);
	public function getFieldTitles() {
		return array(
			"meta_keywords"		=> lang("keywords"),
			"meta_description"	=> lang("web_description", "Description of the Site")
		);
	}
}

class TemplateSettings extends NewSettings {
	/**
	 * database-fields
	 *
	 *@name db
	*/
	static $db = array(
		"stpl"			=> "varchar(64)",
		"css_standard"	=> "text"
	);
	
	public $tab = "{\$_lang_style}";
	/**
	 * gets all templates as an array
	 *
	 *@name getTemplates
	 *@access public
	*/
	public function getTemplates() {
		$path = "tpl/";
		$tpl = array();
		$files = scandir(ROOT . $path);
		foreach($files as $file) {
			if(is_dir(ROOT . $path . $file) && is_file(ROOT . $path . $file . "/site.html")) {
				$tpl[] = $file;
			}
		}
		return $tpl;
	}
	/**
	 * gets the form
	 *
	 *@name getFormFromDB
	 *@access public
	*/
	public function getFormFromDB(&$form) {
		$form->add(new TemplateSwitcher("stpl", lang("available_styles"), ClassInfo::$appENV["app"]["name"], ClassInfo::appVersion(), GOMA_VERSION . "-" . BUILD_VERSION));
		$form->add(new TextArea("css_standard", lang("own_css")));
	}
}

Core::addCMSVarCallback(array("settingsController", "get"));