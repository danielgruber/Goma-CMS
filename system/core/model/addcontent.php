<?php defined("IN_GOMA") OR die();

/**
 * holds add-contents.
 *
 * @package    goma framework
 * @link        http://goma-cms.org
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author        Goma-Team
 * @Version    1.0
 *
 * last modified: 24.07.2015
 */
class AddContent
{
    /**
     * session-key.
     */
    const SESSION_KEY = "__gaddcontent__";

    /**
     * the addcontent of the current session
     */
    public static $addcontent;

    /**
     * adds addcontent
     *
     * @name add
     * @param string - content
     */
    static public function add($content)
    {
        Core::globalSession()->set(self::SESSION_KEY, self::getCurrentSessionContent() . $content);
    }

    /**
     * adds addcontent
     *
     * @name add
     * @param string - content
     */
    static public function addSuccess($content)
    {
        Core::globalSession()->set(self::SESSION_KEY, self::getCurrentSessionContent() . '<div class="success">' . $content . '</div>');
    }

    /**
     * adds addcontent
     *
     * @name add
     * @param string - content
     */
    static public function addError($content)
    {
        Core::globalSession()->set(self::SESSION_KEY, self::getCurrentSessionContent() . '<div class="error">' . $content . '</div>');
    }

    /**
     * adds addcontent
     *
     * @name add
     * @param string - content
     */
    static public function addNotice($content)
    {
        Core::globalSession()->set(self::SESSION_KEY, self::getCurrentSessionContent() . '<div class="notice">' . $content . '</div>');
    }

    /**
     * gets the current addcontent
     *
     * @name get
     * @return string
     */
    public static function get()
    {
        self::$addcontent .= self::getCurrentSessionContent();

        Core::globalSession()->remove(self::SESSION_KEY);

        return self::$addcontent;
    }

    /**
     * flushes the addcontent
     *
     * @name flush
     * @access public
     */
    public static function flush()
    {
        Core::globalSession()->remove(self::SESSION_KEY);
        self::$addcontent = "";
    }

    /**
     * returns current content of session.
     */
    protected static function getCurrentSessionContent() {
        return Core::globalSession()->get(self::SESSION_KEY) ?: "";
    }
}
