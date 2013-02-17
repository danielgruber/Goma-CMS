<?php
/* *
  *@package goma framework
  *@subpackage templateInfo
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
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
	
	public function get_plist_contents($template)
	{
		$path = ROOT . "tpl/";
		$plist_path = $template . "/info.plist";
		
		return parse_plist($path . $plist_path);
	}
	
	/* *
	 * get array of an plist content
	 * @access public
	 * @param string - path to plist file
	 * @return array - [key] = string
	 * */
	
	public function parse_plist($file)
	{
		if(file_exists($file))
		{
			$plist = new CFPropertyList();
			$plist->parse($this->getFileContents($file));
			$content = $plist->ToArray();
			
			if(isset($content["screenshot"]))
				$content["screenshot"] = "tpl/" . $template . "/" . $content["screenshot"];
				
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
	
	public function get_key($template, $key)
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
			
	public function get_available_templates($app, $version)
	{
		$tpl = self::getTemplates();
		$availTpl = array();
		
		foreach($tpl as $curTpl)
		{
			if(self::get_key($curTpl, "requireApp") == $app)
			{
				if(goma_version_compare(self::get_key($curTpl, "requireAppVersion"), $version, "<=")
					array_push($availTpl, $curTpl);
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
	public function getTemplates() 
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
