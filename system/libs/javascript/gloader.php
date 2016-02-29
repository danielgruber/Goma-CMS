<?php defined("IN_GOMA") OR die();

/**
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 12.05.2013
 * $Version 1.1.3
 */
class gLoader extends RequestHandler
{
    const VERSION = "1.1.3";
    /**
     * url-handlers
     *
     * @name url_handlers
     * @access public
     */
    public $url_handlers = array(
        "v2/\$name" => "deliver",
        "\$name" => "deliver"
    );

    /**
     * allowed actions
     */
    public $allowed_actions = array(
        "deliver"
    );

    /**
     * loadable resources
     *
     * @name resources
     * @access public
     */
    public static $resources = array();

    /**
     * preloaded resources
     *
     * @name preloaded
     * @access public
     */
    public static $preloaded = array();

    /**
     * adds a loadable resource
     * @name addLoadAble
     * @access public
     * @param string - name
     * @param string - filename
     * @param array - required other resources
     */
    public static function addLoadAble($name, $file, $required = array())
    {

        self::$resources[$name] = array(
            "file" => $file,
            "required" => $required
        );
    }

    /**
     * this is the php-function for the js-function gloader.load, it loads it for pageload
     * @name load
     * @access public
     */
    public static function load($name)
    {
        if (!isset(self::$preloaded[$name])) {
            if (isset(self::$resources[$name])) {
                foreach (self::$resources[$name]["required"] as $_name) {
                    self::load($_name);
                }
                Resources::add(self::$resources[$name]["file"], "js", "preload");
            }
            self::$preloaded[$name] = true;
        }
    }

    /**
     * delivers a specified resource
     *
     * @name deliver
     * @access public
     */
    public function deliver()
    {
        $name = $this->getParam("name");
        if (substr($name, -3) == ".js") {
            $name = substr($name, 0, -3);
        }

        HTTPResponse::addHeader('content-type', "text/javascript");
        if (isset(self::$resources[$name])) {
            HTTPResponse::addHeader('Cache-Control', 'public, max-age=5511045');
            HTTPResponse::addHeader("pragma", "Public");

            $data = self::$resources[$name];
            if (file_exists($data["file"])) {
                $mtime = 0;
                $this->checkMTime($name, $data, $mtime);

                $etag = strtolower(md5("gload_" . $name . "_" . md5(var_export($data, true)) . "_" . $mtime));
                HTTPResponse::addHeader("Etag", '"' . $etag . '"');

                // 304 by HTTP_IF_MODIFIED_SINCE
                if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                    if (strtolower(gmdate('D, d M Y H:i:s', $mtime) . ' GMT') == strtolower($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                        HTTPResponse::setResHeader(304);
                        HTTPResponse::sendHeader();
                        if (PROFILE)
                            Profiler::End();

                        exit;
                    }
                }
                // 304 by ETAG
                if (isset($_SERVER["HTTP_IF_NONE_MATCH"])) {
                    if ($_SERVER["HTTP_IF_NONE_MATCH"] == '"' . $etag . '"') {
                        HTTPResponse::setResHeader(304);
                        HTTPResponse::sendHeader();

                        if (PROFILE)
                            Profiler::End();

                        exit;
                    }
                }

                $temp = ROOT . CACHE_DIRECTORY . '/gloader.' . $name . self::VERSION . "." . md5(var_export($data, true)) . ".js";
                $expiresAdd = defined("DEV_MODE") ? 3 * 60 * 60 : 48 * 60 * 60;
                HTTPResponse::setCachable(NOW + $expiresAdd, $mtime, true);
                if (!file_exists($temp) || filemtime($temp) < $mtime) {
                    FileSystem::write($temp, $this->buildFile($name, $data));
                }


                HTTPResponse::sendHeader();
                readfile($temp);
                exit;
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    /**
     * this is building the file and modifiing mtime
     *
     * @name buildFile
     * @access protected
     */
    protected function buildFile($name, $data)
    {
        $js = "";
        if ($data["required"]) {
            foreach ($data["required"] as $_name) {
                if (isset(self::$resources[$_name])) {
                    if (file_exists(self::$resources[$_name]["file"])) {
                        $js .= $this->buildFile($_name, self::$resources[$name]);
                    } else {
                        header("HTTP/1.1 404 Not Found");
                        exit;
                    }
                }
            }
        }

        $js .= '/* file ' . $data["file"] . " */
goma.ui.setLoaded('" . $name . "'); goma.ui.registerResource('js', '" . $data["file"] . "?" . filemtime($data["file"]) . "');\n\n";

        $js .= jsmin::minify(file_get_contents($data["file"]));

        return $js;
    }

    /**
     * this is for checking cache active
     *
     * @name buildMTime
     * @access protected
     */
    protected function checkMTime($name, $data, &$mtime)
    {
        if ($data["required"]) {
            foreach ($data["required"] as $_name) {
                if (isset(self::$resources[$_name])) {
                    if (file_exists(self::$resources[$_name]["file"])) {
                        $this->checkMTime($_name, self::$resources[$name], $mtime);
                    } else {
                        return false;
                    }
                }
            }
        }

        if ($mtime < filemtime($data["file"])) {
            $mtime = filemtime($data["file"]);
        }


    }
}

StaticsManager::addSaveVar("gLoader", "resources");
