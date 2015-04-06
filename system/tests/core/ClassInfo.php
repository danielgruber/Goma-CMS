<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ClassInfoTests.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ClassInfoTests extends GomaUnitTest {
	
	static $area = "framework";
	/**
	 * name
	*/
	public $name = "ClassInfo";

	public function testGetExpansionFolder() {
		$this->unitGetExpansionFolder("blah", "test/", "blah", "test/");

		$c = new StdClass();
		$c->inExpansion = "blah";
		$this->unitGetExpansionFolder("blah", "test/", $c, "test/");

		$this->unitGetExpansionFolder("blah", "test/", "bullshitNotExisting", null);
	}

	public function unitGetExpansionFolder($name, $folder, $query, $expected) {

		$this->assertFalse(isset(ClassInfo::$appENV["expansion"][$name]));
		ClassInfo::$appENV["expansion"][$name] = array(
			"folder" => $folder
		);

		$this->assertEqual(ExpansionManager::getExpansionFolder($query), $expected, "Expansion Test $name %s");

		unset(ClassInfo::$appENV["expansion"][$name]);
	}

}