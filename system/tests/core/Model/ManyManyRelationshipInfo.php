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
     * @param bool $controlling
     * @param bool $useOld
     * @param string $expected
     */
    public function unitTestTableNameStyle($owner, $relationshipName, $target, $targetRelationName, $controlling, $useOld, $expected) {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClassInfo($owner, array(
            $relationshipName => array(
                "table"         => null,
                "ef"            => array(),
                "target"        => $target,
                "belonging"     => $targetRelationName,
                "isMain"        => $controlling
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

        $this->assertThrows(function() {
            $this->unitFindInverseManyManyRelationship(
                "belongs", "ManyManyRelationshipTest", array("ManyManyRelationshipTestBelonging", "test"), false,
                null
            );
        }, "LogicException");

        $this->assertThrows(function() {
            $this->unitFindInverseManyManyRelationship(
                "mains", "ManyManyRelationshipTestBelonging", array("ManyManyRelationshipTest", "belonging"), true,
                "belongs"
            );
        }, "LogicException");

        $this->unitFindInverseManyManyRelationship(
            "tests", "ManyManyRelationshipTest", array("ManyManyTestObject"), false,
            null
        );

        $this->unitFindInverseManyManyRelationship(
            "mains", "ManyManyRelationshipTestBelonging", array("ManyManyRelationshipTest", "belongs"), true,
            "belongs"
        );

        $this->unitFindInverseManyManyRelationship(
            " mains ", " ManyManyRelationshipTestBelonging ", array(" ManyManyRelationshipTest ", " belongs "), true,
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

    /**
     * tests inverted.
     */
    public function testInverted() {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTest");

        /** @var ModelManyManyRelationShipInfo $relationShip */
        foreach($relationShips as $name => $relationShip) {
            $inverted = $relationShip->getInverted();

            $this->assertEqual($inverted->isControlling(), !$relationShip->isControlling());
            $this->assertEqual($inverted->getTarget(), $relationShip->getOwner());
            $this->assertEqual($inverted->getOwner(), $relationShip->getTarget());

            $this->assertEqual($inverted->getTargetField(), $relationShip->getOwnerField());
            $this->assertEqual($inverted->getTargetSortField(), $relationShip->getOwnerSortField());

            $this->assertEqual($inverted->getOwnerField(), $relationShip->getTargetField());
            $this->assertEqual($inverted->getOwnerSortField(), $relationShip->getTargetSortField());

            $this->assertEqual($inverted->getRelationShipName(), $relationShip->getBelongingName());
            $this->assertEqual($inverted->getBelongingName(), $relationShip->getRelationShipName());

            $this->assertNotIdentical($inverted, $relationShip);
        }
    }

    /**
     * tests if exception is correctly thrown.
     */
    public function testExceptionOnClassInfoGeneration() {
        $this->assertThrows(function() {
            ModelManyManyRelationShipInfo::generateFromClassInfo("test", array(
                "" => array(
                    "table" => "",
                    "ef" => array(),
                    "target" => "test",
                    "belonging" => "",
                    "isMain" => ""
                )
            ));
        }, "LogicException");

        $this->assertThrows(function() {
            ModelManyManyRelationShipInfo::generateFromClassInfo("test", array(
                "test" => array(
                    "table" => "",
                    "ef" => array(),
                    "target" => "",
                    "belonging" => "",
                    "isMain" => ""
                )
            ));
        }, "LogicException");

        $this->assertThrows(function(){
            ModelManyManyRelationShipInfo::generateFromClassInfo("", array(
                "" => array(
                    "table"         => "",
                    "ef"            => array(),
                    "target"        => "test",
                    "belonging"     => "",
                    "isMain"        => ""
                )
            ));
        }, "LogicException");
    }

    /**
     * tests if all properties are assigned correctly and accessable.
     */
    public function testAssignMent() {
        $this->unittestAssignMent("test", "test_many", array("test" => 1), "blub", "blah", "myrelation", true);
        $this->unittestAssignMent("test", "test_many", array(), "blub", "blah", "myrelation", false);
        $this->unittestAssignMent(randomString(10), randomString(10), null, randomString(10), randomString(10), randomString(10), false);
    }

    /**
     * test if everything is assigned correctly.
     * @param string $owner
     * @param string $table
     * @param array $extraFields
     * @param string $target
     * @param string $targetRelationName
     * @param string $relationshipName
     * @param bool $controlling
     */
    public function unittestAssignMent($owner, $table, $extraFields, $target, $targetRelationName, $relationshipName, $controlling) {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClassInfo($owner, array(
            $relationshipName => array(
                "table"         => $table,
                "ef"            => $extraFields,
                "target"        => $target,
                "belonging"     => $targetRelationName,
                "isMain"        => $controlling
            )
        ));

        $this->assertIsA($relationShips[$relationshipName], "ModelManyManyRelationShipInfo");

        /** @var ModelManyManyRelationShipInfo $relation */
        $relation = $relationShips[$relationshipName];
        $this->assertEqual($relation->getTableName(), $table);

        if($extraFields == null) {
            $this->assertEqual($relation->getExtraFields(), array());
        }
        $this->assertEqual($relation->getTarget(), $target);
        $this->assertEqual($relation->getRelationShipName(), $relationshipName);
        $this->assertEqual($relation->getBelongingName(), $targetRelationName);
        $this->assertEqual($relation->isControlling(), $controlling);
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