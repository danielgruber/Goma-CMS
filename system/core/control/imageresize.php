<?php
/**
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 30.04.2013
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class imageResize extends RequestHandler
{
    /**
     * url-handlers
     *
     * @name url_handler
     */
    public $url_handlers = array(
        "x/\$height!"        => "resizeByHeight",
        "\$width!/\$height!" => "resize",
        "\$width!"           => "resizeByWidth"
    );

    public $allowed_actions = array(
        "resize", "resizeByWidth", "resizeByHeight"
    );

    public function handleRequest($request, $subController = false)
    {
        GlobalSessionManager::globalSession()->stopSession();

        return parent::handleRequest($request, $subController);
    }

    /**
     * resizes an image
     *
     * @name resizeByWidth
     * @access public
     * @return bool
     */
    public function resizeByWidth()
    {
        $width = $this->getParam("width");

        if ($this->getParam("height")) {
            $file = FileSystem::protect($this->getParam("height") . "/" . $this->request->remaining());
        } else {
            $file = FileSystem::protect($this->request->remaining());
        }

        if (!preg_match('/\.(jpg|jpeg|png|bmp|gif)$/i', $file)) {
            return false;
        }

        if (!file_exists(ROOT . URL . ".permit")) {
            Core::Deprecate(2.0, "Direct linking of Resizable files is not allowed cause of DDOS. Use tpl::imageSetWidth for generating the URL.");
        }

        $image = new Image($file);

        if (isset($image->md5)) {
            $img = $image->resizeByWidth($width, !isset($_GET["nocrop"]));
            if (substr($file, 0, 7) == "Uploads") {
                FileSystem::requireDir(dirname(ROOT . URL));
                $img->toFile(ROOT . URL);
            }
            $img->Output();
            exit;
        }

        return false;
    }

    /**
     * resizes an image
     *
     * @name resizeByHeight
     * @access public
     * @return bool
     */
    public function resizeByHeight()
    {

        $height = $this->getParam("width");

        $file = FileSystem::protect($this->request->remaining());
        if (!preg_match('/\.(jpg|jpeg|png|bmp|gif)$/i', $file)) {
            return false;
        }

        if (!file_exists(ROOT . URL . ".permit")) {
            Core::Deprecate(2.0, "Direct linking of Resizable files is not allowed cause of DDOS. Use tpl::imageSetHeight for generating the URL.");
        }

        $image = new Image($file);

        if (isset($image->md5)) {
            $img = $image->resizeByHeight($height, !isset($_GET["nocrop"]));
            if (substr($file, 0, 7) == "Uploads") {
                FileSystem::requireDir(dirname(ROOT . URL));
                $img->toFile(ROOT . URL);
            }
            $img->Output();
            exit;
        }

        return false;
    }

    /**
     * resizes an image
     *
     * @name resize
     * @access public
     * @return bool
     */
    public function resize()
    {
        $width = $this->getParam("width");
        $height = $this->getParam("height");

        if (!preg_match('/^([0-9]+)$/', $height)) {
            return $this->resizeByWidth();
        }

        if (!file_exists(ROOT . URL . ".permit")) {
            Core::Deprecate(2.0, "Direct linking of Resizable files is not allowed cause of DDOS. Use tpl::imageSetSize for generating the URL.");
        }

        $file = FileSystem::protect($this->request->remaining());
        if (!preg_match('/\.(jpg|jpeg|png|bmp|gif)$/i', $file)) {
            return false;
        }

        $image = new Image($file);

        if (isset($image->md5)) {
            $img = $image->resize($width, $height, !isset($_GET["nocrop"]));
            if (substr($file, 0, 7) == "Uploads") {
                FileSystem::requireDir(dirname(ROOT . URL));
                $img->toFile(ROOT . URL);
            }
            $img->Output();
            exit;
        }

        return false;
    }
}

