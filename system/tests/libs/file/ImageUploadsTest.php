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

        $this->unitTestResizeCase("SetSize", 500, 375);

        /* org set size */

        $this->unitTestResizeCase("OrgSetSize", 500, 375);

        /* no crop set size */
        $this->unitTestResizeCase("NoCropSetSize", 500, 375);

        /* set width */
        $this->unitTestResizeCase("SetWidth", 500, null);

        /* set height */
        $this->unitTestResizeCase("SetHeight", null, 375);

        /* org set width */
        $this->unitTestResizeCase("OrgSetWidth", 500, null);

        /* org set height */
        $this->unitTestResizeCase("OrgSetHeight", null, 375);

        /* no crop set width */
        $this->unitTestResizeCase("NoCropSetWidth", 500, null);

        /* no crop set height */
        $this->unitTestResizeCase("NoCropSetHeight", null, 375);

    }

    /**
     * unit test resizing for specific case.
     */
    protected function unitTestResizeCase($action, $width, $height) {

        $args = array();
        $html = "";
        $url = "";
        $urlRetina = "";
        if(isset($width)) {
            $args[] = $width;
            $html .= 'width="'.$width.'"';
            $url .= $width;
            $urlRetina .= ($width * 2);
            if(isset($height)) {
                $html = " " . $html;
                $url .= "/";
                $urlRetina .= "/";
            }
        }

        if(isset($height)) {
            $args[] = $height;
            $html = 'height="'.$height.'"' . $html;
            $url .= $height;
            $urlRetina .= ($height * 2);
        }

        $path = 'Uploads/test/img.jpg/'.$action.'/'.$url.'.jpg';
        $retinaPath = 'Uploads/test/img.jpg/'.$action.'/'.$urlRetina.'.jpg';

        /* set size */
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            $action,
            $args,
            '<img src="'.$path.'" '.$html.' data-retina="'.$retinaPath.'" alt="img.jpg" style=""  />');

        $args[] = true;
        $this->unitTestResizer(
            "test/img.jpg",
            new Size(1000, 750),
            $action,
            $args,
            '<img src="'.BASE_URI . BASE_SCRIPT. $path . '" '.$html.' data-retina="'.BASE_URI . BASE_SCRIPT.$retinaPath.'" alt="img.jpg" style=""  />');

        $this->assertTrue(file_exists(ImageUploadsController::calculatePermitFile($path)));
        $this->assertTrue(file_exists(ImageUploadsController::calculatePermitFile($retinaPath)));

        unlink(ImageUploadsController::calculatePermitFile($path));
        unlink(ImageUploadsController::calculatePermitFile($retinaPath));
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
