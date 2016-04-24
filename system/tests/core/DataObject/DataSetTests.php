<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for DataObject-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class DataSetTests extends GomaUnitTest {
    static $area = "Model";
    /**
     * name
     */
    public $name = "DataSet";

    protected $daniel;
    protected $kathi;
    protected $patrick;
    protected $janine;
    protected $nik;
    protected $julian;

    public function setUp()
    {
        $this->daniel =  new DumpElementPerson("Daniel", 20, "M");
        $this->kathi = new DumpElementPerson("Kathi", 22, "W");
        $this->patrick = new DumpElementPerson("Patrick", 16, "M");
        $this->janine = new DumpElementPerson("Janine", 19, "W");
        $this->nik = new DumpElementPerson("Nik", 21, "M");
        $this->julian = new DumpElementPerson("Julian", 20, "M");
    }

    public function testCreate() {
        $list = new DataSet();

        $this->assertEqual($list->count(), 0);
        $this->assertEqual($list->DataClass(), null);
        $this->assertEqual($list->first(), null);
    }

    public function testCreateWithElements() {
        $list = new DataSet(array(
            $this->daniel,
            $this->kathi,
            $this->patrick
        ));

        $this->assertEqual($list->count(), 3);
        $this->assertEqual($list->first(), $this->daniel);
        $this->assertEqual($list[1], $this->kathi);
        $this->assertEqual($list[2], $this->patrick);
    }

    public function testRemoveAdd() {
        $list = new DataSet(array(
            $this->daniel,
            $this->kathi,
            $this->patrick
        ));

        $list->add($this->janine);

        $this->assertEqual($list->count(), 4);
        $this->assertEqual($list[3], $this->janine);

        $list->remove($this->kathi);

        $this->assertEqual($list->count(), 3);
        $this->assertEqual($list[2], $this->janine);
        $this->assertEqual($list[3], null);

        $list->add($this->kathi);
        $list->add($this->kathi);

        $this->assertEqual($list->count(), 5);
        $this->assertEqual($list[2], $this->janine);
        $this->assertEqual($list[3], $this->kathi);
        $this->assertEqual($list[4], $this->kathi);
    }

    public function testRemoveDuplicates() {
        $list = new DataSet(array(
            $this->daniel,
            $this->kathi,
            $this->kathi,
            $this->daniel
        ));

        $list->removeDuplicates("name");

        $this->assertEqual($list->count(), 2);
        $this->assertEqual($list[1], $this->kathi);
    }

    public function testSort() {
        $list = new DataSet($orgArray = array(
            $this->daniel,
            $this->kathi,
            $this->janine,
            $this->patrick,
            $this->julian
        ));

        $this->assertEqual($list->ToArray(), $orgArray);

        $sortedList = $list->sort("age", "asc");
        $age = 0;
        foreach($sortedList as $person) {
            $this->assertFalse($age > $person->age);
            $age = $person->age;
        }

        $this->assertNotEqual($list->ToArray(), $orgArray);
        $this->assertEqual($list->count(), $sortedList->count());

        $sortByGenderAndName = $list->sort(array("gender" => "asc", "age" => "asc"));
        $inW = false;
        $age = 0;
        foreach($sortByGenderAndName as $person) {
            if($person->gender == "W") {
                if(!$inW) {
                    $inW = true;
                    $age = 0;
                }
            } else if($inW) {
                $this->assertTrue(false);
            }
            $this->assertFalse($age > $person->age);
            $age = $person->age;
        }
    }

    public function testFilter() {
        $list = new DataSet($orgArray = array(
            $this->daniel,
            $this->kathi,
            $this->janine,
            $this->patrick,
            $this->julian
        ));

        $filteredList = $list->filter(array("name" => array("LIKE", "Janine")));
        $this->assertEqual($filteredList->first(), $this->janine);
        $this->assertEqual($filteredList->count(), 1);

        $this->assertEqual($list->filter(array("name" => "janine"))->count(), 0);

        $this->assertEqual($list->first(), null);
        // reset filter
        $list->filter();
        $this->assertEqual($list->first(), $this->daniel);

        $advancedFilter = $list->filter(array("age" => array(">=", 20)));
        foreach($advancedFilter as $person) {
            $this->assertFalse($person->age < 20);
        }

        $this->assertEqual($list->find("name", "patrick", true), null);

        // reset filter
        $list->filter();
        $this->assertEqual($list->find("name", "patrick", true), $this->patrick);
        $this->assertNull($list->find("name", "patrick"));
    }

    public function testMove() {
        $list = new DataSet($orgArray = array(
            $this->daniel,
            $this->kathi,
            $this->janine,
            $this->patrick,
            $this->julian
        ));

        $list->moveBehind($this->daniel, $this->janine);

        $this->assertEqual($list->first(), $this->kathi);
        $this->assertEqual($list[1], $this->janine);
        $this->assertEqual($list[2], $this->daniel);

        $list->moveBefore($this->daniel, $this->janine);

        $this->assertEqual($list->first(), $this->kathi);
        $this->assertEqual($list[2], $this->janine);
        $this->assertEqual($list[1], $this->daniel);

        $list->remove($this->daniel);

        $this->assertThrows(function() use($list) {
            $list->moveBefore($this->janine, $this->daniel);
        }, "ItemNotFoundException");

        $list->moveBefore($this->daniel, $this->janine, true);
        $this->assertEqual($list[1], $this->daniel);
    }
}
