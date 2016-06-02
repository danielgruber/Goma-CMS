<?php
defined("IN_GOMA") OR die();
/**
 * Unit-Tests for HiddenField.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class HiddenFieldTest extends GomaUnitTest implements TestAble {
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "HiddenField";

    public function testResult() {
        $hidden = new HiddenField("test", "123");
        $this->assertEqual($hidden->result(), "123");
    }
}
