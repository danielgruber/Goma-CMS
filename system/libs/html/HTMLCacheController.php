<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 2.03.2013
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLCacheController extends gObject
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
	 * gets an array with key word and value an integer which defines the importance of the word.
	*/
	static function getWordsRated($resource)
	{
		$charlist = 'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ';
		$return = array();
		$present_words = str_word_count($resource, 1, $charlist);
		$resource_words = preg_split("/ /", $resource, -1);
		$resource_sentences = preg_split("/|\?|\!/", $resource, -1);
	
		foreach($resource_sentences as $key => $rsentence)
		{
			$rsentence = preg_split("/|\./", $rsentence, -1);
			$key = $rsentence[count($rsentence) - 1];
			$key = str_word_count($resource, 1, $charlist);
		}
	
		foreach($present_words as $key)
		{
			if (strpos($key, "<") != false && $key == "?" && $key == "!")
				continue;
	
			// general burst and burst in html tags
			$value = 0;
			$count = 0;
			$increment = 1;
	
			foreach($resource_words as $rword)
			{
				// if $rword closes a HTML-Tag, set increment correctly
				if ($rword === "</h1>")
					$increment -= 5;
				if ($rword === "</h2>")
					$increment -= 4.5;
				if ($rword === "</h3>" || $rword === "</img>")
					$increment -= 3.5;
				if ($rword === "</h4>" || $rword === "</b>" || $rword === "</u>")
					$increment -= 3;
				if ($rword === "</h5>" || $rword === "</i>" || $rword === "</span>")
					$increment -= 2.5;
	
				if ($rword === $key)
				{
					$value += $increment;
					$count += 1;
					unset($rword);
				}
	
				// if $rword is a HTML-Tag, set increment correctly
				if ($rword === "<h1>")
					$increment += 5;
				if ($rword === "<h2>")
					$increment += 4.5;
				if ($rword === "<h3>" || $rword === "<img>")
					$increment += 3.5;
				if ($rword === "<h4>" || $rword === "<b>" || $rword === "<u>")
					$increment += 3;
				if ($rword === "<h5>" || $rword === "<i>" || $rword === "<span>")
					$increment += 2.5;
			}
	
	
			// Word burst in important sentences (!, ?)
	
			$sentence_count = 0;
	
			foreach ($resource_sentences as $rsentence)
			{
				foreach ($rsentence as $rsword)
				{
					if ($rsword === $rword)
					{
						$sentence_count += 1;
						unset($rsword);
					}
	
				}
			}
	
			$result = ($value + $sentence_count * 3) / $count;
	
			$return[$rword] = $result;
		}
	
		return $return;
	}

	/**
	 * Builds a cache based on sentences with important words which can be
	 * indexed
	 * @param - string content
	 * @return - array cache
	*/
	static function build_cache($content, $lang = "de")
	{
		self::load_word_filter($lang);
		
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
			
			if(!self::is_uppercase($word)) {
			
				// $word is a noun
				
				if(strlen($word) < 4)
					continue;
				
				// $word is ! (in german) a help word (nothing to be found)
					
				if(strlen($word) < 7)
				{
					if(self::is_unnecessary($word))
						continue;
				}
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
		
		return preg_split("/ /", $content, -1, PREG_SPLIT_NO_EMPTY);
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
