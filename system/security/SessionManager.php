<?php defined("IN_GOMA") OR die();
/**
 * this class provides session-managment with ids.
 *
 * @package     goma framework
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author      Goma-Team
 * @version     1.0
 *
 * last modified: 21.07.2015
 */
class SessionManager implements ISessionManager {

    /**
     * store-indicator.
     */
    const STORE_INDICATOR = "__STORE__:";

    /**
     * threshold for storing in filesystem.
     */
    const FILE_THRESHOLD = 2048;

    /**
     * id of session.
     *
     * @var string
     */
    protected $id;

    /**
     * name of the session.
     *
     * @var string
     */
    protected $name;

    /**
     * static if session id existing.
     */
    protected static $existing;

    /**
     * starts session with different id.
     * the old session will be stopped.
     *
     * @param string|null $id
     * @param string|null $name name of session
     * @return ISessionManager
     */
    public static function startWithIdAndName($id, $name = null)
    {
        return new SessionManager($id, $name);
    }

    /**
     * protected constructor.
     *
     * @param string|null $id
     * @param string|null $name name of session
     */
    protected function __construct($id, $name) {
        $this->init($id, $name);
    }

    /**
     * inits current session.
     *
     * @param string|null $id
     * @param string|null $name
     * @return void
     */
    public function init($id = null, $name = null) {

        $this->setName($name);
        $this->setId($id);

        if(!isset(self::$existing) || self::$existing != $this->id) {
            if (self::$existing != null) {
                session_write_close();
            }

            $this->setSessionParams();

            session_start();

            $this->id = session_id();
            self::$existing = $this->id;
        }
    }

    /**
     * sets id and name.
     */
    protected function setSessionParams() {
        if(isset($this->name)) {
            session_name($this->name);
        }

        if(isset($this->id)) {
            session_id($this->id);
        }
    }

    /**
     * gets a value for key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $data = null;

        if(isset($_SESSION[$key])) {
            $fileKey = self::getStoreIndicator($_SESSION[$key]);
            if ($fileKey !== null && file_exists(self::getFilePathForKey($fileKey))) {
                $data = unserialize(file_get_contents(self::getFilePathForKey($fileKey)));
            } else {
                $data = $_SESSION[$key];
            }

            if(is_object($data) && method_exists($data, "__wakeup")) {
                $data->__wakeup();
            }
        }

        return $data;
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
        $matchValue = $value;
        if(is_object($value)) {
            $matchValue = serialize($value);
        }

        if(is_array($value)) {
            $matchValue = serialize($value);
        }

        if(strlen($matchValue) > self::FILE_THRESHOLD) {
            $random = randomString(20);

            FileSystem::write(self::getFilePathForKey($random), $matchValue, LOCK_EX, 0773);

            $_SESSION[$key] = self::STORE_INDICATOR . $random;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * unsets a session-key.
     *
     * @param string $key
     * @return boolean if something happended
     */
    public function remove($key)
    {
        if(isset($_SESSION[$key])) {

            $fileKey = self::getStoreIndicator($_SESSION[$key]);
            if ($fileKey !== null && file_exists(self::getFilePathForKey($fileKey))) {
                unlink(self::getFilePathForKey($fileKey));
            }

            unset($_SESSION[$key]);

            return true;
        }

        return false;
    }

    /**
     * purges the session.
     */
    public function purge()
    {
        foreach($_SESSION as $key => $val) {
            $this->remove($key);
        }
    }

    /**
     * stops session-manager.
     */
    public function stopSession()
    {
        session_write_close();
        self::$existing = null;
    }

    /**
     * returns session-id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string
     */
    protected function setId($id) {
        if(isset($id)) {
            $this->id = $id;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    protected function setName($name)
    {
        if(isset($name)) {
            $this->name = $name;
        }
    }

    /**
     * returns if key exists.
     *
     * @param $key
     * @return boolean
     */
    public function hasKey($key)
    {
        if(!isset($_SESSION[$key])) {
            return false;
        }

        $fileKey = self::getStoreIndicator($_SESSION[$key]);
        if ($fileKey !== null && !file_exists(self::getFilePathForKey($fileKey))) {
            return false;
        }

        return true;
    }

    /**
     * returns if it is the store inidcator.
     *
     * @param mixed $value
     * @return string|null
     */
    protected function getStoreIndicator($value) {
        if(is_string($value) && substr($value, 0, strlen(self::STORE_INDICATOR)) == self::STORE_INDICATOR) {
            return substr($value, strlen(self::STORE_INDICATOR));
        }

        return null;
    }

    /**
     * returns file-path for extension-file by key.
     *
     * @param string $key
     * @return string path
     */
    protected static function getFilePathForKey($key) {
        return ROOT . CACHE_DIRECTORY . "data." . $key . ".goma";
    }
}