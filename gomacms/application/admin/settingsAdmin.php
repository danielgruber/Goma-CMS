<?php
defined("IN_GOMA") OR die();

/**
 * Settings.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma-CMS/Admin
 * @version 1.0.5
 */
class settingsAdmin extends adminItem
{
	// config
	public $text = '{$_lang_settings}';

	public $sort = 980;

	public $rights = "SETTINGS_ADMIN";

	public $models = array("newsettings");

	public $template = "admin/settings.html";

	static $icon = "templates/images/settings.png";

	static $less_vars = "tint-blue.less";

	/**
	 * history-url
	 */
	public function historyURL() {
		return "admin/history/newsettings";
	}

	public function index() {
		$form = $this->Form();

		if(is_string($form)) {
			$this->tplVars["form"] = $form;
			return parent::index();
		} else {
			return $form;
		}
	}

	/**
	 * generates the form
	 */
	public function Form() {
		$data = DataObject::get("newsettings", array("id" => 1))->first();
		return parent::Form(null, $data);
	}

	/**
	 * writes correct settings to correct location
	 *
	 * @param array $data
	 * @param Form|null $form
	 * @param null $model
	 * @return string|void
	 * @throws Exception
	 */
	public function submit_form($data, $form, $model = null) {
		if(isset($data["lang"], $data["status"], $data["timezone"], $data["date_format_date"])) {
			if(!file_exists(ROOT . LANGUAGE_DIRECTORY . $data["lang"])) {
				throw new LogicException("Selected language is not existing!");
			}

			$status = (SITE_MODE == STATUS_DISABLED) ? STATUS_DISABLED : $data["status"];
			writeProjectConfig(array(	'lang' => $data["lang"],
					"status" => $status,
					"safe_mode" => isset($data["safe_mode"]) ? $data["safe_mode"] : FileSystem::$safe_mode,
					"timezone" => $data["timezone"],
					"date_format_date" => $data["date_format_date"],
					"date_format_time" => $data["date_format_time"]));

			if(isset($data["safe_mode"]) && FileSystem::$safe_mode != $data["safe_mode"]) {
				FileSystem::$safe_mode = (bool) $data["safe_mode"];
				register_shutdown_function(array("settingsAdmin", "upgradeSafeMode"));
			}
		} else {
			throw new LogicException("settingsAdmin::submit_form needs at least lang, status, timezone and date_format_date.");
		}

		return parent::safe($data, $form, $model);
	}

	/**
	 * upgrades data regarding safe-mode.
	 */
	public static function upgradeSafeMode() {
		GlobalSessionManager::globalSession()->stopSession();
		FileSystem::applySafeMode(null, null, true);
	}

	/**
	 * returns an array of the wiki-article and youtube-video for this controller
	 *
	 * @name helpArticle
	 * @access public
	 * @return array
	 */
	public function helpArticle() {
		return array("wiki" => "Einstellungen");
	}
}
