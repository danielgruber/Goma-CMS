<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for SessionManager-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class SessionManagerTests extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Session";

    /**
     * internal name.
     */
    public $name = "SessionManager";

    public function TestClass() {
        $inst = new ISessionTests("SessionManager", $this);

        $inst->executeTests();
    }
}