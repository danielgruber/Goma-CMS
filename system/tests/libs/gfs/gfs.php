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

	public function tearDown()
	{
		FileSystem::delete(FRAMEWORK_ROOT . "temp/testmd5.gfs");
		FileSystem::delete(FRAMEWORK_ROOT . "temp/testdir.gfs");
		FileSystem::delete(FRAMEWORK_ROOT . "temp/testcontents.gfs");
	}

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
		$gfs->addDir("./blub");
		file_put_contents(FRAMEWORK_ROOT . "temp/testmd5big.txt", randomString(1024));
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5big.txt", "t2.txt");
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5big.txt", "blub/t4.txt");
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5big.txt", "blub/great/t4.txt");
		$this->assertTrue($gfs->isDir("test"));
		$this->assertFalse($gfs->isDir("t2.txt"));
		$this->assertFalse($gfs->isDir("blah"));
		$this->assertFalse($gfs->isDir("blah/t4.txt"));
		$this->assertTrue($gfs->isDir("blub/great"));

		$this->assertEqual($gfs->getMd5("blub/t4.txt"), md5_file(FRAMEWORK_ROOT . "temp/testmd5big.txt"));

		$gfs->close();

		FileSystem::delete(FRAMEWORK_ROOT . "temp/testdir.gfs");
	}

	/**
	 * tests file content.
	*/
	public function testFileContent() {
		$gfs = new GFS(FRAMEWORK_ROOT . "temp/testcontents.gfs");

		file_put_contents(FRAMEWORK_ROOT . "temp/testmd5.txt", randomString(20));
		file_put_contents(FRAMEWORK_ROOT . "temp/testmd5big.txt", randomString(1024));
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5big.txt", "blah/t2.txt");
		$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5.txt", "blub/t2.txt");
		$gfs->addFile("test/test.txt", "Hello World");
		$gfs->addFile("test/testbig.txt", $random = randomString(1024));


		$this->assertEqual($gfs->getFileContents("blah/t2.txt"), file_get_contents(FRAMEWORK_ROOT . "temp/testmd5big.txt"));
		$this->assertEqual($gfs->getFileContents("blub/t2.txt"), file_get_contents(FRAMEWORK_ROOT . "temp/testmd5.txt"));

		$this->assertThrows(function() use($gfs) {
			$gfs->getFileContents("test/myfile.txt");
		}, "GFSFileNotFoundException");
		$this->assertNull($gfs->touch("test/myfile.txt"));
		$this->assertEqual($gfs->getFileContents("test/myfile.txt"), "");
		$this->assertEqual($gfs->getFileContents("test/test.txt"), "Hello World");
		$this->assertEqual($gfs->getFileContents("test/testbig.txt"), $random);
		$this->assertEqual($gfs->getFileSize("test/testbig.txt"), 1024);
		$this->assertWithinMargin($gfs->getLastModified("test/testbig.txt"), time(), 1);

		$gfs->touch("test/testbig.txt", NOW - 2014);
		$this->assertEqual($gfs->getLastModified("test/testbig.txt"), NOW - 2014);

		$gfs->writeToFileSystem("test/testbig.txt", FRAMEWORK_ROOT . "temp/testcbig.txt");
		$this->assertTrue(file_exists(FRAMEWORK_ROOT . "temp/testcbig.txt"));
		$this->assertEqual(file_get_contents(FRAMEWORK_ROOT . "temp/testcbig.txt"), $random);		

		$this->assertThrows(function() use($gfs) {
			$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5.txt", "test/myfile.txt");
		}, "GFSFileExistsException");
		$this->assertThrows(function() use($gfs) {
			$gfs->addFromFile(FRAMEWORK_ROOT . "temp/testmd5doesnotexist.txt", "test/myfile.txt");
		}, "GFSRealFileNotFoundException");

		$gfs->close();

		FileSystem::delete(FRAMEWORK_ROOT . "temp/testcontents.gfs");
	}

	public function testWritePlist() {
		$data = array(
				"blub" => randomString(10),
				"blah" => 2
		);
		$file = ROOT . "system/temp/test3plist.gfs";
		if(file_exists($file) && filesize($file) > 1000 * 1000) {
			FileSystem::delete($file);
		}

		$gfs = new GFS($file);
		$gfs->writePlist("test.plist", $data);
		$gfs->close();
		$info = g_SoftwareType::getPlistFromGFS($file, "test.plist");
		$this->assertEqual($info, $data);
		//FileSystem::delete($file); // without this line it does not work

	}
}
