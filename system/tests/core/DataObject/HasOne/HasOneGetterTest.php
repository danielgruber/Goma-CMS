<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Field-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class HasOneGetterTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "HasOne";

    /**
     * internal name.
     */
    public $name = "HasOneGetter";

    public function testAssign() {
        $mockDBObject1 = new MockDBObjectHasOne();
        $mockDBObject2 = new MockDBObjectHasOne();

        $this->assertEqual($mockDBObject1->hasone, null);

        $mockDBObject1->hasone = $mockDBObject2;

        $this->assertEqual($mockDBObject1->hasone, $mockDBObject2);
        $this->assertEqual($mockDBObject2->hasone, null);
    }
}

/**
 * Class MockDBObjectHasOne
 *
 * @property MockDBObjectHasOne hasone
 */
class MockDBObjectHasOne extends DataObject {
    static $has_one = array(
        "hasone" => "MockDBObjectHasOne"
    );
}
