<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
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
		public function __construct($name, $js = null, $form = null)
		{
				parent::__construct($name, null, null, $form);
				$this->js = $js;
		}
		public function field()
		{
				return null;
		}
		
		public function js()
		{
				return $this->js;
		}
}