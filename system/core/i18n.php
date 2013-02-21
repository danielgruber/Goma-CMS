<?php
/**
  * data-class for timezone-names, etc.
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 22.03.2012
  * $Version 1.4.1
*/   


defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 *@class lang
*/

classinfo::addSaveVar("i18n", "languagefiles");
classinfo::addSaveVar("i18n", "defaultLanguagefiles");

class i18n extends Object
{
		/**
		 * files to load
		*/
		public static $languagefiles = array
		(
		
		);
		
		/**
		 * files to load
		*/
		public static $defaultLanguagefiles = array
		(
		
		);
		
		/**
		 * adds new loader
		*/
		public static function addLang($name, $default = "de")
		{
				if(defined('GENERATE_CLASS_INFO'))
				{
						self::$languagefiles[] = $name;
						self::$defaultLanguagefiles[$name] = $default;	
				}
		}
		
		/**
		 * timezones
		 *@name timezones
		 *@access public
		 *@var array
		 *@todo make a full list
		*/
		public static $timezones = array(
			'Europe/Berlin',
			'Europe/London',
			'Europe/Paris',
			'Europe/Helsinki',
			'Europe/Moscow',
			'Europe/Madrid',
			'Pacific/Kwajalein',
			'Pacific/Samoa',
			'Pacific/Honolulu',
			'America/Juneau',
			'America/Los_Angeles',
			'America/Denver',
			'America/Mexico_City',
			'America/New_York',
			'America/Caracas',
			'America/St_Johns',
			'America/Argentina/Buenos_Aires',
			'Atlantic/Azores',
			'Atlantic/Azores',
			'Asia/Tehran',
			'Asia/Baku',
			'Asia/Kabul',
			'Asia/Karachi',
			'Asia/Calcutta',
			'Asia/Colombo',
			'Asia/Bangkok',
			'Asia/Singapore',
			'Asia/Tokyo',
			'Australia/ACT',
			'Australia/Currie',
			'Australia/Lindeman',
			'Australia/Perth',
			'Australia/Victoria',
			'Australia/Adelaide',
			'Australia/Darwin',
			'Australia/Lord_Howe',
			'Australia/Queensland',
			'Australia/West',
			'Australia/Brisbane',
			'Australia/Eucla',
			'Australia/Melbourne',
			'Australia/South',
			'Australia/Yancowinna',
			'Australia/Broken_Hill',
			'Australia/Hobart',
			'Australia/North',
			'Australia/Sydney',
			'Australia/Canberra',
			'Australia/LHI',
			'Australia/NSW',
			'Australia/Tasmania',
			'Pacific/Guam',
			'Asia/Magadan',
			'Asia/Kamchatka',
			'Africa/Abidjan',
			'Africa/Asmera',
			'Africa/Blantyre',
			'Africa/Ceuta',
			'Africa/Douala',
			'Africa/Johannesburg',
			'Africa/Windhoek',
			'Africa/Sao_Tome',
			'Africa/Timbuktu',
			'Africa/Niamey'
		);
		
		/**
		 * date-formats
		 *
		 *@name date_formats
		 *@access public
		*/
		public static $date_formats = array(
			"d.m.Y - H:i",
			"d-m-Y H:i",
			"F j, Y, g:i a",
			"F jS, Y, g:i a",
			"Y-m-d H:i"
		);
		
