<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ModelInfoGeneartor-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ModelInfoGeneratorTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "DataObject";

    /**
     * internal name.
     */
    public $name = "ModelInfoGenerator";


    public function testgenerate_combined_array() {
        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_MockFirstClass", "db"),
            ModelInfo_MockFirstClass::$db
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_MockFirstClass", "db", null, true),
            ModelInfo_MockFirstClass::$db
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_MockFirstClass", "db", "extendDB", true),
            ModelInfo_MockFirstClass::$db
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedMockClass", "db"),
            array()
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedMockClass", "db", "extendDB", true),
            ModelInfo_MockFirstClass::$db
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedMockClass", "db", null, true),
            ModelInfo_MockFirstClass::$db
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedMockClassWithProp", "db", null, false),
            ModelInfo_extendedMockClassWithProp::$db
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedMockClassWithProp", "db", null, true),
            array_merge(ModelInfo_extendedMockClassWithProp::$db, ModelInfo_MockFirstClass::$db)
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedInterceptMockClassWithProp", "db", null, true),
            ArrayLib::map_key("strtolower", ModelInfo_extendedInterceptMockClassWithProp::$db)
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_extendedInterceptMockClassWithProp", "db", null, false),
            ArrayLib::map_key("strtolower", ModelInfo_extendedInterceptMockClassWithProp::$db)
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_doubleExtendedClass", "db", null, false),
            array()
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_doubleExtendedClass", "db", null, true),
            ArrayLib::map_key("strtolower", ModelInfo_extendedInterceptMockClassWithProp::$db)
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_doubleExtendedClassWithProp", "db", null, false),
            ArrayLib::map_key("strtolower", ModelInfo_doubleExtendedClassWithProp::$db)
        );

        $this->assertEqual(
            ModelInfoGenerator::generate_combined_array("ModelInfo_doubleExtendedClassWithProp", "db", null, true),
            ArrayLib::map_key("strtolower",
                array_merge(ModelInfo_extendedInterceptMockClassWithProp::$db, ModelInfo_doubleExtendedClassWithProp::$db))
        );
    }
}

class ModelInfo_MockFirstClass {
    static $db = array(
        "test" => "int(10)"
    );
}

class ModelInfo_extendedMockClass extends ModelInfo_MockFirstClass {

}

class ModelInfo_extendedMockClassWithProp extends ModelInfo_MockFirstClass {
    static $db = array(
        "blah" => "int(10)"
    );
}

class ModelInfo_extendedInterceptMockClassWithProp extends ModelInfo_MockFirstClass {
    static $db = array(
        "TEST" => "int(20)"
    );
}

class ModelInfo_doubleExtendedClass extends ModelInfo_extendedInterceptMockClassWithProp {
}

class ModelInfo_doubleExtendedClassWithProp extends ModelInfo_extendedInterceptMockClassWithProp {
    static $db = array(
        "blah" => "int(10)"
    );
}