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
 *
 * last modified: 07.06.2015
 */
class ImageUploads extends Uploads {
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
        "thumbLeft"			=> "int(3)",
        "thumbTop"			=> "int(3)",
        "thumbWidth"		=> "int(3)",
        "thumbHeight"		=> "int(3)"
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
                    FileSystem::write($file . ".permit", 1);
                }
            } else {
                if(file_exists(substr($file, strlen("index.php/")))) {
                    $file = substr($file, strlen("index.php/"));
                } else {
                    FileSystem::requireDir(substr(dirname($file), strlen("index.php/")));
                    FileSystem::write(substr(dirname($file), strlen("index.php/")) . ".permit", 1);
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
        switch($size) {
            case 16:
                if($this->width > 15) {
                    if($retina && $this->width > 31) {
                        $icon = $this->path . "/setWidth/32" . $ext;
                    } else {
                        $icon = $this->path . "/setWidth/16" . $ext;
                    }
                } else {
                    if($retina) {
                        return "images/icons/goma16/image@2x.png";
                    }
                    return "images/icons/goma16/image.png";
                }
                break;
            case 32:
                if($this->width > 31) {
                    if($retina && $this->width > 63) {
                        $icon = $this->path . "/setWidth/64" . $ext;
                    } else {
                        $icon = $this->path . "/setWidth/32" . $ext;
                    }
                } else {
                    if($retina) {
                        return "images/icons/goma32/image@2x.png";
                    }
                    return "images/icons/goma32/image.png";
                }
                break;
            case 64:
                if($this->width > 63) {
                    if($retina && $this->width > 127) {
                        $icon = $this->path . "/setWidth/128" . $ext;
                    } else {
                        $icon = $this->path . "/setWidth/64" . $ext;
                    }
                } else {
                    if($retina) {
                        return "images/icons/goma64/image@2x.png";
                    }
                    return "images/icons/goma64/image.png";
                }
                break;
            case 128:
                if($this->width > 127) {
                    if($retina && $this->width > 255) {
                        $icon = $this->path . "/setWidth/256" . $ext;
                    } else {
                        $icon = $this->path . "/setWidth/128" . $ext;
                    }
                } else {
                    return "images/icons/goma/128x128/image.png";
                }
                break;
        }

        if(isset($icon)) {
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
        FileSystem::write($file . ".permit", 1);
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

        return $file;
    }

    /**
     * makes a path absolute.
     *
     * @param string $file
     * @return string
     */
    public function makeAbsolute($file) {
        return BASE_URI . $this->checkForBase($file);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
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
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
        }

        return '<img src="' . $file . '" width="'.$width.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * sets the height on the original, so not the thumbnail we saved
     *
     * @name noCropSetHeight
     * @access public
     * @return string
     */
    public function noCropSetHeight($height, $absolute = false, $html = "", $style = "") {
        if(!$this->path)
            return "";

        $file = $this->getResizeUrl(-1, $height, false, true);
        $fileRetina = $this->getResizeUrl(-1, $height * 2, false, true);

        if($absolute === true) {
            $file = $this->makeAbsolute($file);
            $fileRetina = $this->makeAbsolute($fileRetina);
        }

        return '<img src="' . $file . '" height="'.$height.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
    }

    /**
     * helper for width() and height()
     *
     * @param String $size
     * @return int
     */
    protected function getSize($size) {
        if(preg_match('/^[0-9]+$/', $this->fieldGET($size)) && $this->fieldGET($size) != 0) {
            return $this->fieldGet($size);
        }

        $image = new RootImage($this->realfile);
        $this->setField($size, $image->$size);
        $this->write(false, true);
        return $image->$size;
    }

    /**
     * returns width
     *
     * @name width
     * @access public
     * @return int
     */
    public function width() {
        return $this->getSize("width");
    }

    /**
     * returns height
     *
     * @name height
     * @access public
     * @return int
     */
    public function height() {
        return $this->getSize("height");
    }

}