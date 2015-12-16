<?php defined("IN_GOMA") OR die();
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 31.03.2013
  * $Version 2.1.8
*/

class Security extends gObject {
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