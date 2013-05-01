<?php
/*
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 01.05.2013
  * $Version 1.0.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLFieldTest extends UnitTestCase implements TestAble {
	/**
	 * tests size-matching
	 *
	 *@name testSizeMatching
	*/
	public function testSizeMatching() {
		$this->assertEqual(HTMLText::matchSizes('style="width: 100px; height: 31px;"'), array("width" => 100, "height" => 31));
		$this->assertEqual(HTMLText::matchSizes('style="width:150px;height:36px;"'), array("width" => 150, "height" => 36));
		$this->assertEqual(HTMLText::matchSizes('style="width: 150px; height: 216px; float: left; padding-right: 10px;"'), array("width" => 150, "height" => 216));
		$this->assertEqual(HTMLText::matchSizes('style="float: left; width: 150px; height: 216px;padding-right: 10px;"'), array("width" => 150, "height" => 216));
		$this->assertEqual(HTMLText::matchSizes('style="cursor: default; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; float: left; width: 250px; height: 225px; "'), array("height" => 225, "width" => 250));
		
		$this->assertEqual(HTMLText::matchSizes('style="width:250px;cursor:default;border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; float: left;height: 225px; "'), array("height" => 225, "width" => 250));
		
		$this->assertEqual(HTMLText::matchSizes('<img alt="" src="http://lemkebuch.de/Uploads/ckeditor_uploads/4K4U59/Titel_Cuba_Castro.jpg" style="font-size: 13pt; line-height: 1.5em; width: 150px; height: 207px; float: left; padding-right: 10px;" />'), array("height" => 207, "width" => 150));
		
		$this->assertEqual(HTMLText::matchSizes('<img src="" height="100" width="200" />'), array("height" => 100, "width" => 200));
		$this->assertEqual(HTMLText::matchSizes('<img src=""height="100"width="200" />'), array("height" => 100, "width" => 200));
	}
}