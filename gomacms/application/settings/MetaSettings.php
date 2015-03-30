<?php defined("IN_GOMA") OR die();
/**
  * Meta-Settings DataObject.
  *
  *	@package 	goma cms
  *	@link 		http://goma-cms.org
  *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.2.9
*/
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
