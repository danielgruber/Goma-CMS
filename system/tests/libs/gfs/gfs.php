<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for GFS-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class GFSTest extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "GFS";

	/**
	 * internal name.
	*/
	public $name = "GFS";

	/**
	 * tests md5.
	*/
	public function testmd5() {
		$gfs = new GFS(FRAMEWORK_ROOT . "temp/testmd5.gfs");

		file_put_contents(FRAMEWORK_ROOT . "temp/testmd5.txt", randomString(20));
		file_put_contents(FRAMEWORK_ROOT . "temp/testmd5big.txt", randomString(1024));

		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5.txt", "t.txt");
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5big.txt", "t2.txt");
		$this->assertEqual($gfs->getMd5("t.txt"), md5_file(FRAMEWORK_ROOT . "temp/testmd5.txt"));
		$this->assertEqual($gfs->getMd5("t2.txt"), md5_file(FRAMEWORK_ROOT . "temp/testmd5big.txt"));

		$gfs->close();

		FileSystem::delete(FRAMEWORK_ROOT . "temp/testmd5.gfs");
	}

	/**
	 * tests addir and isDir
	*/
	public function testDir() {
		$gfs = new GFS(FRAMEWORK_ROOT . "temp/testdir.gfs");

		$gfs->addDir("./test");
		file_put_contents(FRAMEWORK_ROOT . "temp/testmd5big.txt", randomString(1024));
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5big.txt", "t2.txt");
		$this->assertTrue($gfs->isDir("test"));
		$this->assertFalse($gfs->isDir("t2.txt"));
		$this->assertFalse($gfs->isDir("blah"));
	}
}
