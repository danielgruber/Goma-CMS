<?php defined("IN_GOMA") OR die();
/**
 * Utils for Pages.
 *
 * @package		Goma\Utilites
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class PageUtils {
    public static function cleanPath($path) {
        $path = trim($path);
        $path = strtolower($path);

        // special chars
        $path = str_replace("ä", "ae", $path);
        $path = str_replace("ö", "oe", $path);
        $path = str_replace("ü", "ue", $path);
        $path = str_replace("ß", "ss", $path);
        $path = str_replace("ù", "u", $path);
        $path = str_replace("û", "u", $path);
        $path = str_replace("ú", "u", $path);

        $path = str_replace(" ",  "-", $path);
        // normal chars
        $path = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $path);
        $path = str_replace('--', '-', $path);

        return $path;
    }
}