<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for CacheManager-Class.
 *
 * @package		Goma\Cache
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class CacheManagerTests extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "Cache";

	/**
	 * internal name.
	*/
	public $name = "CacheManager";

	/**
 	 * tests cache-manager init.
	*/
	public function testInit() {
		$cacheManager = new CacheManager(CACHE_DIRECTORY);

		$this->assertTrue(file_exists(CACHE_DIRECTORY . "/autoloader_exclude"));
	}

	/**
	 * tests path-generation.
	*/
	public function testPath() {
		$c = new CacheManager(APPLICATION . "/temp");

		$this->assertEqual($c->dir(),  APPLICATION . "/temp/");
	}

	/**
	 * tests writing
	*/
	public function testWrite() {
		$c = new CacheManager(APPLICATION . "/temp");
		$c->put("test", $h = randomString(10));
		$this->assertTrue($c->exists("test"));
		$this->assertEqual($c->contents("test"), $h);

		$this->assertFalse($c->exists("notExistingBullshit"));
		$this->assertNull($c->contents("notExistingBullshit"));
	}
}
