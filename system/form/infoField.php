<?php
/**
 * @package goma form framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 21.02.2012
 * $Version 1.1.2
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class infoField extends HTMLField
{
    /**
     * this var stores the html for this field
     *
     * @name html
     * @access public
     */
    public $html;

    /**
     * special field with special style
     * @param string|null $name
     * @param string|null $html
     * @param Form|null $form
     */
    public function __construct($name = null, $html = null, &$form = null)
    {
        parent::__construct($name, null, $form);
        $this->html = $html;
    }

    /**
     * generates the field
     *
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->callExtending("beforeField");

        $this->container->append('
							<div class="info_box">
								' . $this->html . '
							</div>');
        $this->container->addClass("hidden");
        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormField::field");

        return $this->container;
    }
}
