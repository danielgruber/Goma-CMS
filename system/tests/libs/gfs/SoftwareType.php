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

			if(isset($info["icon"]) && $info["icon"]) {
				$this->assertTrue(file_exists($info["icon"]), "Icon '" . $info["icon"] . "' Not found %s");
			}
		}
	}

	public function testIndexing() {
		$i = G_SoftwareType::getIndexData();

		$this->assertNotNull($i["fileindex"]);
		$this->assertNotNull($i["packages"]);

		$generatedIndex = G_SoftwareType::buildPackageIndex();

		$this->assertEqual($generatedIndex, array(), "Checking for errors: " . print_r($generatedIndex, true));
	}

	public function testListing() {
		$installList = G_SoftwareType::listInstallPackages();

		$this->assertNotNull($installList);

		foreach($installList as $k => $v) {
			$this->assertNotNull($v["filename"]);
			$this->assertNotNull($v["file"]);
			$this->assertNotNull($v["type"]);
			$this->assertNotNull($v["plist_type"]);

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

    public function testIsValidPackageType() {
        $this->unitIsValidPackageType(array(), false, true);
        $this->unitIsValidPackageType(array("installable" => false), false, false);

        $data = array(
            "installable"       => true,
            "installType"       => "update",
            "version"           => "1.0-010",
            "installed_version" => "1.0-009"
        );

        $this->unitIsValidPackageType($data, false, false);
        $this->unitIsValidPackageType($data, true, true);

        $ndata = array(
            "installable"       => true,
            "version"           => "1.0-010",
        );

        $this->unitIsValidPackageType($ndata, false, true);
        $this->unitIsValidPackageType($ndata, true, false);
    }

    public function unitIsValidPackageType($data, $shouldUpdate, $expected) {
        $this->assertEqual(g_SoftwareType::isValidPackageTypeAndVersion($data, $shouldUpdate), $expected, "%s " . print_r($data, true) . " Update: [$shouldUpdate]");
    }

	public function testReadingPlist() {
		$data = g_SoftwareType::getPlistFromPlist(ROOT . "system/info.plist");
		$this->assertNotEqual($data, array());
		$this->assertEqual($data["type"], "framework");
	}

	public function testReadingPlistFromGFS() {
		$data = array(
			"blub" => randomString(10),
			"blah" => 2
		);
		$file = ROOT . "system/temp/test3plist.gfs";
		$gfs = new GFS($file);
		$gfs->writePlist("test.plist", $data);
		$gfs->close();

		$info = g_SoftwareType::getPlistFromGFS($file, "test.plist");
		$this->assertEqual($info, $data);

		FileSystem::delete($file);
	}

	public function testGetPlistorGFS() {
		$data = array(
			"blub" => randomString(10),
			"blah" => 2
		);
		$file = ROOT . "system/temp/test2plist.gfs";
		$pfile = ROOT . "system/temp/test2plist.plist";
		$gfs = new GFS($file);
		$gfs->writePlist("test.plist", $data);
		FileSystem::write($pfile, $gfs->getFileContents("test.plist"));

		$this->assertNotEqual(file_get_contents($pfile), "");
		$this->assertEqual(file_get_contents($pfile), $gfs->getFileContents("test.plist"));

		$gfs->close();

		touch($pfile, NOW);
		touch($file, NOW - 1);

		clearstatcache();

		$this->assertEqual(g_SoftwareType::getFromPlistOrGFS($pfile, $file, "test.plist", true), $data);
		$this->assertTrue(file_exists($pfile));

		touch($pfile, NOW);
		touch($file, NOW);

		clearstatcache();

		$this->assertEqual(filemtime($file), filemtime($pfile));

		$this->assertEqual(g_SoftwareType::getFromPlistOrGFS($pfile, $file, "test.plist"), $data);
		$this->assertTrue(file_exists($pfile));

		$this->assertEqual(g_SoftwareType::getFromPlistOrGFS($pfile, $file, "test.plist", true), $data);
		$this->assertFalse(file_exists($pfile));

		FileSystem::delete($file);
		FileSystem::delete($pfile);
	}
}
