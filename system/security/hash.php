<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.06.2011
  * $Version 2.0.0 - 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Hash extends Object {
	/**
	 * generates a hash
	 *
	 *@name makeHash
	 *@Œccess public
	*/
	public static function makeHash($string) {
		
	}
	/**
	 * gets the hash from the current default function
	 *
	 *@name getHashFromDefaultFunction
	 *@access public
	*/
	public static function getHashFromDefaultFunction($string) {
		return GomaHash::makeHash($string);
		
	}
	/**
	 * checks if a hash matches any of the hash-functions
	 *
	 *@name checkHashMatches
	 *@access public
	 *@param string - string of which the hash will made of
	 *@param string - hash
	*/
	public static function checkHashMatches($string, $hash) {
		foreach(classinfo::getchildren("hash") as $class) {
			if(call_user_func_array(array($class, "makeHash"), array($string)) == $hash)
				return true;
		}
		return false;
	}
}

class md5Hash extends Hash {
	/**
	 * generates a md5-hash
	 *
	 *@name makeHash
	 *@Œccess public
	*/
	public static function makeHash($string) {
		return md5($string);
	}
}

class GomaHash extends Hash {
	/**
	 * generates a Goma-hash
	 *
	 *@name makeHash
	 *@Œccess public
	*/
	public static function makeHash($string) {
		
		return md5("GOMA_PASSWORD_PREFIX" . md5($string));
	}
}