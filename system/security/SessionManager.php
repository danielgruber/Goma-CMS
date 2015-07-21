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
class SessionManager {
    /**
     * inits the session.
     */
    public static function Init() {
        session_start();
    }
}