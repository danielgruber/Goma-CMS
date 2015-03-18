<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for LangSelect.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class LangSelectTest extends GomaUnitTest implements TestAble {
	/**
	 * area
	*/
	static $area = "Form";

	/**
	 * internal name.
	*/
	public $name = "LangSelectFormField";
	/**
	 * tests langselect
	*/
	public function testLangSelect() {
		$select = new langSelect("blah", "Test");

		$select->includeFirstOption = true;
		$options = $select->options();

		$this->assertEqual($options[""], lang("all"));

		$select->includeFirstOption = array("blub", "huch");
		$options = $select->options();

		$this->assertEqual($options["blub"], "huch");

		$select->includeFirstOption = "blub";
		$options = $select->options();

		$this->assertEqual($options["blub"], "blub");

		$this->assertEqual(count($options), count(i18n::listLangs()) + 1);
	}
}