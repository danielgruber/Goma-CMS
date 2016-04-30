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
        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", true, true, "many_many_test_testname_target");
        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", true, false, "many_test_testname");

        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", false, true, "many_many_target_targetname_test");
        $this->unitTestTableNameStyle("test", "testName", "target", "targetName", false, false, "many_target_targetname");

        $this->unitTestTableNameStyle("test", "testName", "test", "testReverse", true, false, "many_test_testname");
        $this->unitTestTableNameStyle("test", "testName", "test", "testReverse", false, false, "many_test_testreverse");
        $this->unitTestTableNameStyle("test", "testName", "test", "testReverse", false, true, "many_many_test_testreverse_test");
        $this->unitTestTableNameStyle("test", "testName", "test", "testReverse", true, true, "many_many_test_testname_test");
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
                "table"             => null,
                "ef"                => array(),
                "target"            => $target,
                "inverse"           => $targetRelationName,
                "isMain"            => $controlling,
                "validatedInverse"  => true
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
            "mains", "ManyManyRelationshipTestBelonging", "ManyManyRelationshipTest", true,
            "belongs"
        );

        $this->unitFindInverseManyManyRelationship(
            "belongs", "ManyManyRelationshipTest", "ManyManyRelationshipTestBelonging", false,
            "mains"
        );

        $that = $this;
        $this->assertThrows(function() use($that) {
            $that->unitFindInverseManyManyRelationship(
                "belongs", "ManyManyRelationshipTest", array("ManyManyRelationshipTestBelonging", "test"), false,
                null
            );
        }, "LogicException");

        $this->assertThrows(function() use($that) {
            $that->unitFindInverseManyManyRelationship(
                "mains", "ManyManyRelationshipTestBelonging", array("ManyManyRelationshipTest", "belonging"), true,
                "belongs"
            );
        }, "LogicException");

        $this->unitFindInverseManyManyRelationship(
            "tests", "ManyManyRelationshipTest", "ManyManyTestObject", false,
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

    public function unitFindInverseManyManyRelationship($relationName, $class, $info, $belonging, $expected) {
        $model = new ModelManyManyRelationShipInfo($class, $relationName, $info, !$belonging);

        $this->assertEqual($model->getInverse(), $expected);
    }

    public function testGenerateFromClass() {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTest");

        /** @var ModelManyManyRelationShipInfo $relationShip */
        foreach($relationShips as $name => $relationShip) {
            if($name == "tests") {
                $this->assertTrue($relationShip->isControlling());
                $this->assertNull($relationShip->getInverse(), null);
                $this->assertEqual($relationShip->getTargetClass(), "manymanytestobject");
                $this->assertEqual($relationShip->getExtraFields(), array());
                $this->assertNotNull($relationShip->getOwnerSortField());

            } else if($name == "belongs") {
                $this->assertEqual($relationShip->getInverse(), "mains");
                $this->assertEqual($relationShip->getTargetClass(), strtolower("ManyManyRelationshipTestBelonging"));
                $this->assertEqual($relationShip->getExtraFields(), ManyManyRelationshipTest::$many_many_extra_fields["belongs"]);
            }

            $this->assertNull($relationShip->getTargetTableName());

            ClassInfo::$class_info[$relationShip->getTargetClass()]["table"] = $relationShip->getTargetClass() . "table";

            $this->assertEqual($relationShip->getTargetTableName(), $relationShip->getTargetClass() . "table");
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
            $this->assertEqual($inverted->getTargetClass(), $relationShip->getOwner());
            $this->assertEqual($inverted->getOwner(), $relationShip->getTargetClass());

            $this->assertEqual($inverted->getTargetField(), $relationShip->getOwnerField());
            $this->assertEqual($inverted->getTargetSortField(), $relationShip->getOwnerSortField());

            $this->assertEqual($inverted->getOwnerField(), $relationShip->getTargetField());
            $this->assertEqual($inverted->getOwnerSortField(), $relationShip->getTargetSortField());

            $this->assertEqual($inverted->getRelationShipName(), $relationShip->getInverse());
            $this->assertEqual($inverted->getInverse(), $relationShip->getRelationShipName());

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
                    "inverse" => "",
                    "isMain" => ""
                )
            ));
        }, "InvalidArgumentException");

        $this->assertThrows(function() {
            ModelManyManyRelationShipInfo::generateFromClassInfo("test", array(
                "test" => array(
                    "table" => "",
                    "ef" => array(),
                    "target" => "",
                    "inverse" => "",
                    "isMain" => ""
                )
            ));
        }, "InvalidArgumentException");

        $this->assertThrows(function(){
            ModelManyManyRelationShipInfo::generateFromClassInfo("", array(
                "" => array(
                    "table"         => "",
                    "ef"            => array(),
                    "target"        => "test",
                    "inverse"     => "",
                    "isMain"        => ""
                )
            ));
        }, "InvalidArgumentException");
    }

    /**
     * tests if all properties are assigned correctly and accessable.
     */
    public function testAssignMent() {
        $this->unittestAssignMent("test", "test_many", array("test" => 1), "blub", "blah", "myrelation", true);
        $this->unittestAssignMent("test", "test_many", array(), "blub", "blah", "myrelation", false);
        $this->unittestAssignMent(randomString(10), randomString(10), array(), randomString(10), randomString(10), randomString(10), false);
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
                "inverse"       => $targetRelationName,
                "isMain"        => $controlling,
                "validatedInverse"  => true
            )
        ));

        $this->assertIsA($relationShips[$relationshipName], "ModelManyManyRelationShipInfo");

        /** @var ModelManyManyRelationShipInfo $relation */
        $relation = $relationShips[$relationshipName];
        $this->assertEqual($relation->getTableName(), $table);

        $this->assertEqual($relation->getExtraFields(), $extraFields);
        $this->assertEqual($relation->getTargetClass(), strtolower($target));
        $this->assertEqual($relation->getRelationShipName(), strtolower($relationshipName));
        $this->assertEqual($relation->getInverse(), strtolower($targetRelationName));
        $this->assertEqual($relation->isControlling(), $controlling);
    }

    /**
     * tests extra-fields.
     */
    public function testExtraFields() {
        $relationShips = ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTest");
        $relationShipsForBelonging = ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTestBelonging");

        $this->assertEqual($relationShips["belongs"]->getExtraFields(), $relationShipsForBelonging["mains"]->getExtraFields());

        ManyManyRelationshipTestBelonging::$belongs_many_many["mains"] = array(
            "test" => "varchar(10)"
        );

        try {
            ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTestBelonging");
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "LogicException");
        }
    }
}

class ManyManyRelationshipTest {
    static $many_many = array(
        "belongs"   => "ManyManyRelationshipTestBelonging",
        "tests"     => "ManyManyTestObject"
    );
    static $many_many_extra_fields = array(
        "belongs" => array(
            "test" => "int(10)"
        )
    );
}

class ManyManyTestObject {}

class ManyManyRelationshipTestBelonging {
    static $belongs_many_many = array(
        "mains"     => "ManyManyRelationshipTest"
    );
    static $many_many_extra_fields = array();
}
