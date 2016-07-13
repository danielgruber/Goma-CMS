<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for HTMLText-Field.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class HTMLFieldTest extends GomaUnitTest implements TestAble {
	/**
	 * area
	*/
	static $area = "HTML";

	/**
	 * internal name.
	*/
	public $name = "HTMLField";

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

        $this->assertEqual(HTMLText::matchSizes('<img alt="" height="980" src="./Uploads/c6f796ae12b4667f6aa0f3ed6d812456/kGBRR3/bild_9469.jpg/index.jpg" width="1000" />'), array("width" => 1000, "height" => 980));
	}
}
