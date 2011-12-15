<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 06.01.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ScaleTest extends Test
{
	public $name = "jqueryScale";
	public function render()
	{
			return tpl::render("test/jquery.scale.rotate.html");
	}
}

Object::extend("TestController", "ScaleTest");