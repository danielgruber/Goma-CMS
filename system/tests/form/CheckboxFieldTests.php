<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for FileUploadSet.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class CheckBoxFieldTests extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "Checkbox";

    public function testDisabled() {
        $form = new Form($this, "checkbox", array(
            $checkbox = new CheckBox("name", "Name")
        ));

        $form->post["name"] = 0;

        $this->assertEqual($checkbox->result(), false);

        $form->post["name"] = 1;

        $this->assertEqual($checkbox->result(), true);

        $checkbox->value = 0;
        $form->disabled = true;
        $this->assertEqual($checkbox->result(), false);

        $form->disabled = false;

        $this->assertEqual($checkbox->result(), true);

        $checkbox->disabled = true;
        $this->assertEqual($checkbox->result(), false);
    }

}