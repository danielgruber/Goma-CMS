<?php
defined("IN_GOMA") OR die();

/**
 * Mock for Session-Manager.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version 1.0
 */

class MockSessionManager implements ISessionManager {

    public $session = array();
    public $functionCalls = array();

    /**
     * starts session with different id.
     * the old session will be stopped.
     *
     * @param $id
     * @return ISessionManager
     */
    public static function startWithIdAndName($id, $name = null)
    {
        // TODO: Implement startWithIdAndName() method.
    }

    /**
     * you can restart the session after you stopped it.
     *
     * @param null|string $id
     * @param null|string $name
     * @return void
     */
    public function init($id = null, $name = null)
    {
        // TODO: Implement init() method.
    }

    /**
     * gets a value for key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->functionCalls[] = array("get", func_get_args());
        return isset($this->session[$key]) ? $this->session[$key] : null;
    }

    /**
     * sets value for key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->functionCalls[] = array("set", func_get_args());
        $this->session[$key] = $value;
    }

    /**
     * unsets a session-key.
     *
     * @param string $key
     * @return boolean if something happended
     */
    public function remove($key)
    {
        $this->functionCalls[] = array("remove", func_get_args());
        unset($this->session[$key]);
    }

    /**
     * returns if key exists.
     *
     * @param $key
     * @return boolean
     */
    public function hasKey($key)
    {
        $this->functionCalls[] = array("hasKey", func_get_args());
        return isset($this->session[$key]);
    }

    /**
     * lists all keys which exist at the moment.
     *
     * @return array
     */
    public function listKeys()
    {
        $this->functionCalls[] = array("listKeys", func_get_args());
        return array_keys($this->session);
    }

    /**
     * purges the session.
     */
    public function purge()
    {
        $this->functionCalls[] = array("purge", func_get_args());
        $this->session = array();
    }

    /**
     * stops session-manager.
     */
    public function stopSession()
    {
        // TODO: Implement stopSession() method.
    }

    /**
     * returns session-id.
     */
    public function getId()
    {
        return "";
    }

    /**
     * remove by key prefix.
     *
     * @param string $prefix
     * @return int
     */
    public function removeByPrefix($prefix)
    {
        $i = 0;
        foreach($this->listKeys() as $key) {
            if(substr($key, 0, strlen($prefix)) == $prefix) {
                if($this->remove($key)) {
                    $i++;
                }
            }
        }

        return $i;
    }

    /**
     * regenerates session-id.
     *
     * @return string new session
     */
    public function regenerateId()
    {
        // TODO: Implement regenerateId() method.
    }
}
