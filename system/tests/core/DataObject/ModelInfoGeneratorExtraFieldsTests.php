<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ModelInfoGeneartor-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ModelInfoGeneratorExtraFieldsTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "DataObject";

    /**
     * internal name.
     */
    public $name = "ModelInfoGeneratorExtra";


    public function testManyManyExtraFieldGeneration() {
        $this->assertEqual(ModelInfoGenerator::get_many_many_extraFields("DummyModelForGenerator", "second"), array(
            "mayThird" => "int(10)",
            "mayFourth" => "int(5)"
        ));
        $this->assertEqual(ModelInfoGenerator::get_many_many_extraFields("DummyModelForGenerator", "third"), array(
            "mayFourth" => "int(2)"
        ));
    }
}

class DummyModelForGenerator {
    static $many_many = array(
        "second"    => "SecondDummyModelForGenerator"
    );

    static $many_many_extra_fields = array(
        "second" => array(
            "mayThird" => "int(10)"
        )
    );
}

class SecondDummyModelForGenerator {
    static $belongs_many_many = array(
        "first" => "DummyModelForGenerator"
    );
}

class DummyExtensionForGenerator extends DataObjectExtension {
    static $many_many_extra_fields = array(
        "second" => array(
            "mayFourth" => "int(5)"
        ),
        "third" => array(
            "mayFourth" => "int(2)"
        )
    );
}
gObject::extend("DummyModelForGenerator", "DummyExtensionForGenerator");