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

    public function testDBGeneration() {
        $this->unittestGeneration("db", array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), "generateDBFields", false, "ModelInfoTestClass", array(
            "test" => "varchar(10)",
            "parent" => "int(10)"
        ));

        $this->unittestGeneration("db", array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), "generateDBFields", true, "ModelInfoTestClass", array(
            "test" => "varchar(10)",
            "parent" => "int(10)"
        ));

        $this->unittestGeneration("db", array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), "generateDBFields", true, "ModelInfoTestChildClass", array(
            "test" => "varchar(10)",
            "blub" => "varchar(20)",
            "parent" => "int(10)"
        ));

        $this->unittestGeneration("db", array(
            "test" => "varchar(10)"
        ), array(
            "blub" => "varchar(20)"
        ), array(
            "parent" => "int(10)"
        ), "generateDBFields", false, "ModelInfoTestChildClass", array(
            "blub" => "varchar(20)"
        ));

        $this->unittestGeneration("db", array(
            "test" => "varchar(10)"
        ), array(
            "test" => "varchar(20)"
        ), array(
            "test" => "varchar(30)"
        ), "generateDBFields", false, "ModelInfoTestChildClass", array(
            "test" => "varchar(20)"
        ));

        $this->unittestGeneration("db", array(
            "test" => "varchar(10)"
        ), array(
            "test" => "varchar(20)"
        ), array(
            "test" => "varchar(30)"
        ), "generateDBFields", false, "ModelInfoTestClass", array(
            "test" => "varchar(30)"
        ));
    }

    public function unittestGeneration($name, $base, $child, $extension, $method, $parents, $useClass, $expected) {
        StaticsManager::setStatic("ModelInfoTestClass", $name, $base);
        StaticsManager::setStatic("ModelInfoTestChildClass", $name, $child);
        StaticsManager::setStatic("ModelInfoTestExtensionClass", $name, $extension);

        $info = call_user_func_array(array("ModelInfoGenerator", $method), array($useClass, $parents));

        $this->assertEqual($info, $expected, "Test $useClass with parents: [$parents] Exptected " . print_r($expected, true) . ". %s");
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

Object::extend("ModelInfoTestClass", "ModelInfoTestExtensionClass");