<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for FileUploadSet.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class LangSelectTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "FileUploadSet";

    /**
     * tests assigning properties.
     */
    public function testAssignProps() {
        $defaultTypes = Object::instance("FileUploadSet")->allowed_file_types;
        $defaultCollection = Object::instance("FileUploadSet")->collection;

        $this->unitTestAssignProps("test", "blub", $defaultTypes, "blub");
        $this->unitTestAssignProps("*", "blub", "*", "blub");
        $this->unitTestAssignProps(array("png", "jpg"), "blub", array("png", "jpg"), "blub");
        $this->unitTestAssignProps("*", null, "*", $defaultCollection);

        $this->assertEqual(Object::instance("FileUploadSet")->handleUpload(null), "No Upload defined.");
    }

    public function unitTestAssignProps($fileTypes, $collection, $expectedTypes, $expectedCollection) {
        $set = new FileUploadSet("test", "test", $fileTypes, null, $collection);

        $this->assertEqual($set->allowed_file_types, $expectedTypes);
        $this->assertEqual($set->collection, $expectedCollection);
    }
}
