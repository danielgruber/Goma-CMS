<?php defined("IN_GOMA") OR die();
/**
 * simple type to have a HTMLField, which outputs its data as HTML.
 * it also parses images and resizes them to just deliver the size needed.
 *
 * @package		Goma\SQL-Fields
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version     2.0
 */
class HTMLText extends Varchar {

    const MAX_RESIZE_WIDTH = 4000;
    const MAX_RESIZE_HEIGHT = 4000;

    /**
     * gets the SQL field-type
     *
     * @return string
     */
    static public function getFieldType($args = array()) {
        return "mediumtext";
    }

    /**
     * generates WYSIWYG-Editor.
     *
     * @param string $title
     * @return FormField
     */
    public function formfield($title = null)
    {
        return new HTMLEditor($this->name, $title, $this->value);
    }

    /**
     * returns width and height for given HTML. it parses the HTML.
     *
     * @param string $style contents of style-attribute
     * @return array
     */
    public static function matchSizes($style) {
        $data = array();
        if(preg_match('/(;|\s+|\")width\s*:\s*([0-9]+)(px)/i', $style, $sizes)) {
            $data["width"] = $sizes[2];
        } else if(preg_match('/(\s+|")width\="([0-9]+)"/i', $style, $sizes)) {
            $data["width"] = $sizes[2];
        }

        if(preg_match('/(;|\s+|\")height\s*:\s*([0-9]+)(px)/i', $style, $sizes)) {
            $data["height"] = $sizes[2];
        } else if(preg_match('/(\s+|")height\="([0-9]+)"/i', $style, $sizes)) {
            $data["height"] = $sizes[2];
        }

        return $data;
    }

    /**
     * parses images and converts them so only the size is delivered which is required.
     *
     * @name forTemplate
     * @return string
     */
    public function forTemplate() {
        // parse a bit
        $value = $this->value;

        preg_match_all('/\<img[^\>]+src\="([^"]+)"[^\>]*>/Usi', $value, $matches);

        foreach($matches[1] as $k => $machingSrcAttribute) {
            // match if may be upload
            if(preg_match('/^\.?\/?Uploads\/([a-zA-Z0-9_\-\.]+)\/([a-zA-Z0-9_\-\.]+)\/([a-zA-Z0-9_\-\.]+)\/?(index\.[a-zA-Z0-9_]+)?$/Ui', $machingSrcAttribute, $params)) {
                if($sizes = self::matchSizes($matches[0][$k])) {
                    // generate information for resizing and caching of it
                    $width = isset($sizes["width"]) ? $sizes["width"] : null;
                    $height = isset($sizes["height"]) ? $sizes["height"] : null;

                    $wString = $width ?: 0;
                    $hString = $height ?: 0;

                    $cache = new Cacher(md5("upload_" . $machingSrcAttribute . $wString . "_" . $hString));

                    if(false) {//$cache->checkValid()) {
                        $value = str_replace($machingSrcAttribute, $cache->getData(), $value);
                    } else {
                        /** @var ImageUploads $upload */
                        $upload = DataObject::get_one("ImageUploads", array("path" => $params[1] . "/" . $params[2] . "/" . $params[3]));
                        // generate new URLs for Resizing
                        if($upload && $replace = $this->generateResizeUrls($upload, $height, $width)) {
                            if(substr($replace, -1) == "/") {
                                $cache->write($replace, 60);
                            } else {
                                $cache->write($replace, 86400);
                            }
                            $value = str_replace($machingSrcAttribute, $replace, $value);
                        } else {
                            $cache->write($machingSrcAttribute, 86400);
                        }
                    }
                }
            }
        }

        return (string) $value;
    }

    /**
     * creates the two urls with resized images and returns new HTML for replacement.
     *
     * @param ImageUploads $uploadsObject
     * @param int $desiredHeight
     * @param int $desiredWidth
     * @return string
     */
    protected function generateResizeUrls($uploadsObject, $desiredHeight = null, $desiredWidth = null) {
        if($url = $this->generateResizeUrl($uploadsObject, $desiredHeight, $desiredWidth)) {
            $retinaUrlResized = $this->generateResizeUrl($uploadsObject, $desiredHeight * 2, $desiredWidth * 2);

            $filename = $uploadsObject->filename;
            $retinaUrl = $retinaUrlResized ?:
                $this->manageUrl("./" . $uploadsObject->path . "/" . substr($filename, 0, strrpos($filename, ".")), $uploadsObject);

            if($retinaUrl != $url) {
                return $url . '" data-retina="' . $retinaUrl;
            }

            return $url;
        }

        return $uploadsObject->getUrl();
    }

    /**
     * checks if it is good to create a resized image for given image and given dimensions.
     *
     * @param ImageUploads $uploadsObject
     * @param int $desiredHeight
     * @param int $desiredWidth
     * @return string
     */
    protected function generateResizeUrl($uploadsObject, $desiredHeight = null, $desiredWidth = null)
    {
        if($uploadsObject->width() > self::MAX_RESIZE_WIDTH || $uploadsObject->height() > self::MAX_RESIZE_HEIGHT ||
            $uploadsObject->width() <= $desiredWidth || $uploadsObject->height() <= $desiredHeight) {
            return null;
        }

        return $uploadsObject->getResizeUrl($desiredWidth, $desiredHeight, true);
    }

    /**
     * manages url.
     *
     * @param string $url
     * @param ImageUploads $uploadsObject
     * @return string
     */
    protected function manageUrl($url, $uploadsObject) {
        if($url) {
            if(substr($url, -1) == "/") {
                $url = substr($url, 0, -1);
            }

            $url .= substr($uploadsObject->filename, strrpos($uploadsObject->filename, "."));

            $uploadsObject->manageURL($url);
            return $url;
        }

        return null;
    }
}
