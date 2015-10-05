<?php defined("IN_GOMA") OR die();
/**
 * this is the main-interface-definition for each session-manager.
 *
 * @package     goma framework
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author      Goma-Team
 * @version     1.0
 *
 * last modified: 21.07.2015
 */
interface ISessionManager {

    /**
     * starts session with different id.
     * the old session will be stopped.
     *
     * @param $id
     * @return ISessionManager
     */
    public static function startWithIdAndName($id, $name = null);

    /**
     * you can restart the session after you stopped it.
     *
     * @param null|string $id
     * @param null|string $name
     * @return void
     */
    public function init($id = null, $name = null);

    /**
     * gets a value for key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * sets value for key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value);

    /**
     * unsets a session-key.
     *
     * @param string $key
     * @return boolean if something happended
     */
    public function remove($key);

    /**
     * returns if key exists.
     *
     * @param $key
     * @return boolean
     */
    public function hasKey($key);

    /**
     * lists all keys which exist at the moment.
     *
     * @return array
     */
    public function listKeys();

    /**
     * purges the session.
     */
    public function purge();

    /**
     * stops session-manager.
     */
    public function stopSession();

    /**
     * returns session-id.
     */
    public function getId();

    /**
     * remove by key prefix.
     *
     * @param string $prefix
     * @return int
     */
    public function removeByPrefix($prefix);

    /**
     * regenerates session-id.
     *
     * @return string new session
     */
    public function regenerateId();
}
