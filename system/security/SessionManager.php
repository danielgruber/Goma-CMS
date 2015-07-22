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
     */
    protected function init($id = null, $name = null) {

        $this->setName($name);
        $this->setId($id);

        if(self::$existing != $this->id) {
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
        if(isset($name)) {
            session_name($name);
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
            if(substr($_SESSION[$key], 0, strlen(self::STORE_INDICATOR)) == self::STORE_INDICATOR) {
                $fileKey = substr($_SESSION[$key], strlen(self::STORE_INDICATOR));

                if(file_exists(ROOT . CACHE_DIRECTORY . "data." . $fileKey . ".goma")) {
                    $data = unserialize(file_get_contents(ROOT . CACHE_DIRECTORY . "data." . $fileKey . ".goma"));
                }
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
        if(is_object($value)) {
            $value = serialize($value);
        }

        if(strlen($value) > self::FILE_THRESHOLD) {
            $random = randomString(20);

            FileSystem::write(ROOT . CACHE_DIRECTORY . "data." . $random . ".goma", serialize($value), LOCK_EX, 0773);

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
            if (substr($_SESSION[$key], 0, strlen(self::STORE_INDICATOR)) == self::STORE_INDICATOR) {
                $fileKey = substr($_SESSION[$key], strlen(self::STORE_INDICATOR));

                if (file_exists(ROOT . CACHE_DIRECTORY . "data." . $fileKey . ".goma")) {
                    unlink(ROOT . CACHE_DIRECTORY . "data." . $fileKey . ".goma");
                }
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
        return isset($_SESSION[$key]);
    }
}