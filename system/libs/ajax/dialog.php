<?php
/**
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 26.11.2012
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Dialog extends AjaxResponse
{
    /**
     * key-indentifier
     * this will be the name of the var this box is saves
     * @name key
     */
    public $key;

    /**
     * addon-js
     * @name js
     * @access public
     */
    public $js = "";

    /**
     * the content of the dialog
     * @name content
     * @access public
     */
    public $content;

    /**
     * title
     * @name title
     * @access public
     */
    public $title;

    /**
     * close-button
     */
    public $closeButton = true;

    /**
     * drop-position
     */
    public $dropPosition = "auto";

    /**
     * renders a dialig
     *
     * @name __construct
     * @access public
     * @param string - title
     * @param string - content
     */
    public function __construct($content = "", $title = "", $closetime = 0)
    {
        $this->title = $title;
        $this->content = $content;
        $this->key = randomString(10, false);
        if ($closetime != 0)
            $this->close($closetime);
    }

    /**
     * close-function
     * @name close
     * @access public
     * @param time to wait before close in minutes
     */
    public function close($timeout = 0)
    {
        $timeout = $timeout * 1000;
        if (Core::is_ajax() && isset($_GET["dropdownDialog"])) {
            $this->js .= "setTimeout(function(){
						\$this.close();
					}, " . $timeout . ");";
        } else {

            $this->js .= "setTimeout(function(){
						" . $this->key . ".close();
					}, " . $timeout . ");";
        }

    }

    /**
     * getCloseJS
     *
     * @name getCloseJS
     */
    public function getCloseJS($timeout = 0)
    {
        $timeout = $timeout * 1000;
        if (Core::is_ajax() && isset($_GET["dropdownDialog"])) {
            return "setTimeout(function(){
						\$this.close();
					}, " . $timeout . ");";
        } else {

            return "setTimeout(function(){
						" . $this->key . ".close();
					}, " . $timeout . ");";
        }
    }

    /**
     * renders the JavaScript
     * @name render
     * @access public
     */
    public function render()
    {
        if (Core::is_ajax() && isset($_GET["dropdownDialog"])) {
            $array = array("content" => $this->content, "exec" => 'var $this = this;' . $this->js);
            if ($this->dropPosition != "auto") {
                $array["position"] = $this->dropPosition;
            }
            $array["closeButton"] = $this->closeButton;

            $response = new JSONResponseBody($array);
            return $response->toServableBody($this);
        } else {
            return 'gloader.load("dialog");
$("body").append("' . convert::raw2js($this->renderHTML()) . '");
self.' . $this->key . ' = new ExistingBluebox("dialog_' . $this->key . '");' . $this->js;
        }
    }

    /**
     * you can also render this as HTML, so without any javascript-functionallity and just for styling
     *
     * @name renderHTML
     * @access public
     * @param bool - close-button
     */
    public function renderHTML($close = true)
    {
        $html = '<div id="dialog_' . $this->key . '" class="bluebox bluebox_wrapper" style="display: block;">
							<table class="bluebox_container windowzindex" cellspacing="0" cellpadding="0" style="display: block;">
								<tr>
									<td class="con_shadow" style="opacity: 0.7;width: 16px;height: 16px;background-image: url(system/templates/css/images/tl.png);background-repeat: no-repeat;"></td>
									<td class="con_shadow bluebox_border" style="opacity: 0.7;height: 16px;background-repeat: repeat-x;"></td>
									<td class="con_shadow" style="opacity: 0.7;width: 16px;height: 16px;background-image: url(system/templates/css/images/tr.png);background-repeat: no-repeat;"></td>
								</tr>
								<tr>
									<td class="con_shadow bluebox_border" style="opacity: 0.7;width: 16px;background-repeat: repeat-y;"></td>
									<td class="bluebox_inner" style="background-color: #ffffff;">
										<div align="center" class="bluebox_loading"><img src="images/loading.gif" alt="loading..." /></div>
										<div class="bluebox_data">
											';

        if ($close)
            $html .= '
													
											<span class="bluebox_close" style="display: none;" onmouseover="this.style.color = \'#ffffff\';" onmouseout="this.style.color = \'#afafaf\';" onclick="getblueboxbyid($(this).parents(\'.bluebox_wrapper\').attr(\'id\').replace(\'bluebox_\',\'\')).close();">x</span>';

        $html .= '
											<div class="bluebox_title">' . $this->title . '</div>
											<div class="bluebox_content">' . $this->content . '</div>
											<div class="bluebox_placeholder"></div>
										</div>
									</td>
									<td class="con_shadow bluebox_border" style="opacity: 0.7;width: 16px;background-repeat: repeat-y;"></td>
								</tr>
								<tr>
									<td class="con_shadow" style="opacity: 0.7;width: 16px;height: 16px;background-image: url(system/templates/css/images/bl.png);background-repeat: no-repeat;"></td>
									<td class="con_shadow bluebox_border" style="opacity: 0.7;height: 16px;background-repeat: repeat-x;"></td>
									<td class="con_shadow" style="width: 16px;opacity: 0.7;height: 16px;background-image: url(system/templates/css/images/br.png);background-repeat: no-repeat;"></td>
								</tr>
								
							</table>
						</div>';

        return $html;
    }

    /**
     * closes the dialog by given id
     * @name closeByID
     * @access public
     */
    public static function closeByID($id)
    {
        return "getblueboxbyid(" . $id . ").close();";
    }
}