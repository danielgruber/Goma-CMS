<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.12.2011
  * $Version
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TimeField extends HiddenField
{
		/**
		 *@name __construct
		 *@param string -name
		 *@param object - form
		*/
		public function __construct($name = null, $form = null)
		{
				parent::__construct($name, 1, $form);
		}
		/**
		 * result (current date)
		*/
		public function result()
		{
				return NOW;
		}
}