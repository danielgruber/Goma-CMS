<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 31.03.2013
  * $Version 2.1.8
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Security extends Object {
	/**
	 * registers a IP as potential attacker
	 *
	 *@name registerAttacker
	 *@access public
	*/
	public function registerAttacker($ip, $browser) {
		// add a record in temporary
		$temp = ROOT . "system/temp/" . md5($ip . $browser);
		if(file_exists($temp) && filemtime($temp) > NOW - 60 * 60 * 3) {
			$content = file_get_contents($temp);
			if(is_int($content)) {
				$content++;
				file_put_contents($temp, $content);
			} else {
				file_put_contents($temp, 20);
			}
		} else {
			logging("Maybe we are under attack from IP: " . $ip . "; browser: " . $browser);
			file_put_contents($temp, 20);
		}
	}
}