<?php defined("IN_GOMA") OR die();
/**
 * simple type to have a HTMLField, which outputs its data as HTML.
 * it also parses images and resizes them to just deliver the size needed.
 *
 * @package		Goma\SQL-Fields
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @versio      2.0
 */
class HTMLText extends Varchar {
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
     * returns width and height for given style property. it parses the HTML.
     *
     * @param string $style contents of style-attribute
     * @return array
     */
    public function matchSizes($style) {
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

        foreach($matches[1] as $k => $m) {

            // match if may be upload
            if(preg_match('/^\.?\/?Uploads\/([a-zA-Z0-9_\-\.]+)\/([a-zA-Z0-9_\-\.]+)\/([a-zA-Z0-9_\-\.]+)\/?(index\.[a-zA-Z0-9_]+)?$/Ui', $m, $params)) {

                if($sizes = $this->matchSizes($matches[0][$k])) {

                    if(isset($sizes["width"])) {
                        $width = $sizes["width"];
                    }

                    if(isset($sizes["height"])) {
                        $height = $sizes["height"];
                    }

                    $w = isset($width) ? $width : 0;
                    $h = isset($height) ? $height : 0;

                    $cache = new Cacher(md5("upload_" . $m . $w . "_" . $h));

                    if($cache->checkValid()) {
                        $value = str_replace($m, $cache->getData(), $value);
                    } else {

                        $data = DataObject::Get("Uploads", array("path" => $params[1] . "/" . $params[2] . "/" . $params[3]));

                        if($data->count() == 0) {
                            continue;
                        }

                        if(isset($width, $height) && $data->width && $data->height) {

                            if($data->width > $width && $data->width < 4000 && $data->height > $height && $data->height < 4000) {

                                $url = "./" . $data->path . '/noCropSetSize/'.$width.'/'.$height . substr($data->filename, strrpos($data->filename, "."));
                                // retina
                                if($width * 2 < $data->width && $height * 2 < $data->height) {
                                    $retinaURL = "./" . $data->path . '/noCropSetSize/'.($width * 2).'/'.($height * 2) . substr($data->filename, strrpos($data->filename, "."));
                                } else {
                                    $retinaURL = "./" . $data->path;
                                }

                                $data->manageURL($url);
                                $data->manageURL($retinaURL);

                                $replace = $url . '" data-retina="' . $retinaURL;
                                $cache->write($replace, 86400);

                                $value = str_replace($m, $replace, $value);
                            } else {
                                $cache->write($m, 86400);
                            }
                        } else if(isset($width)) {
                            if($data->width > $width && $data->width < 4000) {
                                $url =  "./" . $data->path . '/noCropSetWidth/' . $width . substr($data->filename, strrpos($data->filename, "."));
                                // retina
                                if($width * 2 < $data->width) {
                                    $retinaURL =  "./" . $data->path . '/noCropSetWidth/' . ($width * 2) . substr($data->filename, strrpos($data->filename, "."));
                                } else {
                                    $retinaURL = "./" . $data->path;
                                }

                                $data->manageURL($url);
                                $data->manageURL($retinaURL);

                                $replace = $url . '" data-retina="' . $retinaURL;
                                $cache->write($replace, 86400);

                                $value = str_replace($m, $replace, $value);
                            } else {
                                $cache->write($m, 86400);
                            }
                        } else {
                            if($data->height > $height && $data->height < 4000) {
                                $url = "./" . $data->path . '/noCropSetHeight/' . $height . substr($data->filename, strrpos($data->filename, "."));
                                // retina
                                if($height * 2 < $data->height) {
                                    $retinaURL =  "./" . $data->path . '/noCropSetWidth/' . ($height * 2) . substr($data->filename, strrpos($data->filename, "."));
                                } else {
                                    $retinaURL = "./" . $data->path;
                                }

                                $data->manageURL($url);
                                $data->manageURL($retinaURL);

                                $replace = $url . '" data-retina="' . $retinaURL;
                                $cache->write($replace, 86400);

                                $value = str_replace($m, $replace, $value);
                            } else {
                                $cache->write($m, 86400);
                            }
                        }
                    }
                }
            }
        }

        return (string) $value;
    }

    /**
     * creates the two urls with reized images and returns new HTML for replacement.
     *
     * @param ImageUploads $uploadsObject
     * @param string $action URL-Method
     * @param int $desiredHeight
     * @param int $desiredWidth
     * @return string
     */
    protected function generateResizeUrls($uploadsObject, $action, $desiredHeight = null, $desiredWidth = null) {
        $url = "./" . $uploadsObject->path . '/'.$action.'/';

        if(isset($desiredWidth)) {
            if($desiredWidth > $uploadsObject->width) {
                $url .= $desiredWidth . "/";

                if($desiredWidth * 2 > $uploadsObject->width) {
                    $retinaURL = $url . ($desiredWidth * 2) . "/";
                } else {
                    $retinaURL = false;
                }
            } else {
                return null;
            }
        }

        if(isset($desiredHeight)) {
            if($desiredHeight > $uploadsObject->height) {
                $url .= $desiredHeight . "/";

                if($desiredHeight * 2 > $uploadsObject->height) {

                    if(!isset($retinaURL)) {
                        $retinaURL = $url . ($desiredHeight * 2) . "/";
                    } else if($retinaURL) {
                        $retinaURL = $retinaURL . ($desiredHeight * 2) . "/";
                    }
                }
            } else {
                return null;
            }
        }


        $data->manageURL($url);
        $data->manageURL($retinaURL);

        $replace = $url . '" data-retina="' . $retinaURL;

        return $replace;
    }
}
