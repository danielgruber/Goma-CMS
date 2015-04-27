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

            $this->assertEqual($reflection->invoke($relationShips[$relationshipName]), $expected, "Expected $expected %s");
        } else {
            $reflection = new ReflectionMethod("ModelManyManyRelationShipInfo", "getNewTableName");
            $reflection->setAccessible(true);

            $this->assertEqual($reflection->invoke($relationShips[$relationshipName]), $expected, "Expected $expected %s");
        }

    }

    /**
     * checks for inverse valid.
     */
    public function testFindInverseManyManyRelationship() {
        $this->unitFindInverseManyManyRelationship(
            "mains", "ManyManyRelationshipTestBelonging", array("ManyManyRelationshipTest"), true,
            "belongs"
        );

        $this->unitFindInverseManyManyRelationship(
            "belongs", "ManyManyRelationshipTest", array("ManyManyRelationshipTestBelonging"), false,
            "mains"
        );

        try {
            $this->unitFindInverseManyManyRelationship(
                "belongs", "ManyManyRelationshipTest", array("ManyManyRelationshipTestBelonging", "test"), false,
                null
            );
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "LogicException");
        }

        try {
            $this->unitFindInverseManyManyRelationship(
                "mains", "ManyManyRelationshipTestBelonging", array("ManyManyRelationshipTest", "belonging"), true,
                "belongs"
            );
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "LogicException");
        }

        $this->unitFindInverseManyManyRelationship(
            "tests", "ManyManyRelationshipTest", array("ManyManyTestObject"), false,
            null
        );

        $this->unitFindInverseManyManyRelationship(
            "mains", "ManyManyRelationshipTestBelonging", array("ManyManyRelationshipTest", "belongs"), true,
            "belongs"
        );
    }

    protected function unitFindInverseManyManyRelationship($relationName, $class, $info, $belonging, $expected) {
        $data = ModelManyManyRelationShipInfo::findInverseManyManyRelationship($relationName, $class, $info, $belonging);

        $this->assertEqual($data, $expected);
    }

    public function testGenerateFromClass() {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTest");

        /** @var ModelManyManyRelationShipInfo $relationShip */
        foreach($relationShips as $name => $relationShip) {
            if($name == "tests") {
                $this->assertTrue($relationShip->isControlling());
                $this->assertNull($relationShip->getBelongingName(), null);
                $this->assertEqual($relationShip->getTarget(), "manymanytestobject");
                $this->assertEqual($relationShip->getExtraFields(), array());
                $this->assertNotNull($relationShip->getOwnerSortField());

            } else if($name == "belongs") {
                $this->assertEqual($relationShip->getBelongingName(), "mains");
                $this->assertEqual($relationShip->getTarget(), strtolower("ManyManyRelationshipTestBelonging"));
            }

            $this->assertNull($relationShip->getTargetTableName());

            ClassInfo::$class_info[$relationShip->getTarget()]["table"] = $relationShip->getTarget() . "table";

            $this->assertEqual($relationShip->getTargetTableName(), $relationShip->getTarget() . "table");
        }
    }
}

class ManyManyRelationshipTest {
    static $many_many = array(
        "belongs"   => "ManyManyRelationshipTestBelonging",
        "tests"     => "ManyManyTestObject"
    );
}

class ManyManyTestObject {}

class ManyManyRelationshipTestBelonging {
    static $belongs_many_many = array(
        "mains"     => "ManyManyRelationshipTest"
    );
}