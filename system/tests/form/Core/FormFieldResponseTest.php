<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for FormFieldResponse-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class FormFieldResponseTests extends GomaUnitTest
{

    static $area = "Form";
    /**
     * name
     */
    public $name = "FormFieldResponse";

    /**
     * tests assignment.
     */
    public function testAssignment() {
        $this->unitTestAssignment("123", "blah", "abc", "abc-div", "title");
        $this->unitTestAssignment("", "blah", "abc", "div", "title");
        $this->unitTestAssignment(123, "blah", "abc", "abc-div", "title");
    }
    protected function unitTestAssignment($name, $type, $id, $divId, $title) {
        $info = FormFieldRenderData::create($name, $type, $id, $divId)
            ->setTitle($title);

        $this->assertEqual($info->getName(), $name);
        $this->assertEqual($info->getType(), $type);
        $this->assertEqual($info->getId(), $id);
        $this->assertEqual($info->getDivId(), $divId);
        $this->assertEqual($info->getTitle(), $title);
    }
}
