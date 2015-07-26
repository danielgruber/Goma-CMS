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
		new CacheManager(CACHE_DIRECTORY);

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

    public function testDeleteFolder() {
        $this->unitDeleteFolder("__test", true, false, false);
        $this->unitDeleteFolder("__blah", false, false, true);
        $this->unitDeleteFolder("__toll", true, true, true);
        $this->unitDeleteFolder("__hui", false, true, true);
    }

    public function unitDeleteFolder($folder, $dontRemove, $force, $expected) {
        $c = new CacheManager(CACHE_DIRECTORY);
        mkdir($c->dir() . $folder);
        if($dontRemove) {
            file_put_contents($c->dir() . $folder . "/.dontremove", "");
        }

        $this->assertEqual($c->shouldDeleteCacheFolder($folder, $force), $expected, "%s $folder with .dontremove: [$dontRemove] and Force[$force].");
        FileSystem::delete($c->dir() . $folder);
    }

    public function testShouldDeleteCacheFile() {
        $this->unitShouldDelete("__test.php", time(), 0, true);
        $this->unitShouldDelete("__test.php", time() - 3601, 3600, true);

        $this->unitShouldDelete("__test.php", time(), 1, false);
        $this->unitShouldDelete("__test.php", time() - 3600, 3600, false);

        $this->unitShouldDelete("gfs.__test.php", time(), 0, false);
        $this->unitShouldDelete("gfs.__test.php", time() - 120, 0, false);
        $this->unitShouldDelete("gfs.__test.php", time() - 7500, 8000, false);

        $this->unitShouldDelete("gfs.__test.php", time() - 7500, 0, true);
        $this->unitShouldDelete("gfs.__test.php", time() - 7500, 6000, true);


        $this->unitShouldDelete("data.test1234567890test12.goma", time() - 120, 1, false);

        $this->unitShouldDelete("data.test1234567890test12.goma", time() - 3700, 1, true);
        $this->unitShouldDelete("data.test1234567890test12.goma", time() - 7500, 6000, true);

        $this->unitShouldDelete("deletecache", time() - 3700, 0, false);
        $this->unitShouldDelete("autoloader_exclude", time() - 3700, 0, false);
    }

    /**
     * @param string $filename
     * @param int $timestamp
     * @param int $minLifeTime
     * @param bool $expected
     */
    public function unitShouldDelete($filename, $timestamp, $minLifeTime, $expected) {
        $cacheManager = new CacheManager(CACHE_DIRECTORY);
        touch($cacheManager->dir() . $filename, $timestamp);

        $diff = $timestamp - time();
        $this->assertEqual(
            $cacheManager->shouldDeleteCacheFile($filename, $minLifeTime),
            $expected,
            "%s $filename with timestamp-diff $diff and lifetime $minLifeTime."
        );

        FileSystem::delete($cacheManager->dir() . $filename);
    }
}
