<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 23.07.2011
*/          

/**
* this parses lanuage veriables in a string, e.g. {$_lang_imprint}
*@name parse_lang
*@param string - the string to parse
*@param array - a array of variables in the lanuage like %e%
*@return string - the parsed string
*/
function parse_lang($str, $arr = array())
{
		return preg_replace('/\{\$_lang_(.*)\}/Usie' , "''.var_lang('\\1').''" , $str);  // find lang vars
}
    /**
   * parses the %e% in the string
   *@name var_lang
   *@param string - the name of the languagevar
   *@param array - the array of variables
   *@return string - the parsed string
  */
function var_lang($str, $replace = array())
{
		$language = lang($str, "");
		preg_match_all('/%(.*)%/',$language,$regs);
		foreach($regs[1] as $key => $value)
		{
				$re = $replace[$value];
				$language = preg_replace("/%".preg_quote($value,'/')."%/",$re,$language);
		}

		return $language;   // return it!!
}
/**
* the function ereg with preg_match
*@name _ereg
*@params: view php manual of ereg
*/
function _ereg($pattern, $needed, &$reg = "")
{
		if(is_array($needed)) {
			return false;
		}
		return preg_match('/'.str_replace('/','\\/',$pattern).'/',$needed, $reg);
}
/**
* the function eregi with preg_match
*@name _eregi
*@params: view php manual of eregi
*/
function _eregi($pattern, $needed, &$reg = "")
{
		return preg_match('/'.str_replace('/','\\/',$pattern).'/i',$needed, $reg);
}
/**
 * escapes a string to use it in json
 *@name escapejson
 *@param string - string to escape
 *@return string - escaped string
*/
function escapejson($str)
{
		$str = convert::raw2js($str);
		$str = utf8_encode($str);
		return $str;
}
