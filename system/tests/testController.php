<?php defined("IN_GOMA") OR die();
/**
 * Controller for handling test-classes.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class GomaTestController extends RequestHandler {
	/**
	 * handles the request
	*/
	public function handleRequest() {
		restore_error_handler();
		
		define("IN_UNIT_TEST", true);

		require_once(ROOT . "system/libs/thirdparty/simpletest/autorun.php");
		
		$areas = $this->getAreas();
		$candidates = array();

		foreach(ClassInfo::$files as $class => $file) {
			if(ClassInfo::hasInterface($class, "Testable")) {
                if(!class_exists($class, false)) {
                    ClassManifest::load($class);
                }

				if(!$areas || $this->shouldLoadArea(StaticsManager::getStatic($class, "area"), $areas)) {
					$candidates[] = $class;
				}
			}
		}
		
		$GLOBALS["SIMPLETEST_CANDIDATES"] = $candidates;

		exit;
	}

	/**
	 * checks if area is in areas.
	*/
	public function shouldLoadArea($area, $areas) {
		return in_array(strtolower($area), $areas);
	}

	/**
	 * gets all areas given.
	*/
	public function getAreas() {
		
		if(isset($_GET["area"])) {
			if(is_array($_GET["area"])) {
				return array_map("strtolower", $_GET["area"]);
			} else {
				return array(strtolower($_GET["area"]));
			}
		}

		return array();
	}
}

Director::addRules(array(
	"dev/unit-test" => "GomaTestController"
), 50);
