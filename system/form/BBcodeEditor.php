<?php
defined("IN_GOMA") OR die();

/**
 * A BB code editor.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 3.0
 */
class BBcodeEditor extends Textarea {
	/**
	 * options for the editor
	 *
	 *@name options
	 *@access public
	 */
	public $options = array();

	/**
	 *@name __construct
	 *@param string - name
	 *@param string - title
	 *@param string - default-value
	 *@param string - height
	 *@param string - width
	 *@param options
	 *@param null|object - form
	 */
	public function __construct($name = null, $title = null, $value = null, $height = null, $width = null, $options = null, &$form = null) {
		parent::__construct($name, $title, $value, $height, $width, $form);

		if(is_array($options))
			$this->options = array_merge($this->options, $options);
	}

	/**
	 * generates the JavaScript for this field
	 *
	 *@name JS
	 *@access public
	 */
	public function JS() {
		Resources::add("system/form/BBCodeEditor.js", "js", "tpl");
		Resources::add("bbcode.css");
		$js = "$(function(){ 
							$('#" . $this->ID() . "').BBCodeEditor(" . json_encode($this->options) . "); 
						});";
		return $js;
	}

}
