<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */

class ModelTransactionCache {
    /**
     * cache for transactions at all.
     */
    private static $dataCache = array();

    /**
     * clears cache.
     * @param string|object $class
     */
    public static function clear($class = null) {
        if(isset($class)) {
            self::$dataCache[$class] = array();
        } else {
            self::$dataCache = array();
        }
    }

    /**
     * puts data into it.
     * @param string|object $class
     * @param string $name
     * @param mixed $data
     */
    public static function put($class, $name, $data) {

        $class = ClassManifest::resolveClassName($class);

        self::$dataCache[$class][$name] = $data;
    }

    /**
     * gets cache.
     * @param string|object $class
     * @param string $name
     * @return mixed|null
     */
    public static function get($class, $name) {
        $class = ClassManifest::resolveClassName($class);

        return isset(self::$dataCache[$class][$name]) ? self::$dataCache[$class][$name] : null;
    }

    /**
     * deletes specific entry.
     * @param string|object $class
     * @param string $name
     */
    public static function delete($class = null, $name) {
        $class = ClassManifest::resolveClassName($class);

        unset(self::$dataCache[$class][$name]);
    }

}