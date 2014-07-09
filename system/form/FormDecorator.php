<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 21.12.2011
  * $Version 1.0
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

abstract class FormDecorator extends Extension
{
		public function beforeRender(){}
		public function afterRender(){}
}