		/**
		 * inits i18n
		 *
		 *@name init
		 *@access public
		 *@param string - to init special lang
		*/
		public static function Init($lang = null) {
			
			if(PROFILE) Profiler::mark("i18n::Init");
			classinfo::setSaveVars("i18n");

			// check lang selection
			
			if(!isset($lang)) {
				// if a user want to have another language
				if(isset($_GET['setlang']) && !empty($_GET["setlang"]))
				{
					if(file_exists(LANGUAGE_DIRECTORY . $_GET["setlang"]))
						$_SESSION['lang'] = $_GET['setlang'];
				} else if(isset($_POST['setlang']) && !empty($_POST["setlang"]))
				{
					if(file_exists(LANGUAGE_DIRECTORY . $_POST["setlang"]))
						$_SESSION['lang'] = $_POST['setlang'];
				}
				
				// if a user want to have another language
				if(isset($_GET['locale']) && !empty($_GET["locale"]))
				{
					if(file_exists(LANGUAGE_DIRECTORY . $_GET["locale"]))
						$_SESSION['lang'] = $_GET['locale'];
				} else if(isset($_POST['locale']) && !empty($_POST["locale"]))
				{
					if(file_exists(LANGUAGE_DIRECTORY . $_POST["locale"]))
						$_SESSION['lang'] = $_POST['locale'];
				}
	
				// define current language
				if(isset($_SESSION['lang']) && !empty($_SESSION["lang"]) && file_exists(LANGUAGE_DIRECTORY . $_SESSION["lang"])) {
						Core::$lang = $_SESSION["lang"];
				} else if(defined("PROJECT_LANG")) {
					Core::$lang = PROJECT_LANG;
				} else {
					Core::$lang = DEFAULT_LANG;
				}
			} else {
				$_SESSION['lang'] = $lang;
				Core::$lang = $lang;
			}
			
			global $lang;
			$lang = array();

			// cache lang in registriy for faster access
			if(isset(ClassInfo::$appENV["expansion"])) {
				$cacher = new Cacher("lang_" . Core::$lang . count(self::$languagefiles) . count(ClassInfo::$appENV["expansion"]));
			} else {
				$cacher = new Cacher("lang_" . Core::$lang . count(self::$languagefiles));	
			}
			if($cacher->checkvalid())
			{
					$lang = $cacher->getData();
			} else
			{
					require_once(ROOT . LANGUAGE_DIRECTORY . '/' .  Core::$lang . '/lang.php');
					
					if(isset(ClassInfo::$appENV["expansion"])) {
						foreach(ClassInfo::$appENV["expansion"] as $name => $data) {
							$folder = $data["folder"];
							if(isset($data["langFolder"])) {
								if(file_exists($folder . $data["langFolder"] . "/" . Core::$lang . ".php")) {
									self::loadExpansionLang($folder . $data["langFolder"] . "/" . Core::$lang . ".php", "exp_" . $name, $lang);
								} else if(isset($data["defaulLang"]) && file_exists($folder . $data["langFolder"] . "/" . $data["defaultLang"] . ".php")) {
									self::loadExpansionLang($folder . $data["langFolder"] . "/" . $data["defaultLang"] . ".php", "exp_" . $name, $lang);
								}
							} else if(is_dir($folder . "languages")) {
								if(file_exists($folder . "languages/" . Core::$lang . ".php")) {
									self::loadExpansionLang($folder . "languages/" . Core::$lang . ".php", "exp_" . $name, $lang);
								} else if(isset($data["defaulLang"]) && file_exists($folder . "languages/" . $data["defaultLang"] . ".php")) {
									self::loadExpansionLang($folder . "languages/" . $data["defaultLang"] . ".php", "exp_" . $name, $lang);
								}
							}
						}
					}
					
					// load app-language
					if(isset(ClassInfo::$appENV["app"]["langPath"])) {
						$langName = isset(ClassInfo::$appENV["app"]["langName"]) ? ClassInfo::$appENV["app"]["langName"] : ClassInfo::$appENV["app"]["name"];
						if(file_exists(ROOT . APPLICATION . "/" . ClassInfo::$appENV["app"]["langPath"] . "/" . Core::$lang . ".php")) {
							self::loadExpansionLang(ROOT . APPLICATION . "/" . ClassInfo::$appENV["app"]["langPath"] . "/" . Core::$lang . ".php", $langName, $lang);
						} else if(isset(ClassInfo::$appENV["app"]["defaultLang"])) {
							$default = ClassInfo::$appENV["app"]["defaultLang"];
							if(file_exists(ROOT . APPLICATION . "/" . ClassInfo::$appENV["app"]["langPath"] . "/" . $default . ".php")) {
								self::loadExpansionLang(ROOT . APPLICATION . "/" . ClassInfo::$appENV["app"]["langPath"] . "/" . $default . ".php", $langName, $lang);
							}
							unset($default);
						}
					}
					
					foreach(self::$languagefiles as $file)
					{
							if(file_exists(ROOT . LANGUAGE_DIRECTORY . '/' .  Core::$lang . '/' . $file . '.php'))
							{
									require_once(ROOT . LANGUAGE_DIRECTORY . '/' .  Core::$lang . '/' . $file . '.php');
							} else if(isset(self::$defaultLanguagefiles[$file])) {
								if(file_exists(ROOT . LANGUAGE_DIRECTORY . '/' .  self::$defaultLanguagefiles[$file] . '/' . $file . '.php')) {
									copy(ROOT . LANGUAGE_DIRECTORY . '/' .  self::$defaultLanguagefiles[$file] . '/' . $file . '.php', ROOT . LANGUAGE_DIRECTORY . '/' .  Core::$lang . '/' . $file . '.php');
									require_once(ROOT . LANGUAGE_DIRECTORY . '/' .  Core::$lang . '/' . $file . '.php');
								}
							}
					}
					
					$cacher->write($lang, 600);
			}
			if(PROFILE) Profiler::unmark("i18n::Init");
		}
		
		/**
		 * loads expansion-lang
		 *
		 *@name loadExpansionLang
		 *@access public
		*/
		public static function loadExpansionLang($file, $name, &$language) {
			require($file);
			foreach($lang as $key => $val) {
				$language[$name . "." . $key] = $val;
			}
		}
		
		/**
		 * lists all languages
		 *
		 *@name listLangs
		 *@access public
		*/
		public static function listLangs() {
			if(PROFILE) Profiler::mark("i18n::listLangs");
			
			$data = array();
			foreach(scandir(ROOT . LANGUAGE_DIRECTORY) as $lang) {
				if($lang != "." && $lang != ".." && is_dir(ROOT . LANGUAGE_DIRECTORY . "/" . $lang) && file_exists(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist") && file_exists(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/lang.php")) {
					$plist = new CFPropertyList();
					$plist->parse(file_get_contents(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist"));
					$contents = $plist->ToArray();
					if(isset($contents["title"], $contents["type"], $contents["icon"]) && $contents["type"] == "language") {
						$contents["icon"] = LANGUAGE_DIRECTORY . "/" . $lang . "/" . $contents["icon"];
						$data[$lang] = $contents;
					}
				}
			}
			
			if(PROFILE) Profiler::unmark("i18n::listLangs");
			
			return $data;
		}
		
		/**
		 * returns contents of info.plist of current or given language
		 *
		 *@name getLangInfo
		 *@access public
		 *@param string - language
		*/
		public static function getLangInfo($lang = null) {
			if(!isset($lang))
				$lang = Core::$lang;
			
			if(file_exists(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist")) {
				$plist = new CFPropertyList();
				$plist->parse(file_get_contents(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist"));
				$contents = $plist->ToArray();
				if(isset($contents["title"], $contents["type"], $contents["icon"]) && $contents["type"] == "language") {
					$contents["icon"] = LANGUAGE_DIRECTORY . "/" . $lang . "/" . $contents["icon"];
					return $contents;
				}
			}
			return false;
		}
}