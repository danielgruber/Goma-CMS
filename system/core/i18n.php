<?php
/**
 * @package		Goma\System\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined("IN_GOMA") OR die();

ClassInfo::addSaveVar("i18n", "languagefiles");
ClassInfo::addSaveVar("i18n", "defaultLanguagefiles");

/**
 * Class for localization.
 *
 * @package		Goma\System\Core
 * @version		1.4.4
 */
class i18n extends Object {
	/**
	 * files to load
	 */
	public static $languagefiles = array();

	/**
	 * files to load
	 */
	public static $defaultLanguagefiles = array();

	/**
	 * adds new loader
	 */
	public static function addLang($name, $default = "de") {
		if(defined('GENERATE_CLASS_INFO')) {
			self::$languagefiles[] = $name;
			self::$defaultLanguagefiles[$name] = $default;
		}
	}

	/**
	 * timezones
	 *@var array
	 *@todo make a full list
	 */
	public static $timezones = array('Europe/Berlin', 'Europe/London', 'Europe/Paris', 'Europe/Helsinki', 'Europe/Moscow', 'Europe/Madrid', 'Pacific/Kwajalein', 'Pacific/Samoa', 'Pacific/Honolulu', 'America/Juneau', 'America/Los_Angeles', 'America/Denver', 'America/Mexico_City', 'America/New_York', 'America/Caracas', 'America/St_Johns', 'America/Argentina/Buenos_Aires', 'Atlantic/Azores', 'Atlantic/Azores', 'Asia/Tehran', 'Asia/Baku', 'Asia/Kabul', 'Asia/Karachi', 'Asia/Calcutta', 'Asia/Colombo', 'Asia/Bangkok', 'Asia/Singapore', 'Asia/Tokyo', 'Australia/ACT', 'Australia/Currie', 'Australia/Lindeman', 'Australia/Perth', 'Australia/Victoria', 'Australia/Adelaide', 'Australia/Darwin', 'Australia/Lord_Howe', 'Australia/Queensland', 'Australia/West', 'Australia/Brisbane', 'Australia/Eucla', 'Australia/Melbourne', 'Australia/South', 'Australia/Yancowinna', 'Australia/Broken_Hill', 'Australia/Hobart', 'Australia/North', 'Australia/Sydney', 'Australia/Canberra', 'Australia/LHI', 'Australia/NSW', 'Australia/Tasmania', 'Pacific/Guam', 'Asia/Magadan', 'Asia/Kamchatka', 'Africa/Abidjan', 'Africa/Asmera', 'Africa/Blantyre', 'Africa/Ceuta', 'Africa/Douala', 'Africa/Johannesburg', 'Africa/Windhoek', 'Africa/Sao_Tome', 'Africa/Timbuktu', 'Africa/Niamey');

	/**
	 * date-formats
	 *
	 */
	public static $date_formats = array("d.m.Y", "d-m-Y", "F j, Y", "F jS Y", "Y-m-d");
	
	public static $time_formats = array("H:i", "g:i a", "g.i a", "H.i");

	/**
	 * inits i18n
	 *
	 *@param string - to init special lang
	 */
	public static function Init($language = null) {

		if(PROFILE)
			Profiler::mark("i18n::Init");
		ClassInfo::setSaveVars("i18n");

		// check lang selection
		Core::$lang = self::AutoSelectLang($language);
		$_SESSION["lang"] = Core::$lang;

		global $lang;
		$lang = array();

		// cache lang in registriy for faster access
		if(isset(ClassInfo::$appENV["expansion"])) {
			$cacher = new Cacher("lang_" . Core::$lang . count(self::$languagefiles) . count(ClassInfo::$appENV["expansion"]));
		} else {
			$cacher = new Cacher("lang_" . Core::$lang . count(self::$languagefiles));
		}
		if($cacher->checkvalid()) {
			$lang = $cacher->getData();
		} else {
			require_once (ROOT . LANGUAGE_DIRECTORY . '/' . Core::$lang . '/lang.php');

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
						unset($default);
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

			foreach(self::$languagefiles as $file) {
				if(file_exists(ROOT . LANGUAGE_DIRECTORY . '/' . Core::$lang . '/' . $file . '.php')) {
					require_once (ROOT . LANGUAGE_DIRECTORY . '/' . Core::$lang . '/' . $file . '.php');
				} else if(isset(self::$defaultLanguagefiles[$file])) {
					if(file_exists(ROOT . LANGUAGE_DIRECTORY . '/' . self::$defaultLanguagefiles[$file] . '/' . $file . '.php')) {
						copy(ROOT . LANGUAGE_DIRECTORY . '/' . self::$defaultLanguagefiles[$file] . '/' . $file . '.php', ROOT . LANGUAGE_DIRECTORY . '/' . Core::$lang . '/' . $file . '.php');
						require_once (ROOT . LANGUAGE_DIRECTORY . '/' . Core::$lang . '/' . $file . '.php');
					}
				}
			}

			$lang = ArrayLib::map_key("strtoupper", $lang);
			
			$cacher->write($lang, 600);
		}
		
		if(PROFILE)
			Profiler::unmark("i18n::Init");
	}
	
