<?php defined("IN_GOMA") OR die();
/**
 * this class provides some methods to check validity of formats.
 *
 * @package     goma framework
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author      Goma-Team
 * @version     1.0
 *
 * last modified: 22.07.2015
 */

class RegexpUtil {
    /**
     * returns if string is a number.
     *
     * @param string $string
     * @return bool
     */
    public static function isNumber($string) {
        return preg_match('/^[0-9]+$/', $string);
    }

    /**
     * returns if email is valid.
     *
     * @param string $email
     * @return bool
     */
    public static function isEmail($email) {
        return preg_match('/^([a-zA-Z0-9\-\._]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9]{2,9})$/', $email);
    }
}