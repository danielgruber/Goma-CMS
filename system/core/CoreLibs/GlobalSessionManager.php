<?php defined("IN_GOMA") OR die();

/**
 * session-timeout for goma-cookie. this also lives after browser has closed.
 */
define("SESSION_TIMEOUT", 24*3600);

/**
 * @package		Goma\Session
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class GlobalSessionManager {

    /**
     * session-id-cookie.
     */
    const SESSION_ID_COOKIE = "goma_sessid";

    /**
     * life-id-cookie.
     */
    const LIFE_ID_COOKIE = "goma_lifeid";

    /**
     * session.
     */
    protected static $globalSession;

    /**
     * init.
     *
     * @param string|null $sessionId
     */
    public static function Init($sessionId = null) {
        if(!isset($sessionId)) {
            if(isset($_COOKIE[self::SESSION_ID_COOKIE])) {
                $sessionId = $_COOKIE[self::SESSION_ID_COOKIE];
            }
        }

        self::$globalSession = SessionManager::startWithIdAndName($sessionId, null);
    }

    /**
     * sets session-id + cookie.
     *
     * @param string $sessionId
     */
    public static function setSessionWithCookie($sessionId) {
        self::$globalSession = SessionManager::startWithIdAndName($sessionId);
        self::setGomaCookies($sessionId, self::getCookieHost());
    }

    /**
     * returns session.
     *
     * @return ISessionManager
     */
    public static function globalSession()
    {
        return self::$globalSession;
    }

    /**
     * sets global session.
     *
     * @param ISessionManager $session
     */
    public static function __setSession($session)
    {
        self::$globalSession = $session;
    }

    /**
     * sets goma cookies.
     *
     * @param string $user_identifier
     * @param string $host
     */
    public static function setGomaCookies($user_identifier, $host)
    {
        setCookie(self::SESSION_ID_COOKIE, $user_identifier, TIME + SESSION_TIMEOUT, '/', $host, false, true);
        setCookie(self::LIFE_ID_COOKIE, $user_identifier, TIME + 365 * 24 * 60 * 60, '/', $host, false, true);
    }

    /**
     * gets host with dot before, so we can use it for cookies.
     *
     * @param string|null $host
     * @return string
     */
    public static function getCookieHost($host = null) {
        // set correct host, avoid problems with localhost
        if(!isset($host)) $host = $_SERVER["HTTP_HOST"];

        if (!preg_match('/^[0-9]+/', $host) && $host != "localhost" && strpos($host, ".") !== false)
            $host = "." . $host;

        return $host;
    }
}
