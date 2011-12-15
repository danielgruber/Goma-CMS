<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 30.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class JavaScriptField extends FormField 
{
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