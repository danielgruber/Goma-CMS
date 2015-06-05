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
     */
    public static function clearCache() {
        self::$datacache = array();
    }
}