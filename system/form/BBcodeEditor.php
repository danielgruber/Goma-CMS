<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 13.03.2012
  * $Version 3.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class BBcodeEditor extends Textarea
{
		/**
		 * options for the editor
		 *
		 *@name options
		 *@access public
		*/
		public $options = array(
			
		);
		
		
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
		public function __construct($name, $title = null, $value = null, $height = null,$width = null, $options = null, &$form = null)
		{
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
		public function JS()
		{
				Resources::add("system/form/BBCodeEditor.js", "js", "tpl");
				Resources::add("bbcode.css");
				$js = "$(function(){ 
							$('#".$this->ID()."').BBCodeEditor(".json_encode($this->options)."); 
						});";
				return $js;
		}
}