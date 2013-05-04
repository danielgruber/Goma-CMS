<?php
/**
  * Test Classes
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 25.04.13
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class GomaTestController extends RequestHandler {
	/**
	 * handles the request
	*/
	public function handleRequest() {
		restore_error_handler();
		
		require_once(ROOT . "system/libs/thirdparty/simpletest/autorun.php");
		
		foreach(ClassInfo::$files as $class => $file) {
			if(ClassInfo::hasInterface($class, "Testable")) {
				ClassManifest::load($class);
			}
		}
		
		exit;
	}
}