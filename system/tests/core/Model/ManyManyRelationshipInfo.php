<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for ManyManyRelationShipInfo-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ManyManyRelationShipInfoTests extends GomaUnitTest
{

    static $area = "Model";
    /**
     * name
     */
    public $name = "ManyManyRelationShipInfo";

    /**
     * test table-name styles.
     */
    public function testTableNameStyle() {
        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", true, true, "many_many_test_testName_target");
        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", true, false, "many_test_testName");

        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", false, true, "many_many_target_targetName_test");
        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", false, false, "many_target_targetName");
    }

    /**
     * @param string $owner
     * @param string $relationshipName
     * @param string $target
     * @param string $targetRelationName
     * @param bool $belonging
     * @param bool $useOld
     * @param string $expected
     */
    public function unitTestTableNameStyle($owner, $relationshipName, $target, $targetRelationName, $belonging, $useOld, $expected) {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClassInfo($owner, array(
            $relationshipName => array(
                "table"         => null,
                "ef"            => array(),
                "target"        => $target,
                "belonging"     => $targetRelationName,
                "isMain"        => $belonging
            )
        ));

        if($useOld) {

            $reflection = new ReflectionMethod("ModelManyManyRelationShipInfo", "getOldTableName");
            $reflection->setAccessible(true);

            $this->assertEqual($reflection->invoke($relationShips[0]), $expected, "Expected $expected %s");
        } else {
            $reflection = new ReflectionMethod("ModelManyManyRelationShipInfo", "getNewTableName");
            $reflection->setAccessible(true);

            $this->assertEqual($reflection->invoke($relationShips[0]), $expected, "Expected $expected %s");
        }

    }
}