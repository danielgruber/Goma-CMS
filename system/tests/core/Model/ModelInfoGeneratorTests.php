<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for ManyManyRelationShipInfo-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ModelInfoGeneratorTests extends GomaUnitTest
{

    static $area = "Model";
    /**
     * name
     */
    public $name = "ModelInfoGenerator";

    public function testManyManyException() {

        try {
            ModelInfoTestClass::$many_many = array(
                "test" => array("test")
            );

            ModelInfoGenerator::generateMany_many("ModelInfoTestClass");
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "LogicException");
        }
        try {
            ModelInfoTestClass::$belongs_many_many = array(
                "test"  => array("test")
            );

            ModelInfoGenerator::generateBelongs_many_many("ModelInfoTestClass");

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "LogicException");
        }

    }

    public function unitTestFieldGenerator($field, $method) {
        $this->unittestGeneration($field, array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), $method, false, "ModelInfoTestClass", array(
            "test" => "varchar(10)",
            "parent" => "int(10)"
        ));

        $this->unittestGeneration($field, array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), $method, true, "ModelInfoTestClass", array(
            "test" => "varchar(10)",
            "parent" => "int(10)"
        ));

        $this->unittestGeneration($field, array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), $method, true, "ModelInfoTestChildClass", array(
            "test" => "varchar(10)",
            "blub" => "varchar(20)",
            "parent" => "int(10)"
        ));

        $this->unittestGeneration($field, array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), $method, false, "ModelInfoTestChildClass", array(
            "blub" => "varchar(20)"
        ));

        $this->unittestGeneration($field, array(
            "test" => "varchar(10)"
        ), array(
            "test" => "varchar(20)"
        ), array(
            "test" => "varchar(30)"
        ), $method, false, "ModelInfoTestChildClass", array(
            "test" => "varchar(20)"
        ));

        $this->unittestGeneration($field, array(
            "test" => "varchar(10)"
        ), array(
            "test" => "varchar(20)"
        ), array(
            "test" => "varchar(30)"
        ), $method, false, "ModelInfoTestClass", array(
            "test" => "varchar(30)"
        ));
    }

    public function testFieldGeneration() {
        $this->unitTestFieldGenerator("db", "generateDBFields");
        $this->unitTestFieldGenerator("many_many", "generateMany_many");
        $this->unitTestFieldGenerator("belongs_many_many", "generateBelongs_Many_Many");
        $this->unitTestFieldGenerator("has_one", "generateHas_One");
        $this->unitTestFieldGenerator("has_many", "generateHas_Many");
        $this->unitTestFieldGenerator("search_fields", "generate_search_fields");
        $this->unitTestFieldGenerator("default", "generateDefaults");

    }

    public function testAutorIdEditorId() {

        ModelInfoTestDOTestClass::$has_one = array(
            "test" => "ModelInfoTestClass"
        );

        $localInfo = call_user_func_array(array("ModelInfoGenerator", "generateHas_One"), array("ModelInfoTestDoTestClass", false));

        $this->assertEqual($localInfo, array("test" => "ModelInfoTestClass", "autor" => "user", "editor" => "user"));

        $parentInfo = call_user_func_array(array("ModelInfoGenerator", "generateHas_One"), array("ModelInfoTestDoTestClass", true));

        $this->assertEqual($parentInfo, array("test" => "ModelInfoTestClass", "autor" => "user", "editor" => "user"));
    }

    public function unittestGeneration($name, $base, $child, $extension, $method, $parents, $useClass, $expected) {
        StaticsManager::setStatic("ModelInfoTestClass", $name, $base);
        StaticsManager::setStatic("ModelInfoTestChildClass", $name, $child);
        StaticsManager::setStatic("ModelInfoTestExtensionClass", $name, $extension);

        $info = call_user_func_array(array("ModelInfoGenerator", $method), array($useClass, $parents));

        $this->assertEqual($info, $expected, "Test $useClass with $method with parents: [$parents] Exptected " . print_r($expected, true) . ". %s");
    }
}

class ModelInfoTestClass {
    public static $default = array();
    public static $many_many = array();
    public static $belongs_many_many = array();
    public static $db = array();
    public static $has_one = array();
    public static $has_many = array();
    public static $casting = array();
    public static $index = array();
    public static $search_fields = array();
}

class ModelInfoTestChildClass extends ModelInfoTestClass {
    public static $default = array();
    public static $many_many = array();
    public static $belongs_many_many = array();
    public static $db = array();
    public static $has_one = array();
    public static $has_many = array();
    public static $casting = array();
    public static $index = array();
    public static $search_fields = array();
}

class ModelInfoTestExtensionClass extends DataObjectExtension {
    public static $default = array();
    public static $many_many = array();
    public static $belongs_many_many = array();
    public static $db = array();
    public static $has_one = array();
    public static $has_many = array();
    public static $casting = array();
    public static $index = array();
    public static $search_fields = array();
}

class ModelInfoTestDOTestClass extends DataObject {
    public static $has_one = array();
}

gObject::extend("ModelInfoTestClass", "ModelInfoTestExtensionClass");