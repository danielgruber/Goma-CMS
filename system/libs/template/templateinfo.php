<?php defined("IN_GOMA") OR die();

/**
 * This is a base-class that gets information about templates.
 *
 * @package     Goma\View
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.1
 * @changed 	20.03.2015
 */
class templateInfo extends gObject
{
	
	/**
	 * cache for plists.
	*/
	protected static $plists = array();

	/**
	 * get contents of a plist-file of a template.
	 *
	 * @param 	string - name of the template
	 * @return 	array object
	*/
	public static function get_plist_contents($template)
	{
		$path = ROOT . "tpl/";
		$plist_path = $template . "/info.plist";
		
		return self::parse_plist($path . $plist_path);
	}
	
	/**
	 * get array of a plist file
	 * it also searchs for a screenshot.
	 *
	 * @param 	string - path to plist file
	 * @return 	array - [key] = string
	*/
	public static function parse_plist($file)
	{
		if(file_exists($file))
		{
			if(isset(self::$plists[$file])) {
				return self::$plists[$file];
			}

			$plist = new CFPropertyList();
			$plist->parse(file_get_contents($file));
			$content = $plist->ToArray();
			
			if(isset($content["screenshot"])) {
				$f =  substr(dirname($file), strlen(ROOT)) . "/" . $content["screenshot"];
				if(file_exists($f)) {
					$content["screenshot"] = $f;
				}
			}
			
			self::$plists[$file] = $content;	

			return $content;
		}
		
		return array();
	}
	
	/**
	 * get the value for an specific key of the template-plist.
	 *
	 * @access 	public
	 * @param 	string 	name of the template
	 * @param 	string  name of the key
	 * @return 	string  value of the key (empty string if not available)
	*/
	public static function get_key($template, $key)
	{
		$content = self::get_plist_contents($template);
		
		if(!isset($content[$key])) {
			return null;
		}
			
		return $content[$key];
	}
	
	/**
	 * get all available templates for the given versions of framework and cms.
	 *
	 * @access 	public
	 * @param 	string 	version to check
	 * @return 	array 	numbered array with all available templates
	*/
	public static function get_available_templates($app, $versionCMS, $versionFramework)
	{
		$tpl = self::getTemplates();
		$availTpl = array();
		
		foreach($tpl as $curTpl)
		{
			if($app === null || self::get_key($curTpl, "requireApp") == $app)
			{
				if(strtolower(self::get_key($curTpl, "type")) == "template" || strtolower(self::get_key($curTpl, "Type")) == "template")
				{
					if(($app === null || $versionCMS === null || goma_version_compare(self::get_key($curTpl, "requireAppVersion"), $versionCMS, "<=")) && goma_version_compare(self::get_key($curTpl, "requireFrameworkVersion"), $versionFramework, "<=")) {
						array_push($availTpl, $curTpl);
					}
				}
			}
		}
		
		return $availTpl;
	}
	
	/**
	 * gets all templates as an array not checking dependencies.
	*/
	public static function getTemplates() 
	{
		$path = "tpl/";
		$tpl = array();
		$files = scandir(ROOT . $path);
		foreach($files as $file) {
			if(is_dir(ROOT . $path . $file) && is_file(ROOT . $path . $file . "/info.plist")) {
				$tpl[] = $file;
			}
		}
		return $tpl;
	}
}
