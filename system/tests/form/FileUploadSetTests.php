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
        $defaultTypes = gObject::instance("FileUploadSet")->allowed_file_types;
        $defaultCollection = gObject::instance("FileUploadSet")->collection;

        $this->unitTestAssignProps("test", "blub", $defaultTypes, "blub");
        $this->unitTestAssignProps("*", "blub", "*", "blub");
        $this->unitTestAssignProps(array("png", "jpg"), "blub", array("png", "jpg"), "blub");
        $this->unitTestAssignProps("*", null, "*", $defaultCollection);

        $this->assertThrows(function() {
            gObject::instance("FileUploadSet")->handleUpload(null);
        }, "Exception");
    }

    public function unitTestAssignProps($fileTypes, $collection, $expectedTypes, $expectedCollection) {
        $set = new FileUploadSet("test", "test", $fileTypes, null, $collection);

        $this->assertEqual($set->allowed_file_types, $expectedTypes);
        $this->assertEqual($set->collection, $expectedCollection);
    }
}
