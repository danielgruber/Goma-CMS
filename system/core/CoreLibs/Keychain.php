<?php
defined("IN_GOMA") OR die();

/**
 * Keychain is used for storing passwords in a session.
 *
 * @package Goma/Controller
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class Keychain {
    /**
     * keychain constant for session.
     */
    const SESSION_KEYCHAIN = "c_keychain";

    /**
     * @var bool
     */
    protected $useCookies = false;

    /**
     * @var int
     */
    protected $cookieLifeTime = 1209800;

    /**
     * @var ISessionManager
     */
    protected $session;

    /**
     * @var string
     */
    protected $sessionName;

    /**
     * shared instance.
     *
     * @var Keychain
     */
    private static $sharedInstance;

    /**
     * @return Keychain
     */
    public static function sharedInstance() {
        if(!isset(static::$sharedInstance)) {
            static::$sharedInstance = new static();
        }

        return static::$sharedInstance;
    }

    /**
     * @param Keychain $shared
     * @return Keychain
     */
    public static function setSharedInstance($shared) {
        static::$sharedInstance = $shared;
        return $shared;
    }

    /**
     * Keychain constructor.
     * @param bool $useCookies
     * @param int|null $cookieLifeTime
     * @param ISessionManager|null $session
     * @param string|null $sessionName
     */
    public function __construct($useCookies = false, $cookieLifeTime = null, $session = null, $sessionName = null)
    {
        $this->useCookies = $useCookies;
        $this->sessionName = isset($sessionName) ? $sessionName : self::SESSION_KEYCHAIN;
        $this->session = isset($session) ? $session : GlobalSessionManager::globalSession();
        if(isset($cookieLifeTime)) {
            $this->cookieLifeTime = $cookieLifeTime;
        }
    }

    /**
     * adds a password to the keychain
     *
     * @param string $password
     */
    public function add($password)
    {
        $keychain = $this->getCurrentKeychain();
        $keychain[] = $password;

        $this->session->set($this->sessionName, $keychain);

        if ($this->useCookies) {
            setCookie("keychain_" . md5(md5($password)), md5($password), NOW + $this->cookieLifeTime);
        }
    }

    /**
     * checks if a password is in keychain
     *
     * @param string $password
     * @return bool
     */
    public function check($password)
    {
        $keychain = self::getCurrentKeychain();
        if ((in_array($password, $keychain)) ||
            (
                $this->useCookies &&
                isset($_COOKIE["keychain_" . md5(md5($password))]) &&
                $_COOKIE["keychain_" . md5(md5($password))] == md5($password)
            )) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * removes a password from keychain
     * @param string $password
     */
    public function remove($password)
    {
        $keychain = $this->getCurrentKeychain();

        if ($key = array_search($password, $keychain)) {
            unset($keychain[$key]);
        }

        $this->session->set($this->sessionName, $keychain);

        if($this->useCookies) {
            setCookie("keychain_" . md5(md5($password)), null, -1);
        }
    }

    /**
     * returns current keychain-array.
     */
    protected function getCurrentKeychain() {
        if($this->session->hasKey($this->sessionName)) {
            return $this->session->get($this->sessionName);
        }

        return array();
    }

    /**
     * clears keychain.
     */
    public function clear()
    {
        $this->session->set($this->sessionName, array());
    }
}
