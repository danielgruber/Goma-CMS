<?php
defined("IN_GOMA") OR die();

/**
 * A simple textarea.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1
 */
class Textarea extends FormField
{
    /**
     * height of this textarea
     *
     * @name height
     * @access public
     */
    public $height = "200px";

    /**
     * width of this textarea
     *
     * @name width
     * @access public
     */
    public $width = "100%";

    /**
     * this field needs to have the full width
     *
     * @name fullSizedField
     */
    protected $fullSizedField = true;

    /**
     * @name __construct
     * @param string - name
     * @param string - title
     * @param string - default-value
     * @param string - height
     * @param string - width
     * @param null|object - form
     */
    public function __construct($name = null, $title = null, $value = null, $height = null, $width = null, &$form = null)
    {
        if (isset($height))
            $this->height = $height;

        if (isset($width))
            $this->width = $width;

        parent::__construct($name, $title, $value, $form);
    }

    /**
     * generates the field in HTML
     *
     * @name createNode
     * @access public
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = parent::createNode();
        $node->css("height", $this->height);
        $node->css("width", $this->width);
        $node->removeAttr("type");
        $node->setTag("textarea");
        return $node;
    }
}
