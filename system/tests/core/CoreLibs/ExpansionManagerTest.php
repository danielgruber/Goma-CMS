<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Expansion-Manageer-Class.
 *
 * @package		Goma\Cache
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ExpansionManagerTests extends GomaUnitTest
{
    /**
     * area
     */
    static $area = "Expansions";

    /**
     * internal name.
     */
    public $name = "ExpansionManager";

    /**
     * tests get expansion name.
     */
    public function testGetExpansionName() {
        $this->assertEqual($this->unittestGetExpansionName("test", "test", "blub"), "test");
        $this->assertEqual($this->unittestGetExpansionName("blub", "test", "blub"), "test");
        $this->assertEqual($this->unittestGetExpansionName("test", null, "blub"), null);
        $this->assertEqual($this->unittestGetExpansionName("blub", null, "blub"), null);

        $testStdClass = new StdClass();
        $testStdClass->classname = "blub";
        $testStdClass->inExpansion = "test";

        $this->assertEqual($this->unittestGetExpansionName($testStdClass, "test", "blub"), "test");

        $testStdClass->inExpansion = null;
        $this->assertEqual($this->unittestGetExpansionName(new MockClassForExpansionBlub(), "test", "mockclassforexpansionblub"), "test");
        $this->assertEqual($this->unittestGetExpansionName(new MockClassForExpansionBlub(), "test", "blub2"), null);

        $this->assertEqual($this->unittestGetExpansionName(new StdClass(), "test", "blub"), null);
    }

    protected function unittestGetExpansionName($nameArg, $expansionShouldExist, $classShouldExistWithExpansion) {
        if(isset($expansionShouldExist) && !isset(ClassInfo::$appENV["expansion"][$expansionShouldExist])) {
            ClassInfo::$appENV["expansion"][$expansionShouldExist] = array(
                "folder" => 123
            );
        }

        if(isset($classShouldExistWithExpansion) && !isset(ClassInfo::$class_info[$classShouldExistWithExpansion])) {
            ClassInfo::$class_info[$classShouldExistWithExpansion] = array(
                "parent" => null
            );
        }

        ClassInfo::$class_info[$classShouldExistWithExpansion]["inExpansion"] = $expansionShouldExist;

        $return = ExpansionManager::getExpansionName($nameArg);

        unset(ClassInfo::$appENV["expansion"][$expansionShouldExist]);
        unset(ClassInfo::$class_info[$classShouldExistWithExpansion]);

        return $return;
    }

    /**
     * tests to get resource folder.
     */
    public function testGetResourceFolder() {
        ClassInfo::$appENV["expansion"]["blah"] = array(
            "folder" => "./system/"
        );

        $this->assertEqual(ExpansionManager::getResourceFolder("blah"), "./system/resources");
        $this->assertEqual(ExpansionManager::getResourceFolder("blah", true), ROOT . "system/resources");
        $this->assertIdentical(ExpansionManager::getResourceFolder(null), null);

        unset(ClassInfo::$appENV["expansion"]["blah"]);
    }

    /**
     * tests folders.
     */
    public function testFolders() {
        foreach(ClassInfo::$appENV["expansion"] as $expansion) {
            $this->assertTrue($expansion["folder"]);
            $this->assertEqual(substr($expansion["folder"], -1), "/");
        }
    }

    /**
     * tests getting expansion folder.
     */
    public function testGetExpansionFolder() {
        ClassInfo::$appENV["expansion"]["blah"] = array(
            "folder" => "./system/"
        );

        $this->assertIdentical(ExpansionManager::getExpansionFolder(null), null);
        $this->assertEqual(ExpansionManager::getExpansionFolder("blah"), "./system/");
        $this->assertEqual(ExpansionManager::getExpansionFolder("blah", true), ROOT . "system/");

        ClassInfo::$appENV["expansion"]["blah"] = array(
            "folder" => "./system"
        );

        $this->assertEqual(ExpansionManager::getExpansionFolder("blah"), "./system");
        $this->assertEqual(ExpansionManager::getExpansionFolder("blah", true), ROOT . "system/");

        unset(ClassInfo::$appENV["expansion"]["blah"]);
    }
}

class MockClassForExpansionBlub {}