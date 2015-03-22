<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Cacher-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class CacheTest extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "Cache";

	/**
	 * internal name.
	*/
	public $name = "Cacher";

	public function setUp() {
		Core::deletecache();
	}

	public function tearDown() {
		$cacher = new Cacher("testNotExisting");
		$cacher->delete();
	}

	/**
	 * tests cacher.
	*/
	public function testCache() {

		if(isset($_GET["flush"])) {
			$this->assertFalse(true, "Test for Cache just works if flush is set to 0.");
		} else {

			$this->caseCache("test", "blah", 120);
			$this->caseCache("testArray", array("blub" => 1), 10);
			$this->caseCache("testException", new StdClass(), 10);
			$this->caseCache("testNumber", 1234, 10);
			$this->caseCache("testNotExisting", "blub", 10, false);

			$this->caseCache("test1second", "blu", 1, true);
			$this->caseCache("test1second", "blu", 1, true);

			//$this->caseCache("testProblemWithSeconds", "blub", 0, false);

			$this->caseDeleteCache("testdelete", "blah", 10);
			$this->caseDeleteCache("testDeleteArray", array("blub" => 1), 10);
			$this->caseDeleteCache("testDeleteException", new StdClass(), 10);

			$this->caseCacheThreadSafety("testdelete", "blah", 10);
			$this->caseCacheThreadSafety("testDeleteArray", array("blub" => 1), 10);
			$this->caseCacheThreadSafety("testDeleteException", new StdClass(), 10);
		}
	}

	/**
	 * test-cases for test-cache.
	*/
	public function caseCache($name, $value, $time, $canExist = true) {
		$cacher = new Cacher($name);
		if($cacher->checkValid()) {
			if(!$canExist) {
				throw new Exception("Test-Case for Cache with name $name. Cache should not exist.");
			}

			$this->assertEqual($cacher->getData(), $value, $name . " %s");

			// same count of asserts.
			$this->assertTrue(true);
		} else {
			$this->assertTrue($cacher->write($value, $time), $name . " %s");
			$this->assertEqual($cacher->getData(), $value, $name . " %s");
		}

		Cacher::clearInstanceCache();

		unset($cacher);

		$cacher = new Cacher($name);
		$this->assertTrue($cacher->checkValid(), $name . " %s");
		$this->assertEqual($cacher->getData(), $value, $name . " %s");
	}

	/**
	 * test-case for Cache-Deletion.
	*/
	public function caseDeleteCache($name, $val) {
		$this->caseCache($name, $val, 60);

		$cacher = new Cacher($name);
		$this->assertTrue($cacher->delete(), $name . " %s");

		unset($cacher);

		$cacher = new Cacher($name);
		$this->assertFalse($cacher->checkValid(), $name . " %s");
	}

	/**
  	 * checks if local cache is managed thread-safe.
	*/
	public function caseCacheThreadSafety($name, $val) {
		$cacher = new Cacher($name);
		$cacher->write($val, 60);

		unset($cacher);

		// now we loaded data from memory.
		$cacher = new Cacher($name);
		$this->assertTrue($cacher->checkValid(), $name . " %s");

		// we are bad-ass and remove local cache from other thread.
		Cacher::clearInstanceCache();

		$this->assertTrue($cacher->checkValid(), $name . " %s");
		$this->assertEqual($cacher->getData(), $val, $name . " %s");

		unset($cacher);

		// force loading from file
		Cacher::clearInstanceCache();
		$cacher1 = new Cacher($name);

		Cacher::clearInstanceCache();
		$cacher2 = new Cacher($name);

		$this->assertTrue($cacher1->checkValid() && $cacher2->checkValid(), $name . " %s");

		$cacher1->delete();
		$this->assertTrue($cacher2->checkValid(), $name . " %s");
		$this->assertFalse($cacher1->checkValid(), $name . " %s");

		$this->assertEqual($cacher2->getData(), $val, $name . " %s");
	}
}