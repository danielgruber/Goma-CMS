<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 2.03.2013
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLCacheController extends Object
{
	/**
	 * unnecessary words caused of filter
	 *
	 *@name unnecessary_words
	 *@access public
	*/
	static $unnecessary_words;
	
	/**
	 * lang of currently loaded filter
	 *
	 *@name current_filter
	*/
	static $current_filter;
	
	/**
	 * Builds a cache based on sentences with important words which can be
	 * indexed
	 * @param - string content
	 * @return - array cache
	*/
	static function build_cache($content)
	{
		self::load_word_filter("de-de");
		
		$cache = array();
		$words = self::get_wordlist($content);
		foreach($words as $word)
		{
			
			if(!ctype_alpha($word))
			{
				array_push($cache, $word);
				continue;
			}
			
			// $word is a letter
			
			if(!self::is_uppercase($word))
				continue;
			
			// $word is a noun
			
			if(strlen($word) < 4)
				continue;
			
			// $word is ! (in german) a help word (nothing to be found)
				
			if(strlen($word) < 7)
			{
				if(self::is_unnecessary($word))
					continue;
			}
			
			// $word is ! a longer help word

			$cache [] = $word;
		}
		
		return $cache;
	}
	
	/**
	 * checks if first char is upper-case
	 * @param - string word
	 * @return - boolean
	*/
	static function is_uppercase($str)
	{
		$char = substr($str, 0, 1);
		return strtolower($char) != $char;
	}
	
	/**
	 * checks if word is in the "list of unnecessary words"
	 * @param - string word
	 * @return - boolean
	*/	
	static function is_unnecessary($word)
	{
		return in_array(strtolower($word), self::$$unnecessary_words);
	}
	
	static function get_wordlist($content)
	{
		if(strlen($content) == 0)
			return false;
		
		return preg_split(" ", $content, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	/**
	 * load the list of unneccessary words for a specific language
	 * @param - string language
	 **/
	static function load_word_filter($lang)
	{
		if(self::$current_filter != $lang) {
			self::$current_filter = $lang;
			$lang = ROOT . LANGUAGE_DIRECTORY . "/".$lang."/word-filter.php";
			include $lang;
			self::$unnecessary_words = $unnecessaryWords;
		}
	}
		
}
