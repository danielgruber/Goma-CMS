<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 16.09.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TabsTest extends Test
{
		public $name = "tabs";
		public function render()
		{
			$tabs = new Tabs("test");
			$tabs->addTab("test", "Hallo Welt", "tab1");
			$tabs->addAjaxTab("ajaxtab", array($this, "ajaxtab"), "ajaxtab");
			return getPage($tabs->render(), "TabTest");
		}
		public function ajaxtab() {
			return array(randomString(3), "Dieser Tab nutzt dynamische Titelgenerierung zur Generierung des Titels: <code>randomString(3)</code>");
		}
}

Object::extend("TestController", "TabsTest");