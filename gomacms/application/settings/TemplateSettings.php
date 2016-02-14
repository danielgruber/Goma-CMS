<?php defined("IN_GOMA") OR die();
/**
  * Template-Settings DataObject.
  *
  *	@package 	goma cms
  *	@link 		http://goma-cms.org
  *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.2.9
*/
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
		$form->add($img = new ImageUploadField("favicon", lang("favicon")));
		
		$img->allowed_file_types = array("jpg", "png", "bmp", "gif", "jpeg", "ico");
		
		$form->add(new TextArea("css_standard", lang("own_css")));
	}
}