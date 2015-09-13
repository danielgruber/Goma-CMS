<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for GlobalSession-Class.
 *
 * @package		Goma\Session
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class GlobalSessionTests extends GomaUnitTest
{
    /**
     * area
     */
    static $area = "Session";

    /**
     * internal name.
     */
    public $name = "GlobalSessionManager";

    /**
     * tests host resolution.
     */
    public function testHostResolution() {
        $this->assertEqual(GlobalSessionManager::getCookieHost("www.google.de"), ".www.google.de");
        $this->assertEqual(GlobalSessionManager::getCookieHost("localhost"), "localhost");
        $this->assertEqual(GlobalSessionManager::getCookieHost("192.168.2.2"), "192.168.2.2");
        $this->assertEqual(GlobalSessionManager::getCookieHost("testdomain"), "testdomain");
        $this->assertEqual(GlobalSessionManager::getCookieHost("google.de"), ".google.de");
    }

    /**
     * tests getter + setter
     */
    public function testSettingSession() {
        $session = GlobalSessionManager::globalSession();
        $this->assertIsA($session, "ISessionManager");

        GlobalSessionManager::__setSession(null);
        $this->assertNull(GlobalSessionManager::globalSession());

        GlobalSessionManager::__setSession($session);

        $this->assertIdentical(GlobalSessionManager::globalSession(), $session);

        $this->assertIdentical(Core::globalSession(), GlobalSessionManager::globalSession());
    }
}
