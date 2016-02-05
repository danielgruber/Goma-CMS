<?php defined('IN_GOMA') OR die();

/**
 * This class provides methods to resample images.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.1
 */
class GD extends gObject
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
     * @var Array
     */
    protected $type;

    /**
     * content-type of the image
     *
     * @var string
     */
    public $content_type;

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
     * file types supported.
     */
    public static $supported_types = array(
        2 => array(
            "type"          => 2,
            "extension"     => "jpg,jpeg",
            "create"        => "ImageCreateFromJPEG",
            "content_type"  => "image/jpeg",
            "output"        => "imagejpeg"
        ),
        3 => array(
            "type"          => 3,
            "extension"     => "png",
            "create"        => "ImageCreateFromPNG",
            "content_type"  => "image/png",
            "output"        => array("GD", "imagepng"),
            "generation"    => array("GD", "generatePNG")
        ),
        1 => array(
            "type"          => 1,
            "extension"     => "gif",
            "create"        => "ImageCreateFromGIF",
            "content_type"  => "image/gif",
            "output"        => "imagegif"
        ),
        6 => array(
            "type"          => 6,
            "extension"     => "bmp",
            "create"        => "ImageCreateFromBMP",
            "content_type"  => "image/jpeg",
            "output"        => "imagejpeg"
        ),
        array(
            "extension"     => "ico",
            "content_type"  => "image/x-icon",
            "output"        => array("gd", "toICO")
        ),
    );

    /**
     * @param null $image
     * @throws FileNotFoundException
     * @throws GDFileMalformedException
     * @throws GDFiletypeNotSupportedException
     */
    public function __construct($image = null)
    {
        parent::__construct();

        if($image === null) {
            return;
        }


        $this->pic = $image;
        if(!is_file($image)) {
            throw new FileNotFoundException("File for GD-Lib not found. " . $image);
        }

        $this->initWithImage($image);
    }

    /**
     * returns file-path.
     *
     * @return string $filePath
     */
    public function getFilePath() {
        return $this->pic;
    }

    /**
     * gets information about file-extension by filename.
     */

    /**
     * inits this object with given image.
     * it does not validate if image exists.
     *
     * @param string $image
     * @throws FileNotFoundException
     * @throws GDFileMalformedException
     * @throws GDFiletypeNotSupportedException
     */
    protected function initWithImage($image) {
        if($this->info = GetImageSize($image)) {
            $this->width = $this->info[0];
            $this->height = $this->info[1];
            $type = $this->info[2];

            if(isset(self::$supported_types[$type])) {
                $this->type = self::$supported_types[$type];
            } else {
                throw new GDFiletypeNotSupportedException("GD-Lib does not support type of image.");
            }
        } else {
            throw new GDFileMalformedException("Image $image seems to be malformed");
        }
    }

    /**
     * computed property for the image-resource
     *
     * @param resource $gd optional to set a new gd
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

        if(isset($this->type["create"])) {
            if(is_callable($this->type["create"])) {
                $this->gd = call_user_func_array($this->type["create"], array($this->pic));
                return $this->gd;
            }
        }

        return null;
    }

    /**
     * this function resizes an image to another size and let the relation height-width normal
     *
     * @name resize
     * @access public
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param Position $cropPosition position to start cropping
     * @param Size $cropSize size for cropping
     * @return bool|GD
     */
    public function resize($width, $height, $crop = true, $cropPosition = null, $cropSize = null)
    {
        if($this->type)
        {
            $old = $this->gd();
            $newgd = clone $this;

            if($crop) {
                $new = $this->resizeCropped($old, $width, $height, $cropPosition, $cropSize);
            } else {
                $new = $this->generateImage($width, $height, $this->type);
                // just resize
                imagecopyresampled($new, $old, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
            }

            $newgd->width = imagesx($new);
            $newgd->height = imagesy($new);

            $this->destroy();
            // now put resource
            $newgd->gd($new);

            return $newgd;
        } else
        {
            return null;
        }
    }

    /**
     * resizes cropped and returns new image
     *
     * @param $old
     * @param int $width
     * @param int $height
     * @param Position $cropPosition position to start cropping
     * @param Size $cropSize size for cropping
     * @return resource
     * @internal param resource $gd
     */
    protected function resizeCropped($old, $width, $height, $cropPosition = null, $cropSize = null) {
        $imgSize = $this->getDestImageSize($this->width, $this->height, $width, $height);

        $srcImageArea = $this->getSrcImageArea($this->width, $this->height, $imgSize, $cropPosition, $cropSize);

        $destImageArea = $this->getDestImageArea($srcImageArea->getSecond(), $imgSize);

        $new = $this->generateImage($imgSize->getWidth(), $imgSize->getHeight(), $this->type);

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

        return $new;
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
        return new Size($destWidth, $destHeight);
    }

    /**
     * returns source image "area" where we get the image-data from.
     * the first value of the tuple is the position and second is the size.
     *
     * @param int $srcWidth
     * @param int $srcHeight
     * @param Size $imageSize
     * @param Position|null $cropPosition
     * @param Size|null $cropSize
     * @return Tuple <Position,Size> information about the area where we get data from
     */
    protected function getSrcImageArea($srcWidth, $srcHeight, $imageSize, $cropPosition = null, $cropSize = null) {
        $size = new Size($srcWidth, $srcHeight);
        $position = new Position(0, 0);
        $cropLeft = isset($cropPosition) ? $cropPosition->getX() : 50;
        $cropTop = isset($cropPosition) ? $cropPosition->getY() : 50;

        if(isset($cropSize)) {
            $size = $size->updateWidth($srcWidth * $cropSize->getWidth() / 100);
            $position = $position->updateX(($srcWidth - $size->getWidth()) * $cropLeft / 100);

            $size = $size->updateHeight($srcHeight * $cropSize->getHeight() / 100);
            $position = $position->updateY(($srcHeight - $size->getHeight()) * $cropTop / 100);
        }

        $relation = ($size->getWidth() / $size->getHeight());
        $newRelation = $imageSize->getWidth() / $imageSize->getHeight();
        if($relation - $newRelation > 0) {
            $multiplier = $imageSize->getHeight() / $size->getHeight();
            $calculatedWidth = $size->getWidth() * $multiplier;

            if($calculatedWidth > $imageSize->getWidth()) {

                $cropWidth = isset($cropSize) ? $cropSize->getWidth() : 100;

                $positionSizePair = $this->calculateCropSize($srcWidth, $calculatedWidth, $imageSize->getWidth(), $cropLeft, $cropWidth);

                $position = $position->updateX($positionSizePair->getFirst());
                $size = $size->updateWidth($positionSizePair->getSecond());
            }

        } else {
            $multiplier = $imageSize->getWidth() / $size->getWidth();
            $calculatedHeight = $size->getHeight() * $multiplier;

            if($calculatedHeight > $imageSize->getHeight()) {

                $cropHeight = isset($cropSize) ? $cropSize->getHeight() : 100;

                $positionSizePair = $this->calculateCropSize($srcHeight, $calculatedHeight, $imageSize->getHeight(), $cropTop, $cropHeight);

                $position = $position->updateY($positionSizePair->getFirst());
                $size = $size->updateHeight($positionSizePair->getSecond());
            }

        }

        return new Tuple($position, $size);
    }

    /**
     * returns smaller size when cropping is needed.
     *
     * @param int $srcSize size that source has
     * @param int $calculatedSrcSize size that the source image would have if it was scaled by other dimension
     * @param int $imageSize size that destination image should have
     * @param int $cropPosition percentage where it will be cropped
     * @param int $cropSize percentage on how big the crop will be
     * @return Tuple<int, int>
     */
    protected function calculateCropSize($srcSize, $calculatedSrcSize, $imageSize, $cropPosition = 50, $cropSize = 100) {
        $position = 0;
        $size = $srcSize * $cropSize / 100;
        $multiplier = $calculatedSrcSize / $size;

        if($calculatedSrcSize > $imageSize) {
            $size = round($imageSize / $multiplier);

            $position = round(($srcSize - $size) * $cropPosition / 100);
        }

        return new Tuple($position, $size);
    }

    /**
     * returns destination image "area" where we put the image-data to.
     * the first value of the tuple is the position and second is the size.
     *
     * @param Size $srcArea
     * @param Size $imageSize
     * @return Tuple<Position,Size> information about the area where we put data to
     */
    protected function getDestImageArea($srcArea, $imageSize) {
        /** @var Size $size */
        $size = $imageSize->copy();
        $position = new Position(0, 0);

        $relation = $srcArea->getWidth() / $srcArea->getHeight();
        if($relation > 1) {
            $calculatedWidth = ceil($imageSize->getHeight() * $srcArea->getWidth() / $srcArea->getHeight());

            if($calculatedWidth < $imageSize->getWidth()) {
                $position = $position->updateX(($imageSize->getWidth() - $calculatedWidth) / 2);
                $size = $size->updateWidth($calculatedWidth);
            }
        } else {
            $calculatedHeight = ceil($imageSize->getHeight() * $srcArea->getHeight() / $srcArea->getWidth());
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
     * generates a new image and sets specific things for the current extensions
     *
     * @param int $width
     * @param int $height
     * @param array $type
     * @return resource
     */
    protected function generateImage($width, $height, $type) {
        if(function_exists("imagecreatetruecolor"))
        {
            $image = imagecreatetruecolor($width, $height);
        } else
        {
            $image = imagecreate($width, $height);
        }

        if(isset($type["generation"])) {
            $image = call_user_func_array($type["generation"], array($image));
        }

        return $image;
    }

    /**
     * generates png-image.
     * @param Resource $image
     * @return Resource
     */
    public static function generatePNG($image) {
        // Turn off transparency blending (temporarily)
        imagealphablending($image, false);

        // Create a new transparent color for image
        $color = imagecolorallocatealpha($image, 0, 0, 0, 127);

        // Completely fill the background of the new image with allocated color.
        imagefill($image, 0, 0, $color);

        // Restore transparency blending
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * rotates an image
     *
     * @param int $angle
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
     * @param string $file path
     * @param int $quality quality from 0 (worst) until 100 (best)
     * @param array $type of image
     * @param int $mode file-mode
     * @return string filepath
     */
    public function toFile($file, $quality = 70, $type = null, $mode = 0777)
    {
        $type = $this->getType($type);
        if(!isset($type["output"])) {
            return null;
        }

        if(!PermissionChecker::isValidPermission($mode)) {
            $mode = 0777;
        }

        if($this->gd || !$this->pic) {
            call_user_func_array($type["output"], array($this->gd(), $file, $quality));
        } else {
            copy($this->pic, $file);
        }

        $this->pic = $file;

        @chmod($file, $mode);

        $this->destroy();

        clearstatcache();
        return $file;
    }


    /**
     * explicit output for ico-files
     *
     * @param string $file destination-file
     * @param array $sizes icon sizes embedded
     * @return string $file
     */
    public function toIco($file, $sizes = array()) {
        $ico = new PHP_ICO();

        if(isset($this->pic) && !isset($this->gd)) {
            $ico->add_image($this->pic, $sizes);
        } else {
            $ico->add_image_resource($this->gd(), $sizes);
        }

        if(!$ico->save_ico($file)) {
            throw new LogicException("Could not convert Image to Icon.");
        }

        return $file;
    }

    /**
     * tries to send image to browser. it returns false if it failed, else it just terminates php-execution.
     *
     * @param int $quality
     * @param array $type override type
     */
    public function output($quality = 70, $type = null)
    {
        $type = $this->getType($type);
        if(isset($type["content_type"])) {
            $this->setHTTPHeaders($type["content_type"]);
        }

        HTTPResponse::sendHeader();

        if($this->pic != "" && file_exists($this->pic))
        {
            readfile($this->pic);
        } else if(isset($type["output"])) {
            call_user_func_array($type["output"], array($this->gd(), null, $quality));
        } else {
            throw new LogicException("Type ".print_r($type, true) . " can not be sent to browser.");
        }

        if(PROFILE) Profiler::End();

        exit;
    }

    /**
     * output for png.
     *
     * @param resource $gd
     * @param string $file
     * @param int $quality
     */
    public static function imagePNG($gd, $file, $quality) {
        if($quality > 9 && $quality < 100)
        {
            $quality = round(10 / $quality / 10);
        } else
        {
            $quality = 7;
        }

        // set transparency
        imagealphablending($gd, false);
        imagesavealpha($gd, true);

        // output
        imagepng($gd, $file, $quality);
    }

    /**
     * output image as icon.
     *
     * @name imageIco
     */
    public static function imageICO($gd, $file) {
        $ico = new PHP_ICO();
        $ico->add_image_resource($gd);
        $ico->save_ico($file);
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

        $modifiedHTTPHeader = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?: null;
        $noneMatchHTTPHeader = isset($_SERVER["HTTP_IF_NONE_MATCH"]) ?: null;
        if($this->check304($etag, $mtime, $modifiedHTTPHeader, $noneMatchHTTPHeader)) {
            HTTPResponse::setResHeader(304);
            HTTPResponse::sendHeader();

            if(PROFILE) Profiler::End();

            exit;
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
     * defines HTTP-Headers for Caching.
     */
    protected function setHTTPHeaders($contentType) {
        HTTPResponse::addHeader('Cache-Control','public, max-age=5511045');
        HTTPResponse::addHeader('content-type', $contentType);

        HTTPResponse::addHeader("pragma","Public");

        $filename = isset($this->filename) ? $this->filename : $this->pic;
        HTTPResponse::addHeader('content-disposition', "inline; filename='".basename($filename)."'");

        if(isset($this->pic) && $this->pic != "")
        {
            $mtime = filemtime($this->pic);
            $etag = strtolower(md5_file($this->pic));
            $this->checkAndSend304($etag, $mtime, $this->expires);

            HTTPResponse::addHeader("content-length", filesize($this->pic));
        }
    }

    /**
     * returns the type of this image by given type.
     *
     * @param array|int|string type
     * @return Array
     */
    protected function getType($type = null) {
        if(isset($type)) {
            if (isset($this->type[$type])) {
                return $this->type[$type];
            }

            foreach ($this->type as $typeDefinition) {
                if (isset($typeDefinition["extension"]) && strpos(strtolower($typeDefinition["extension"]), strtolower($type)) !== false) {
                    return $typeDefinition;
                }

                if (isset($typeDefinition["content_type"]) && strtolower($type) == strtolower($typeDefinition["content_type"])) {
                    return $typeDefinition;
                }
            }
        }

        return $this->type;
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
if (!function_exists('imagecreatefrombmp')) {
    function imagecreatefrombmp($filename) {
        // version 1.00

        if(!file_exists($filename)) {
            throw new FileNotFoundException("imagecreatefrombmp: Can not find" . $filename);
        }

        if (!($fh = fopen($filename, 'rb'))) {
            throw new FileNotPermittedException('imagecreatefrombmp: Can not open ' . $filename);
        }

        // read file header
        $meta = unpack('vtype/Vfilesize/Vreserved/Voffset', fread($fh, 14));
        // check for bitmap
        if ($meta['type'] != 19778) {
            throw new GDFileMalformedException('imagecreatefrombmp: ' . $filename . ' is not a bitmap!');
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
                    throw new GDFileMalformedException('imagecreatefrombmp: Can not obtain filesize of ' . $filename);
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

        $ex = new GDFileMalformedWithDataException($error);

        // loop through the image data beginning with the lower left corner
        while ($y >= 0) {
            $x = 0;
            while ($x < $meta['width']) {
                switch ($meta['bits']) {
                    case 32:
                    case 24:
                        if (!($part = substr($data, $p, 3))) {
                            $ex->data = $im;
                            throw $ex;
                        }
                        $color = unpack('V', $part . $vide);
                        break;
                    case 16:
                        if (!($part = substr($data, $p, 2))) {
                            $ex->data = $im;
                            throw $ex;
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
                        throw new GDFileMalformedException('imagecreatefrombmp: ' . $filename . ' has ' . $meta['bits'] . ' bits and this is not supported!');
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
    }
}

class GDException extends Exception {
    protected $standardCode = ExceptionManager::GD_EXCEPTION;

    public function __construct($message = "Unknown GD-Exception occurred.", $code = null, Exception $previous = null) {
        if(!isset($code)) {
            $code =  $this->standardCode;
        }

        parent::__construct($message, $code, $previous);
    }
}

class GDFileMalformedException extends GDException {
    protected $standardCode = ExceptionManager::GD_FILE_MALFORMED;
}
class GDFiletypeNotSupportedException extends GDException {
    protected $standardCode = ExceptionManager::GD_TYPE_NOT_SUPPORTED;
}
class GDFileMalformedWithDataException extends GDFileMalformedException {
    public $data;

    protected $standardCode = ExceptionManager::GD_FILE_MALFORMED;
}
