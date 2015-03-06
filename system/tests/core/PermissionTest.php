<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for HTMLText-Field.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class PermissionCheckerTest extends GomaUnitTest implements Testable {
	

	/**
	 * area
	*/
	static $area = "framework";

	/**
	 * internal name.
	*/
	public $name = "PermissionChecker";
	/**
	 * setup test
	*/
	public function setUp() {

		if(file_exists(FRAMEWORK_ROOT . "temp/testNotWritable")) {
			$this->tearDown();
		}

		mkdir(FRAMEWORK_ROOT . "temp/testNotWritable", 0000, true);
		mkdir(FRAMEWORK_ROOT . "temp/testWritable", 0777, true);
	}

	public function tearDown() {
		@rmdir(FRAMEWORK_ROOT . "temp/testNotWritable");
		@rmdir(FRAMEWORK_ROOT . "temp/testWritable");
		@rmdir(FRAMEWORK_ROOT . "temp/testNewFolder");
	}

	public function testPermissionChecker() {
		$permChecker = new PermissionChecker();
		$permChecker->addFolders(array(
			"system/temp/testNotWritable",
			"system/temp/testWritable"
		));
		$permChecker->setPermissionMode(false);

		$this->assertEqual($permChecker->tryWrite(), array("system/temp/testNotWritable"));
		$permChecker->setPermissionMode(0777);
		$this->assertEqual($permChecker->tryWrite(), array());

		$permChecker->addFolders(array(
			"system/temp/testNewFolder"
		));

		$this->assertEqual($permChecker->tryWrite(), array());
		$this->assertTrue(file_exists("system/temp/testNewFolder"));
	}

	public function testPermissionOptions() {
		$perms = array(0777, 0755, 0775, 0774, 0111, 000, 0444, 0111, 0222, 0555, 0711, 0744, 999);

		foreach($perms as $perm) {
			$this->assertEqual(PermissionChecker::isValidPermission($perm), true, "Test Permission $perm; Should pass.");
		}

		$permsNotMatch = array(1234, -1, 55555);

		foreach($permsNotMatch as $perm) {
			$this->assertEqual(PermissionChecker::isValidPermission($perm), false, "Test Permission $perm; Should fail.");
		}
	}
}