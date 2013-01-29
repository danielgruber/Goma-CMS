<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 29.01.2013
  * $Version 2.0.0 - 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Hash extends Object {
	/**
	 * generates a hash
	 *
	 *@name makeHash
	 *@�ccess public
	*/
	public static function makeHash($string) {
		
	} 
	
	/**
	 * generates a random salt
	 */
	public static function generateSalt() {
		//chars
	        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ß!§%&[]}{";
	
	        //lenght 
	        $lenght = rand(236, 255); 
	
	        $salt = ""; 
	
	        for ($i = 1; $i < $lenght; ++$i) {
	            $salt .= $chars[rand(0, (strlen($chars) - 1))];
	        }
	       
	        return $salt; 
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

class sha512Hash extends Hash {
	/**
	 * generates a md5-hash
	 *
	 *@name makeHash
	 *@�ccess public
	*/
	public static function makeHash($string) {
		return hash('sha512', $string);
	}
}

class GomaHash extends Hash {
	/**
	 * generates a Goma-hash
	 *
	 *@name makeHash
	 *@�ccess public
	*/
	public static function makeHash($string) {
		
		return sha512::makeHash("GOMA_PASSWORD_PREFIX" . sha512Hash::makeHash($string));
	}
}
