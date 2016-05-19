<?php defined("IN_GOMA") OR die();
/**
 * Tests for DataObjectSet for ManyMany
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ManyManyDataObjectSet extends GomaUnitTest implements TestAble {
    /**
     * area
     */
    static $area = "ManyMany";

    /**
     * internal name.
     */
    public $name = "ManyMAayDataObjectSet";

    /**
     * test filter.
     */
    public function testFilter() {
        $user = new User();
        $relationShip = $user->getManyManyInfo("groups");

        $getRecordIdMethod = new ReflectionMethod("ManyMany_DataObjectSet", "getRecordIdQuery");
        $getRecordIdMethod->setAccessible(true);

        $set = new ManyMany_DataObjectSet();
        $set->setRelationENV($relationShip, $user);

        $this->assertEqual($set->getFilterForQuery(), array(" " . $relationShip->getTargetBaseTableName() . ".recordid IN (".$getRecordIdMethod->invoke($set)->build("distinct recordid").") "));

        $set->setSourceData(array(
            1, 2, 3
        ));

        $this->assertEqual($set->getFilterForQuery(), array($relationShip->getTargetBaseTableName() . ".id IN ('".implode("','", array(1, 2, 3))."') "));

        $filter1 = array("name" => "blub");
        $set->filter($filter1);

        $this->assertEqual($set->getFilterForQuery(), array_merge($filter1,
            array($relationShip->getTargetBaseTableName() . ".id IN ('".implode("','", array(1, 2, 3))."') ")
        ));

        $set->filter("name = 'blub'");
        $this->assertEqual($set->getFilterForQuery(), array("name = 'blub'", $relationShip->getTargetBaseTableName() . ".id IN ('".implode("','", array(1, 2, 3))."') "));
    }
}
