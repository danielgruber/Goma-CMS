<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObjectClassInfo-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class DataObjectClassInfoTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "DataObject";

    /**
     * internal name.
     */
    public $name = "DataObjectClassInfo";

    /**
     * tests getManyManyRelationships
     */
    public function testgetManyManyRelationships() {
        $this->unitgetManyManyRelationships(array("test", "blah"), array("blub"), "DataObjectClassInfoTest_ChildMockupclass", array(
            "test", "blah", "blub"
        ));

        $this->unitgetManyManyRelationships(array("test", "blah"), array("blub"), "DataObjectClassInfoTest_BaseMockupclass", array(
            "blub"
        ));

        $this->unitgetManyManyRelationships(array(), array("blub"), "DataObjectClassInfoTest_ChildMockupclass", array(
            "blub"
        ));

        $this->unitgetManyManyRelationships(array(), array("blub"), "DataObjectClassInfoTest_BaseMockupclass", array(
            "blub"
        ));
    }

    protected function unitgetManyManyRelationships($child, $base, $class, $expected) {
        $this->generateClassInfoForClass("DataObjectClassInfoTest_ChildMockupclass", $child);
        $this->generateClassInfoForClass("DataObjectClassInfoTest_BaseMockupclass", $base);

        $reflectionProperty = new ReflectionProperty("DataObjectClassInfo", "relationShips");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, array());

        $relations = DataObjectClassInfo::getManyManyRelationships($class);

        $this->assertEqual(ArrayLib::key_value(array_keys($relations)), ArrayLib::key_value($expected),
            "Expected names on Class $class: ".print_r($expected, true) . " %s");

    }

    protected function generateClassInfoForClass($class, $names) {
        $class = strtolower($class);

        if(!empty($names)) {

            ClassInfo::$class_info[$class]["many_many_relations"] = array();
            $i = 0;
            foreach ($names as $name) {
                ClassInfo::$class_info[$class]["many_many_relations"][$name] = array(
                    "table"         => null,
                    "ef"            => array(),
                    "target"        => "target_" . $name,
                    "belonging"     => "target_relation_" . $name,
                    "isMain"        => ($i % 2 == 0),
                    "bidirectional" => false
                );
                $i++;
            }
        } else {
            unset(ClassInfo::$class_info[$class]["many_many_relations"]);
        }
    }
}

class DataObjectClassInfoTest_BaseMockupclass {

}

class DataObjectClassInfoTest_ChildMockupclass extends DataObjectClassInfoTest_BaseMockupclass {

}