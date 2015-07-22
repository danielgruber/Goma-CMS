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
    public function __construct($callback = null, $code = "")
    {
        parent::__construct();

        if (!$callback) {
            throw new InvalidArgumentException("Callback for Ajaxpopup required.");
        }


        $code = ($code == "") ? strtolower(randomString(20)) : strtolower($code);
        Core::globalSession()->set("ajaxpopups_" . $code, $callback);
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
     * @return bool|mixed
     */
    public function popup()
    {
        $code = $this->getParam("id");
        if (Core::globalSession()->hasKey("ajaxpopups_" . $code)) {
            $callback = Core::globalSession()->get("ajaxpopups_" . $code);

            return call_user_func_array($callback, array());
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
     * @return string
     */
    public function action()
    {
        $code = $this->getParam("id");
        $code = trim($code);
        if (Core::globalSession()->hasKey("ajaxlinks." . $code)) {
            return tpl::init(Core::globalSession()->get("ajaxlinks." . $code));
        } else {
            return $code . " wasn't found.";
        }
    }
}
