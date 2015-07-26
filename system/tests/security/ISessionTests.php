<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for each ISession-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ISessionTests {

    /**
     * class-name to test.
     */
    protected $sessionClass;

    /**
     * test-class.
     *
     * @var GomaUnitTest
     */
    protected $testClass;

    /**
     * contructor.
     *
     * @param string $sessionClass must be subclass of ISessionManager
     */
    public function __construct($sessionClass, $testClass) {
        $this->sessionClass = $sessionClass;
        $this->testClass = $testClass;
    }

    public function executeTests() {
        if($this->sessionClass) {
            $this->initTest();
        }
    }

    public function initTest() {
        $instance = call_user_func_array(array($this->sessionClass, "startWithIdAndName"), array(null));

        $this->testClass->assertNotNull($instance);
        $this->testClass->assertIsA($instance, "ISessionManager");
    }

}