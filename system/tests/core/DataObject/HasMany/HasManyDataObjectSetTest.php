<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Field-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class HasManyDataObjectSetTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "HasMany";

    /**
     * internal name.
     */
    public $name = "HasManyDataObjectSet";


    /**
     *
     */
    public function testPush() {
        $set = new HasMany_DataObjectSet();

        $set->setData(array());

        $e = new MockWriteEntity();
        $oldE = clone $e;
        $set->push($e);

        $this->assertEqual($e->ToArray(), $oldE->ToArray());

        $set->setRelationENV("test", "blah", 1);
        $newE = clone $oldE;
        $set->push($newE);

        $this->assertNotEqual($e->ToArray(), $oldE->ToArray());
        $this->assertNotEqual($newE->ToArray(), $oldE->toArray());
        $this->assertEqual($newE->blah, 1);
        $this->assertEqual($e->blah, 1);
    }
}