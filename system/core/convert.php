<?php
defined("IN_GOMA") OR die();

/**
 * This contains some text converting methods.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Framework
 * @version 1.3.1
 */
class Convert {
	/**
	 * converts raw-code to js
	 *@param string - raw
	 */
	static function raw2js($str) {
		if(is_array($str)) {
			foreach($str as $k => $v)
				$str[$k] = self::raw2js($v);
			return $str;
		} else {
			return str_replace(array("\\", "\"", "'", "\n", "\r", "\t", "\b", "\f", "/"), array("\\\\", "\\\"", "\\'", '\n', '\r', '\t', '\b', '\f', '\/'), $str);
		}
	}

	/**
	 * converts raw to sql
	 */
	static function raw2sql($str) {
		if(is_array($str)) {
			foreach($str as $k => $v)
				self::raw2sql($v);
			return $str;
		} else {
			if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
				return sql::escape_string(stripslashes($str));
			}
			$str = sql::escape_string($str);
			return $str;
		}
	}

	/**
	 * converts raw to text with correct Lines
	 *
	 */
	static function raw2xmlLines($val) {
		if(is_array($val)) {
			foreach($val as $k => $v)
				$val[$k] = self::raw2xmlLines($v);
			return $val;
		} else {
			return nl2br(self::raw2xml($val));
		}
	}

	/**
	 * Makes a string or array of strings XML suitable.
	 *
	 * @param string $val Input Text
	 *
	 * @return string XML suitable string.
	 */
	static function raw2xml($val) {
		if(is_array($val)) {
			foreach($val as $k => $v)
				$val[$k] = self::raw2xml($v);
			return $val;
		} else {
			return htmlentities($val, ENT_COMPAT, "UTF-8", false);
		}
	}

	/**
	 * Alias for Convert::raw2xml().
	 *
	 * @see Convert::raw2xml()
	 *
	 * @param string $val Input Text
	 *
	 * @return string XML suitable string.
	 */
	static function raw2text($val) {
		return self::raw2xml($val);
	}

	/**
	 * There are no real specifications on correctly encoding mailto-links,
	 * but this seems to be compatible with most of the user-agents.
	 * Does nearly the same as rawurlencode().
	 * Please only encode the values, not the whole url, e.g.
	 * "mailto:test@test.com?subject=" . Convert::raw2mailto($subject)
	 *
	 * @param $data string
	 * @return string
	 * @see http://www.ietf.org/rfc/rfc1738.txt
	 */
	static function raw2mailto($data) {
		return str_ireplace(array("\n", '?', '=', ' ', '(', ')', '&', '@', '"', '\'', ';'), array('%0A', '%3F', '%3D', '%20', '%28', '%29', '%26', '%40', '%22', '%27', '%3B'), $data);
	}

	/**
	 * Convert a JSON encoded string into an object.
	 *
	 * @param string $val
	 * @return gObject|boolean
	 */
	static function json2obj($val) {
		return json_decode($val);
	}

	/**
	 * Encode a value as a JSON encoded string.
	 *
	 * @param mixed $val Value to be encoded
	 * @return string JSON encoded string
	 */
	static function raw2json($val) {
		return json_encode($val);
	}

	/**
	 * Encode a value as a URL
	 *
	 *@param val
	 *@access public
	 */
	static function raw2url($val) {
		if(is_array($val)) {
			foreach($url as $k => $v)
				$url[$k] = self::raw2url($v);
			return $val;
		} else {
			return urlencode($url);
		}
	}

	/**
	 * Encode a URL as raw
	 *
	 *@param val
	 *@access public
	 */
	static function url2raw($val) {
		if(is_array($val)) {
			foreach($url as $k => $v)
				$url[$k] = self::url2raw($v);
			return $val;
		} else {
			return urldecode($url);
		}
	}

}