	/**
	 * load template language.
	*/
	public static function loadTPLLang($tpl) {
		
		if(file_exists(ROOT . "/tpl/" . $tpl . "/lang/" . Core::$lang . ".php")) {
			self::loadExpansionLang(ROOT . "tpl/" . $tpl . "/lang/" . Core::$lang . ".php", "tpl", $GLOBALS["lang"]);
		}
	}

	/**
	 * loads expansion-lang
	 *
	 */
	public static function loadExpansionLang($file, $name, &$language) {
		require ($file);
		foreach($lang as $key => $val) {
			$language[strtoupper($name . "." . $key)] = $val;
		}
	}

	/**
	 * lists all languages
	 *
	 */
	public static function listLangs() {
		if(PROFILE)
			Profiler::mark("i18n::listLangs");

		$data = array();
		foreach(scandir(ROOT . LANGUAGE_DIRECTORY) as $lang) {
			if($lang != "." && $lang != ".." && is_dir(ROOT . LANGUAGE_DIRECTORY . "/" . $lang) && file_exists(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist") && file_exists(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/lang.php")) {
				$plist = new CFPropertyList();
				$plist->parse(file_get_contents(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist"));
				$contents = $plist->ToArray();
				if(isset($contents["title"], $contents["type"], $contents["icon"]) && $contents["type"] == "language") {
					$contents["icon"] = LANGUAGE_DIRECTORY . "/" . $lang . "/" . $contents["icon"];
					$contents["code"] = $lang;
					$data[$lang] = $contents;
				}
			}
		}

		if(PROFILE)
			Profiler::unmark("i18n::listLangs");
		
		return $data;
	}

	/**
	 * returns contents of info.plist of current or given language
	 *
	 *@param string - language
	 */
	public static function getLangInfo($lang = null) {
		if(!isset($lang))
			$lang = Core::$lang;

		if(is_dir(ROOT . LANGUAGE_DIRECTORY . "/" . $lang) && file_exists(ROOT . LANGUAGE_DIRECTORY . "/" . $lang . "/info.plist")) {
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

	/**
	 * select language based on current data
	 *
	 *@name AutoSelectLang
	 */
	public function AutoSelectLang($code) {
		if(isset($code) && self::LangExists($code)) {
			return $code;
		}

		// if a user want to have another language
		if(isset($_GET['setlang']) && !empty($_GET["setlang"])) {
			if(self::LangExists($_GET["setlang"]))
				return $_GET["setlang"];
		} else if(isset($_POST['setlang']) && !empty($_POST["setlang"])) {
			if(self::LangExists($_POST["setlang"]))
				return $_POST['setlang'];
		}

		// if a user want to have another language
		if(isset($_GET['locale']) && !empty($_GET["locale"])) {
			if(self::LangExists($_GET["locale"]))
				return $_GET['locale'];
		} else if(isset($_POST['locale']) && !empty($_POST["locale"])) {
			if(self::LangExists($_POST["locale"]))
				return $_POST['locale'];
		}

		// define current language
		if(isset($_SESSION['lang']) && !empty($_SESSION["lang"]) && self::LangExists($_SESSION["lang"])) {
			return $_SESSION["lang"];
		} else if(defined("PROJECT_LANG")) {
			return PROJECT_LANG;
		} else {
			return DEFAULT_LANG;
		}
	}

	/**
	 * select language based on lang-code
	 *
	 *@name selectLang
	 */
	public function selectLang($code) {

		if($code != "." && $code != ".." && is_dir(LANGUAGE_DIRECTORY . "/" . $code)) {
			return $code;
		} else {
			$db = self::getLangDB();
			if(isset($db[$code])) {
				return $db[$code];
			}

			if(defined("PROJECT_LANG") && self::LangExists("PROJECT_LANG")) {
				return self::selectLang(PROJECT_LANG);
			} else {
				if(self::LangExists(DEFAULT_LANG))
					return DEFAULT_LANG;
				else
					throwError(6, "Language-Error", "No Language found");
			}
		}
	}

	/**
	 * returns if given lang exists
	 *
	 *@name LangExists
	 */
	public function LangExists($code) {
		if($code == "." || $code == "..")
			return false;

		if(is_dir(LANGUAGE_DIRECTORY . "/" . $code)) {
			return true;
		} else {
			$db = self::getLangDB();
			if(isset($db[$code])) {
				return true;
			}

			return false;
		}
	}

	/**
	 * this function builds a cache which has a langcode-lang-database
	 *
	 */
	public function getLangDB() {
		$cacher = new Cacher("langDB" . count(scandir(LANGUAGE_DIRECTORY)));
		if($cacher->checkValid()) {
			return $cacher->getData();
		} else {
			$db = array();
			foreach(scandir(LANGUAGE_DIRECTORY) as $lang) {
				$data = self::getLangInfo($lang);
				$db[$lang] = $lang;
				if(isset($data["langCodes"]))
					foreach($data["langCodes"] as $code) {
						$db[$code] = $lang;
					}
			}
			$cacher->write($db, 86400);
			return $db;
		}
	}
	/**
	 * returns an array of locale-codes for given code
	 *
	 *@name getLangCodes
	 */
	public function getLangCodes($code) {
		$data = self::getLangInfo(self::selectLang($code));
		if(isset($data["langCodes"]))
			return array_merge($data["areaCodes"], array($code));
		else
			return array($code);
	}
}