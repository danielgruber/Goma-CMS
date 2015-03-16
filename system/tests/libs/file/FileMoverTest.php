<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for FileMover-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class FileMoverTest extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "Files";

	/**
	 * internal name.
	*/
	public $name = "FileMover";

	/**
	 * setup.
	*/
	public function setUp() {
		$this->dir = "system/temp/filemovertest";
		$this->dir2 = "system/temp/filemovertest2";
		$this->db = array("", "/testNotWritable", "/testWritable");
		$this->db2 = array("", "/testNotWritable/test.txt", "/testNotWritable", "/testWritable/write.txt");

		if(!file_exists($this->dir)) {
			mkdir($this->dir, 0777, true);
			mkdir($this->dir . "/testNotWritable", 0000, true);
			mkdir($this->dir . "/testWritable", 0777, true);

			mkdir($this->dir2, 0777, true);
			mkdir($this->dir2 . "/testNotWritable", 0777, true);
			mkdir($this->dir2 . "/testWritable", 0777, true);
			file_put_contents($this->dir2 . "/testNotWritable/test.txt", "blah");
			file_put_contents($this->dir2 . "/testWritable/write.txt", "lol");
		}
	}

	public function tearDown() {
		if(file_exists($this->dir)) {
			chmod($this->dir . "/testNotWritable", 0777);
			FileSystem::delete($this->dir);
		}

		if(file_exists($this->dir2)) {
			chmod($this->dir2 . "/testNotWritable", 0777);
			FileSystem::delete($this->dir2);
		}
	}

	public function testCheckValidWhenParentNotExists() {

		// check if test can execute and does not have side effects
		$this->assertFalse(file_exists($this->dir2 . "/testWritable/blah/test.txt"));
		$this->assertTrue(file_exists($this->dir2 . "/testWritable"));

		$filemover = new FileMover(array("blah/test.txt"), null, ROOT . $this->dir2 . "/testWritable");
		$this->assertTrue($filemover->checkValid());
	}

	public function testCheckValid() {

		$this->assertTrue(file_exists($this->dir2 . "/testNotWritable/test.txt"));
		$this->assertTrue(file_exists($this->dir2 . "/testWritable/write.txt"));

		$this->assertFalse(file_exists($this->dir . "/testNotWritable/test.txt"));
		$this->assertFalse(file_exists($this->dir . "/testWritable/write.txt"));

		$filemover = new FileMover($this->db2, $this->dir2, $this->dir);

		$filemover->setValid(false);
		$this->assertEqual($filemover->checkValid(), false);

		$filemover->setValid(false);
		$this->assertEqual($filemover->checkValid(true), false);

		$filemover->setValid(false);
		$this->assertEqual($filemover->checkValid(false, true), array("", "/testNotWritable/test.txt", "/testNotWritable"));

		$filemover->setValid(false);
		$this->assertEqual($filemover->checkValid(true, true), array("/testNotWritable/test.txt"));

		chmod($this->dir . "/testNotWritable", 0777);

		$filemover->setValid(false);
		$this->assertEqual($filemover->checkValid(true, true), array());

		$filemover->setValid(false);
		$this->assertFalse($filemover->checkValid(), false);

		$filemover->setValid(false);
		$this->assertTrue($filemover->checkValid(true), true);

		$filemover->setValid(true);
		chmod($this->dir . "/testNotWritable", 0000);
		$this->assertEqual($filemover->checkValid(true, true), array("/testNotWritable/test.txt"));
	}

	public function testMove() {
		$filemover = new FileMover($this->db2, $this->dir2, $this->dir);

		chmod($this->dir . "/testNotWritable", 0777);
		$this->assertEqual($filemover->execute(), count($this->db2));
		$this->assertTrue(file_exists($this->dir . "/testNotWritable/test.txt"));
		$this->assertTrue(file_exists($this->dir . "/testWritable/write.txt"));

		$this->assertFalse(file_exists($this->dir2 . "/testNotWritable/test.txt"));
		$this->assertFalse(file_exists($this->dir2 . "/testWritable/write.txt"));
	}
}