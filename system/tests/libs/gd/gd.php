<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for GD-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class GDTest extends GomaUnitTest
{

    /**
     * area
     */
    static $area = "GD";

    /**
     * internal name.
     */
    public $name = "GD";

    /**
     * tests check304
     */
    public function testCheck304() {
        $this->unitCheck304("123", time() - 1000, gmdate('D, d M Y H:i:s', (time() - 2000)).' GMT', '"123"', true);
        $this->unitCheck304("123", time() - 1000, gmdate('D, d M Y H:i:s', (time() - 1000)).' GMT', null, true);
        $this->unitCheck304("123", time() - 1000, null, '"123"', true);

        $this->unitCheck304("123", time() - 1000, gmdate('D, d M Y H:i:s', (time() - 500)).' GMT', null, false);
        $this->unitCheck304("123", time() - 1000, gmdate('D, d M Y H:i:s', (time() - 500)).' GMT', '"1234"', false);
        $this->unitCheck304("123", time() - 1000, null, '"1234"', false);

    }

    public function unitCheck304($etag, $mtime, $http_mod, $http_etag, $expected) {
        $gd = new GD();

        $reflectionMethod = new ReflectionMethod('GD', 'check304');
        $reflectionMethod->setAccessible(true);
        $this->assertEqual($reflectionMethod->invoke($gd, $etag, $mtime, $http_mod, $http_etag), $expected);
    }
}