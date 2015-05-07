<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ImageUploads-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ImageUploadsTest extends GomaUnitTest
{

    /**
     * area
     */
    static $area = "Files";

    /**
     * internal name.
     */
    public $name = "ImageUploads";

    /**
     * tests generating images.
     */
    public function testImageResizer() {

        /* set size */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "setSize",
            array(500, 375),
            '<img src="Uploads/test/img.jpg/SetSize/500/375.jpg" height="375" width="500" data-retina="Uploads/test/img.jpg/SetSize/1000/750.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "setSize",
            array(500, 375, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/SetSize/500/375.jpg" height="375" width="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/SetSize/1000/750.jpg" alt="img.jpg" style=""  />');

        /* org set size */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "orgSetSize",
            array(500, 375, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/OrgSetSize/500/375.jpg" height="375" width="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/OrgSetSize/1000/750.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "orgSetSize",
            array(500, 375),
            '<img src="Uploads/test/img.jpg/OrgSetSize/500/375.jpg" height="375" width="500" data-retina="Uploads/test/img.jpg/OrgSetSize/1000/750.jpg" alt="img.jpg" style=""  />');

        /* no crop set size */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "noCropSetSize",
            array(500, 375, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/noCropSetSize/500/375.jpg" height="375" width="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/noCropSetSize/1000/750.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "noCropSetSize",
            array(500, 375),
            '<img src="Uploads/test/img.jpg/noCropSetSize/500/375.jpg" height="375" width="500" data-retina="Uploads/test/img.jpg/noCropSetSize/1000/750.jpg" alt="img.jpg" style=""  />');

        /* set width */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "setWidth",
            array(500, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/SetWidth/500.jpg" width="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/SetWidth/1000.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "setWidth",
            array(500),
            '<img src="Uploads/test/img.jpg/SetWidth/500.jpg" width="500" data-retina="Uploads/test/img.jpg/SetWidth/1000.jpg" alt="img.jpg" style=""  />');

        /* set height */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "setHeight",
            array(500, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/SetHeight/500.jpg" height="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/SetHeight/1000.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "setHeight",
            array(500),
            '<img src="Uploads/test/img.jpg/SetHeight/500.jpg" height="500" data-retina="Uploads/test/img.jpg/SetHeight/1000.jpg" alt="img.jpg" style=""  />');

        /* org set width */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "orgSetWidth",
            array(500, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/OrgSetWidth/500.jpg" width="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/OrgSetWidth/1000.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "orgSetWidth",
            array(500),
            '<img src="Uploads/test/img.jpg/OrgSetWidth/500.jpg" width="500" data-retina="Uploads/test/img.jpg/OrgSetWidth/1000.jpg" alt="img.jpg" style=""  />');

        /* org set height */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "OrgSetHeight",
            array(500, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/OrgSetHeight/500.jpg" height="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/OrgSetHeight/1000.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "OrgSetHeight",
            array(500),
            '<img src="Uploads/test/img.jpg/OrgSetHeight/500.jpg" height="500" data-retina="Uploads/test/img.jpg/OrgSetHeight/1000.jpg" alt="img.jpg" style=""  />');

        /* no crop set width */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "NoCropSetWidth",
            array(500, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/NoCropSetWidth/500.jpg" width="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/NoCropSetWidth/1000.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "NoCropSetWidth",
            array(500),
            '<img src="Uploads/test/img.jpg/NoCropSetWidth/500.jpg" width="500" data-retina="Uploads/test/img.jpg/NoCropSetWidth/1000.jpg" alt="img.jpg" style=""  />');

        /* no crop set height */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "NoCropSetHeight",
            array(500, true),
            '<img src="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/NoCropSetHeight/500.jpg" height="500" data-retina="'.BASE_URI . BASE_SCRIPT.'Uploads/test/img.jpg/NoCropSetHeight/1000.jpg" alt="img.jpg" style=""  />');

        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            "NoCropSetHeight",
            array(500),
            '<img src="Uploads/test/img.jpg/NoCropSetHeight/500.jpg" height="500" data-retina="Uploads/test/img.jpg/NoCropSetHeight/1000.jpg" alt="img.jpg" style=""  />');


    }

    /**
     * @param string $path
     * @param Size $size
     * @param string $action
     * @param array $args
     * @param string $expected
     */
    public function unitTestResizer($path, $size, $action, $args, $expected) {
        $imageUpload = new ImageUploads(array(
            "path"      => $path,
            "width"     => $size->getWidth(),
            "height"    => $size->getHeight(),
            "filename"  => basename($path),
            "type"      => "file"
        ));

        $this->assertEqual(strtolower(call_user_func_array(array($imageUpload, $action), $args)), strtolower($expected), " Expected " . convert::raw2text($expected) . " %s");
    }
}