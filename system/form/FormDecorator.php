<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.12.2011
  * $Version 1.0
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

abstract class FormDecorator extends Extension
{
		public function beforeRender(){}
		public function afterRender(){}
}