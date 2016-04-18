<?php defined('IN_GOMA') OR die();

/**
 *
 * @package 	goma framework
 * @link 		http://goma-cms.org
 * @license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @Version 	1.5
 *
 * @property int width
 * @property int height
 * @property float thumbWidth
 * @property float thumbHeight
 * @property float thumbLeft
 * @property float thumbTop
 * @property ImageUploads|null sourceImage
 * @property int sourceImageId
 * @property bool realizedSize
 *
 * @method HasMany_DataObjectSet imageVersions($filter = null, $sort = null, $limit = null)
 *
 * last modified: 07.06.2015
 */
class ImageUploads extends Uploads {

    const ID = "ImageUploads";

    /**
     * add some db-fields
     * inherits fields from Uploads
     *
     *@name db
     *@access public
     */
    static $db = array(
        "width"				=> "int(5)",
        "height"			=> "int(5)",
        "thumbLeft"			=> "float(8, 5)",
        "thumbTop"			=> "float(8, 5)",
        "thumbWidth"		=> "float(8, 5)",
        "thumbHeight"		=> "float(8, 5)",
        "realizedSize"      => "int(1)"
    );

    /**
     * add index for aspect-query.
     *
     * @var array
     */
    static $index = array(
        "aspectQuery" => array(
            "name"      => "aspect",
            "fields"    => "thumbWidth,thumbHeight,width,height",
            "type"      => "INDEX"
        )
    );

    /**
     * extensions in this files are by default handled by this class
     *
     *@name file_extensions
     *@access public
     */
    static $file_extensions = array(
        "png",
        "jpeg",
        "jpg",
        "gif",
        "bmp"
    );

    /**
     * some defaults
     */
    static $default = array(
        "thumbLeft"		=> 50,
        "thumbTop"		=> 50,
        "thumbWidth"	=> 100,
        "thumbHeight"	=> 100
    );

    /**
     * @var array
     */
    static $has_many = array(
        "imageVersions" => array(
            DataObject::RELATION_TARGET => ImageUploads::ID,
            DataObject::RELATION_INVERSE => "sourceImage"
        )
    );

    /**
     * @var array
     */
    static $has_one = array(
        "sourceImage"   => ImageUploads::ID
    );

    /**
     * returns the raw-path
     *
     * @name raw
     * @access public
     * @return string
     */
    public function raw() {
        return $this->path;
    }

