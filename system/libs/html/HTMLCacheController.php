<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 2.03.2013
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLCacheController extends Object
{
	
	public $unnecessaryWords;
	
	/**
	 * Builds a cache based on sentences with important words which can be
	 * indexed
	 * @param - string content
	 * @return - string cache
	 * */
	
	
	public function build_cache($content)
	{
		$cache = "";
		$words = get_wordlist($content);
		foreach($words as $word)
		{
			
			if(!ctype_alpha($word))
			{
				$cache .= $word . " ";
				continue;
			}
			
			// $word is a letter
			
			if(!is_uppercase($word))
				continue;
			
			// $word is a noun
			
			if(strlen($word) < 3)
				continue;
			
			// $word is ! (in german) a help word (nothing to be found)
				
			if(strlen($word) < 7)
			{
				if(is_unnecessary($word))
					continue;
			}
			
			// $word is ! a longer help word
			
			$cache .= $word . " ";
		}
		
		return $cache;
	}
	
	/**
	 * checks if first char is upper-case
	 * @param - string word
	 * @return - boolean
	 * */
	
	public function is_uppercase($str)
	{
		$char = substr($str, 0, 1);
		return strtolower($char) != $char;
	}
	
	/**
	 * checks if word is in the "list of unnecessary words"
	 * @param - string word
	 * @return - boolean
	 * */
	
	public function is_unnecessary($word)
	{
		foreach($unnecessaryWords as $exp)
		{
			if(strtolower($word) == $exp)
				return true;
		}
		
		return false;
	}
	
	public function get_wordlist($content)
	{
		if(strlen($content) == 0)
			return false;
		
		$words = preg_split(" ", $content, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	/**
	 * load the list of unneccessary words for a specific language
	 * @param - string language
	 **/
	
	public function load_word_filter($lang)
	{
		include $lang;
	}
		
}
