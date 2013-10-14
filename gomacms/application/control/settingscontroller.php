<?php
/**
  * Settings
  *
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 14.10.2013
  * $Version 1.2.8
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
		$cacher = new Cacher("settings");
		if($cacher->checkValid()) {
			self::$settingsCache = new newSettings($cacher->getData());
		} else {
			self::$settingsCache = DataObject::get("newsettings", array("id" => 1))->first();
			$cacher->write(self::$settingsCache->toArray(), 3600);
		}
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
		"gzip"				=> "Switch",
		"useSSL"			=> "Switch"
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
		"stpl"				=> "default",
		"useSSL"			=> "0"
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
			"gzip"				=> lang("gzip", "G-Zip"),
			"useSSL"			=> lang("useSSL")
		);
	}
	
	/**
	 * returns the titles for the fields for automatic form generation
	 *
	 *@name getFieldTitles
	*/
	public function getFieldInfo() {
		$http = (isset($_SERVER["HTTPS"])) && $_SERVER["HTTPS"] != "off" ? "https" : "http";
		if($http == "https")
			return  array(
				"useSSL"			=> lang("useSSL_info")
			);
		else {
			$port = $_SERVER["SERVER_PORT"];
			if ($http == "http" && $port == 80) {
				$port = "";
			} else if ($http == "https" && $port == 443) {
				$port = "";
			} else {
				$port = ":" . $port;
			}

			$url = 'https://' . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];
			return  array(
				"useSSL"			=> str_replace('$link', $url, lang("useSSL_unsupported"))
			);
		}
	}
	
	public function onBeforeWrite() {
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
		$general->add(new select("timezone",lang("timezone"), ArrayLib::key_value(i18n::$timezones) ,Core::getCMSVar("TIMEZONE")));
		$general->add($date_format = new Select("date_format", lang("date_format"), $this->generateDate(), DATE_FORMAT));			
						
		$general->add($status = new select('status',lang("site_status"),array(STATUS_ACTIVE => lang("SITE_ACTIVE"), STATUS_MAINTANANCE => lang("SITE_MAINTENANCE")), SITE_MODE));
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
		
		$http = (isset($_SERVER["HTTPS"])) && $_SERVER["HTTPS"] != "off" ? "https" : "http";
		if($http == "http" && $this->useSSL != 1) {
			$form->useSSL->disable();
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
		"meta_description"	        => "varchar(100)",
        "google_site_verification"  => "varchar(100)"
	);
	
	public $tab = "{\$_lang_meta}";
	
	public $fieldInfo = array(
		"meta_description"	=> "{\$_lang_description_info}"
	);
	public function getFieldTitles() {
		return array(
			"meta_description"	        => lang("web_description", "Description of the Site"),
            "google_site_verification"  => lang("google-site-verification", "Google-Webmaster-Key")
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
	
	/**
	 * has-one
	*/
	static $has_one = array(
		"favicon"	=> "ImageUploads"
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
		$form->add($img = new ImageUpload("favicon", lang("favicon")));
		
		$img->allowed_file_types = array("jpg", "png", "bmp", "gif", "jpeg", "ico");
		
		$form->add(new TextArea("css_standard", lang("own_css")));
	}
}

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


class EditorSettings extends NewSettings {
	/**
	 * database-fields
	 *
	 *@name db
	*/
	static $db = array(
		"editor" 	=> "varchar(200)"
	);
	
	public $tab = "{\$_lang_EDITOR}";
	
	public function getFieldTitles() {
		return array(
			"editor"	=> "{\$_lang_EDITOR}"
		);
	}
	
	/**
	 * gets the form
	 *
	 *@name getFormFromDB
	 *@access public
	*/
	public function getFormFromDB(&$form) {
		
	}
}

Core::addCMSVarCallback(array("settingsController", "get"));
