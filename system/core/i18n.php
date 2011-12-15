<?php
/**
  * data-class for timezone-names, etc.
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 26.10.2011
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
			'Australia/Darwin',
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
		 * inits i18n
		 *
		 *@name init
		 *@access public
		*/
		public static function Init() {
			
			if(PROFILE) Profiler::mark("i18n::Init");
			classinfo::setSaveVars("i18n");

			// check lang selection
			
			
			// if a user want to have another language
			if(isset($_GET['setlang']))
			{
					$_SESSION['lang'] = $_GET['setlang'];
			} else if(isset($_POST['setlang']))
			{
					$_SESSION['lang'] = $_POST['setlang'];
			}
			
			// if a user want to have another language
			if(isset($_GET['locale']))
			{
					$_SESSION['lang'] = $_GET['locale'];
			} else if(isset($_POST['locale']))
			{
					$_SESSION['lang'] = $_POST['locale'];
			}

			// define current language
			if(isset($_SESSION['lang'])) {
				Core::$lang = $_SESSION["lang"];
			} else if(defined("PROJECT_LANG")) {
				Core::$lang = PROJECT_LANG;
			} else {
				Core::$lang = DEFAULT_LANG;
			}
			
			
			global $lang;
			// cache lang in registriy for faster access
			$cacher = new Cacher("lang_" . Core::$lang . count(self::$languagefiles));
			if($cacher->checkvalid())
			{
					$lang = $cacher->getData();
			} else
			{
					require_once(ROOT . LANGUAGE_DIRECTORY . '/' .  Core::$lang . '/lang.php');
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
}