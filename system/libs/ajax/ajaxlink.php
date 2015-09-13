<?php
/**
 * @package goma
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 08.07.2010
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ajaxlink extends RequestHandler
{
    public $url_handlers = array(
        "link/\$id"  => "action",
        "popup/\$id" => "popup"
    );

    public $allowed_actions = array(
        "action",
        "popup"
    );

    /**
     * for PHP
     */
    public $code;

    /**
     * @name __construct
     * @param callback - function
     * @access public
     */
    public function __construct($callback = false, $code = "")
    {
        parent::__construct();

        if ($callback === false) {
            throw new InvalidArgumentException("Callback for Ajaxpopup required.");
        }


        $code = ($code == "") ? strtolower(randomString(20)) : strtolower($code);
        if(GlobalSessionManager::globalSession() != null) {
            GlobalSessionManager::globalSession()->set("ajaxpopups_" . $code, $callback);
        }
        $this->code = $code;
    }

    /**
     * generates a link
     *
     * @name link
     * @access public
     * @param string  $title
     * @param array $classes css classes
     * @return string
     */
    public function link($title, $classes = array())
    {
        return '<a href="system/ajax/popup/' . $this->code . '" class="' . implode(' ', $classes) . '" rel="bluebox" title="' . $title . '">' . $title . '</a>';
    }

    /**
     * shows the popup
     *
     * @name popup
     * @access public
     * @return string|false
     */
    public function popup()
    {
        $code = trim($this->getParam("id"));
        if (GlobalSessionManager::globalSession()->hasKey("ajaxpopups_" . $code)) {
            return call_user_func_array(GlobalSessionManager::globalSession()->get("ajaxpopups_" . $code), array());
        } else {
            return false;
        }
    }

    /* --- */

    /**
     * for TEMPLATE
     */
    /**
     * control
     *
     * @name action
     * @access public
     * @return string|false
     */
    public function action()
    {
        $code = trim($this->getParam("id"));
        if (GlobalSessionManager::globalSession()->hasKey("ajaxlinks." . $code)) {
            return tpl::init(GlobalSessionManager::globalSession()->get("ajaxlinks." . $code));
        } else {
            return false;
        }
    }
}
