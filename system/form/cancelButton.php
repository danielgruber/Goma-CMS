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
     * @param $data
     * @return bool
     */
    public function canSubmit($data)
    {
        return true;
    }

    /**
     * @return array
     */
    public function getSubmit()
    {
        return array($this, "redirect");
    }

    /**
     * @return null|string
     */
    public function __getSubmit() {
        return array($this, "redirect");
    }

    /**
     * @return GomaResponse
     */
    public function redirect() {
        if ($this->redirect !== null)
            return GomaResponse::redirect($this->redirect);
        else if (isset($this->form()->getRequest()->post_params["redirect"]))
            return GomaResponse::redirect($this->form()->getRequest()->post_params["redirect"]);
        else if (isset($this->form()->getRequest()->get_params["redirect"]))
            return GomaResponse::redirect($this->form()->getRequest()->get_params["redirect"]);
        else
            return GomaResponse::redirect(BASE_URI);

    }
}