    /**
     * to string
     *
     * @name __toString
     * @access public
     * @return null|string
     */
    public function __toString() {
        if(preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $this->filename)) {
            $file = $this->raw().'/index'.substr($this->filename, strrpos($this->filename, "."));


            if(substr($file, 0, strlen("index.php/")) != "index.php/") {
                if(!file_exists($file)) {
                    FileSystem::requireDir(dirname($file));
                    FileSystem::write(ImageUploadsController::calculatePermitFile($file), 1);
                }
            } else {
                if(file_exists(substr($file, strlen("index.php/")))) {
                    $file = substr($file, strlen("index.php/"));
                } else {
                    FileSystem::requireDir(substr(dirname($file), strlen("index.php/")));
                    FileSystem::write(ImageUploadsController::calculatePermitFile(substr(dirname($file), strlen("index.php/"))), 1);
                }
            }

            return '<img src="'.$file.'" height="'.$this->height.'" width="'.$this->width.'" alt="'.$this->filename.'" />';
        } else
            return '<a href="'.$this->raw().'">' . $this->filename . '</a>';
    }

    /**
     * returns the path to the icon of the file
     *
     * @param int $size
     * @param bool $retina
     * @return string
     */
    public function getIcon($size = 128, $retina = false) {
        $ext = substr($this->filename, strrpos($this->filename, "."));
        if ($this->width() >= $size) {
            if ($retina && $this->width() >= $size * 2) {
                $icon = $this->path;
            } else {
                $icon = $this->path . "/setSize/" . $size . "/" . $size . $ext;
            }
        } else {
            switch ($size) {
                case 16:
                case 32:
                case 64:
                    if ($retina) {
                        return "images/icons/goma" . $size . "/image@2x.png";
                    }
                    return "images/icons/goma" . $size . "/image.png";
                    break;
            }
        }

        if (isset($icon)) {
            $this->manageURL($icon);
            return $icon;
        }

        return "images/icons/goma/128x128/image.png";
    }

    /**
     * authenticates a specific url and removes cache-files if necessary
     *
     * @name manageURL
     * @return string
     */
    public function manageURL($file) {
        $file = $this->removePrefix($file, "index.php/");
        $file = $this->removePrefix($file, "./index.php/");

        FileSystem::requireDir(dirname($file));
        FileSystem::write(ImageUploadsController::calculatePermitFile($file), 1);
        if(file_exists($file) && filemtime($file) < NOW - Uploads::$cache_life_time) {
            @unlink($file);
        }
        return $file;
    }

    /**
     * remove prefixes from a path.
     */
    protected function removePrefix($file, $prefix) {
        if(substr($file, 0, strlen($prefix)) == $prefix) {
            return substr($file, strlen($prefix));
        }

        return $file;
    }

    /**
     * returns url for specific scenario.
     *
     * @param int $desiredWidth -1 for no desired with
     * @param int $desiredHeight -1 for no desired height
     * @param bool $useThumb
     * @param bool $noCrop
     * @return string
     */
    public function getResizeUrl($desiredWidth, $desiredHeight, $useThumb = true, $noCrop = false) {
        if($useThumb === true && $noCrop === true) {
            throw new InvalidArgumentException("You can't use the thumbnail when not cropping.");
        }

        if(!$this->path) {
            return "";
        }

        // get action
        $action = ($noCrop === true) ? "NoCrop" : (($useThumb === false) ? "Org" : "");
        if($desiredWidth == -1 && $desiredHeight == -1) {
            throw new InvalidArgumentException("At least one of the size-parameters should be set.");
        } else if($desiredHeight == -1) {
            $action .= "SetWidth";
        } else if($desiredWidth == -1) {
            $action .= "SetHeight";
        } else {
            $action .= "SetSize";
        }

        // get appendix
        $file = $this->path . "/" . $action . "/";
        if($desiredWidth != -1) {
            $file .= $desiredWidth;
        }

        if($desiredHeight != -1) {
            if($desiredWidth != -1) {
                $file .= "/";
            }

            $file .= $desiredHeight;
        }

        // add extension
        $file .= substr($this->filename, strrpos($this->filename, "."));

        // enable it
        $this->manageURL($file);

        return $this->checkForBase($file);
    }

    /**
     * sets the height
     *
     * @param int $height
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $setHeight
     * @access public
     */
    public function setHeight($height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl(-1, $height, true, false);
        $fileRetina = $this->getResizeUrl(-1, $height * 2, true, false);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file . '" height="'.$height.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the width
     *
     * @param $width
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $setWidth
     * @access public
     */
    public function setWidth($width, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl($width, -1, true, false);
        $fileRetina = $this->getResizeUrl($width * 2, -1, true, false);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file . '" width="'.$width.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the Size
     *
     * @param $width
     * @param $height
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $setSize
     * @access public
     */
    public function setSize($width, $height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl($width, $height, true, false);
        $fileRetina = $this->getResizeUrl($width * 2, $height * 2, true, false);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the size on the original,  so not the thumbnail we saved
     *
     * @param $width
     * @param $height
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $orgSetSize
     * @access public
     */
    public function orgSetSize($width, $height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl($width, $height, false, false);
        $fileRetina = $this->getResizeUrl($width * 2, $height * 2, false, false);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the width on the original, so not the thumbnail we saved
     *
     * @param $width
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $orgSetWidth
     * @access public
     */
    public function orgSetWidth($width, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl($width, -1, false, false);
        $fileRetina = $this->getResizeUrl($width * 2, -1, false, false);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file . '" width="'.$width.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the height on the original, so not the thumbnail we saved
     *
     * @name orgSetHeight
     * @access public
     * @return string
     */
    public function orgSetHeight($height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl(-1, $height, false, false);
        $fileRetina = $this->getResizeUrl(-1, $height * 2, false, false);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file . '" height="'.$height.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the size on the original,  so not the thumbnail we saved
     *
     * @param $width
     * @param $height
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $noCropSetSize
     * @access public
     */
    public function noCropSetSize($width, $height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl($width, $height, false, true);
        $fileRetina = $this->getResizeUrl($width * 2, $height * 2, false, true);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the width on the original, so not the thumbnail we saved
     *
     * @param $width
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     * @internal param $noCropSetWidth
     * @access public
     */
    public function noCropSetWidth($width, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl($width, -1, false, true);
        $fileRetina = $this->getResizeUrl($width * 2, -1, false, true);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file . '" width="'.$width.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the height on the original, so not the thumbnail we saved
     *
     * @param $height
     * @param bool $absolute
     * @param string $html
     * @param string $style
     * @return string
     */
    public function noCropSetHeight($height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl(-1, $height, false, true);
        $fileRetina = $this->getResizeUrl(-1, $height * 2, false, true);

        if($absolute === true) {
            $file = BASE_URI . $file;
            $fileRetina = BASE_URI . $fileRetina;
        }

        return '<img src="' . $file . '" height="'.$height.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * helper for width() and height()
     *
     * @param String $size
     * @return int
     * @throws FileNotFoundException
     */
    protected function getSize($size) {
        if(preg_match('/^[0-9]+$/', $this->fieldGET($size)) && $this->fieldGET($size) != 0) {
            return $this->fieldGet($size);
        }

        if(!$this->realfile) {
            throw new FileNotFoundException("File for ImageUploads was not found.");
        }

        $image = new RootImage($this->realfile);
        $this->setField($size, $image->$size);
        $this->writeToDB(false, true);
        return $image->$size;
    }

    /**
     * adds an image-version by size in pixels.
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     * @param bool $write
     * @return ImageUploads
     */
    public function addImageVersionBySizeInPx($left, $top, $width, $height, $write = true) {
        $imageUploads = clone $this;
        $imageUploads->thumbHeight = min($height / $imageUploads->height * 100, 100);
        $imageUploads->thumbWidth = min($width / $imageUploads->width * 100, 100);

        $leftPercentage = ($this->width - $width) > 1 ? min($left / ($this->width - $width) * 100, 100) : 50;
        $imageUploads->thumbLeft = $leftPercentage;

        $topPercentage = ($this->height - $height) > 1 ? min($top / ($this->height - $height) * 100, 100) : 50;
        $imageUploads->thumbTop = $topPercentage;

        $imageUploads->sourceImage = $this;
        $imageUploads->path = $imageUploads->buildPath($imageUploads->collection, $imageUploads->filename);

        if($this->id != 0 && $write) {
            $imageUploads->writeToDB(true, true);
        }

        $this->viewcache = array();

        return $imageUploads;
    }

    /**
     * gets best version of file for given aspect-ratio.
     * aspect is width / height.
     *
     * @param float $aspect
     * @return ImageUploads
     */
    public function getBestVersionForAspect($aspect) {
        // if aspect is on a precision of 5% correct we use this.
        if(round($aspect * 20) / 20 == round($this->getAspect() * 20) / 20) {
            return $this;
        }

        // query for best fitting aspect.
        /** @var ImageUploads|null $aspectVersion */
        $aspectVersion = $this->imageVersions(
            null, " ABS((width * thumbWidth / (height * thumbHeight)) - $aspect) ASC ", 1
        )->first(false);

        // check if is really the better version.
        if(isset($aspectVersion) && abs($aspectVersion->getAspect() - $aspect) < ($this->getAspect() - $aspect)) {
            return $aspectVersion;
        }

        return $this;
    }

    /**
     * calculates aspect for current ImageUploads instance.
     */
    public function getAspect() {
        return ($this->width() * $this->thumbWidth) / ($this->height() * $this->thumbHeight);
    }

    /**
     * returns width
     * @return int
     * @throws Exception
     */
    public function width() {
        try {
            return $this->getSize("width") * $this->thumbWidth / 100;
        } catch(Exception $e) {
            if ($e instanceof FileException OR $e instanceof GDException) {
                return -1;
            } else {
                // Keep throwing it.
                throw $e;
            }
        }
    }

    /**
     * returns height
     * @return int
     * @throws Exception
     */
    public function height() {
        try {
            return $this->getSize("height") * $this->thumbHeight / 100;
        } catch(Exception $e) {
            if ($e instanceof FileException OR $e instanceof GDException) {
                return -1;
            } else {
                // Keep throwing it.
                throw $e;
            }
        }
    }
}
