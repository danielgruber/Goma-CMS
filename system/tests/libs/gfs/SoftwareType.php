<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for GFS-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class SoftwareTypeTest extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "Installation";

	/**
	 * internal name.
	*/
	public $name = "SoftwareType";

	public function testListAllSoftware() {
		$list = G_SoftwareType::listAllSoftware();

		$this->assertNotNull($list);
		$this->assertNotNull($list["framework"]);

		foreach($list as $app => $info) {
			$this->assertNotNull($info["title"]);
			$this->assertNotNull($info["version"]);
			$this->assertNotNull($info["canDisable"]);

			if(isset($info["icon"])) {
				$this->assertTrue(file_exists($info["icon"]));
			}
		}
	}

	public function testIndexing() {
		$i = G_SoftwareType::getIndexData();

		$this->assertNotNull($i["fileindex"]);
		$this->assertNotNull($i["packages"]);

		$generatedIndex = G_SoftwareType::buildPackageIndex();

		$this->assertEqual($generatedIndex, array());
	}

	public function testListing() {
		$installList = G_SoftwareType::listInstallPackages();

		$this->assertNotNull($installList);

		foreach($installList as $k => $v) {
			$this->assertNotNull($v["filename"]);
			$this->assertNotNull($v["file"]);
			$this->assertNotNull($v["type"]);
			$this->assertNotNull($v["plist_type"]);
			$this->assertNotNull($v["installType"]);
			$this->assertNotNull($v["appInfo"]);
			$this->assertTrue(file_exists($v["file"]));

			$inst = $this->unitGetByType($v["plist_type"], "G_SoftwareType", $v["file"]);

			$this->unitPackageInfo($inst, $v);
		}

	}

	public function unitPackageInfo($inst, $v) {
		$infos = $inst->getPackageInfo();
		$infos["plist_type"] = $v["plist_type"];
		$infos["file"] = $v["file"];

		if(isset($infos["installable"])) {
			$this->assertFalse($infos["installable"]);
		}
		$this->assertEqual($infos, $v);
	}

	public function testGetByType() {
		$this->unitGetByType("backup", "G_AppSoftwareType", "file.gfs");
		$this->unitGetByType("app", "G_AppSoftwareType", "file.gfs");
		$this->unitGetByType("BACKUP", "G_AppSoftwareType", "file.gfs");
		$this->unitGetByType("APP", "G_AppSoftwareType", "file.gfs");

		$this->unitGetByType("APP", "G_AppSoftwareType", "");
		$this->unitGetByType("APP", "G_AppSoftwareType", null);

		$this->unitGetByType("framework", "G_FrameworkSoftwareType", "");
		$this->unitGetByType("FRAMEWORK", "G_FrameworkSoftwareType", null);

		$this->unitGetByType("expansion", "G_ExpansionSoftwareType", "");
		$this->unitGetByType("EXPANSION", "G_ExpansionSoftwareType", null);

		$this->unitGetByType("notexistingtype", null, "file.gfs");
		$this->unitGetByType("BLAHBLUBT", null, "file.gfs");
	}

	public function unitGetByType($type, $class, $file) {
		if(isset($class)) {
			$this->assertIsA($t = G_SoftwareType::getByType($type, $file), $class);
			$this->assertEqual($t->getFile(), $file);

			return $t;
		} else {
			try {
				G_SoftwareType::getByType($type, $file);
				$this->fail("$type was found as SoftwareType.");
			} catch(LogicException $e) {
				$this->assertEqual($e->getMessage(), "Could not find SoftwareType '".convert::raw2text($type)."'.");
			}
		}
	}
}