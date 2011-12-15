<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 16.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class settingsAdmin extends adminItem
{
		// config
		public $text = '{$_lang_settings}';
		
		public $sort = 980;
		
		public $rights = "ADMIN_SETTINGS";
		
		public $models = array("newsettings");
		
		public $template = "admin/settings.html";

		
		
		public function providePermissions()
		{
				return array(
					"SETTINGS_ALL" => array(
						"title" 	=> '{$_lang_edit_settings}',
						"default"	=> 9,
						"implements"=> array(
							"ADMIN_ALL"
						)
					)
				);
		}
		
		public function Form() {
			$data = DataObject::get("newsettings", array("id" => 1));
			return parent::Form(null, $data);
		}
		
		public function safe($data, $form, $model = null) {
			$status = (SITE_MODE == STATUS_DISABLED) ? STATUS_DISABLED : $data["status"]; 
			writeProjectConfig(array('lang' => $data["lang"], "status" => $status, "timezone" => $data["timezone"], "date_format" => $data["date_format"]));
			parent::safe($data, $form, $model);
		}
}