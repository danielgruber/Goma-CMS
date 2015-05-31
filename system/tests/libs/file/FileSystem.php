<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for FileSystem-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class FileSystemTest extends GomaUnitTest {

	/**
	 * area
	*/
	static $area = "Files";

	/**
	 * internal name.
	*/
	public $name = "FileSystem";

	/**
	 * setup.
	*/
	public function setUp() {
		$this->dir = FRAMEWORK_ROOT . "system/temp/filesystemtest";

		if(!file_exists($this->dir)) {
			mkdir($this->dir, 0777, true);
		}
	}

	public function tearDown() {
		if(file_exists($this->dir)) {
			rmdir($this->dir);
		}
	}

	public function testDirFunctions() {
		FileSystem::requireDir($this->dir . "/blah", 0755);
		$p = substr(sprintf('%o', fileperms($this->dir . "/blah")), -4);
		$this->assertEqual($p, "0755");

		FileSystem::requireDir($this->dir . "/blah/blub", 0755);
		FileSystem::createFile($this->dir . "/blah/blub/test.txt");

		$this->assertTrue(file_exists($this->dir . "/blah/blub/test.txt"));

		// test copy
		FileSystem::copy($this->dir . "/blah", $this->dir . "/copyofblah");
		$this->assertTrue(file_exists($this->dir . "/copyofblah/blub/test.txt"));

		// test move
		FileSystem::move($this->dir . "/copyofblah", $this->dir . "/movedblah");
		$this->assertFalse(file_exists($this->dir . "/copyofblah/blub/test.txt"));
		$this->assertTrue(file_exists($this->dir . "/movedblah/blub/test.txt"));

		FileSystem::delete($this->dir . "/copyofblah");
		FileSystem::delete($this->dir . "/movedblah");
		FileSystem::delete($this->dir);

		$this->assertFalse(file_exists($this->dir));
	}

	public function testMoveSubfolders() {
		FileSystem::requireDir($this->dir . "/movetest", 0755);
		FileSystem::requireDir($this->dir . "/movetest/test", 0755);
		FileSystem::requireDir($this->dir . "/movetest/test/blub", 0755);
		touch($this->dir . "/movetest/test/blub/test.txt");

		$this->assertTrue(file_exists($this->dir . "/movetest/test/blub/test.txt"));
		FileSystem::moveFolderContents($this->dir . "/movetest", $this->dir . "/movetest2");
		$this->assertTrue(file_exists($this->dir . "/movetest"));
		$this->assertTrue(file_exists($this->dir . "/movetest2"));
		$this->assertTrue(file_exists($this->dir . "/movetest2/test/blub/test.txt"));
		$this->assertFalse(file_exists($this->dir . "/movetest/test/blub/test.txt"));

		FileSystem::delete($this->dir . "/movetest2");
		FileSystem::delete($this->dir . "/movetest");
	}

	public function testFileSize() {

		$sizes = array(
			1000 	=> "1000B",
			10000 	=> "9.8K",
			20000	=> "19.5K",
			2097152	=> "2M"
		);

		foreach($sizes as $size => $nice) {
			$this->assertEqual(FileSizeFormatter::format_nice($size), $nice, "FileSize-Nice: $size should be printed as $nice, but is ".FileSizeFormatter::format_nice($size));
		}
		
	}
}