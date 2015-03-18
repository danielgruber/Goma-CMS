<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for 503-Handling.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class UnavailableTest extends GomaUnitTest implements TestAble {

	
	static $area = "framework";
	/**
	 * name
	*/
	public $name = "UnavailableTest";


	/**
	 * test availability functions.
	*/
	public function testAvailability() {

		$this->assertEqual(isProjectUnavailable(), false);
		$this->assertEqual(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"]), false);

		makeProjectUnavailable();

		$this->assertEqual(isProjectUnavailable(), true);
		$this->assertEqual(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"]), false);
		$this->assertEqual(isProjectUnavailableForIP("1.2.3.4"), true);

		makeProjectAvailable();

		$this->assertEqual(isProjectUnavailable(), false);
		$this->assertEqual(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"]), false);
		$this->assertEqual(isProjectUnavailableForIP("1.2.3.4"), false);

		makeProjectUnavailable(APPLICATION, "1.2.3.4");

		$this->assertEqual(isProjectUnavailable(), true);
		$this->assertEqual(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"]), true);
		$this->assertEqual(isProjectUnavailableForIP("1.2.3.4"), false);

		makeProjectUnavailable();

		$this->assertEqual(isProjectUnavailable(), true);
		$this->assertEqual(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"]), false);
		$this->assertEqual(isProjectUnavailableForIP("1.2.3.4"), true);

		makeProjectAvailable();

		$this->assertEqual(isProjectUnavailable(), false);
		$this->assertEqual(isProjectUnavailableForIP($_SERVER["REMOTE_ADDR"]), false);
		$this->assertEqual(isProjectUnavailableForIP("1.2.3.4"), false);
	}
}