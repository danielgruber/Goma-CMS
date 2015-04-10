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
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(1000, 700), new Size(1000, 480), new Tuple(new Position(0,0), new Size(1000,480)), new Tuple(new Position(0,0), new Size(1000, 480)));
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(700, 240), new Size(700, 240), new Tuple(new Position(0,0), new Size(1000,480)), new Tuple(new Position(100,0), new Size(500, 240)));
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(480, 480), new Size(480, 480), new Tuple(new Position(260,0), new Size(480,480)), new Tuple(new Position(0,0), new Size(480, 480)));
        $this->unitGDResizeCalculation(new Size(1000, 480), new Size(250, 250), new Size(250, 250), new Tuple(new Position(260,0), new Size(480,480)), new Tuple(new Position(0,0), new Size(250, 250)));
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(480, 480), new Size(480, 480), new Tuple(new Position(0,260), new Size(480,480)), new Tuple(new Position(0,0), new Size(480, 480)));
        $this->unitGDResizeCalculation(new Size(480, 1000), new Size(240, 240), new Size(240, 240), new Tuple(new Position(0,260), new Size(480,480)), new Tuple(new Position(0,0), new Size(240, 240)));

    }

    public function unitGDResizeCalculation($sourceSize, $targetSize, $expectedSize, $expectedSourceArea, $expectedDestArea) {
        $gd = new GD();

        $reflectionMethodImageSize = new ReflectionMethod('GD', 'getDestImageSize');
        $reflectionMethodImageSize->setAccessible(true);
        $size = $reflectionMethodImageSize->invoke($gd, $sourceSize->getWidth(), $sourceSize->getHeight(),
            $targetSize->getWidth(), $targetSize->getHeight());
        $this->assertEqual($size, $expectedSize,'Expected  Size Area: '.print_r($expectedSize, true).' %s');

        $reflectionMethodSourceArea = new ReflectionMethod('GD', 'getSrcImageArea');
        $reflectionMethodSourceArea->setAccessible(true);

        $source = $reflectionMethodSourceArea->invoke($gd, $sourceSize->getWidth(), $sourceSize->getHeight(), $size);

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

        $file = ROOT . CACHE_DIRECTORY . "/test.img.gd.test." . $newgd->extension;
        $newgd->toFile($file);

        $info = GetImageSize($file);

        $this->assertEqual($info[0], $expectedSize->getWidth());
        $this->assertEqual($info[1], $expectedSize->getHeight());

        FileSystem::delete($file);
    }
}