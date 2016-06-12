<?php defined('IN_GOMA') OR die();

/**
 * HTML-Field.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
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
