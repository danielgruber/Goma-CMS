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
        $set = new HasMany_DataObjectSet("MockWriteEntity");

        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);

        $e = new MockWriteEntity();
        $oldE = clone $e;
        $set->push($e);

        $this->assertEqual($e->ToArray(), $oldE->ToArray());

        $set->setRelationENV($info = new ModelHasManyRelationShipInfo("myclass", "blah", array(
            "target" => "User",
            "inverse"   => "blub",
            "validatedInverse"  => true
        )), 1);

        $newE = clone $oldE;
        $set->push($newE);

        $this->assertEqual($set->getRelationENV(), array(
            "info" => $info,
            "value" => 1
        ));

        $this->assertNotEqual($e->ToArray(), $oldE->ToArray());
        $this->assertNotEqual($newE->ToArray(), $oldE->toArray());
        $this->assertEqual($set->first()->blubid, 1);
        $this->assertEqual($newE->blubid, 1);
        $this->assertEqual($e->blubid, 1);

        $set->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);

        $this->assertEqual($set->first()->blubid, 1);
    }
}
