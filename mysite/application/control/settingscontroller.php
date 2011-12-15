<?php
/**
  * Settings
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 20.11.2011
*/

class Newsettings extends DataObject {
	/**
	 * fields for general
	*/
	public $db_fields = array(
		"titel"				=> "varchar(50)",
		"register"			=> "varchar(100)",
		"register_enabled"	=> "Switch",
		"register_email"	=> "Switch",
		"gzip"				=> "Switch",
		"livecounter"		=> "Switch"
	);
	public $defaults = array(
		"titel"				=> "Goma - Open Source CMS / Framework",
		"gzip"				=> "0",
		"register_email"	=> "1",
		"register_enabled"	=> "0",
		"status"			=> "1",
		"stpl"				=> "default"
	);
	public $fieldInfo = array(
		"register_enabled"	=> "{\$_lang_register_enabled_info}",
		"register"			=> "{\$_lang_registercode_info}",
		"gzip"				=> "{\$_lang_gzip_info}",
		"livecounter"		=> "{\$_lang_livecounter_info}",
		"register_email"	=> "{\$_lang_register_require_email_info}"
	);
	/**
	 * gets field titles
	*/
	public function getFieldTitles() {
		return  array(
			"register"			=> lang("registercode"),
			"register_enabled"	=> lang("register_enabled", "Enable Registration"),
			"register_email"	=> lang("register_require_email", "Send Registration Mail"),
			"titel"				=> lang("title"),
			"gzip"				=> lang("gzip", "G-Zip"),
			"livecounter"		=> lang("livecounter")
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
		$general->add(new select("timezone",lang("timezone"),i18n::$timezones ,Core::getCMSVar("TIMEZONE")));
		$general->add($date_format = new textfield("date_format", lang("date_format"), DATE_FORMAT));			
						
		$general->add($status = new select('status',lang("site_status"),array(STATUS_ACTIVE => $GLOBALS['lang']['normal'], STATUS_MAINTANANCE => $GLOBALS['lang']['wartung']), SITE_MODE));
		if(STATUS_DISABLED)
			$status->disable();
			
		$status->info = lang("sitestatus_info");
		$date_format->info = lang("date_format_info");
		//$general->add();
		foreach(ClassInfo::getChildren("newsettings") as $child) {
			$tabs->add($currenttab = new Tab($child, array(),parse_lang(Object::instance($child)->tab)));
			$inst = Object::Instance($child);
			// sync data
			$inst->sync($this);
			$inst->getFormFromDB($currenttab);
		}
		
		$form->addAction(new FormAction("submit", lang("save")));
	}
	
	/**
	 * provides permissions
	*/
	public function providePermissions() {
		return array(
			"SETTINGS_ALL" => array(
				"title" 	=> '{$_lang_edit_settings}',
				"default"	=> 7,
				"implements"=> array(
					"ADMIN_ALL"
				)
			)
		);
	}
	
	public $controller = "SettingsController";
}

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
		self::$settingsCache = DataObject::get("newsettings", array("id" => 1));
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

class metaSettings extends Newsettings {
	public $db_fields = array(
		"meta_keywords"		=> "varchar(100)",
		"meta_description"	=> "varchar(100)"
	);
	public $tab = "{\$_lang_meta}";
	public $fieldTitles = array(
		//"meta_keywords"		=> lang("keywords"),
		//"meta_descriotion"	=> lang("web_description", "Description of the Site")
	);
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
	public $db_fields = array(
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
	*/
	public function getFormFromDB(&$form) {
		$form->add(new Select("stpl", lang("available_styles"), $this->getTemplates()));
		$form->add(new TextArea("css_standard", lang("own_css")));
	}
}