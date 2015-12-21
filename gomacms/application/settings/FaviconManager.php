<?php
defined("IN_GOMA") OR die();

/**
 * Favicon-Manager stores Favicon.
 *
 * @package Goma
 *
 * @author Daniel Gruber
 * @copyright 2015 Daniel Gruber
 *
 * @version 1.0
 */
class FaviconManager
{
    const ID = "FaviconManager";

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @param string $html
     */
    public static function handleIcon(&$html) {
        if(!empty(self::$favicon)) {
            self::addFavicon($html, self::$favicon);
        } else if(settingsController::get("favicon")) {
            Core::$favicon = "./favicon.ico";
        }
    }

    /**
     * @param string $html
     * @param string $favicon
     */
    public static function addFavicon(&$html, $favicon) {
        $html .= '		<link rel="icon" href="' . $favicon . '" type="image/x-icon" />';
        $html .= '		<link rel="apple-touch-icon-precomposed" href="'.RetinaPath($favicon).'" />';
    }
}

Core::addToHook(Core::HEADER_HTML_HOOK, array(FaviconManager::ID, "handleIcon"));
