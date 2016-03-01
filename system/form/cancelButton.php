<?php
defined("IN_GOMA") OR die();

/**
 * A cancel button.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0
 */
class CancelButton extends FormAction
{
    /**
     * the javascript for this button on cancel
     *
     * @name js
     * @access public
     */
    public $js;

    /**
     * @name __construct
     * @access public
     * @param string - name
     * @param string - title
     * @param string - optional submission
     * @param object - form
     */
    public function __construct($name = null, $value = null, $redirect = null, $js = "", &$form = null)
    {
        $this->js = $js;
        parent::__construct($name, $value);
        $this->redirect = ($redirect === null) ? getredirect() : $redirect;
    }

    /**
     * creates the node
     *
     * @name createNodes
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = parent::createNode();
        $node->onClick = $this->js;
        $node->addClass("cancel");
        return $node;
    }

    /**
     * just don't let the system submit and redirect back
     */
    public function canSubmit($data)
    {
        if ($this->redirect !== null)
            HTTPResponse::redirect($this->redirect);
        else if (isset($_POST["redirect"]))
            HTTPResponse::redirect($_POST["redirect"]);
        else if (isset($_GET["redirect"]))
            HTTPResponse::redirect($_GET["redirect"]);
        else
            HTTPResponse::redirect(BASE_URI);
        exit;
    }
}
