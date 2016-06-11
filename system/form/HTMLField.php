<?php
/**
 * @package goma form framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 24.03.2012
 * $Version 1.0.2
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLField extends FormField
{
    /**
     * this var stores the html for this field
     *
     * @name html
     * @access public
     */
    public $html;

    /**
     * defines that these fields doesn't have a value
     *
     * @name hasNoValue
     */
    public $hasNoValue = true;

    /**
     * constructor
     * @param string|null $name
     * @param string|null $html
     * @param Form|null $form
     */
    public function __construct($name = null, $html = null, &$form = null)
    {
        parent::__construct($name, null, null, $form);
        $this->html = $html;
    }

    /**
     * generates the field
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        $this->callExtending("beforeField");


        $this->container->append($this->html);
        $this->container->addClass("hidden");

        // some patch
        if ($this->html == "" || strlen($this->html) < 15) {
            $this->container->addClass("hidden");
        }

        $this->callExtending("afterField");

        return $this->container;
    }
}
