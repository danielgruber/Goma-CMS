<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 27.07.2010
  * $Version 2.0.0 - 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class StringLib extends gObject
{
		/**
		 * the function ereg with preg_match
		 *@name _ereg
		 *@params: view php manual of ereg
		*/
		public static function ereg($pattern, $needed, &$reg = "")
		{
				return preg_match('/'.str_replace('/','\\/',$pattern).'/',$needed, $reg);
		}
		/**
		 * the function eregi with preg_match
		 *@name _eregi
	 	*@params: view php manual of eregi
		*/
		public static function eregi($pattern, $needed, &$reg = "")
		{
				return preg_match('/'.str_replace('/','\\/',$pattern).'/i',$needed, $reg);
		}
		/**
		 * highlights words in an array with a given start and end-tag
		 *@name highlight
		 *@access public
		 *@param string - text
		 *@param array - words
		 *@param string - start-tag
		 *@param string - endtag
		*/
		public static function highlight($text, $words, $start = "<highlight>", $end = "</highlight>")
		{
				if($words)
				{
						foreach($words as $word)
						{
								$text = preg_replace('/('.preg_quote($word).')/i', $start . "\\1" . $end, $text);
						}
				}
				return $text;
		}
}