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

    public function testModel() {
        $this->unittestWithModel(0);
        $this->unittestWithModel(1);
    }

    public function unittestWithModel($model) {
        $form = new Form(new Controller(), "checkbox", array(
            $checkbox = new CheckBox("name", "Name", $model)
        ));

        $form->getRequest()->post_params["name"] = 0;

        $this->assertEqual($checkbox->result(), false);

        $form->getRequest()->post_params["name"] = null;

        $this->assertEqual($checkbox->result(), false);

        $form->getRequest()->post_params["name"] = 1;

        $this->assertEqual($checkbox->result(), true);

        $checkbox->setModel(0);
        $form->disabled = true;
        $this->assertEqual($checkbox->result(), false);

        $form->disabled = false;

        $this->assertEqual($checkbox->result(), true);

        $checkbox->disabled = true;
        $this->assertEqual($checkbox->result(), false);
    }
}
