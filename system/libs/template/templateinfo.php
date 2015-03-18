<?php
/* *
  *@package goma framework
  *@subpackage templateInfo
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 - 2013 Goma-Team
  * last modified: 17.02.2013
* */  

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class templateInfo extends object
{
	
	/* *
	 * get contents of an plist-file
	 * @access public
	 * @param string - name of the template
	 * @return plist object
	 * */
	
	public static function get_plist_contents($template)
	{
		$path = ROOT . "tpl/";
		$plist_path = $template . "/info.plist";
		
		return self::parse_plist($path . $plist_path);
	}
	
	/* *
	 * get array of an plist content
	 * @access public
	 * @param string - path to plist file
	 * @return array - [key] = string
	 * */
	
	public static function parse_plist($file)
	{
		if(file_exists($file))
		{
			$plist = new CFPropertyList();
			$plist->parse(file_get_contents($file));
			$content = $plist->ToArray();
			
			if(isset($content["screenshot"]))
				$content["screenshot"] = substr(dirname($file), strlen(ROOT)) . "/" . $content["screenshot"];
				
			return $content;
		}
		
		return array();
	}
	
	/* *
	 * get the value for an specific key
	 * @access public
	 * @param string - name of the template
	 * @param string - name of the key
	 * @return string - value of the key (empty string if not available)
	 * */
	
	public static function get_key($template, $key)
	{
		$content = self::get_plist_contents($template);
		
		if(!isset($content[$key]))
			return "";
			
		if($key == "screenshot")
			$content[$key] = "tpl/" . $template . "/" . $content[$key];
			
		return $content[$key];
	}
	
	/* *
	 * get all available templates for the given version
	 * @access public
	 * @param string - version to check
	 * @return array - numbered array with all available templates
	 * */
			
	public static function get_available_templates($app, $versionCMS, $versionFramework)
	{
		$tpl = self::getTemplates();
		$availTpl = array();
		
		foreach($tpl as $curTpl)
		{
			if(self::get_key($curTpl, "requireApp") == $app)
			{
				if(strtolower(self::get_key($curTpl, "type")) == "template" || strtolower(self::get_key($curTpl, "Type")) == "template")
				{
					if(goma_version_compare(self::get_key($curTpl, "requireAppVersion"), $versionCMS, "<=") && goma_version_compare(self::get_key($curTpl, "requireFrameworkVersion"), $versionFramework, "<="))
						array_push($availTpl, $curTpl);
				}
			}
		}
		
		return $availTpl;
	}
	
	/**
	 * gets all templates as an array
	 *
	 *@name getTemplates
	 *@access public
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
