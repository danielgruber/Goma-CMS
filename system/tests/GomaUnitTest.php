<?php defined("IN_GOMA") OR die();
/**
 * Base-Class for all Goma Unit-Tests.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

require_once("system/libs/thirdparty/simpletest/unit_tester.php");

abstract class GomaUnitTest extends UnitTestCase implements TestAble {
	/**
	 * information about area.
	*/
	static $area = "default";

	/**
	 * name of test.
	*/
	public $name = null;

	public function __construct() {
		if($this->name) {
			parent::__construct($this->name);
		}

		parent::__construct();
	}

    public function assertThrows($callback, $exceptionName) {
        try {
            call_user_func_array($callback, array());

            $this->assertFalse(true, "Expected Exception $exceptionName, but no Exception were thrown.");
        } catch(Exception $e) {
            $this->assertIsA($e, $exceptionName);
        }
    }
}