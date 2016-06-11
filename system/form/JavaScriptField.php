<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 21.12.2011
  * $Version 1.0.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class JavaScriptField extends FormField 
{
		/**
		 * stores the javascript for this field
		 *
		 *@name js
		 *@access public
		*/
		public $js;
		
		/**
		 * modified construct
		 *
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param string - javascript
		 *@param null|object - form
		*/
		public function __construct($name = null, $js = null, $form = null)
		{
				parent::__construct($name, null, null, $form);
				$this->js = $js;
		}
		public function field($info)
		{
				return null;
		}
		
		public function js()
		{
				return JSMin::minify($this->js);
		}
}