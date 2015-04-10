<?php defined('IN_GOMA') OR die();
/**
 * This class provides methods to resample images.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 */

class GD extends Object
{
    /***
     * default expires-time for images in browser-cache.
     */
    const DEFAULT_EXPIRES = 2592000; // 1 month

    /**
     * this var contains the picture
     *
     * @var string
     */
    protected $pic;
    /**
     * information about the image with GetImageSize.
     *
     * @var array
     */
    protected $information;
    /**
     * the current width of the picture
     *
     * @var int
     */
    public $width;
    /**
     * the current height of the picture
     *
     * @var int
     */
    public $height;
    /**
     * type of the image
     *
     * @var int
     */
    public $type;
    /**
     * content-type of the image
     *
     * @var string
     */
    public $content_type;
    /**
     * file-extension
     *
     * @var string
     */
    public $extension;

    /**
     * defines how long a file is valid in browser-cache.
     *
     * @var int
     */
    public $expires = self::DEFAULT_EXPIRES;

    /**
     * this is the current gd-object.
     *
     * @var resource
     */
    public $gd;

    /**
     * filename used to send to browser. if null, filename in storage is used or no filename is used.
     *
     * @var string
     */
    public $filename;

    /**
     * @param null $image
     */
    public function __construct($image = null)
    {
        parent::__construct();

        if($image === null) {
            return;
        }


        $this->pic = $image;
        if(!file_exists($image)) {
            throw new LogicException("File for GD-Lib not found. " . $image);
        }

        $this->initWithImage($image);
    }

    /**
     * inits this object with given image.
     * it does not validate if image exists.
     * @param string $image
     */
    protected function initWithImage($image) {
        $this->info = GetImageSize($image);
        $this->width = $this->info[0];
        $this->height = $this->info[1];
        $this->type = $this->info[2];

        if($this->type == 1)
        {
            $this->content_type = "images/gif";
            $this->extension = "gif";

        } else if($this->type == 2)
        {
            $this->content_type = "image/jpeg";
            $this->extension = "jpg";

        } else if($this->type == 3)
        {
            $this->content_type = "image/png";
            $this->extension = "png";

        } else if($this->type == 6)
        {
            $this->content_type = "image/bmp";
            $this->extension = "bmp";

        } else
        {
            $this->extension = null;
            $this->content_type = null;
        }
    }

    /**
     * holds the gd
     * @name gd
     * @access public
     * @param resource - opional - if new gd
     * @return resource
     */
    public function gd($gd = null)
    {
        if(isset($gd))
        {
            $this->pic = null;
            $this->gd = $gd;
        }
        if(isset($this->gd))
        {
            return $this->gd;
        }
        if($this->extension == "gif")
        {
            $this->gd = ImageCreateFromGIF($this->pic);
            return $this->gd;
        } else if($this->extension == "png")
        {
            $this->gd = ImageCreateFromPNG($this->pic);
            return $this->gd;
        } else if($this->extension == "jpg")
        {
            $this->gd = ImageCreateFromJPEG($this->pic);
            return $this->gd;
        } else if($this->extension == "bmp") {
            $this->gd = ImageCreateFromBMP($this->pic);
            return $this->gd;
        }
    }

