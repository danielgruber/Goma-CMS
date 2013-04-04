<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 25.11.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class settingsAdmin extends adminItem
{
		// config
		public $text = '{$_lang_settings}';
		
		public $sort = 980;
		
		public $rights = "SETTINGS_ADMIN";
		
		public $models = array("newsettings");
		
		public $template = "admin/settings.html";

		/**
		 * history-url
		 *
		 *@name historyURL
		 *@access public
		*/
		public function historyURL() {
			return "admin/history/newsettings";
		}
		
		/**
		 * generates the form
		 *
		 *@name Form
		*/
		public function Form() {
			$data = DataObject::get("newsettings", array("id" => 1))->first();
			return parent::Form(null, $data);
		}
		
		/**
		 * writes correct settings to correct location
		 *
		 *@name submit_form
		*/
		public function submit_form($data, $form, $model = null) {
			if(isset($data["lang"], $data["status"], $data["timezone"], $data["date_format"])) {
				if(!file_exists(ROOT . LANGUAGE_DIRECTORY . $data["lang"])) {
					throwError(6, "Invalid-Error", "Selected language not existing!");
				}
				$status = (SITE_MODE == STATUS_DISABLED) ? STATUS_DISABLED : $data["status"]; 
				writeProjectConfig(array('lang' => $data["lang"], "status" => $status, "timezone" => $data["timezone"], "date_format" => $data["date_format"]));
			} else {
				throwError(6, "Invalid-Error", "Too less keys in data to write settings.");
			}
			parent::safe($data, $form, $model);
		}
		
		/**
		 * returns an array of the wiki-article and youtube-video for this controller
		 *
		 *@name helpArticle
		 *@access public
		*/
		public function helpArticle() {
			return array("wiki" => "Einstellungen");
		}
}