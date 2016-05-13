<?php
defined("IN_GOMA") OR die();

/**
 * Used to respond to Ajax-Calls.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Form
 * @version	2.1.9
 */
class AjaxResponse extends GomaResponse
{
    /**
     * this array contains each action
     *
     * @var string[]
     */
    protected $actions = array();

    public function setDefaultHeader()
    {
        parent::setDefaultHeader();

        $this->setHeader("content-type", "text/javascript");
    }

    /**
     * adds war js to the actions
     *
     * @param string $js
     */
    public function exec($js)
    {
        if (is_object($js)) {
            $js = (string) $js;
        }
        $this->actions[] = $js;
    }

    /**
     * exec before.
     */
    public function execBefore($js) {
        if (is_object($js)) {
            $js = (string) $js;
        }
        array_unshift($this->actions, $js);
        $this->actions[] = $js;
    }

    /**
     * this function replaces html in a given node
     * @name replace
     * @access public
     */
    public function replace($node, $html)
    {
        $this->exec('$("' . convert::raw2js($node) . '").html("' . convert::raw2js($html) . '");');
    }

    /**
     * appends code to a node
     * @name append
     * @access public
     */
    public function append($node, $html)
    {
        $this->exec('$("' . convert::raw2js($node) . '").append("' . convert::raw2js($html) . '");');
    }

    /**
     * appends code to a node
     * @name append
     * @access public
     */
    public function appendHighlighted($node, $html)
    {
        $this->exec('$("' . convert::raw2js($node) . '").append("<div class=\"highlighter\">' . convert::raw2js($html) . '</div>");$("' . convert::raw2js($node) . '").find(".highlighter:last").css("display", "none").slideDown("slow");');
    }

    /**
     * appends code to a node
     * @name append
     * @access public
     */
    public function prependHighlighted($node, $html)
    {
        $this->exec('$("' . convert::raw2js($node) . '").prepend("<div class=\"highlighter\">' . convert::raw2js($html) . '</div>");$("' . convert::raw2js($node) . '").find(".highlighter:first").css("display", "none").slideDown("slow");');
    }

    /**
     * preprend
     * @name prepend
     * @access public
     */
    public function prepend($node, $html)
    {
        $this->exec('$("' . convert::raw2js($node) . '").prepend("' . convert::raw2js($html) . '");');
    }

    /**
     * renders the response
     *
     * @return string
     */
    public function render()
    {
        return implode("\n", $this->actions);
    }

    /**
     * removes a node
     */
    public function removeNode($node)
    {
        $this->exec('$("' . convert::raw2js($node) . '").remove();');
    }

    /**
     * slides a node up (hide)
     */
    public function slideUp($node, $duration = "200", $exec, $exec = "")
    {

        if (is_int($exec)) {
            $exec = $this->actions[$exec];
            unset($this->actions[$exec]);
        }
        $this->exec('$("' . convert::raw2js($node) . '").slideUp(' . var_export($duration, true) . ', function(){
				' . $exec . '
			});');
    }

    /**
     * slides a node down (show)
     */
    public function slideDown($node, $duration = "200", $exec = "")
    {
        if (is_int($exec)) {
            $exec = $this->actions[$exec];
            unset($this->actions[$exec]);
        }
        $this->exec('$("' . convert::raw2js($node) . '").slideDown(' . var_export($duration, true) . ', function(){
				' . $exec . '
			});');
    }

    public function output()
    {
        $data = Resources::get(true, true, true);
        $this->setHeader("X-JavaScript-Load", implode(";", $data["js"]));
        $this->setHeader("X-CSS-Load", implode(";", $data["css"]));


        parent::output();
    }

    public function __toString()
    {
        return $this->render();
    }
}
