<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ArrayLib-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ArrayLibTest extends GomaUnitTest {
	/**
	 * area
	*/
	static $area = "Array";

	/**
	 * internal name.
	*/
	public $name = "ArrayLib";

	/**
	 * tests merge-function.
	*/
	public function testmerge() {
		$arr1 = array(1,2,3);
		$arr2 = array(2,3,4);

		$this->assertEqual(ArrayLib::merge($arr1, $arr2), array(1,2,3,2,3,4));

		$arr1 = array("test" => 1, 2, 3);
		$arr2 = array("test" => "blah", 4, 5);
		$this->assertEqual(ArrayLib::merge($arr1, $arr2), array("test" => "blah", 2, 3, 4, 5));
		$this->assertEqual(ArrayLib::merge($arr2, $arr1), array("test" => 1, 4, 5, 2, 3));

		$set1 = array(1,1,2,3,4);
		$set2 = array(5,6,7,1,2);
		$this->assertEqual(ArrayLib::mergeSets($set1, $set2), array(1,2,3,4,5,6,7));
	}

	/**
	 * key and value funcitons.
	*/
	public function testKeyFunctions() {
		$arr = array("blah" => "test", "blub" => "1");
		$this->assertEqual(ArrayLib::firstkey($arr), "blah");
		$this->assertEqual(ArrayLib::first($arr), "test");

		$this->assertEqual(ArrayLib::map_key($arr, "strtoupper"), array("BLAH" => "test", "BLUB" => "1"));


		$arr2 = ArrayLib::merge($arr, array(1,2,3, 3));
		$this->assertEqual($arr2, array("blah" => "test", "blub" => "1",1,2,3,3));
		$this->assertEqual(ArrayLib::key_value_for_id($arr2), array("blah" => "test", "blub" => "1", 1 => 1, 2 => 2, 3 => 3));
	}
}