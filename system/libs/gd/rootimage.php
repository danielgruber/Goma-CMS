<?php defined('IN_GOMA') OR die();
/**
 * This class manages image uploaded.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.1
 */

class ROOTImage extends GD
{
    /**
     * this is an md5-hash of the picture
     * @var string
     */
    public $md5;

    /**
     * the original pic
     * @var string
     */
    public $org_pic;

    /**
     * constructs the class
     * @name __construct
     * @param string $imagePath filename relative to the uploaded-directory
     * @access public
     */
    public function __construct($imagePath)
    {
        $this->org_pic = $imagePath;
        if (substr($imagePath, 0, 7) == "Uploads") {
            if ($data = DataObject::Get_one("Uploads", array("path" => $imagePath))) {
                $this->md5 = md5_file($data->realfile);
                parent::__construct($data->realfile);
            } else {
                return false;
            }
        } else
            if (file_exists(ROOT . $imagePath)) {
                $this->md5 = md5_file(ROOT . $imagePath);
                parent::__construct(ROOT . $imagePath);
            } else {
                parent::__construct();
            }

    }

    /**
     * this function resizes an image to another size and let the relation height-width normal
     * this function caches the result, too
     *
     * @param int $width new width
     * @param int $height height
     * @param bool $crop
     * @param Position $cropPosition
     * @param Size $cropSize
     * @return bool|GD
     */
    public function resize($width, $height, $crop = true, $cropPosition = null, $cropSize = null)
    {
        $file = ROOT . CACHE_DIRECTORY . '/image_cache.tn_w_' . $width . '_h_' . $height . '_' . $crop . '_'.md5(print_r(array($cropPosition, $cropSize), true)).'_' . $this->md5 . '.img';
        if (file_exists($file)) {
            return new GD($file);
        }
        $gd = parent::resize($width, $height, $crop, $cropPosition, $cropSize);
        $gd->toFile($file);
        return $gd;
    }

    /**
     * rotates an image
     * this function implements caching
     *
     * @param int $angle
     * @return GD
     */
    public function rotate($angle)
    {
        $file = ROOT . CACHE_DIRECTORY . '/image_cache.rotate_' . $angle . '_' . $this->md5 . '.img';
        if (file_exists($file)) {
            return new GD($file);
        }
        $gd = parent::rotate($angle);
        $gd->toFile($file);
        return $gd;
    }

    /**
     * generates a url
     *
     * @param bool|int $width optional
     * @param bool|int $height optional
     * @return string
     */
    public function generate_url($width = false, $height = false)
    {
        if ($width === false) {
            $relation = $this->width / $this->height;
            $height = $height;
            $width = round($height * $relation);

        } else if ($height === false) {
            $relation = $this->height / $this->width;
            $width = $width;
            $height = round($width * $relation);
        }

        $file = CACHE_DIRECTORY . '/image_cache.tn_w_' . $width . '_h_' . $height . '_' . $this->md5 . '.img';
        if (file_exists(ROOT . $file)) {
            return ROOT_PATH . $file;
        } else {
            return BASE_SCRIPT . "images/resampled/" . round($width) . "/" . round($height) . "/" . $this->org_pic;
        }
    }
}

class Image extends RootImage
{

}