<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Form.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class TableFieldTests extends GomaUnitTest
{
    /**
     * area
     */
    static $area = "TableField";

    /**
     * internal name.
     */
    public $name = "TableField";

    public function testAction() {

        $config = TableFieldConfig::create();
        $config->addComponent($c = new TableFieldTestHandleAction());

        $t = new TableField("userTable", lang("users"), array(), $config);

        $request = new Request("get", "testbtn", array(), array());

        $c->r1 = randomString(10);
        $this->assertEqual($t->handleRequest($request), $c->r1);

        $request = new Request("get", "test2", array(), array());

        $this->assertEqual($t->handleRequest($request), $c->r2);
    }

}

class TableFieldTestHandleAction implements TableField_URLHandler {
    public $r1 = "cool";
    public $r2 = 2;
    /**
     * provides url-handlers as in controller, but without any permissions-functionallity
     *
     * this is NOT namespaced, so please be unique
     *
     * @name getURLHandlers
     * @access public
     * @return array
     */
    public function getURLHandlers($tableField) {
        return array(
            'testbtn'   => "test",
            'test2'     => 'blah'
        );
    }


    /**
     * edit-action
     *
     * @name edit
     * @access public
     * @return string
     */
    public function test($tableField, $request)
    {
        if(is_a($tableField, "TableField") && is_a($request, "Request")) {
            return $this->r1;
        }

        throw new LogicException("TableField needs to give TableField and Request to URL-Handlers.");
    }

    public function blah() {
        return $this->r2;
    }
}