    /**
     * this function resizes an image to another size and let the relation height-width normal
     * @name resize
     * @access public
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @return bool|GD
     */
    public function resize($width, $height, $crop = true)
    {
        if($this->extension)
        {
            $old = $this->gd();
            $newgd = clone $this;

            if($crop) {
                $tuple = $this->resizeCropped($old, $width, $height);
                $new = $tuple->getFirst();
                $newgd->width = $tuple->getSecond()->getWidth();
                $newgd->height = $tuple->getSecond()->getHeight();
            } else {
                $new = $this->generateImage($width, $height, $this->extension);
                $newgd->width = $width;
                $newgd->height = $height;
                // just resize
                imagecopyresampled($new, $old, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
            }

            $this->destroy();
            // now put resource
            $newgd->gd($new);

            return $newgd;
        } else
        {
            return false;
        }
    }

    /**
     * resizes cropped and returns new image
     *
     * @pram resource $gd
     * @param int $width
     * @param int $height
     * @return resource
     */
    protected function resizeCropped($old, $width, $height) {
        $imgSize = $this->getDestImageSize($this->width, $this->height, $width, $height);

        $srcImageArea = $this->getSrcImageArea($this->width, $this->height, $imgSize);

        $destImageArea = $this->getDestImageArea($srcImageArea->getSecond(), $imgSize);

        $new = $this->generateImage($imgSize->getWidth(), $imgSize->getHeight(), $this->extension);

        imagecopyresampled(
            $new,
            $old,

            $destImageArea->getFirst()->getX(),
            $destImageArea->getFirst()->getY(),
            $srcImageArea->getFirst()->getX(),
            $srcImageArea->getFirst()->getY(),

            $destImageArea->getSecond()->getWidth(),
            $destImageArea->getSecond()->getHeight(),
            $srcImageArea->getSecond()->getWidth(),
            $srcImageArea->getSecond()->getHeight());

        return new Tuple($new, $imgSize);
    }

    /**
     * returns destination image size.
     *
     * @param int $srcWidth
     * @param int $srcHeight
     * @param int $destWidth
     * @param int $destHeight
     * @return Size
     */
    protected function getDestImageSize($srcWidth, $srcHeight, $destWidth, $destHeight) {
        if($destHeight > $srcHeight) {
            $destHeight = $srcHeight;
        }

        if($destWidth > $srcWidth) {
            $destWidth = $srcWidth;
        }

        return new Size($destWidth, $destHeight);
    }

    /**
     * returns source image "area" where we get the image-data from.
     * the first value of the tuple is the position and second is the size.
     *
     * @param int $srcWidth
     * @param int $srcHeight
     * @param Size $imageSize
     * @return Tuple<Position,Size> information about the area where we get data from
     */
    protected function getSrcImageArea($srcWidth, $srcHeight, $imageSize) {
        $size = new Size($srcWidth, $srcHeight);
        $position = new Position(0, 0);

        $relation = $srcWidth / $srcHeight;
        if($relation > 1) {
            $multiplier = $imageSize->getHeight() / $srcHeight;
            $calculatedWidth = $srcWidth * $multiplier;

            if($calculatedWidth > $imageSize->getWidth()) {

                $getSrcWidth = round($srcWidth * $imageSize->getWidth() / $calculatedWidth);
                $position = $position->updateX(round(($srcWidth - $getSrcWidth) / 2));
                $size = $size->updateWidth($getSrcWidth);
            }

        } else {
            $multiplier = $imageSize->getWidth() / $srcWidth;
            $calculatedHeight = $srcHeight * $multiplier;

            if($calculatedHeight > $imageSize->getHeight()) {

                $getSrcHeight = round($srcHeight * $imageSize->getHeight() / $calculatedHeight);
                $position = $position->updateY(round(($srcHeight - $getSrcHeight) / 2));
                $size = $size->updateHeight($getSrcHeight);
            }

        }

        return new Tuple($position, $size);
    }

    /**
     * returns destination image "area" where we put the image-data to.
     * the first value of the tuple is the position and second is the size.
     *
     * @param Size $srcArea
     * @param Size $destSize
     * @param Size $imageSize
     * @return Tuple<Position,Size> information about the area where we put data to
     */
    protected function getDestImageArea($srcArea, $imageSize) {
        $size = $imageSize->copy();
        $position = new Position(0, 0);

        $relation = $srcArea->getWidth() / $srcArea->getHeight();
        if($relation > 1) {
            $calculatedWidth = $imageSize->getHeight() * $srcArea->getWidth() / $srcArea->getHeight();

            if($calculatedWidth < $imageSize->getWidth()) {
                $position = $position->updateX(($imageSize->getWidth() - $calculatedWidth) / 2);
                $size = $size->updateWidth($calculatedWidth);
            }
        } else {
            $calculatedHeight = $imageSize->getHeight() * $srcArea->getHeight() / $srcArea->getWidth();
            if($calculatedHeight < $imageSize->getHeight()) {
                $position = $position->updateY(($imageSize->getHeight() - $calculatedHeight) / 2);
                $size = $size->updateHeight($calculatedHeight);
            }
        }

        return new Tuple($position, $size);
    }

    /**
     * returns relation between width and height.
     *
     * @return double
     */
    protected function getRelation() {
        return $this->width / $this->height;
    }

    /**
     * resizes an image by its width
     *
     * @param  int $width
     * @param bool $crop allow cropping
     * @return bool|GD
     */
    public function resizeByWidth($width, $crop = true)
    {
        $new_height = round($width / $this->getRelation());
        return $this->resize($width, $new_height, $crop);
    }

    /**
     * resizes an image by its height
     *
     * @param int $height new height
     * @param bool allow cropping
     * @return bool|GD
     */
    public function resizeByHeight($height, $crop = true)
    {
        $new_width = round($height * $this->getRelation());
        return $this->resize($new_width, $height, $crop);
    }


    /**
     * we bring resizing to the next level
     *
     * V2 RESIZING
     */

    /**
     * sets the size with given thumbareas
     *
     * @name createThumb
     * @access public
     * @return bool|GD
     */
    public function createThumb($width = null, $height = null, $cornerLeft, $cornerTop, $thumbWidth, $thumbHeight, $forceSize = false) {


        if($cornerLeft + $thumbWidth > 100) {
            $thumbWidth = 100 - $cornerLeft;
        }

        if($cornerTop + $thumbHeight > 100) {
            $thumbHeight = 100 - $cornerTop;
        }


        // first define the src-points
        $cornerTop = round($this->height * $cornerTop / 100);
        $cornerLeft = round($this->width * $cornerLeft / 100);
        $resampledWidth = $resampledWidthSrc = round($this->width * $thumbWidth / 100);
        $resampledHeight = $resampledHeightSrc = round($this->height * $thumbHeight / 100);

        // get the apect ratio
        $aspectRatio = $resampledWidth / $resampledHeight;

        // if both are set
        if(isset($width, $height)) {
            $aspectRatioNew = $width / $height;
            // if this is true, we cut some pixels from top and bottom
            if($aspectRatioNew > $aspectRatio) {
                $resampledWidth = $width;
                $resampledHeight = round($resampledWidth / $aspectRatio);
                $cornerTop = $cornerTop + ($resampledHeightSrc - $height * ($resampledHeightSrc / $resampledHeight)) / 2;
                $resampledHeightSrc = $height * ($resampledHeightSrc / $resampledHeight);
                $resampledHeight = $height;

                // else we cut some pixels from left and right
            } else {
                $resampledHeight = $height;

                $resampledWidth = round($aspectRatio * $resampledHeight);
                $cornerLeft = $cornerLeft + ($resampledWidthSrc - $width * ($resampledWidthSrc / $resampledWidth)) / 2;
                $resampledWidthSrc = ($width / $resampledWidth) * $resampledWidthSrc;
                $resampledWidth = $width;

            }
            // we've got the width, so just calculate height
        } else if(isset($width)) {
            if($width <= $resampledWidth || $forceSize)
                $multiplier = $width / $resampledWidth;
            else {
                $multiplier = 1;
            }

            $resampledWidth = $width;
            $resampledHeight = round($resampledWidth / $aspectRatio);
            // we've got the width, so calculate the height
        } else if(isset($height)) {
            $resampledHeight = $height;
            $resampledWidth = round($resampledHeight * $aspectRatio);
        } else {
            return false;
        }

        // now we resize
        $new = $this->generateImage($resampledWidth, $resampledHeight, $this->extension);

        imagecopyresampled($new, $this->gd(), 0, 0, $cornerLeft, $cornerTop, $resampledWidth, $resampledHeight, $resampledWidthSrc, $resampledHeightSrc);
        if(isset($this->pic)) {
            $this->destroy();
        }

        // now get new gd
        $newgd = clone $this;
        $newgd->gd($new);

        return $newgd;
    }

    /**
     * generates a new image and sets specific things for the current extensions
     *
     * @name generateImage
     * @access public
     * @param int - width
     * @param int - height
     * @param string - extensions
     * @return resource
     */
    public static function generateImage($width, $height, $extension) {
        if(function_exists("imagecreatetruecolor"))
        {
            $image = imagecreatetruecolor($width, $height);
        } else
        {
            $image = imagecreate($width, $height);
        }

        if($extension == "png") {
            // Turn off transparency blending (temporarily)
            imagealphablending($image, false);

            // Create a new transparent color for image
            $color = imagecolorallocatealpha($image, 0, 0, 0, 127);

            // Completely fill the background of the new image with allocated color.
            imagefill($image, 0, 0, $color);

            // Restore transparency blending
            imagesavealpha($image, true);
        }

        return $image;
    }

    /**
     * rotates an image
     *
     * @name rotate
     * @access public
     * @param numeric - angle
     * @return GD
     */
    public function rotate($angle)
    {
        $new = imagerotate($this->gd(), $angle, 0);
        if(isset($this->pic)) {
            $this->destroy();
        }
        // now get new gd
        $newgd = clone $this;
        $newgd->gd($new);

        return $newgd;
    }
    /**
     * saves the image tp a file.
     *
     * @param string $file
     * @param numeric $quality
     * @param string $extension mode to use, for example as jpeg
     * @param int $mode file-mode
     * @return string filepath
     */
    public function toFile($file, $quality = 70, $extension = null, $mode = 0777)
    {
        $supported = array("gif", "ico", "jpg", "jpeg", "png", "bmp");

        if(!isset($extension) || !in_array(strtolower($extension), $supported)) {
            $extension = $this->extension;
        }

        if(!isset($extension)) {
            return false;
        }

        if(!PermissionChecker::isValidPermission($mode)) {
            $mode = 0777;
        }

        $this->exportToFile($extension, $file, $quality);
        $this->pic = $file;

        @chmod($file, $mode);

        imagedestroy($this->gd);
        unset($this->gd);

        clearstatcache();
        return $file;
    }

    /**
     * exports gd to a file.
     *
     * @param string filename
     * @param int quality
     */
    protected function exportToFile($extension, $file, $quality) {
        if($extension == "gif")
        {
            imagegif($this->gd(), $file, $quality);
        } else if($extension == "jpg" || $extension == "jpeg")
        {
            imageJPEG($this->gd(), $file, $quality);
        } else if($extension == "png")
        {
            imagealphablending($this->gd(), false);
            imagesavealpha($this->gd(), true);
            imagepng($this->gd(), $file, 9);
        } else if($extension == "bmp") {
            ImageJPEG($this->gd(), $file, 100);
        } else if($extension == "ico") {
            $this->toFile(ROOT . CACHE_DIRECTORY . "temp." . $this->extension);

            $ico = new PHP_ICO(ROOT . CACHE_DIRECTORY . "temp." . $this->extension, array($this->width, $this->height));
            $ico->save_ico($file);

            FileSystem::delete(ROOT . CACHE_DIRECTORY . "temp." . $this->extension);
        }
    }

    /**
     * tries to send image to browser. it returns false if it failed, else it just terminates php-execution.
     *
     * @param int $quality
     * @return bool
     */
    public function output($quality = 70)
    {

        $this->setHTTPHeaders($this->getContentTypeForOutput($this->extension, $this->pic, $this->content_type));

        HTTPResponse::sendHeader();

        if(in_array($this->extension, array("png", "jpg", "gif", "bmp")))
        {

            if($this->pic != "" && file_exists($this->pic))
            {
                readfile($this->pic);
            } else
            {
                $this->outputGd($quality);
            }
            if(PROFILE) Profiler::End();

            exit;
        }
        return false;
    }

    /**
     * output file based on gd-function.
     */
    public function outputGd($quality) {
        switch($this->extension) {
            case "gif":
                imagegif($this->gd(),null, $quality);
                break;
            case "jpeg":
            case "jpg":
            case "bmp":
                imagejpeg($this->gd(),null, $quality);
                break;
            case "png":
                if($quality > 9 && $quality < 100)
                {
                    $quality = $quality / 10;
                } else
                {
                    $quality = 7;
                }

                // set transparency
                imagealphablending($this->gd(), false);
                imagesavealpha($this->gd(), true);

                // output
                imagepng($this->gd(),null, $quality);
                break;
        }
    }

    /**
     * checks if 304 should be sent.
     *
     * @param string $etag
     * @param int $mtime
     * @param string $HTTP_IF_MODIFIED_SINCE
     * @param string $HTTP_IF_NONE_MATCH
     * @return bool
     */
    protected function check304($etag, $mtime, $HTTP_IF_MODIFIED_SINCE, $HTTP_IF_NONE_MATCH) {

        if(isset($HTTP_IF_NONE_MATCH))
        {
            if($HTTP_IF_NONE_MATCH == '"' . $etag . '"')
            {
                return true;
            } else {
                return false;
            }
        }

        if(isset($HTTP_IF_MODIFIED_SINCE)) {
            if(strtolower(gmdate('D, d M Y H:i:s', $mtime).' GMT') == strtolower($HTTP_IF_MODIFIED_SINCE))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * returns content-type for output of given file extension/name.
     *
     * @param string extension
     * @param string filepath
     * @return string default mime type for this object
     */
    protected function getContentTypeForOutput($ext, $filename, $defaultType) {
        if($this->extension == "bmp") {
            return "image/jpeg";
        } else {
            return $defaultType;
        }
    }

    /**
     * checks if browser has picture cause of HTTP_IF_MODIFIED_SINCE or HTTP_IF_NONE_MATCH.
     * it terminates execution when browser has picture.
     * it also adds Cacheable-Headers and e-tag to headers.
     *
     * @param string $etag e-tag which should match HTTP_IF_NONE_MATCH
     * @param int $mtime last modified
     * @param int $expires how long is the period the file is valid?
     */
    public function checkAndSend304($etag, $mtime, $expires) {
        HTTPResponse::setCachable(time() + $expires, $mtime, true);
        HTTPResponse::addHeader("Etag", '"'.$etag.'"');

        if($this->check304($etag, $mtime, $_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER["HTTP_IF_NONE_MATCH"])) {
            $this->send304();
        }
    }

    /**
     * sends 304 and terminates execution.
     */
    public function send304() {
        HTTPResponse::setResHeader(304);
        HTTPResponse::sendHeader();

        if(PROFILE) Profiler::End();

        exit;
    }

    /**
     * defines HTTP-Headers for Caching.
     */
    public function setHTTPHeaders($contentType) {
        HTTPResponse::addHeader('Cache-Control','public, max-age=5511045');
        HTTPResponse::addHeader('content-type', $contentType);

        HTTPResponse::addHeader("pragma","Public");

        $this->sendFilename(isset($this->filename) ? $this->filename : $this->pic);

        if(isset($this->pic) && $this->pic != "")
        {
            $mtime = filemtime($this->pic);
            $etag = strtolower(md5_file($this->pic));
            $this->checkAndSend304($etag, $mtime, $this->expires);

            HTTPResponse::addHeader("content-length", filesize($this->pic));
        }
    }

    /**
     * sents filename to browser.
     */
    public function sendFilename($filename) {
        if($filename) {
            HTTPResponse::addHeader('content-disposition', "inline; filename='".basename($filename)."'");
        }

    }

    /**
     * explicit output for ico-files
     *
     *@name toIco
     *@access public
     */
    public function toIco($file, $sizes = array()) {
        $this->toFile(ROOT . CACHE_DIRECTORY . "temp." . $this->extension);
        $ico = new PHP_ICO(ROOT . CACHE_DIRECTORY . "temp." . $this->extension, $sizes);
        $ico->save_ico($file);
        return $file;
    }

    /**
     * destroys image to restore ram.
     */
    public function destroy() {
        if(isset($this->gd)) {
            imagedestroy($this->gd);
            $this->gd = null;
        }
    }

}

/**
 * bmps
 *
 *@thanks to http://www.programmierer-forum.de/function-imagecreatefrombmp-laeuft-mit-allen-bitraten-t143137.htm
 */
if (!function_exists('imagecreatefrombmp')) { function imagecreatefrombmp($filename) {
    // version 1.00
    if (!($fh = fopen($filename, 'rb'))) {
        trigger_error('imagecreatefrombmp: Can not open ' . $filename, E_USER_WARNING);
        return false;
    }
    // read file header
    $meta = unpack('vtype/Vfilesize/Vreserved/Voffset', fread($fh, 14));
    // check for bitmap
    if ($meta['type'] != 19778) {
        trigger_error('imagecreatefrombmp: ' . $filename . ' is not a bitmap!', E_USER_WARNING);
        return false;
    }
    // read image header
    $meta += unpack('Vheadersize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vcolors/Vimportant', fread($fh, 40));
    // read additional 16bit header
    if ($meta['bits'] == 16) {
        $meta += unpack('VrMask/VgMask/VbMask', fread($fh, 12));
    }
    // set bytes and padding
    $meta['bytes'] = $meta['bits'] / 8;
    $meta['decal'] = 4 - (4 * (($meta['width'] * $meta['bytes'] / 4)- floor($meta['width'] * $meta['bytes'] / 4)));
    if ($meta['decal'] == 4) {
        $meta['decal'] = 0;
    }
    // obtain imagesize
    if ($meta['imagesize'] < 1) {
        $meta['imagesize'] = $meta['filesize'] - $meta['offset'];
        // in rare cases filesize is equal to offset so we need to read physical size
        if ($meta['imagesize'] < 1) {
            $meta['imagesize'] = @filesize($filename) - $meta['offset'];
            if ($meta['imagesize'] < 1) {
                trigger_error('imagecreatefrombmp: Can not obtain filesize of ' . $filename . '!', E_USER_WARNING);
                return false;
            }
        }
    }
    // calculate colors
    $meta['colors'] = !$meta['colors'] ? pow(2, $meta['bits']) : $meta['colors'];
    // read color palette
    $palette = array();
    if ($meta['bits'] < 16) {
        $palette = unpack('l' . $meta['colors'], fread($fh, $meta['colors'] * 4));
        // in rare cases the color value is signed
        if ($palette[1] < 0) {
            foreach ($palette as $i => $color) {
                $palette[$i] = $color + 16777216;
            }
        }
    }
    // create gd image
    $im = imagecreatetruecolor($meta['width'], $meta['height']);
    $data = fread($fh, $meta['imagesize']);
    $p = 0;
    $vide = chr(0);
    $y = $meta['height'] - 1;
    $error = 'imagecreatefrombmp: ' . $filename . ' has not enough data!';
    // loop through the image data beginning with the lower left corner
    while ($y >= 0) {
        $x = 0;
        while ($x < $meta['width']) {
            switch ($meta['bits']) {
                case 32:
                case 24:
                    if (!($part = substr($data, $p, 3))) {
                        trigger_error($error, E_USER_WARNING);
                        return $im;
                    }
                    $color = unpack('V', $part . $vide);
                    break;
                case 16:
                    if (!($part = substr($data, $p, 2))) {
                        trigger_error($error, E_USER_WARNING);
                        return $im;
                    }
                    $color = unpack('v', $part);
                    $color[1] = (($color[1] & 0xf800) >> 8) * 65536 + (($color[1] & 0x07e0) >> 3) * 256 + (($color[1] & 0x001f) << 3);
                    break;
                case 8:
                    $color = unpack('n', $vide . substr($data, $p, 1));
                    $color[1] = $palette[ $color[1] + 1 ];
                    break;
                case 4:
                    $color = unpack('n', $vide . substr($data, floor($p), 1));
                    $color[1] = ($p * 2) % 2 == 0 ? $color[1] >> 4 : $color[1] & 0x0F;
                    $color[1] = $palette[ $color[1] + 1 ];
                    break;
                case 1:
                    $color = unpack('n', $vide . substr($data, floor($p), 1));
                    switch (($p * 8) % 8) {
                        case 0:
                            $color[1] = $color[1] >> 7;
                            break;
                        case 1:
                            $color[1] = ($color[1] & 0x40) >> 6;
                            break;
                        case 2:
                            $color[1] = ($color[1] & 0x20) >> 5;
                            break;
                        case 3:
                            $color[1] = ($color[1] & 0x10) >> 4;
                            break;
                        case 4:
                            $color[1] = ($color[1] & 0x8) >> 3;
                            break;
                        case 5:
                            $color[1] = ($color[1] & 0x4) >> 2;
                            break;
                        case 6:
                            $color[1] = ($color[1] & 0x2) >> 1;
                            break;
                        case 7:
                            $color[1] = ($color[1] & 0x1);
                            break;
                    }
                    $color[1] = $palette[ $color[1] + 1 ];
                    break;
                default:
                    trigger_error('imagecreatefrombmp: ' . $filename . ' has ' . $meta['bits'] . ' bits and this is not supported!', E_USER_WARNING);
                    return false;
            }
            imagesetpixel($im, $x, $y, $color[1]);
            $x++;
            $p += $meta['bytes'];
        }
        $y--;
        $p += $meta['decal'];
    }
    fclose($fh);
    return $im;
}}