<?php

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class templateInfo extends object
{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_plist_contents($template)
	{
		$path = ROOT . "tpl/";
		$plist_path = $template . "/info.plist";
		
		return parse_plist($path . $plist_path);
	}
	
	
	public function parse_plist($file)
	{
		if(file_exists($file))
		{
			$plist = new CFPropertyList();
			$plist->parse($this->getFileContents($file));
			return $plist->ToArray();
		}
		
		return array();
	}
	
	public function get_key($template, $key)
	{
		$content = get_plist_contents($template);
		
		if(!isset($content[$key]))
			return "";
			
		if($key == "screenshot")
			$content[$key] = ROOT . "tpl/" . $template . "/" . $content[$key];
			
		return $content[$key];
	}
			
			
		

}
?>
