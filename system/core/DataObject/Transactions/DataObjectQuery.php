<?php defined("IN_GOMA") OR die();

/**
 * implements reading + cache.
 *
 * @package		Goma\DB
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class DataObjectQuery {
    /**
     * cache
     */
    public static $datacache = array();

    /**
     * clears cache.
     *
     * @param string|null $class
     */
    public static function clearCache($class = null) {
        if(isset($class)) {
            self::$datacache[$class] = array();
        } else {
            self::$datacache = array();
        }
    }
}
