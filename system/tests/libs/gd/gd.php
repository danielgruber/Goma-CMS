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

    public function testExceptions() {
        $this->assertThrows(function() {
            new GD("./");
        }, "FileException");

        $this->assertThrows(function() {
            new GD("./index.php");
        }, "GDFileMalformedException");
    }

    /**
     * tests if gd resize resizes correctly.
     */
    public function testGDResize() {

        $f1 = "system/tests/resources/img_1000_480.png";
        $this->assertTrue(file_exists($f1));
        $this->unitGDResize($f1, new Size(1000, 480), new Size(500, 250), true, new Size(500, 250));
        $this->unitGDResize($f1, new Size(1000, 480), new Size(700, 240), true, new Size(700, 240));
        $this->unitGDResize($f1, new Size(1000, 480), new Size(240, 240), true, new Size(240, 240));
    }

    public function testGDResizeCalculation() {
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(1000, 700), new Size(1000, 700), new Tuple(new Position(157,0), new Size(686,480)), new Tuple(new Position(0,0), new Size(1000, 700)));
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(1000, 700), new Size(1000, 700), new Tuple(new Position(157,0), new Size(686,480)), new Tuple(new Position(0,0), new Size(1000, 700)));
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(480, 480), new Size(480, 480), new Tuple(new Position(260,0), new Size(480,480)), new Tuple(new Position(0,0), new Size(480, 480)));
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(250, 250), new Size(250, 250), new Tuple(new Position(260,0), new Size(480,480)), new Tuple(new Position(0,0), new Size(250, 250)));
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(480, 480), new Size(480, 480), new Tuple(new Position(0,260), new Size(480,480)), new Tuple(new Position(0,0), new Size(480, 480)));
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(240, 240), new Size(240, 240), new Tuple(new Position(0,260), new Size(480,480)), new Tuple(new Position(0,0), new Size(240, 240)));

        $this->unitGDResizeCalculation(new Size(1083, 723), new Size(492, 250), new Size(492, 250), new Tuple(new Position(0, 87), new Size(1083,550)), new Tuple(new Position(0,0), new Size(492, 250)));
        $this->unitGDResizeCalculation(new Size(1083, 723), new Size(1968, 1000), new Size(1968, 1000), new Tuple(new Position(0, 87), new Size(1083,550)), new Tuple(new Position(0,0), new Size(1968, 1000)));

        $this->unitGDResizeCalculation(new Size(1000, 1000), new Size(200, 240), new Size(200, 240), new Tuple(new Position(84, 0), new Size(833, 1000)), new Tuple(new Position(0,0), new Size(200, 240)));

        $this->unitGDResizeCalculation(new Size(1678, 790), new Size(1024, 455), new Size(1024, 455), new Tuple(new Position(0, 22), new Size(1678, 746)), new Tuple(new Position(0,0), new Size(1024, 455)));

        // position tests
        $this->unitGDResizeCalculation(new Size(1083, 723), new Size(492, 250), new Size(492, 250), new Tuple(new Position(0, 0), new Size(1083,550)), new Tuple(new Position(0,0), new Size(492, 250)), new Position(0, 0));
        $this->unitGDResizeCalculation(new Size(1083, 723), new Size(492, 250), new Size(492, 250), new Tuple(new Position(0, 173), new Size(1083,550)), new Tuple(new Position(0,0), new Size(492, 250)), new Position(100, 100));

        // complete crop test
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(240, 240), new Size(240, 240), new Tuple(new Position(36,192), new Size(360,360)), new Tuple(new Position(0,0), new Size(240, 240)), new Position(30, 30), new Size(75, 36));
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(240, 240), new Size(240, 240), new Tuple(new Position(36,192), new Size(360,360)), new Tuple(new Position(0,0), new Size(240, 240)), new Position(30, 30), new Size(75, 75));
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(240, 500), new Size(240, 500), new Tuple(new Position(36,75), new Size(360,750)), new Tuple(new Position(0,0), new Size(240, 500)), new Position(30, 30), new Size(75, 75));

    }

    public function unitGDResizeCalculation($sourceSize, $targetSize, $expectedSize, $expectedSourceArea, $expectedDestArea, $cropPosition = null, $cropSize = null) {
        $gd = new GD();

        $reflectionMethodImageSize = new ReflectionMethod('GD', 'getDestImageSize');
        $reflectionMethodImageSize->setAccessible(true);
        $size = $reflectionMethodImageSize->invoke($gd, $sourceSize->getWidth(), $sourceSize->getHeight(),
            $targetSize->getWidth(), $targetSize->getHeight());
        $this->assertEqual($size, $expectedSize,'Expected  Size Area: '.print_r($expectedSize, true).' %s');

        $reflectionMethodSourceArea = new ReflectionMethod('GD', 'getSrcImageArea');
        $reflectionMethodSourceArea->setAccessible(true);

        $source = $reflectionMethodSourceArea->invoke($gd, $sourceSize->getWidth(), $sourceSize->getHeight(), $size, $cropPosition, $cropSize);

        $this->assertEqual($source, $expectedSourceArea, 'Expected Source Area: '.print_r($expectedSourceArea, true).' Got: '.print_r($source, true).' %s');

        $reflectionMethodSourceArea = new ReflectionMethod('GD', 'getDestImageArea');
        $reflectionMethodSourceArea->setAccessible(true);

        $dest = $reflectionMethodSourceArea->invoke($gd, $source->getSecond(), $size);
        $this->assertEqual($dest, $expectedDestArea, 'Expected Dest-Area: '.print_r($expectedDestArea, true).' Got: '.print_r($dest, true).' %s');
    }

    /**
     * @param string $file
     * @param Size $targetSize
     * @param bool $crop
     * @param Size $expectedSize
     */
    public function unitGDResize($file, $expectedOrgSize, $targetSize, $crop, $expectedSize) {
        $gd = new GD($file);
        $this->assertEqual($gd->width, $expectedOrgSize->getWidth());
        $this->assertEqual($gd->height, $expectedOrgSize->getHeight());

        $newgd = $gd->resize($targetSize->getWidth(), $targetSize->getHeight(), $crop);

        $this->assertEqual($newgd->width, $expectedSize->getWidth());
        $this->assertEqual($newgd->height, $expectedSize->getHeight());

        $file = ROOT . CACHE_DIRECTORY . "/test.img.gd.test." . basename($newgd->getFilePath());
        $newgd->toFile($file);

        $info = GetImageSize($file);

        $this->assertEqual($info[0], $expectedSize->getWidth());
        $this->assertEqual($info[1], $expectedSize->getHeight());

        FileSystem::delete($file);
    }
}
