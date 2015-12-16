<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 29.01.2013
  * $Version 2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Hash extends gObject {
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
	        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
	
	        //length
	        $length = rand(236, 255); 
	
	        $salt = ""; 
	
	        for ($i = 1; $i < $length; ++$i) {
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
		return GomaSHA512Hash::makeHash($string);
		
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
			if(gObject::method_exists($class, "HashMatches")) {
				if(call_user_func_array(array($class, "HashMatches"), array($string, $hash))) {
					return true;
				}
				
			} else if(call_user_func_array(array($class, "makeHash"), array($string)) == $hash)
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
	 *@�ccess public
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
	 *@�ccess public
	*/
	public static function makeHash($string) {
		
		return md5("GOMA_PASSWORD_PREFIX" . md5($string));
	}
}

class GomaSHA512Hash extends Hash {
	/**
	 * checks if a hash matches
	 *
	 *@name HashMatches
	 *@�ccess public
	*/
	public static function HashMatches($string, $hash) {
		if(strpos($hash, ":")) {
			$parts = explode(":", $hash);
			$salt = $parts[0];
			$hash = $parts[1];
			if(self::hash512(self::hash512($salt . $string) . "GOMA_PASSWORD_PREFIX") == $hash) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * hashes with 512
	*/
	public static function hash512($str) {
		return hash("sha512", $str);
	}
	
	/**
	 * generates a Goma-hash
	 *
	 *@name makeHash
	 *@�ccess public
	*/
	public static function makeHash($string) {
		$salt = Hash::generateSalt();
		return $salt . ":" . self::hash512(self::hash512($salt . $string) . "GOMA_PASSWORD_PREFIX");
	}
}